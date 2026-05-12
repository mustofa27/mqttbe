<!DOCTYPE html>
<html lang="id">
<head>
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ICMQTT - Syarat & Ketentuan</title>

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
        <div class="guest-shell">
            <div class="page-intro">
                <h1>Syarat & Ketentuan</h1>
                <p>Kebijakan Pengembalian Dana dan Kebijakan Produk ICMQTT</p>
            </div>

            <section class="guest-card">
                <span class="info-badge">Terakhir diperbarui: 9 Februari 2026</span>
                <h2>Syarat dan Ketentuan</h2>
                <p>Dengan mengakses dan menggunakan layanan ICMQTT, Anda menyetujui syarat dan ketentuan berikut. Mohon membaca dengan saksama sebelum menggunakan layanan kami.</p>

                <h3>1. Definisi Layanan</h3>
                <ul>
                    <li>ICMQTT adalah platform manajemen infrastruktur IoT yang menyediakan fitur manajemen proyek, perangkat, topik, dan hak akses.</li>
                    <li>Layanan dapat diakses melalui aplikasi web dan API resmi yang disediakan oleh ICMQTT.</li>
                </ul>

                <h3>2. Akun dan Keamanan</h3>
                <ul>
                    <li>Pengguna bertanggung jawab atas kerahasiaan kredensial akun dan aktivitas yang terjadi di dalam akun.</li>
                    <li>Segera beri tahu kami jika terjadi dugaan akses tidak sah atau kebocoran kredensial.</li>
                </ul>

                <h3>3. Penggunaan yang Dilarang</h3>
                <ul>
                    <li>Penggunaan layanan untuk aktivitas ilegal, spam, atau pelanggaran hak pihak ketiga.</li>
                    <li>Upaya mengganggu keamanan sistem, termasuk eksploitasi celah keamanan, scraping tanpa izin, atau reverse engineering.</li>
                </ul>

                <h3>4. Langganan dan Pembayaran</h3>
                <ul>
                    <li>Detail paket dan harga ditampilkan di halaman langganan, termasuk periode penagihan dan fitur.</li>
                    <li>Pembayaran diproses melalui mitra pembayaran resmi kami. Bukti pembayaran dikirimkan melalui email dan/atau dashboard.</li>
                </ul>

                <h3>5. Perubahan Layanan</h3>
                <ul>
                    <li>ICMQTT dapat memperbarui, menambah, atau menghapus fitur secara berkala untuk meningkatkan kualitas layanan.</li>
                    <li>Perubahan signifikan akan diinformasikan melalui email atau pengumuman di aplikasi.</li>
                </ul>
            </section>

            <section class="guest-card">
                <h2>Kebijakan Pengembalian Dana</h2>
                <p>ICMQTT berkomitmen memberikan transparansi terkait proses pengembalian dana. Kebijakan berikut berlaku untuk semua paket langganan.</p>

                <h3>1. Kelayakan Pengembalian Dana</h3>
                <ul>
                    <li>Pengembalian dana dapat diajukan dalam 7 hari kalender sejak tanggal transaksi berhasil.</li>
                    <li>Pengembalian dana tidak berlaku jika layanan telah digunakan secara signifikan atau melampaui batas penggunaan wajar pada periode berjalan.</li>
                    <li>Biaya transaksi pihak ketiga (jika ada) dapat menjadi tanggungan pengguna dan tidak selalu dapat dikembalikan.</li>
                </ul>

                <h3>2. Proses Pengajuan</h3>
                <ul>
                    <li>Ajukan permintaan melalui email ke <a class="inline-link" href="mailto:icminovasi@gmail.com">icminovasi@gmail.com</a> dengan subjek "Permintaan Refund".</li>
                    <li>Sertakan detail akun, bukti pembayaran, serta alasan permintaan pengembalian dana.</li>
                </ul>

                <h3>3. Waktu Pemrosesan</h3>
                <ul>
                    <li>Permintaan diproses dalam 5-10 hari kerja setelah verifikasi.</li>
                    <li>Pengembalian dana akan dikirim ke metode pembayaran yang sama jika memungkinkan.</li>
                </ul>
            </section>

            <section class="guest-card">
                <h2>Kebijakan Produk</h2>
                <p>Kebijakan produk berikut menjelaskan cakupan layanan, dukungan, dan batasan penggunaan.</p>

                <h3>1. Cakupan Layanan</h3>
                <ul>
                    <li>ICMQTT menyediakan layanan pengelolaan perangkat IoT, topik MQTT, dan otorisasi akses.</li>
                    <li>Fitur dapat berbeda sesuai paket yang dipilih. Detail paket tersedia di halaman langganan.</li>
                </ul>

                <h3>2. Dukungan Pelanggan</h3>
                <ul>
                    <li>Dukungan tersedia pada hari kerja (Senin - Jumat), pukul 09.00 - 17.00 WIB.</li>
                    <li>Permintaan dukungan dapat diajukan melalui email resmi atau pusat bantuan di dashboard.</li>
                </ul>

                <h3>3. Batasan dan Kewajiban Pengguna</h3>
                <ul>
                    <li>Pengguna wajib memastikan perangkat yang terhubung mematuhi regulasi dan standar keamanan yang berlaku.</li>
                    <li>Penyalahgunaan layanan dapat menyebabkan penangguhan atau penghentian akun.</li>
                </ul>
            </section>
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
