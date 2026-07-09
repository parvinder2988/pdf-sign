<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>Sign Now - {{ config('app.name', 'PDF Multi Sign') }}</title>
        <script src="https://unpkg.com/pdf-lib@1.17.1/dist/pdf-lib.min.js"></script>
        <script src="https://unpkg.com/lucide@0.468.0/dist/umd/lucide.min.js"></script>
        <style>
            :root {
                --ink: #1b2528;
                --muted: #627174;
                --line: #d8e0df;
                --soft: #eef4f2;
                --accent: #0e7c66;
                --accent-dark: #075c4b;
                --paper: #ffffff;
                --danger: #9f2e1f;
            }

            * {
                box-sizing: border-box;
            }

            body {
                margin: 0;
                min-height: 100vh;
                background: #f4f7f6;
                color: var(--ink);
                font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            }

            button,
            input {
                font: inherit;
            }

            .page {
                min-height: 100vh;
                display: grid;
                place-items: center;
                padding: 24px;
            }

            .shell {
                width: min(760px, 100%);
                background: var(--paper);
                border: 1px solid var(--line);
                border-radius: 8px;
                box-shadow: 0 18px 48px rgba(29, 47, 49, 0.14);
                overflow: hidden;
            }

            .header,
            .content,
            .actions {
                padding: 22px;
            }

            .header {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 16px;
                background: #fbfdfc;
                border-bottom: 1px solid var(--line);
            }

            h1,
            p {
                margin: 0;
            }

            h1 {
                font-size: 1.35rem;
            }

            p,
            .status {
                color: var(--muted);
                line-height: 1.45;
            }

            .header-links {
                display: flex;
                gap: 12px;
                flex-wrap: wrap;
            }

            .back-link {
                display: inline-flex;
                align-items: center;
                gap: 8px;
                color: var(--accent-dark);
                font-weight: 750;
                text-decoration: none;
            }

            .content {
                display: grid;
                gap: 16px;
            }

            .field {
                display: grid;
                gap: 7px;
            }

            label {
                font-weight: 760;
            }

            input {
                width: 100%;
                min-height: 44px;
                padding: 0 12px;
                border: 1px solid var(--line);
                border-radius: 7px;
                outline: none;
            }

            input:focus {
                border-color: var(--accent);
                box-shadow: 0 0 0 3px rgba(14, 124, 102, 0.12);
            }

            .otp-grid {
                display: grid;
                grid-template-columns: 1fr auto;
                gap: 10px;
            }

            .otp-grid input {
                min-width: 0;
            }

            .otp-grid button {
                min-height: 44px;
                border: 1px solid var(--line);
                background: white;
                color: var(--ink);
                padding: 0 12px;
            }

            .verified-note {
                display: none;
                color: var(--accent-dark);
                font-weight: 760;
            }

            .verified-note.visible {
                display: block;
            }

            #signaturePad {
                width: 100%;
                height: 220px;
                display: block;
                border: 1px solid var(--line);
                border-radius: 8px;
                background: white;
                touch-action: none;
            }

            .actions {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 12px;
                background: #fbfdfc;
                border-top: 1px solid var(--line);
            }

            button {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                gap: 8px;
                min-height: 46px;
                border-radius: 7px;
                font-weight: 800;
                cursor: pointer;
            }

            .primary {
                border: 0;
                background: var(--accent);
                color: white;
            }

            .primary:hover {
                background: var(--accent-dark);
            }

            button:disabled {
                cursor: not-allowed;
                opacity: 0.58;
            }

            .secondary {
                border: 1px solid var(--line);
                background: white;
                color: var(--ink);
            }

            .secondary:hover {
                background: var(--soft);
            }

            .status.error {
                color: var(--danger);
                font-weight: 700;
            }

            @media (max-width: 640px) {
                .header,
                .actions {
                    grid-template-columns: 1fr;
                }

                .header {
                    align-items: flex-start;
                    flex-direction: column;
                }

                .header-links {
                    width: 100%;
                }

                .actions {
                    display: grid;
                }

                .otp-grid {
                    grid-template-columns: 1fr;
                }

            }
        </style>
    </head>
    <body>
        <main class="page">
            <section class="shell">
                <header class="header">
                    <div>
                        <h1>Driver signature</h1>
                        <p>Enter the driver details and sign below.</p>
                    </div>
                    <div class="header-links">
                        <a class="back-link" href="{{ route('signatures.create') }}">
                            <i data-lucide="arrow-left"></i>
                            PDF page
                        </a>
                    </div>
                </header>

                <div class="content">
                    <div class="field">
                        <label for="driverName">Name</label>
                        <input id="driverName" type="text" autocomplete="name" placeholder="Enter driver name" required>
                    </div>

                    <div class="field">
                        <label for="email">Email</label>
                        <div class="otp-grid">
                            <input id="email" type="email" autocomplete="email" placeholder="Enter email address" required>
                            <button id="sendOtp" type="button">
                                <i data-lucide="mail"></i>
                                Send OTP
                            </button>
                        </div>
                        <div class="otp-grid">
                            <input id="otp" type="text" inputmode="numeric" maxlength="6" placeholder="Enter 6 digit OTP">
                            <button id="verifyOtp" type="button">
                                <i data-lucide="badge-check"></i>
                                Verify
                            </button>
                        </div>
                        <p id="otpVerified" class="verified-note">Email verified.</p>
                    </div>

                    <div class="field">
                        <label for="driverNumber">Driver number</label>
                        <input id="driverNumber" type="text" inputmode="text" placeholder="Enter driver number" required>
                    </div>

                    <div class="field">
                        <label for="runNumber">Driver run number</label>
                        <input id="runNumber" type="text" inputmode="text" placeholder="Enter run number" required>
                    </div>

                    <div class="field">
                        <label for="signaturePad">Signature</label>
                        <canvas id="signaturePad" width="720" height="260"></canvas>
                    </div>

                    <p id="status" class="status">Draw the signature, then submit your signed confirmation.</p>
                </div>

                <footer class="actions">
                    <button id="clearPad" class="secondary" type="button">
                        <i data-lucide="eraser"></i>
                        Clear
                    </button>
                    <button id="exportPdf" class="primary" type="button">
                        <i data-lucide="check-circle"></i>
                        Submit signature
                    </button>
                </footer>
            </section>
        </main>

        <script>
            const driverName = document.querySelector('#driverName');
            const email = document.querySelector('#email');
            const otp = document.querySelector('#otp');
            const sendOtp = document.querySelector('#sendOtp');
            const verifyOtp = document.querySelector('#verifyOtp');
            const otpVerified = document.querySelector('#otpVerified');
            const driverNumber = document.querySelector('#driverNumber');
            const runNumber = document.querySelector('#runNumber');
            const signaturePad = document.querySelector('#signaturePad');
            const clearPad = document.querySelector('#clearPad');
            const exportPdf = document.querySelector('#exportPdf');
            const statusText = document.querySelector('#status');
            const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
            const ctx = signaturePad.getContext('2d');

            let isDrawing = false;
            let hasInk = false;
            let isEmailVerified = false;
            const pdfDisplayName = 'ilovepdf-merged.pdf';

            function setEmailVerified(value) {
                isEmailVerified = value;
                otpVerified.classList.toggle('visible', value);
                exportPdf.disabled = !value;
                signaturePad.style.opacity = value ? '1' : '0.48';
                signaturePad.style.pointerEvents = value ? 'auto' : 'none';
            }

            function resizePad() {
                const rect = signaturePad.getBoundingClientRect();
                const scale = window.devicePixelRatio || 1;
                signaturePad.width = Math.max(1, Math.floor(rect.width * scale));
                signaturePad.height = Math.max(1, Math.floor(rect.height * scale));
                ctx.setTransform(scale, 0, 0, scale, 0, 0);
                clearSignature();
            }

            function clearSignature() {
                const rect = signaturePad.getBoundingClientRect();
                ctx.clearRect(0, 0, rect.width, rect.height);
                ctx.fillStyle = '#ffffff';
                ctx.fillRect(0, 0, rect.width, rect.height);
                ctx.strokeStyle = '#182326';
                ctx.lineWidth = 2.7;
                ctx.lineCap = 'round';
                ctx.lineJoin = 'round';
                hasInk = false;
                statusText.classList.remove('error');
                statusText.textContent = 'Draw the signature, then submit your signed confirmation.';
            }

            function position(event) {
                const rect = signaturePad.getBoundingClientRect();
                return {
                    x: event.clientX - rect.left,
                    y: event.clientY - rect.top,
                };
            }

            function startDrawing(event) {
                isDrawing = true;
                hasInk = true;
                signaturePad.setPointerCapture(event.pointerId);
                const point = position(event);
                ctx.beginPath();
                ctx.moveTo(point.x, point.y);
            }

            function draw(event) {
                if (!isDrawing) {
                    return;
                }

                const point = position(event);
                ctx.lineTo(point.x, point.y);
                ctx.stroke();
            }

            function stopDrawing(event) {
                if (!isDrawing) {
                    return;
                }

                isDrawing = false;
                signaturePad.releasePointerCapture(event.pointerId);
            }

            async function exportSignedPdf() {
                statusText.classList.remove('error');

                if (!driverName.value.trim()) {
                    showError('Please enter the driver name.');
                    driverName.focus();
                    return;
                }

                if (!email.value.trim()) {
                    showError('Please enter the email address.');
                    email.focus();
                    return;
                }

                if (!email.checkValidity()) {
                    showError('Please enter a valid email address.');
                    email.focus();
                    return;
                }

                if (!isEmailVerified) {
                    showError('Please verify the email OTP before signing.');
                    otp.focus();
                    return;
                }

                if (!driverNumber.value.trim()) {
                    showError('Please enter the driver number.');
                    driverNumber.focus();
                    return;
                }

                if (!runNumber.value.trim()) {
                    showError('Please enter the driver run number.');
                    runNumber.focus();
                    return;
                }

                if (!hasInk) {
                    showError('Please draw the signature before submitting.');
                    return;
                }

                const { PDFDocument, StandardFonts, rgb } = PDFLib;
                const output = await PDFDocument.create();
                const page = output.addPage([595.28, 841.89]);
                const regular = await output.embedFont(StandardFonts.Helvetica);
                const bold = await output.embedFont(StandardFonts.HelveticaBold);
                const signatureImage = await output.embedPng(signaturePad.toDataURL('image/png'));

                page.drawText('Driver signature', {
                    x: 48,
                    y: 790,
                    size: 22,
                    font: bold,
                    color: rgb(0.09, 0.15, 0.16),
                });

                page.drawText(`PDF: ${pdfDisplayName}`, {
                    x: 48,
                    y: 760,
                    size: 10,
                    font: regular,
                    color: rgb(0.35, 0.43, 0.44),
                });

                drawField(page, bold, regular, rgb, 'Name', driverName.value.trim(), 48, 710);
                drawField(page, bold, regular, rgb, 'Email', email.value.trim(), 48, 650);
                drawField(page, bold, regular, rgb, 'Driver number', driverNumber.value.trim(), 48, 590);
                drawField(page, bold, regular, rgb, 'Driver run number', runNumber.value.trim(), 48, 530);

                page.drawText('Signature', {
                    x: 48,
                    y: 468,
                    size: 11,
                    font: bold,
                    color: rgb(0.09, 0.15, 0.16),
                });

                page.drawRectangle({
                    x: 48,
                    y: 288,
                    width: 499,
                    height: 160,
                    borderColor: rgb(0.82, 0.87, 0.86),
                    borderWidth: 1,
                    color: rgb(0.98, 0.99, 0.99),
                });

                const dimensions = signatureImage.scale(Math.min(380 / signatureImage.width, 110 / signatureImage.height));
                page.drawImage(signatureImage, {
                    x: 82,
                    y: 316,
                    width: dimensions.width,
                    height: dimensions.height,
                });

                page.drawText(`Signed: ${new Date().toLocaleString()}`, {
                    x: 48,
                    y: 250,
                    size: 10,
                    font: regular,
                    color: rgb(0.35, 0.43, 0.44),
                });

                const bytes = await output.save();
                const pdfBase64 = bytesToBase64(bytes);

                exportPdf.disabled = true;
                exportPdf.innerHTML = '<i data-lucide="loader"></i> Saving...';
                lucide.createIcons();

                try {
                    const response = await fetch('/signatures', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'ngrok-skip-browser-warning': 'true',
                        },
                        body: JSON.stringify({
                            name: driverName.value.trim(),
                            email: email.value.trim(),
                            driver_number: driverNumber.value.trim(),
                            driver_run_number: runNumber.value.trim(),
                            signature_data_url: signaturePad.toDataURL('image/png'),
                            signed_pdf_base64: pdfBase64,
                        }),
                    });

                    const result = await response.json();

                    if (!response.ok) {
                        throw new Error(result.message || 'Unable to save the signature.');
                    }

                    statusText.textContent = 'Thank you. Your signature has been saved successfully.';
                    exportPdf.innerHTML = '<i data-lucide="check-circle"></i> Signature saved';
                    window.location.href = '/sign/thanks';
                } catch (error) {
                    showError(error.message);
                } finally {
                    if (!statusText.classList.contains('error')) {
                        exportPdf.disabled = true;
                    } else {
                        exportPdf.disabled = false;
                        exportPdf.innerHTML = '<i data-lucide="check-circle"></i> Submit signature';
                    }
                    lucide.createIcons();
                }
            }

            function drawField(page, bold, regular, rgb, label, value, x, y) {
                page.drawText(label, {
                    x,
                    y,
                    size: 11,
                    font: bold,
                    color: rgb(0.09, 0.15, 0.16),
                });

                page.drawText(value, {
                    x,
                    y: y - 28,
                    size: 14,
                    font: regular,
                    color: rgb(0.09, 0.15, 0.16),
                });

                page.drawLine({
                    start: { x, y: y - 36 },
                    end: { x: x + 360, y: y - 36 },
                    thickness: 0.7,
                    color: rgb(0.45, 0.52, 0.52),
                });
            }

            function showError(message) {
                statusText.textContent = message;
                statusText.classList.add('error');
            }

            async function sendEmailOtp() {
                statusText.classList.remove('error');

                if (!email.value.trim() || !email.checkValidity()) {
                    showError('Please enter a valid email address before requesting OTP.');
                    email.focus();
                    return;
                }

                sendOtp.disabled = true;
                sendOtp.innerHTML = '<i data-lucide="loader"></i> Sending...';
                lucide.createIcons();

                try {
                    const response = await fetch('/sign/otp/send', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'ngrok-skip-browser-warning': 'true',
                        },
                        body: JSON.stringify({
                            email: email.value.trim(),
                        }),
                    });

                    const result = await response.json();

                    if (!response.ok) {
                        throw new Error(result.message || 'Unable to send OTP.');
                    }

                    setEmailVerified(false);
                    statusText.textContent = result.message;
                } catch (error) {
                    showError(error.message);
                } finally {
                    sendOtp.disabled = false;
                    sendOtp.innerHTML = '<i data-lucide="mail"></i> Send OTP';
                    lucide.createIcons();
                }
            }

            async function verifyEmailOtp() {
                statusText.classList.remove('error');

                if (!email.value.trim() || !email.checkValidity()) {
                    showError('Please enter a valid email address.');
                    email.focus();
                    return;
                }

                if (!otp.value.trim()) {
                    showError('Please enter the OTP.');
                    otp.focus();
                    return;
                }

                verifyOtp.disabled = true;
                verifyOtp.innerHTML = '<i data-lucide="loader"></i> Verifying...';
                lucide.createIcons();

                try {
                    const response = await fetch('/sign/otp/verify', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'ngrok-skip-browser-warning': 'true',
                        },
                        body: JSON.stringify({
                            email: email.value.trim(),
                            otp: otp.value.trim(),
                        }),
                    });

                    const result = await response.json();

                    if (!response.ok) {
                        throw new Error(result.message || 'Unable to verify OTP.');
                    }

                    setEmailVerified(true);
                    statusText.textContent = result.message;
                } catch (error) {
                    setEmailVerified(false);
                    showError(error.message);
                } finally {
                    verifyOtp.disabled = false;
                    verifyOtp.innerHTML = '<i data-lucide="badge-check"></i> Verify';
                    lucide.createIcons();
                }
            }

            function bytesToBase64(bytes) {
                let binary = '';
                const chunkSize = 0x8000;

                for (let index = 0; index < bytes.length; index += chunkSize) {
                    binary += String.fromCharCode(...bytes.subarray(index, index + chunkSize));
                }

                return btoa(binary);
            }

            signaturePad.addEventListener('pointerdown', startDrawing);
            signaturePad.addEventListener('pointermove', draw);
            signaturePad.addEventListener('pointerup', stopDrawing);
            signaturePad.addEventListener('pointercancel', stopDrawing);
            clearPad.addEventListener('click', clearSignature);
            exportPdf.addEventListener('click', exportSignedPdf);
            sendOtp.addEventListener('click', sendEmailOtp);
            verifyOtp.addEventListener('click', verifyEmailOtp);
            email.addEventListener('input', () => setEmailVerified(false));
            window.addEventListener('resize', resizePad);

            lucide.createIcons();
            setEmailVerified(false);
            requestAnimationFrame(resizePad);
        </script>
    </body>
</html>
