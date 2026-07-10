<?php

use App\Http\Controllers\SignatureController;
use Illuminate\Support\Facades\Route;

Route::get('/', [SignatureController::class, 'create'])->name('signatures.create');
Route::get('/sign', [SignatureController::class, 'sign'])->name('signatures.sign');
Route::get('/sign/thanks', [SignatureController::class, 'thanks'])->name('signatures.thanks');
Route::post('/sign/otp/send', [SignatureController::class, 'sendOtp'])->name('signatures.otp.send');
Route::post('/sign/otp/verify', [SignatureController::class, 'verifyOtp'])->name('signatures.otp.verify');
Route::post('/signatures', [SignatureController::class, 'store'])->name('signatures.store');
Route::get('/signatures/report/login', [SignatureController::class, 'reportLogin'])->name('signatures.report.login');
Route::post('/signatures/report/login', [SignatureController::class, 'reportAuthenticate'])->name('signatures.report.authenticate');
Route::post('/signatures/report/logout', [SignatureController::class, 'reportLogout'])->name('signatures.report.logout');
Route::get('/signatures/report', [SignatureController::class, 'report'])->name('signatures.report');
Route::delete('/signatures/report/{signature}', [SignatureController::class, 'destroy'])->name('signatures.destroy');
Route::get('/signatures/files/{path}', [SignatureController::class, 'file'])
    ->where('path', '.*')
    ->name('signatures.file');
