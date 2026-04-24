<?php
session_start();
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Partner — UrFarm</title>
    <link rel="stylesheet" href="../css/partner.css">
</head>

<body>

    <nav id="navbar">
        <a href="<?= isset($_SESSION['user_id']) ? '../landing.php' : '../index.php' ?>" class="nav-logo">Ur<span>Farm</span></a>
        <div class="nav-links" id="navLinks">
            <a href="<?= isset($_SESSION['user_id']) ? '../landing.php' : '../index.php?page=home' ?>" class="nav-a">Home</a>
            <a href="../index.php?page=program" class="nav-a">Program</a>
            <a href="partner.php" class="nav-a active">Partner</a>
            <a href="publikasi.php" class="nav-a">Publikasi</a>
            <div class="dropdown">
                <a href="#" class="nav-a">Bantuan ▾</a>
                <div class="dropdown-menu">
                    <a href="about/tentang.php" class="dd-a">Tentang Kami</a>
                    <a href="about/contact.php" class="dd-a">Hubungi Kami</a>
                    <a href="../index.php?page=faq" class="dd-a">FAQ</a>
                </div>
            </div>
            <?php if (isset($_SESSION['user_id'])): ?>
                <div class="dropdown">
                    <a href="#" class="nav-a">👤 <?= htmlspecialchars($_SESSION['user_nama']) ?> ▾</a>
                    <div class="dropdown-menu">
                        <?php if ($_SESSION['user_role'] === 'admin'): ?>
                            <a href="../admin/dashboard.php" class="dd-a">Dashboard</a>
                        <?php endif; ?>
                        <a href="../auth/logout.php" class="dd-a">Keluar</a>
                    </div>
                </div>
            <?php else: ?>
                <a href="../auth/login.php" class="btn-masuk">Masuk</a>
            <?php endif; ?>
        </div>
        <button class="menu-toggle" id="menuToggle">☰</button>
    </nav>

    <section class="partner-hero">
        <div class="partner-hero-overlay"></div>
        <div class="partner-hero-content">
            <span class="partner-hero-badge">KOLABORASI</span>
            <h1 class="partner-hero-title">Partner Kolaborasi UrFarm</h1>
            <p class="partner-hero-sub">Bersama mereka mewujudkan bumi hijau dengan UrFarm</p>
        </div>
    </section>

    <section class="partner-section">
        <div class="partner-grid">

            <a href="https://www.pertamina.com" target="_blank" class="partner-card" title="Pertamina">
                <img src="../assets/partner-assets/pertamina.png" alt="Pertamina">
            </a>

            <a href="https://www.bca.co.id" target="_blank" class="partner-card" title="BCA">
                <img src="../assets/partner-assets/bca.png" alt="BCA">
            </a>

            <a href="https://www.telkomsel.com" target="_blank" class="partner-card" title="Telkomsel">
                <img src="../assets/partner-assets/telkomsel.png" alt="Telkomsel">
            </a>

            <a href="https://www.greenpeace.org/indonesia/" target="_blank" class="partner-card" title="Greenpeace">
                <img src="../assets/partner-assets/greenpeace.png" alt="Greenpeace">
            </a>

            <a href="https://www.ecoware.id" target="_blank" class="partner-card" title="Ecoware">
                <img src="../assets/partner-assets/ecoware.png" alt="Ecoware">
            </a>

            <a href="https://www.gojek.com" target="_blank" class="partner-card" title="Gojek">
                <img src="../assets/partner-assets/gojek.png" alt="Gojek">
            </a>

            <a href="https://www.indomaret.co.id" target="_blank" class="partner-card" title="Indomaret">
                <img src="../assets/partner-assets/indomaret.png" alt="Indomaret">
            </a>

            <a href="https://www.bumn.go.id" target="_blank" class="partner-card" title="BUMN">
                <img src="../assets/partner-assets/bumn.png" alt="BUMN">
            </a>

        </div>
    </section>

    <footer class="partner-footer">
        © 2026 UrFarm — <a href="../landing.php">Kembali ke Beranda</a>
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
