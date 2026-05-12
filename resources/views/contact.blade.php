<!DOCTYPE html>
<html lang="id">
<head>
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ICMQTT - Contact Us</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=manrope:400,500,600,700,800" rel="stylesheet" />

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/guest-pages.css'])
    @else
        <link rel="stylesheet" href="{{ asset('css/guest-pages.css') }}">
    @endif
</head>
<body class="guest-page">
    <header class="guest-header">
        <div class="guest-shell guest-nav">
            <a class="guest-brand" href="{{ route('home') }}">ICMQTT</a>
            <button class="guest-menu-toggle" type="button" aria-expanded="false" aria-controls="primary-menu">
                Menu
            </button>
            <div id="primary-menu" class="guest-links">
                <a href="{{ route('home') }}">Beranda</a>
                <a href="{{ route('legal.policies') }}">Syarat & Kebijakan</a>
                <a href="{{ route('contact.show') }}">Contact</a>
                @auth
                    <a class="guest-highlight" href="{{ route('home.dashboard') }}">Dashboard</a>
                @else
                    <a class="guest-highlight" href="{{ route('login') }}">Masuk</a>
                @endauth
            </div>
        </div>
    </header>

    <main class="guest-main">
        <div class="guest-shell guest-shell-narrow">
            <div class="page-intro">
                <h1>Contact Us</h1>
                <p>Isi formulir di bawah ini, tim ICMQTT akan menghubungi Anda secepatnya.</p>
            </div>

            <div class="guest-card">

                @if (session('success'))
                    <div class="guest-alert guest-alert-success">{{ session('success') }}</div>
                @endif

                @if ($errors->any())
                    <div class="guest-alert guest-alert-error">
                        <ul>
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
                    <button class="guest-btn" type="submit">Kirim Pesan</button>
                </form>
            </div>
        </div>
    </main>

    <footer class="guest-footer">
        © {{ now()->year }} ICMQTT. Semua hak dilindungi.
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const toggle = document.querySelector('.guest-menu-toggle');
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
