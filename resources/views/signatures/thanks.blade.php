<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Thank You - {{ config('app.name', 'PDF Multi Sign') }}</title>
        <script src="https://unpkg.com/lucide@0.468.0/dist/umd/lucide.min.js"></script>
        <style>
            :root {
                --ink: #1b2528;
                --muted: #627174;
                --line: #d8e0df;
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
                display: grid;
                place-items: center;
                padding: 24px;
                background: #f4f7f6;
                color: var(--ink);
                font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            }

            .panel {
                width: min(100%, 560px);
                padding: 28px;
                border: 1px solid var(--line);
                border-radius: 8px;
                background: var(--paper);
                box-shadow: 0 18px 48px rgba(29, 47, 49, 0.12);
            }

            .icon {
                width: 48px;
                height: 48px;
                display: grid;
                place-items: center;
                border-radius: 50%;
                background: #e9f5f1;
                color: var(--accent);
            }

            h1 {
                margin: 18px 0 8px;
                font-size: clamp(26px, 4vw, 38px);
                line-height: 1.05;
            }

            p {
                margin: 0;
                color: var(--muted);
                font-size: 17px;
                line-height: 1.55;
            }

            .actions {
                display: flex;
                flex-wrap: wrap;
                gap: 12px;
                margin-top: 24px;
            }

            a {
                min-height: 44px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                gap: 8px;
                padding: 0 16px;
                border-radius: 7px;
                font-weight: 800;
                text-decoration: none;
            }

            .primary {
                background: var(--accent);
                color: white;
            }

            .primary:hover {
                background: var(--accent-dark);
            }

            .secondary {
                border: 1px solid var(--line);
                color: var(--ink);
                background: #fbfdfc;
            }
        </style>
    </head>
    <body>
        <main class="panel">
            <div class="icon">
                <i data-lucide="check"></i>
            </div>
            <h1>Thank you for signing.</h1>
            <p>Your signature has been saved successfully. You can return to the PDF page using the link below.</p>
            <div class="actions">
                <a class="primary" href="{{ route('signatures.create') }}">
                    <i data-lucide="file-text"></i>
                    View PDF page
                </a>
                <a class="secondary" href="/pdfs/ilovepdf-merged.pdf" target="_blank" rel="noopener">
                    <i data-lucide="external-link"></i>
                    Open PDF
                </a>
            </div>
        </main>

        <script>
            lucide.createIcons();
        </script>
    </body>
</html>
