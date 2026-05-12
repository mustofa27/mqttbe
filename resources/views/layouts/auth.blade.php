<!DOCTYPE html>
<html lang="en">
<head>
        <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Auth')</title>
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
    <style>
        html, body { height: 100%; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display:flex;
            align-items:center;
            justify-content:center;
            color:#333;
            padding:2rem;
        }

        .auth-container {
            width: 100%;
            max-width: 420px;
        }

        .auth-form {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.12);
        }

        h1 { margin-bottom: 1rem; font-size: 1.5rem; }

        .form-group { margin-bottom: 1rem; }

        label { display:block; margin-bottom:0.5rem; color:#333; font-weight:600; }

        input[type="text"], input[type="email"], input[type="password"] {
            width:100%; padding:0.75rem; border:1px solid #e6e6e6; border-radius:6px; font-size:1rem;
        }

        .btn { display:inline-block; padding:0.75rem 1rem; border-radius:6px; text-decoration:none; border:none; cursor:pointer; }
        .btn-primary { background:#667eea; color:white; width:100%; }
        .btn-google {
            position: relative;
            background: #ffffff;
            color: #111827;
            border: 1px solid #d1d5db;
            width: 100%;
            min-height: 46px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.65rem;
            font-weight: 700;
            letter-spacing: 0.01em;
            transition: background-color 0.2s ease, box-shadow 0.2s ease, transform 0.15s ease;
            box-shadow: 0 1px 2px rgba(17, 24, 39, 0.06);
        }

        .btn-google:hover {
            background: #f8fafc;
            box-shadow: 0 4px 10px rgba(17, 24, 39, 0.12);
        }

        .btn-google:active {
            transform: translateY(1px);
            box-shadow: 0 1px 3px rgba(17, 24, 39, 0.12);
        }

        .btn-google:focus-visible {
            outline: 3px solid rgba(66, 133, 244, 0.35);
            outline-offset: 2px;
        }

        .btn-google-icon {
            width: 18px;
            height: 18px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex: 0 0 18px;
        }

        .btn-google-text {
            line-height: 1;
        }

        .auth-divider {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin: 1rem 0;
            color: #9ca3af;
            font-size: 0.85rem;
        }

        .auth-divider::before,
        .auth-divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: #e5e7eb;
        }

        .form-footer { text-align:center; margin-top:1rem; color:#666; }

        .error { color:#dc3545; margin-top:0.25rem; font-size:0.9rem; }

        .password-toggle-wrapper {
            position: relative;
            display: flex;
            align-items: center;
            width: 100%;
        }

        .password-toggle-wrapper input {
            width: 100%;
            padding-right: 2.5rem;
        }

        .password-toggle-btn {
            position: absolute;
            right: 0.75rem;
            background: none;
            border: none;
            cursor: pointer;
            color: #999;
            font-size: 1rem;
            padding: 0.25rem 0.5rem;
            transition: color 0.2s;
        }

        .password-toggle-btn:hover {
            color: #667eea;
        }
    </style>
</head>
<body>
    <div class="auth-container">
        @yield('content')
    </div>

    <script>
        function togglePasswordVisibility(inputId) {
            const input = document.getElementById(inputId);
            const btn = document.querySelector(`[data-toggle="${inputId}"]`);
            if (input.type === 'password') {
                input.type = 'text';
                btn.textContent = '🙈';
            } else {
                input.type = 'password';
                btn.textContent = '👁️';
            }
        }
    </script>
</body>
</html>
