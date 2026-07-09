<?php

namespace App\Http\Controllers;

use App\Models\DriverSignature;
use App\Models\SignatureOtp;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class SignatureController extends Controller
{
    public function create(): View
    {
        return view('signatures.create');
    }

    public function sign(): View
    {
        return view('signatures.sign');
    }

    public function reportLogin(): View|RedirectResponse
    {
        if (session('signatures_report_unlocked') === true) {
            return redirect()->route('signatures.report');
        }

        return view('signatures.report-login');
    }

    public function reportAuthenticate(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'password' => ['required', 'string'],
        ]);

        if (! hash_equals((string) config('app.report_password'), $validated['password'])) {
            return back()
                ->withErrors(['password' => 'The report password is incorrect.'])
                ->onlyInput();
        }

        $request->session()->put('signatures_report_unlocked', true);

        return redirect()->route('signatures.report');
    }

    public function reportLogout(Request $request): RedirectResponse
    {
        $request->session()->forget('signatures_report_unlocked');

        return redirect()->route('signatures.report.login');
    }

    public function sendOtp(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ]);

        $validated['email'] = mb_strtolower($validated['email']);

        if ($this->emailAlreadySigned($validated['email'])) {
            return response()->json([
                'message' => 'This email has already submitted a signature. Each email can sign only once.',
            ], 422);
        }

        $key = 'signature-otp:'.$request->ip().':'.$validated['email'];

        if (RateLimiter::tooManyAttempts($key, 5)) {
            return response()->json([
                'message' => 'Too many OTP requests. Please wait before trying again.',
            ], 429);
        }

        RateLimiter::hit($key, 300);

        $otp = (string) random_int(100000, 999999);
        $expiresAt = now()->addMinutes(10);
        $codeHash = hash('sha256', $otp);

        $signatureOtp = SignatureOtp::create([
            'email' => $validated['email'],
            'code' => $otp,
            'code_hash' => $codeHash,
            'ip_address' => $request->ip(),
            'expires_at' => $expiresAt,
        ]);

        $request->session()->put('signature_otp', [
            'id' => $signatureOtp->id,
            'email' => $validated['email'],
            'code_hash' => $codeHash,
            'expires_at' => $expiresAt->timestamp,
            'verified' => false,
        ]);

        Mail::raw("Your signing OTP is {$otp}. It expires in 10 minutes.", function ($message) use ($validated) {
            $message->to($validated['email'])
                ->subject('Your signing OTP');
        });

        return response()->json([
            'message' => 'OTP sent to the email address.',
        ]);
    }

    public function verifyOtp(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'otp' => ['required', 'digits:6'],
        ]);

        $otp = $request->session()->get('signature_otp');
        $signatureOtp = $otp['id'] ?? null
            ? SignatureOtp::find($otp['id'])
            : null;

        if (! $otp || ! $signatureOtp || $otp['email'] !== $validated['email'] || $otp['expires_at'] < now()->timestamp) {
            return response()->json([
                'message' => 'The OTP has expired. Please request a new code.',
            ], 422);
        }

        if (! hash_equals($signatureOtp->code_hash, hash('sha256', $validated['otp']))) {
            return response()->json([
                'message' => 'The OTP is incorrect.',
            ], 422);
        }

        $signatureOtp->update([
            'verified_at' => now(),
        ]);

        $otp['verified'] = true;
        $request->session()->put('signature_otp', $otp);

        return response()->json([
            'message' => 'Email verified. You can now sign.',
        ]);
    }

    public function report(): View
    {
        $this->ensureReportAccess();

        $signatures = DriverSignature::query()
            ->latest('signed_at')
            ->get();

        return view('signatures.report', [
            'signatures' => $signatures,
            'reportRows' => $signatures->map(fn (DriverSignature $signature) => [
                'id' => $signature->id,
                'name' => $signature->name,
                'email' => $signature->email,
                'driverNumber' => $signature->driver_number,
                'driverRunNumber' => $signature->driver_run_number,
                'signedAt' => optional($signature->signed_at)->format('Y-m-d H:i'),
                'signatureUrl' => route('signatures.file', ['path' => $signature->signature_path]),
                'signedPdfPath' => $signature->signed_pdf_path,
            ])->values(),
        ]);
    }

    public function file(string $path): Response
    {
        $this->ensureReportAccess();

        abort_unless(
            str_starts_with($path, 'driver-signatures/') || str_starts_with($path, 'signed-pdfs/'),
            404
        );

        $signature = DriverSignature::query()
            ->where('signature_path', $path)
            ->orWhere('signed_pdf_path', $path)
            ->first();

        if ($signature && $signature->signature_path === $path && $signature->signature_blob !== null) {
            return response($signature->signature_blob, 200, [
                'Content-Type' => 'image/png',
            ]);
        }

        if ($signature && $signature->signed_pdf_path === $path && $signature->signed_pdf_blob !== null) {
            return response($signature->signed_pdf_blob, 200, [
                'Content-Type' => 'application/pdf',
            ]);
        }

        $fullPath = storage_path("app/{$path}");
        abort_unless(File::exists($fullPath), 404);

        return response(File::get($fullPath), 200, [
            'Content-Type' => File::mimeType($fullPath) ?: 'application/octet-stream',
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'driver_number' => ['required', 'string', 'max:255'],
            'driver_run_number' => ['required', 'string', 'max:255'],
            'signature_data_url' => ['required', 'string'],
            'signed_pdf_base64' => ['required', 'string'],
        ]);

        $validated['email'] = mb_strtolower($validated['email']);

        $this->ensureEmailOtpVerified($request, $validated['email']);

        if ($this->emailAlreadySigned($validated['email'])) {
            return response()->json([
                'message' => 'This email has already submitted a signature. Each email can sign only once.',
            ], 422);
        }

        $signatureBytes = $this->decodeDataUrl($validated['signature_data_url'], 'image/png');
        $signedPdfBytes = base64_decode($validated['signed_pdf_base64'], true);

        if ($signedPdfBytes === false) {
            abort(422, 'The signed PDF is invalid.');
        }

        $id = (string) Str::uuid();
        $signaturePath = "driver-signatures/{$id}.png";
        $signedPdfPath = "signed-pdfs/{$id}.pdf";

        $this->writeStorageFile($signaturePath, $signatureBytes);
        $this->writeStorageFile($signedPdfPath, $signedPdfBytes);

        try {
            $signature = DriverSignature::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'driver_number' => $validated['driver_number'],
                'driver_run_number' => $validated['driver_run_number'],
                'source_pdf' => 'ilovepdf-merged.pdf',
                'signature_path' => $signaturePath,
                'signed_pdf_path' => $signedPdfPath,
                'signature_blob' => $signatureBytes,
                'signed_pdf_blob' => $signedPdfBytes,
                'signed_at' => now(),
            ]);
        } catch (\Illuminate\Database\UniqueConstraintViolationException) {
            return response()->json([
                'message' => 'This email has already submitted a signature. Each email can sign only once.',
            ], 422);
        }

        return response()->json([
            'id' => $signature->id,
            'message' => 'Driver signature saved.',
        ]);
    }

    private function decodeDataUrl(string $dataUrl, string $expectedMime): string
    {
        if (! str_starts_with($dataUrl, "data:{$expectedMime};base64,")) {
            abort(422, 'The signature image is invalid.');
        }

        $base64 = substr($dataUrl, strpos($dataUrl, ',') + 1);
        $bytes = base64_decode($base64, true);

        if ($bytes === false) {
            abort(422, 'The signature image is invalid.');
        }

        return $bytes;
    }

    private function writeStorageFile(string $path, string $contents): void
    {
        $fullPath = storage_path("app/{$path}");

        try {
            File::ensureDirectoryExists(dirname($fullPath));
            File::put($fullPath, $contents);
        } catch (\Throwable) {
            // Vercel's filesystem is not persistent. The database blob remains the source of truth.
        }
    }

    private function ensureEmailOtpVerified(Request $request, string $email): void
    {
        $otp = $request->session()->get('signature_otp');

        if (! $otp || $otp['email'] !== $email || $otp['verified'] !== true || $otp['expires_at'] < now()->timestamp) {
            abort(422, 'Please verify the email OTP before signing.');
        }
    }

    private function emailAlreadySigned(string $email): bool
    {
        return DriverSignature::query()
            ->where('email', mb_strtolower($email))
            ->exists();
    }

    private function ensureReportAccess(): void
    {
        if (session('signatures_report_unlocked') !== true) {
            abort(redirect()->route('signatures.report.login'));
        }
    }
}
