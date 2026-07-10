<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Signature Report - {{ config('app.name', 'PDF Multi Sign') }}</title>
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

            .page {
                width: min(1180px, 100%);
                margin: 0 auto;
                padding: 24px;
            }

            .topbar {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 16px;
                margin-bottom: 18px;
            }

            h1,
            p {
                margin: 0;
            }

            h1 {
                font-size: 1.45rem;
            }

            p {
                margin-top: 4px;
                color: var(--muted);
            }

            .toolbar {
                display: flex;
                gap: 10px;
                flex-wrap: wrap;
            }

            a,
            button {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                gap: 8px;
                min-height: 42px;
                padding: 0 14px;
                border-radius: 7px;
                font: inherit;
                font-weight: 800;
                text-decoration: none;
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

            .secondary {
                border: 1px solid var(--line);
                background: white;
                color: var(--ink);
            }

            .danger {
                border: 1px solid #d9a7a0;
                background: #fff5f3;
                color: #9d2f20;
            }

            .danger:hover {
                background: #ffe8e4;
            }

            .notice {
                margin-bottom: 16px;
                padding: 12px 14px;
                border: 1px solid #b8d8ce;
                border-radius: 8px;
                background: #eef9f4;
                color: #0b604f;
                font-weight: 700;
            }

            .table-wrap {
                overflow: auto;
                background: var(--paper);
                border: 1px solid var(--line);
                border-radius: 8px;
                box-shadow: 0 12px 36px rgba(29, 47, 49, 0.08);
            }

            table {
                width: 100%;
                border-collapse: collapse;
                min-width: 1040px;
            }

            th,
            td {
                padding: 12px 14px;
                border-bottom: 1px solid var(--line);
                text-align: left;
                vertical-align: middle;
                font-size: 0.94rem;
            }

            th {
                background: var(--soft);
                color: var(--ink);
                font-size: 0.82rem;
                text-transform: uppercase;
                letter-spacing: 0;
            }

            tr:last-child td {
                border-bottom: 0;
            }

            .signature-img {
                display: block;
                width: 160px;
                height: 48px;
                object-fit: contain;
                background: white;
                border: 1px solid var(--line);
                border-radius: 6px;
            }

            .row-actions {
                display: flex;
                gap: 8px;
                justify-content: flex-end;
            }

            .empty {
                padding: 30px;
                color: var(--muted);
                text-align: center;
            }

            @media (max-width: 720px) {
                .topbar {
                    align-items: flex-start;
                    flex-direction: column;
                }

                .toolbar,
                .toolbar a,
                .toolbar button {
                    width: 100%;
                }
            }
        </style>
    </head>
    <body>
        <main class="page">
            <header class="topbar">
                <div>
                    <h1>DRIVER SIGNATURES StarTrack API Drivers and Contractors — Victoria</h1>
                    <p>{{ $signatures->count() }} saved {{ Str::plural('signature', $signatures->count()) }}</p>
                </div>
                <div class="toolbar">
                    <a class="secondary" href="{{ route('signatures.sign') }}">
                        <i data-lucide="pen-line"></i>
                        Sign page
                    </a>
                    <form method="post" action="{{ route('signatures.report.logout') }}">
                        @csrf
                        <button class="secondary" type="submit">
                            <i data-lucide="log-out"></i>
                            Lock report
                        </button>
                    </form>
                    <button id="exportReport" class="primary" type="button" @disabled($signatures->isEmpty())>
                        <i data-lucide="download"></i>
                        Export table PDF
                    </button>
                </div>
            </header>

            @if (session('status'))
                <div class="notice">{{ session('status') }}</div>
            @endif

            <section class="table-wrap">
                @if ($signatures->isEmpty())
                    <div class="empty">No signatures saved yet.</div>
                @else
                    <table>
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Driver number</th>
                                <th>Driver run number</th>
                                <th>Signed at</th>
                                <th>Signature</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($signatures as $signature)
                                <tr>
                                    <td>{{ $signature->name }}</td>
                                    <td>{{ $signature->driver_number }}</td>
                                    <td>{{ $signature->driver_run_number }}</td>
                                    <td>{{ optional($signature->signed_at)->timezone('Australia/Melbourne')->format('M d, Y h:i A T') }}</td>
                                    <td>
                                        <img
                                            class="signature-img"
                                            src="{{ $signature->signature_blob !== null ? 'data:image/png;base64,'.base64_encode($signature->signature_blob) : route('signatures.file', ['path' => $signature->signature_path]) }}"
                                            alt="Signature for {{ $signature->name }}"
                                        >
                                    </td>
                                    <td>
                                        <div class="row-actions">
                                            <a class="secondary" href="{{ route('signatures.edit', $signature) }}" title="Edit signature details">
                                                <i data-lucide="pencil"></i>
                                                Edit
                                            </a>
                                            <form
                                                class="delete-signature-form"
                                                method="post"
                                                action="{{ route('signatures.destroy', $signature) }}"
                                                data-name="{{ $signature->name }}"
                                            >
                                                @csrf
                                                @method('DELETE')
                                                <button class="danger" type="submit" title="Delete signature record">
                                                    <i data-lucide="trash-2"></i>
                                                    Delete
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </section>
        </main>

        <script>
            const rows = @json($reportRows);

            document.querySelector('#exportReport')?.addEventListener('click', exportReportPdf);
            document.querySelectorAll('.delete-signature-form').forEach((form) => {
                form.addEventListener('submit', (event) => {
                    const name = form.dataset.name || 'this record';

                    if (!window.confirm(`Delete ${name}? This cannot be undone.`)) {
                        event.preventDefault();
                    }
                });
            });

            async function exportReportPdf() {
                const { PDFDocument, StandardFonts, rgb } = PDFLib;
                const documentPdf = await PDFDocument.create();
                const regular = await documentPdf.embedFont(StandardFonts.Helvetica);
                const bold = await documentPdf.embedFont(StandardFonts.HelveticaBold);
                const pageSize = [841.89, 595.28];
                let page = documentPdf.addPage(pageSize);
                let y = 540;

                drawTitle(page, bold, regular, rgb, rows.length);
                drawHeader(page, bold, rgb, y);
                y -= 30;

                for (const row of rows) {
                    if (y < 74) {
                        page = documentPdf.addPage(pageSize);
                        y = 540;
                        drawHeader(page, bold, rgb, y);
                        y -= 30;
                    }

                    page.drawText(truncate(row.name, 24), { x: 34, y, size: 8, font: regular, color: rgb(0.09, 0.15, 0.16) });
                    page.drawText(truncate(row.driverNumber, 18), { x: 186, y, size: 8, font: regular, color: rgb(0.09, 0.15, 0.16) });
                    page.drawText(truncate(row.driverRunNumber, 18), { x: 310, y, size: 8, font: regular, color: rgb(0.09, 0.15, 0.16) });
                    page.drawText(row.signedAt || '', { x: 438, y, size: 8, font: regular, color: rgb(0.09, 0.15, 0.16) });

                    try {
                        const image = row.signatureDataUrl
                            ? await documentPdf.embedPng(row.signatureDataUrl)
                            : await fetchSignatureImage(documentPdf, row.signatureUrl);
                        const dimensions = image.scale(Math.min(142 / image.width, 34 / image.height));
                        page.drawImage(image, {
                            x: 626,
                            y: y - 12,
                            width: dimensions.width,
                            height: dimensions.height,
                        });
                    } catch (error) {
                        page.drawText('Unavailable', { x: 626, y, size: 8, font: regular, color: rgb(0.55, 0.24, 0.08) });
                    }

                    page.drawLine({
                        start: { x: 30, y: y - 16 },
                        end: { x: 812, y: y - 16 },
                        thickness: 0.5,
                        color: rgb(0.86, 0.89, 0.88),
                    });

                    y -= 48;
                }

                const bytes = await documentPdf.save();
                downloadBlob(new Blob([bytes], { type: 'application/pdf' }), 'signature-report.pdf');
            }

            function drawTitle(page, bold, regular, rgb, count) {
                page.drawText('DRIVER SIGNATURES', {
                    x: 30,
                    y: 562,
                    size: 18,
                    font: bold,
                    color: rgb(0.09, 0.15, 0.16),
                });
                page.drawText('StarTrack API Drivers and Contractors - Victoria', {
                    x: 30,
                    y: 542,
                    size: 10,
                    font: regular,
                    color: rgb(0.09, 0.15, 0.16),
                });
                page.drawText(`${count} saved ${count === 1 ? 'signature' : 'signatures'}`, {
                    x: 30,
                    y: 526,
                    size: 9,
                    font: regular,
                    color: rgb(0.35, 0.43, 0.44),
                });
            }

            function drawHeader(page, bold, rgb, y) {
                page.drawRectangle({
                    x: 30,
                    y: y - 9,
                    width: 782,
                    height: 22,
                    color: rgb(0.93, 0.96, 0.95),
                    borderColor: rgb(0.82, 0.87, 0.86),
                    borderWidth: 0.5,
                });
                page.drawText('Name', { x: 34, y, size: 8, font: bold, color: rgb(0.09, 0.15, 0.16) });
                page.drawText('Driver no.', { x: 186, y, size: 8, font: bold, color: rgb(0.09, 0.15, 0.16) });
                page.drawText('Run no.', { x: 310, y, size: 8, font: bold, color: rgb(0.09, 0.15, 0.16) });
                page.drawText('Signed at', { x: 438, y, size: 8, font: bold, color: rgb(0.09, 0.15, 0.16) });
                page.drawText('Signature', { x: 626, y, size: 8, font: bold, color: rgb(0.09, 0.15, 0.16) });
            }

            function truncate(value, length) {
                if (!value || value.length <= length) {
                    return value || '';
                }

                return `${value.slice(0, length - 1)}...`;
            }

            async function fetchSignatureImage(documentPdf, url) {
                const response = await fetch(url, {
                    headers: {
                        'ngrok-skip-browser-warning': 'true',
                    },
                });

                if (!response.ok) {
                    throw new Error('Signature image unavailable.');
                }

                return documentPdf.embedPng(await response.arrayBuffer());
            }

            function downloadBlob(blob, name) {
                const url = URL.createObjectURL(blob);
                const link = document.createElement('a');
                link.href = url;
                link.download = name;
                document.body.append(link);
                link.click();
                link.remove();
                URL.revokeObjectURL(url);
            }

            lucide.createIcons();
        </script>
    </body>
</html>
