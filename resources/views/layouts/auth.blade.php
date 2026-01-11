<!DOCTYPE html>
<html lang="en">
<head>
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
