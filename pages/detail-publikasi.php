<?php
session_start();
require_once '../config/connection.php';

$id = $_GET['id'] ?? null;
if (!$id) {
    header("Location: publikasi.php");
    exit;
}

$stmt = $conn->prepare("
    SELECT p.*, e.nama_evet AS nama_event, e.jenis_event 
    FROM publikasi p 
    LEFT JOIN event e ON p.id_event = e.id_event 
    WHERE p.id_publikasi = ?
");
$stmt->bind_param("s", $id);
$stmt->execute();
$result = $stmt->get_result();
$artikel = $result->fetch_assoc();

if (!$artikel) {
    header("Location: publikasi.php");
    exit;
}

$page = 'publikasi'; // For active navbar tab
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($artikel['judul']) ?> - UrFarm</title>
    <link rel="stylesheet" href="../css/detail-publikasi.css">
</head>
<body>

    <!-- NAVBAR -->
    <nav id="navbar">
        <div class="nav-brand">
            <div class="navbar-logo-icon"></div>
            <a href="<?= isset($_SESSION['user_id']) ? '/project-urfarm/landing.php' : '/project-urfarm/index.php' ?>" class="nav-logo">Ur<span>Farm</span></a>
        </div>
        <div class="nav-links" id="navLinks">
            <a href="<?= isset($_SESSION['user_id']) ? '/project-urfarm/landing.php' : '/project-urfarm/index.php' ?>">Home</a>
            <a href="program.php" <?= $page == 'program' ? 'class="active"' : '' ?>>Program</a>
            <a href="partner.php" <?= $page == 'partner' ? 'class="active"' : '' ?>>Partner</a>
            <a href="publikasi.php" <?= $page == 'publikasi' ? 'class="active"' : '' ?>>Publikasi</a>
            <div class="dropdown">
                <a href="#">Tentang ▾</a>
                <div class="dropdown-menu">
                    <a href="about/tentang.php">Tentang Kami</a>
                    <a href="about/contact.php">Hubungi Kami</a>
                    <a href="about/faq.php">FAQ</a>
                </div>
            </div>
            <?php if (isset($_SESSION['user_id'])): ?>
                <div class="dropdown">
                    <a href="#">👤 <?= htmlspecialchars($_SESSION['user_nama']) ?> ▾</a>
                    <div class="dropdown-menu">
                        <?php if ($_SESSION['user_role'] === 'admin'): ?>
                        <a href="/project-urfarm/admin/dashboard.php">Dashboard</a>
                        <?php endif; ?>
                        <a href="/project-urfarm/pages/riwayat_donasi.php">Riwayat Donasi</a>
                        <a href="/project-urfarm/auth/logout.php">Keluar</a>
                    </div>
                </div>
            <?php else: ?>
                <a href="/project-urfarm/auth/login.php" class="btn-masuk">Masuk</a>
            <?php endif; ?>
        </div>
        <button class="menu-toggle" id="menuToggle">☰</button>
    </nav>

    <!-- HERO -->
    <section class="detail-hero">
        <span class="detail-badge"><?= htmlspecialchars($artikel['jenis_event'] ?? 'Publikasi') ?></span>
        <h1 class="detail-title"><?= htmlspecialchars($artikel['judul']) ?></h1>
        <div class="detail-meta">
            <div class="meta-item">
                📅 <?= date('d F Y', strtotime($artikel['tanggal_publikasi'])) ?>
            </div>
            <div class="meta-item">
                👤 <?= htmlspecialchars($artikel['nama_event'] ?? 'Tim UrFarm') ?>
            </div>
        </div>
    </section>

    <!-- IMAGE -->
    <div class="detail-img-wrap">
        <?php
        $artImg = $artikel['gambar']
            ? '../assets/publikasi/' . htmlspecialchars($artikel['gambar'])
            : '../assets/img2.jpeg';
        
        // In case the path doesn't exist, fallback to general assets
        if (!file_exists($artImg) && $artikel['gambar']) {
            $artImg = '../assets/' . htmlspecialchars($artikel['gambar']);
        }
        ?>
        <img src="<?= $artImg ?>" alt="<?= htmlspecialchars($artikel['judul']) ?>">
    </div>

    <!-- CONTENT -->
    <section class="detail-content">
        <p><?= nl2br(htmlspecialchars($artikel['isi'])) ?></p>
    </section>

    <!-- FOOTER ACTIONS -->
    <div class="detail-footer-actions">
        <a href="publikasi.php" class="btn-back">← Kembali ke Publikasi</a>
    </div>

    <footer class="pub-footer">
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
