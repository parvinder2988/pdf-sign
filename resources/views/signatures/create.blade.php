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
                padding: clamp(10px, 3vw, 24px);
                overflow: auto;
            }

            .pdf-pages {
                display: grid;
                justify-items: center;
                gap: 22px;
                width: 100%;
            }

            .pdf-page {
                width: 100%;
                max-width: 960px;
                height: auto;
                background: white;
                box-shadow: var(--shadow);
            }

            .mobile-bottom-actions {
                display: none;
            }

            @media (max-width: 860px) {
                .page {
                    grid-template-columns: 1fr;
                }

                .panel {
                    position: static;
                    height: auto;
                    align-content: start;
                    padding: 12px;
                    border-right: 0;
                    border-bottom: 1px solid var(--line);
                }

                .viewer {
                    padding: 10px 6px 18px;
                }

                .pdf-pages {
                    gap: 14px;
                }

                .primary-button {
                    min-height: 42px;
                }

                .mobile-bottom-actions {
                    display: block;
                    width: 100%;
                    padding: 4px 0 10px;
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
                <section class="mobile-bottom-actions">
                    <button class="primary-button sign-now-button" type="button">
                        <i data-lucide="pen-line"></i>
                        Read and Sign
                    </button>
                </section>
            </main>
        </div>

        <script type="module">
            import * as pdfjsLib from 'https://unpkg.com/pdfjs-dist@4.10.38/build/pdf.min.mjs';

            pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://unpkg.com/pdfjs-dist@4.10.38/build/pdf.worker.min.mjs';

            const pdfUrl = '/pdfs/ilovepdf-merged.pdf';
            const pdfPages = document.querySelector('#pdfPages');
            let pdfDocument = null;
            let renderToken = 0;

            document.querySelectorAll('.sign-now-button, #signNow').forEach((button) => {
                button.addEventListener('click', () => {
                    window.location.href = '/sign';
                });
            });

            async function renderAllPages() {
                const token = ++renderToken;

                if (!pdfDocument) {
                    pdfDocument = await pdfjsLib.getDocument({
                        url: pdfUrl,
                        httpHeaders: {
                            'ngrok-skip-browser-warning': 'true',
                        },
                    }).promise;
                }

                pdfPages.replaceChildren();
                const availableWidth = Math.max(280, pdfPages.clientWidth);

                for (let pageNumber = 1; pageNumber <= pdfDocument.numPages; pageNumber += 1) {
                    if (token !== renderToken) {
                        return;
                    }

                    const page = await pdfDocument.getPage(pageNumber);
                    const unscaledViewport = page.getViewport({ scale: 1 });
                    const pageScale = Math.min(1.65, availableWidth / unscaledViewport.width);
                    const viewport = page.getViewport({ scale: pageScale });
                    const outputScale = Math.min(window.devicePixelRatio || 1, 2.25);
                    const canvas = document.createElement('canvas');
                    const ctx = canvas.getContext('2d');

                    canvas.className = 'pdf-page';
                    canvas.width = Math.floor(viewport.width * outputScale);
                    canvas.height = Math.floor(viewport.height * outputScale);
                    canvas.style.width = `${Math.floor(viewport.width)}px`;
                    canvas.style.height = `${Math.floor(viewport.height)}px`;
                    pdfPages.append(canvas);

                    await page.render({
                        canvasContext: ctx,
                        viewport,
                        transform: outputScale !== 1 ? [outputScale, 0, 0, outputScale, 0, 0] : null,
                    }).promise;
                }
            }

            lucide.createIcons();
            renderAllPages();
            window.addEventListener('resize', debounce(renderAllPages, 180));

            function debounce(callback, delay) {
                let timeout = null;

                return () => {
                    window.clearTimeout(timeout);
                    timeout = window.setTimeout(callback, delay);
                };
            }
        </script>
    </body>
</html>
