<?php
session_start();
$page = $_GET['page'] ?? 'home';
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Publikasi — UrFarm</title>
    <link rel="stylesheet" href="../css/publikasi.css">
    <link rel="stylesheet" href="../css/style.css">
</head>

<body>

    <nav id="navbar">
        <a href="?page=home" class="nav-logo">Ur<span>Farm</span></a>
        <div class="nav-links" id="navLinks">
            <a href="?page=home" <?= $page == 'home' ? 'class="active"' : '' ?>>Home</a>
            <a href="?page=program" <?= $page == 'program' ? 'class="active"' : '' ?>>Program</a>
            <a href="?page=partner" <?= $page == 'partner' ? 'class="active"' : '' ?>>Partner</a>
            <a href="?page=publikasi" <?= $page == 'publikasi' ? 'class="active"' : '' ?>>Publikasi</a>
            <div class="dropdown">
                <a href="#">Tentang ▾</a>
                <div class="dropdown-menu">
                    <a href="?page=about">Tentang Kami</a>
                    <a href="?page=contact">Hubungi Kami</a>
                    <a href="?page=faq">FAQ</a>
                </div>
            </div>
            <?php if (isset($_SESSION['user_id'])): ?>
                <div class="dropdown">
                    <a href="#">👤 <?= htmlspecialchars($_SESSION['user_nama']) ?> ▾</a>
                    <div class="dropdown-menu">
                        <?php if ($_SESSION['user_role'] === 'admin'): ?>
                            <a href="admin/dashboard.php">Dashboard</a>
                        <?php endif; ?>
                        <a href="auth/logout.php">Keluar</a>
                    </div>
                </div>
            <?php else: ?>
                <a href="auth/login.php" class="btn-masuk">Masuk</a>
            <?php endif; ?>
        </div>
        <button class="menu-toggle" id="menuToggle">☰</button>
    </nav>

    <section class="pub-hero">
        <div class="pub-hero-overlay"></div>
        <div class="pub-hero-content">
            <span class="pub-hero-badge">KABAR TERBARU</span>
            <h1 class="pub-hero-title">Publikasi UrFarm</h1>
            <p class="pub-hero-sub">Ikuti perkembangan program penanaman dan dampak positif yang telah kami ciptakan
                bersama</p>
        </div>
    </section>

    <section class="highlight-wrap">
        <div class="highlight-inner">
            <div class="highlight-card">
                <div class="highlight-img">
                    <img src="https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=600&q=80" alt="Mangrove">
                </div>
                <div class="highlight-body">
                    <span class="badge-gold">HIGHLIGHT UTAMA</span>
                    <h2 class="highlight-title">UrFarm &amp; GreenCo Berhasil Menanam 500 Pohon Mangrove di Pesisir
                        Sungai Code, Yogyakarta</h2>
                    <p class="highlight-desc">Program konservasi mangrove terbesar yang pernah dilakukan di kawasan
                        perkotaan Yogyakarta. Sebanyak 500 bibit mangrove jenis Rhizophora berhasil ditanam dengan
                        partisipasi lebih dari 200 relawan dan donatur.</p>
                    <div class="highlight-meta">
                        <span class="meta-info">01 April 2024</span>
                        <span class="meta-info">Tim UrFarm</span>
                        <a href="#" class="btn-green">Baca Selengkapnya</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="artikel-wrap">
        <div class="artikel-inner">
            <div class="artikel-grid">

                <div class="artikel-card">
                    <div class="artikel-img">
                        <img src="../assets/img2.jpeg" alt="pohon">
                    </div>
                    <div class="artikel-body">
                        <span class="artikel-date">25 Mar 2024</span>
                        <h3 class="artikel-title">Kolaborasi UrFarm dengan 5 Perusahaan untuk Tanam 1.000 Pohon Jati di
                            Gunungkidul</h3>
                        <p class="artikel-desc">Kerja sama strategis antara UrFarm dan lima perusahaan besar berhasil
                            menggerakkan program penanaman massal...</p>
                        <div class="artikel-footer">
                            <span class="artikel-author">Tim Media &amp; Publikasi UrFarm</span>
                            <a href="#" class="link-baca">Baca →</a>
                        </div>
                    </div>
                </div>

                <div class="artikel-card">
                    <div class="artikel-img">
                        <img src="../assets/img7.jpg" alt="bambu">
                    </div>
                    <div class="artikel-body">
                        <span class="artikel-date">14 Mar 2024</span>
                        <h3 class="artikel-title">Program Benih Bambu: Solusi Hijau untuk Lahan Kritis di Lereng Merapi
                        </h3>
                        <p class="artikel-desc">Bambu dipilih sebagai tanaman prioritas karena kemampuannya menyerap
                            karbon dioksida 35% lebih efisien...</p>
                        <div class="artikel-footer">
                            <span class="artikel-author">Tim Riset</span>
                            <a href="#" class="link-baca">Baca →</a>
                        </div>
                    </div>
                </div>

                <div class="artikel-card">
                    <div class="artikel-img">
                        <img src="../assets/img8.png" alt="Peta">
                    </div>
                    <div class="artikel-body">
                        <span class="artikel-date">10 Mar 2024</span>
                        <h3 class="artikel-title">Donatur UrFarm Kini Bisa Lacak Bibit Mereka Secara Real-Time di Peta
                        </h3>
                        <p class="artikel-desc">Fitur terbaru UrFarm memungkinkan setiap donatur melacak posisi tepat
                            bibit yang telah mereka donasikan...</p>
                        <div class="artikel-footer">
                            <span class="artikel-author">Tim Produk</span>
                            <a href="#" class="link-baca">Baca →</a>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <footer class="pub-footer">
        © 2026 UrFarm — <a href="landing.php">Kembali ke Beranda</a>
    </footer>

    <script>
        window.addEventListener('scroll', () => {
            document.getElementById('navbar').classList.toggle('scrolled', window.scrollY > 10);
        });
        document.getElementById('menuToggle').addEventListener('click', () => {
            document.getElementById('navLinks').classList.toggle('mobile-open');
        });
    </script>

</body>

</html>