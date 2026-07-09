<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name', 'PDF Multi Sign') }}</title>
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
                --shadow: 0 18px 48px rgba(29, 47, 49, 0.14);
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

            button {
                font: inherit;
            }

            .page {
                min-height: 100vh;
                display: grid;
                grid-template-columns: minmax(320px, 420px) minmax(0, 1fr);
            }

            .panel {
                position: sticky;
                top: 0;
                height: 100vh;
                display: grid;
                align-content: start;
                padding: 24px;
                background: #fbfdfc;
                border-right: 1px solid var(--line);
            }

            .actions {
                width: 100%;
            }

            .primary-button {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                gap: 8px;
                width: 100%;
                min-height: 46px;
                border: 0;
                border-radius: 7px;
                background: var(--accent);
                color: white;
                font-weight: 800;
                cursor: pointer;
            }

            .primary-button:hover {
                background: var(--accent-dark);
            }

            .viewer {
                min-width: 0;
                display: grid;
                place-items: start center;
                padding: 24px;
                overflow: auto;
            }

            .pdf-pages {
                display: grid;
                justify-items: center;
                gap: 22px;
                width: 100%;
            }

            .pdf-page {
                max-width: 100%;
                height: auto;
                background: white;
                box-shadow: var(--shadow);
            }

            @media (max-width: 860px) {
                .page {
                    grid-template-columns: 1fr;
                }

                .panel {
                    position: static;
                    height: auto;
                    align-content: start;
                    border-right: 0;
                    border-bottom: 1px solid var(--line);
                }
            }
        </style>
    </head>
    <body>
        <div class="page">
            <aside class="panel">
                <section class="actions">
                    <button id="signNow" class="primary-button" type="button">
                        <i data-lucide="pen-line"></i>
                        Read and Sign
                    </button>
                </section>
            </aside>

            <main class="viewer">
                <div id="pdfPages" class="pdf-pages"></div>
            </main>
        </div>

        <script type="module">
            import * as pdfjsLib from 'https://unpkg.com/pdfjs-dist@4.10.38/build/pdf.min.mjs';

            pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://unpkg.com/pdfjs-dist@4.10.38/build/pdf.worker.min.mjs';

            const pdfUrl = @json(asset('pdfs/ilovepdf-merged.pdf'));
            const pdfPages = document.querySelector('#pdfPages');

            document.querySelector('#signNow').addEventListener('click', () => {
                window.location.href = @json(route('signatures.sign'));
            });

            async function renderAllPages() {
                const documentProxy = await pdfjsLib.getDocument({ url: pdfUrl }).promise;

                for (let pageNumber = 1; pageNumber <= documentProxy.numPages; pageNumber += 1) {
                    const page = await documentProxy.getPage(pageNumber);
                    const viewport = page.getViewport({ scale: 1.45 });
                    const scale = window.devicePixelRatio || 1;
                    const canvas = document.createElement('canvas');
                    const ctx = canvas.getContext('2d');

                    canvas.className = 'pdf-page';
                    canvas.width = Math.floor(viewport.width * scale);
                    canvas.height = Math.floor(viewport.height * scale);
                    canvas.style.width = `${Math.floor(viewport.width)}px`;
                    canvas.style.height = `${Math.floor(viewport.height)}px`;
                    pdfPages.append(canvas);

                    await page.render({
                        canvasContext: ctx,
                        viewport,
                        transform: scale !== 1 ? [scale, 0, 0, scale, 0, 0] : null,
                    }).promise;
                }
            }

            lucide.createIcons();
            renderAllPages();
        </script>
    </body>
</html>
