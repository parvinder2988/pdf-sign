<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Edit Signature Details - {{ config('app.name', 'PDF Multi Sign') }}</title>
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
                --error: #9d2f20;
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
                width: min(720px, 100%);
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

            .panel {
                background: var(--paper);
                border: 1px solid var(--line);
                border-radius: 8px;
                padding: 22px;
                box-shadow: 0 12px 36px rgba(29, 47, 49, 0.08);
            }

            label {
                display: block;
                margin-bottom: 8px;
                font-size: 0.86rem;
                font-weight: 800;
            }

            .field {
                margin-bottom: 18px;
            }

            input {
                width: 100%;
                min-height: 48px;
                border: 1px solid var(--line);
                border-radius: 7px;
                padding: 0 12px;
                color: var(--ink);
                font: inherit;
                background: white;
            }

            input:focus {
                border-color: var(--accent);
                outline: 2px solid rgba(14, 124, 102, 0.16);
            }

            .error {
                margin-top: 7px;
                color: var(--error);
                font-size: 0.86rem;
                font-weight: 700;
            }

            .actions {
                display: flex;
                justify-content: flex-end;
                gap: 10px;
                flex-wrap: wrap;
                padding-top: 4px;
            }

            @media (max-width: 640px) {
                .topbar {
                    align-items: flex-start;
                    flex-direction: column;
                }

                .topbar a,
                .actions,
                .actions a,
                .actions button {
                    width: 100%;
                }
            }
        </style>
    </head>
    <body>
        <main class="page">
            <header class="topbar">
                <div>
                    <h1>Edit signature details</h1>
                    <p>Update the driver information saved for this signature.</p>
                </div>
                <a class="secondary" href="{{ route('signatures.report') }}">
                    <i data-lucide="arrow-left"></i>
                    Back to report
                </a>
            </header>

            <form class="panel" method="post" action="{{ route('signatures.update', $signature) }}">
                @csrf
                @method('PUT')

                <div class="field">
                    <label for="name">Name</label>
                    <input id="name" name="name" type="text" value="{{ old('name', $signature->name) }}" required>
                    @error('name')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="field">
                    <label for="driver_number">Driver number</label>
                    <input id="driver_number" name="driver_number" type="text" value="{{ old('driver_number', $signature->driver_number) }}" required>
                    @error('driver_number')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="field">
                    <label for="driver_run_number">Driver run number</label>
                    <input id="driver_run_number" name="driver_run_number" type="text" value="{{ old('driver_run_number', $signature->driver_run_number) }}" required>
                    @error('driver_run_number')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="actions">
                    <a class="secondary" href="{{ route('signatures.report') }}">Cancel</a>
                    <button class="primary" type="submit">
                        <i data-lucide="save"></i>
                        Save changes
                    </button>
                </div>
            </form>
        </main>

        <script>
            lucide.createIcons();
        </script>
    </body>
</html>
