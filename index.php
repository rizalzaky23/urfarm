<?php
session_start();
$page = $_GET['page'] ?? 'home';
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UrFarm - Tanam Bibit, Lestarikan Bumi Kita</title>
    <link rel="stylesheet" href="css/style.css">

</head>

<body>



    <!-- NAVBAR -->
    <nav id="navbar">
        <div class="nav-brand">
    <div class="navbar-logo-icon"></div>
    <a href="?page=home" class="nav-logo">Ur<span>Farm</span></a>
</div>
        <div class="nav-links" id="navLinks">
            <a href="?page=home" <?= $page == 'home' ? 'class="active"' : '' ?>>Home</a>
            <a href="pages/program.php" <?= $page == 'program' ? 'class="active"' : '' ?>>Program</a>
            <a href="pages/partner.php" <?= $page == 'partner' ? 'class="active"' : '' ?>>Partner</a>
            <a href="pages/publikasi.php" <?= $page == 'publikasi' ? 'class="active"' : '' ?>>Publikasi</a>
            <div class="dropdown">
                <a href="#">Tentang ▾</a>
                <div class="dropdown-menu">
                    <a href="pages/about/tentang.php">Tentang Kami</a>
                    <a href="pages/about/contact.php">Hubungi Kami</a>
                    <a href="pages/about/faq.php">FAQ</a>
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



        <!-- HERO -->
        <section id="hero">
            <div class="hero-bg"></div>
            <div class="hero-overlay"></div>
            <div class="hero-content">
                <span class="hero-badge">Platform Lingkungan Indonesia</span>
                <h1 class="hero-title">
                    Tanam Bibit,
                    <span class="highlight">Lestarikan</span>
                    Bumi Kita
                </h1>
                <p class="hero-sub">Be the part of the green! Setiap donasi kamu menghijaukan bumi, satu bibit pada satu
                    waktu.</p>
                <div class="hero-buttons">

                    <a href="?page=program" class="btn-outline">Selengkapnya</a>
                </div>
            </div>
        </section>

        <!-- WAVE -->
        <div class="wave-divider">
            <svg viewBox="0 0 1440 60" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none"
                style="height:60px;width:100%;background:transparent;">
                <path d="M0,60 C360,0 1080,60 1440,0 L1440,60 Z" fill="#ffffff" />
            </svg>
        </div>

        <!-- STATS -->
        <section id="stats">
            <div class="stats-grid">
                <div class="stat-item">
                    <div class="stat-number" id="stat1">0</div>
                    <div class="stat-label">Program Aktif</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number gold" id="stat2">0</div>
                    <div class="stat-label">Dana Terhimpun</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number" id="stat3">0</div>
                    <div class="stat-label">Partner Kolaborasi</div>
                </div>
            </div>
        </section>

        <!-- ABOUT -->
        <section id="about">
            <div class="about-grid">
                <div class="about-image">
                    <img src="assets/img1.png" alt="Tanam Bibit">
                </div>
                <div>
                    <span class="about-badge">Tentang UrFarm</span>
                    <h2 class="about-title">Platform Donasi Lingkungan yang <span>Transparan & Terukur</span></h2>
                    <p class="about-desc">UrFarm menghubungkan donatur dengan penerima manfaat nyata di seluruh Indonesia.
                        Setiap rupiah yang kamu donasikan menjadi bibit nyata yang ditanam dan bisa kamu lacak secara
                        real-time.</p>
                    <div class="feature-list">
                        <div class="feature-item">
                            <div class="feature-icon">🌱</div>
                            <div>
                                <div class="feature-title">Tanam & Lacak</div>
                                <div class="feature-desc">Setiap donasi menghasilkan bibit asli dan kamu bisa tahu lacak di
                                    peta interaktif kami.</div>
                            </div>
                        </div>
                        <div class="feature-item">
                            <div class="feature-icon">💰</div>
                            <div>
                                <div class="feature-title">Dana Transparan</div>
                                <div class="feature-desc">Kelola dana berbasis akuntabel, donatur bisa memantau penggunaan
                                    setiap donasi secara langsung.</div>
                            </div>
                        </div>
                        <div class="feature-item">
                            <div class="feature-icon">🤝</div>
                            <div>
                                <div class="feature-title">Kolaborasi Luas</div>
                                <div class="feature-desc">Bersinergi dengan 120+ perusahaan dari berbagai industri untuk
                                    dampak yang lebih besar.</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- PROGRAMS -->
        <section id="programs">
            <div class="programs-inner">
                <span class="section-badge">RITA • REAL IMPACT THROUGH ACTION</span>
                <h2 class="section-title">Program <span>Kami</span></h2>
                <p class="section-subtitle">Melalui tiga pilar utama, UrFarm menggerakkan aksi nyata pelestarian lingkungan
                    Indonesia.</p>

                <div class="tabs">
                    <button class="tab-btn active" onclick="switchTab(1)">Program 1</button>
                    <button class="tab-btn" onclick="switchTab(2)">Program 2</button>
                    <button class="tab-btn" onclick="switchTab(3)">Program 3</button>
                </div>

                <div class="tab-content active" id="tab1">
                    <div class="program-card">
                        <div class="program-card-img">
                            <img src="assets/img1.png" alt="Hutan Lindung">
                        </div>
                        <div class="program-card-body">
                            <div class="program-tag">KONSERVASI HUTAN</div>
                            <h3 class="program-title">Penghijauan Hutan Lindung</h3>
                            <p class="program-desc">Bersama komunitas lokal, kami menanam ribuan pohon di area hutan lindung
                                yang gundul akibat deforestasi. Setiap donasi berkontribusi langsung pada pemulihan
                                ekosistem hutan.</p>
                            <a href="?page=program&detail=1" class="btn-green">Lihat Detail Program</a>
                        </div>
                    </div>
                </div>

                <div class="tab-content" id="tab2">
                    <div class="program-card">
                        <div class="program-card-img">
                            <img src="assets/img3.jpeg" alt="Mangrove">
                        </div>
                        <div class="program-card-body">
                            <div class="program-tag">PESISIR & LAUT</div>
                            <h3 class="program-title">Restorasi Mangrove Nusantara</h3>
                            <p class="program-desc">Program penanaman mangrove di sepanjang pesisir Indonesia untuk
                                melindungi garis pantai dari abrasi dan menjaga habitat ikan serta biota laut.</p>
                            <a href="?page=program&detail=2" class="btn-green">Lihat Detail Program</a>
                        </div>
                    </div>
                </div>

                <div class="tab-content" id="tab3">
                    <div class="program-card">
                        <div class="program-card-img">
                            <img src="assets/img4.jpeg" alt="Pertanian">
                        </div>
                        <div class="program-card-body">
                            <div class="program-tag">PERTANIAN BERKELANJUTAN</div>
                            <h3 class="program-title">Agroforestri Komunitas</h3>
                            <p class="program-desc">Membantu petani lokal menerapkan sistem agroforestri yang ramah
                                lingkungan sambil meningkatkan produktivitas lahan dan pendapatan keluarga petani.</p>
                            <a href="?page=program&detail=3" class="btn-green">Lihat Detail Program</a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- WAVE 2 -->
        <div class="wave-divider">
            <svg viewBox="0 0 1440 60" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none"
                style="height:60px;width:100%;background:transparent;">
                <path d="M0,60 C360,0 1080,60 1440,0 L1440,60 Z" fill="#ffffff" />
            </svg>
        </div>

        <!-- PARTNERS -->
        <section id="partners">
            <div class="partners-inner">
                <span class="section-badge" style="color:var(--green-light)">UNGGULAN • URFARM COLLABORATION</span>
                <h2 class="section-title-dark">Perusahaan yang Telah <span>Bergabung</span></h2>
                <p class="section-subtitle-dark">Bersama lebih dari 100 mitra yang turut berperan aktif dalam kelestarian
                    lingkungan.</p>

                <div class="partners-grid">
                    <div class="partner-logo" title="Pertamina">
                        <img src="assets/partner-assets/pertamina.png" alt="Pertamina">
                    </div>
                    <div class="partner-logo" title="Ecoware">
                        <img src="assets/partner-assets/ecoware.png" alt="Ecoware">
                    </div>
                    <div class="partner-logo" title="BCA">
                        <img src="assets/partner-assets/bca.png" alt="BCA">
                    </div>
                    <div class="partner-logo" title="Gojek">
                        <img src="assets/partner-assets/gojek.png" alt="Gojek">
                    </div>
                    <div class="partner-logo" title="Telkomsel">
                        <img src="assets/partner-assets/telkomsel.png" alt="Telkomsel">
                    </div>
                </div>

                <!-- CTA Banner -->
                <div class="cta-banner">
                    <div class="cta-content">
                        <h2 class="cta-title">Siap Menjadi Bagian dari Perubahan?</h2>
                        <p class="cta-desc">Login atau mulai donasi sekarang. Setiap bibit yang kamu tanam adalah langkah
                            nyata menuju bumi yang lebih hijau.</p>
                    </div>
                    <div class="cta-buttons">

                        <a href="?page=program" class="btn-outline-white">Pelajari Lebih Lanjut</a>
                    </div>
                </div>
            </div>
        </section>



<?php include 'footer.php'; ?>