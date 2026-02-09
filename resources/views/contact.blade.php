<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ICMQTT - Contact Us</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: #f5f7fb;
            color: #2d2f33;
            line-height: 1.7;
        }
        .navbar {
            background: #ffffff;
            border-bottom: 1px solid #e6e8ef;
            padding: 1rem 2rem;
            position: sticky;
            top: 0;
            z-index: 10;
        }
        .navbar-inner {
            max-width: 1100px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .brand {
            font-weight: 700;
            font-size: 1.25rem;
            color: #2d2f33;
            text-decoration: none;
        }
        .nav-links { display: flex; gap: 1rem; }
        .menu-toggle {
            display: none;
            align-items: center;
            gap: 0.5rem;
            background: #eef2ff;
            border: 1px solid #c7d2fe;
            color: #1f2937;
            padding: 0.45rem 0.85rem;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
        }
        .nav-links a {
            text-decoration: none;
            color: #475569;
            font-weight: 500;
            padding: 0.4rem 0.75rem;
            border-radius: 6px;
            transition: all 0.2s ease;
        }
        .nav-links a:hover { background: #eef2ff; color: #4f46e5; }
        .wrapper { max-width: 900px; margin: 0 auto; padding: 2.5rem 2rem 4rem; }
        .card {
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.08);
            padding: 2rem;
        }
        h1 { font-size: 2rem; margin-bottom: 0.5rem; color: #111827; }
        p { color: #6b7280; margin-bottom: 1.5rem; }
        .form-group { margin-bottom: 1rem; }
        label { display: block; margin-bottom: 0.4rem; font-weight: 600; }
        input, textarea {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #e6e8ef;
            border-radius: 8px;
            font-size: 1rem;
            font-family: inherit;
        }
        textarea { min-height: 140px; resize: vertical; }
        .btn {
            display: inline-block;
            padding: 0.75rem 1.25rem;
            border: none;
            border-radius: 8px;
            background: #4f46e5;
            color: #ffffff;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .btn:hover { background: #4338ca; }
        .alert {
            padding: 0.75rem 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            font-weight: 500;
        }
        .alert-success { background: #ecfdf3; color: #047857; border: 1px solid #a7f3d0; }
        .alert-error { background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; }
        .footer { text-align: center; color: #94a3b8; font-size: 0.9rem; padding: 2rem 1rem 3rem; }
        @media (max-width: 768px) {
            .navbar-inner { flex-direction: column; gap: 0.75rem; }
            .nav-links {
                width: 100%;
                flex-direction: column;
                align-items: center;
                display: none;
                padding: 0.5rem 0 0.75rem;
            }
            .nav-links.is-open { display: flex; }
            .menu-toggle { display: inline-flex; }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="navbar-inner">
            <a class="brand" href="{{ route('home') }}">ICMQTT</a>
            <button class="menu-toggle" type="button" aria-expanded="false" aria-controls="primary-menu">
                ☰ Menu
            </button>
            <div id="primary-menu" class="nav-links">
                <a href="{{ route('home') }}">Beranda</a>
                <a href="{{ route('legal.policies') }}">Syarat & Kebijakan</a>
                <a href="{{ route('contact.show') }}">Contact</a>
                @auth
                    <a href="{{ route('dashboard') }}">Dashboard</a>
                @else
                    <a href="{{ route('login') }}">Masuk</a>
                @endauth
            </div>
        </div>
    </nav>

    <main class="wrapper">
        <div class="card">
            <h1>Contact Us</h1>
            <p>Isi formulir di bawah ini, tim ICMQTT akan menghubungi Anda secepatnya.</p>

            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            @if ($errors->any())
                <div class="alert alert-error">
                    <ul style="margin-left: 1.25rem;">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('contact.send') }}">
                @csrf
                <div class="form-group">
                    <label for="name">Nama</label>
                    <input id="name" name="name" type="text" value="{{ old('name') }}" required>
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required>
                </div>
                <div class="form-group">
                    <label for="subject">Subjek</label>
                    <input id="subject" name="subject" type="text" value="{{ old('subject') }}" required>
                </div>
                <div class="form-group">
                    <label for="message">Pesan</label>
                    <textarea id="message" name="message" required>{{ old('message') }}</textarea>
                </div>
                <button class="btn" type="submit">Kirim Pesan</button>
            </form>
        </div>
    </main>

    <footer class="footer">
        © {{ now()->year }} ICMQTT. Semua hak dilindungi.
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const toggle = document.querySelector('.menu-toggle');
            const menu = document.getElementById('primary-menu');

            if (!toggle || !menu) {
                return;
            }

            toggle.addEventListener('click', () => {
                const isOpen = menu.classList.toggle('is-open');
                toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
            });
        });
    </script>
</body>
</html>
