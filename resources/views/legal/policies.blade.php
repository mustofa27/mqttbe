<!DOCTYPE html>
<html lang="id">
<head>
        <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ICMQTT - Syarat & Ketentuan</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

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

        .nav-links {
            display: flex;
            gap: 1rem;
        }

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

        .nav-links a:hover {
            background: #eef2ff;
            color: #4f46e5;
        }

        .wrapper {
            max-width: 1100px;
            margin: 0 auto;
            padding: 2.5rem 2rem 4rem;
        }

        .page-title {
            font-size: 2rem;
            margin-bottom: 0.5rem;
            color: #111827;
        }

        .page-subtitle {
            color: #6b7280;
            margin-bottom: 2rem;
        }

        .card {
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.08);
            padding: 2rem;
            margin-bottom: 1.5rem;
        }

        .card h2 {
            font-size: 1.4rem;
            margin-bottom: 0.75rem;
            color: #1f2937;
        }

        .card h3 {
            font-size: 1.1rem;
            margin-top: 1.5rem;
            margin-bottom: 0.5rem;
            color: #374151;
        }

        .card ul {
            padding-left: 1.25rem;
            margin-top: 0.75rem;
        }

        .card ul li {
            margin-bottom: 0.5rem;
        }

        .badge {
            display: inline-block;
            background: #e0e7ff;
            color: #4338ca;
            font-size: 0.85rem;
            padding: 0.25rem 0.6rem;
            border-radius: 999px;
            margin-bottom: 1rem;
        }

        .footer {
            text-align: center;
            color: #94a3b8;
            font-size: 0.9rem;
            padding: 2rem 1rem 3rem;
        }

        a.inline-link {
            color: #4f46e5;
            text-decoration: none;
        }

        a.inline-link:hover {
            text-decoration: underline;
        }

        @media (max-width: 768px) {
            .navbar-inner {
                flex-direction: column;
                gap: 0.75rem;
            }

            .nav-links {
                width: 100%;
                flex-direction: column;
                align-items: center;
                display: none;
                padding: 0.5rem 0 0.75rem;
            }

            .nav-links.is-open {
                display: flex;
            }

            .menu-toggle {
                display: inline-flex;
            }

            .wrapper {
                padding: 2rem 1.5rem 3rem;
            }
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
        <div class="page-title">Syarat & Ketentuan</div>
        <div class="page-subtitle">Kebijakan Pengembalian Dana dan Kebijakan Produk ICMQTT</div>

        <section class="card">
            <span class="badge">Terakhir diperbarui: 9 Februari 2026</span>
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

        <section class="card">
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

        <section class="card">
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
