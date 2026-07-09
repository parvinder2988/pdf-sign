<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Report Password - {{ config('app.name', 'PDF Multi Sign') }}</title>
        <script src="https://unpkg.com/lucide@0.468.0/dist/umd/lucide.min.js"></script>
        <style>
            :root {
                --ink: #1b2528;
                --muted: #627174;
                --line: #d8e0df;
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
                display: grid;
                place-items: center;
                padding: 24px;
                background: #f4f7f6;
                color: var(--ink);
                font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            }

            .card {
                width: min(420px, 100%);
                padding: 24px;
                background: var(--paper);
                border: 1px solid var(--line);
                border-radius: 8px;
                box-shadow: 0 18px 48px rgba(29, 47, 49, 0.14);
            }

            h1,
            p {
                margin: 0;
            }

            h1 {
                font-size: 1.35rem;
            }

            p {
                margin-top: 6px;
                color: var(--muted);
                line-height: 1.45;
            }

            form {
                display: grid;
                gap: 14px;
                margin-top: 20px;
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
                font: inherit;
                outline: none;
            }

            input:focus {
                border-color: var(--accent);
                box-shadow: 0 0 0 3px rgba(14, 124, 102, 0.12);
            }

            button,
            a {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                gap: 8px;
                min-height: 44px;
                border-radius: 7px;
                font: inherit;
                font-weight: 800;
                text-decoration: none;
                cursor: pointer;
            }

            button {
                border: 0;
                background: var(--accent);
                color: white;
            }

            button:hover {
                background: var(--accent-dark);
            }

            a {
                color: var(--accent-dark);
            }

            .error {
                color: var(--danger);
                font-weight: 700;
            }
        </style>
    </head>
    <body>
        <main class="card">
            <h1>Report password</h1>
            <p>Enter the password to view saved signatures.</p>

            <form method="post" action="{{ route('signatures.report.authenticate') }}">
                @csrf

                <div>
                    <label for="password">Password</label>
                    <input id="password" name="password" type="password" autofocus autocomplete="current-password">
                    @error('password')
                        <p class="error">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit">
                    <i data-lucide="lock-keyhole"></i>
                    Open report
                </button>

                <a href="{{ route('signatures.sign') }}">
                    <i data-lucide="arrow-left"></i>
                    Back to sign page
                </a>
            </form>
        </main>

        <script>
            lucide.createIcons();
        </script>
    </body>
</html>
