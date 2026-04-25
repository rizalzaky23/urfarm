<?php
session_start();
$page = $_GET['page'] ?? 'home';
require_once '../config/connection.php';

// Ambil semua publikasi, urutkan dari terbaru
$query = "SELECT p.*, e.nama_evet AS nama_event, e.jenis_event 
          FROM publikasi p 
          LEFT JOIN event e ON p.id_event = e.id_event 
          ORDER BY p.tanggal_publikasi DESC";
$result = $conn->query($query);

$publikasi = [];
while ($row = $result->fetch_assoc()) {
    $publikasi[] = $row;
}

// Artikel pertama jadi highlight
$highlight = !empty($publikasi) ? array_shift($publikasi) : null;
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Publikasi - UrFarm</title>
    <link rel="stylesheet" href="../css/publikasi.css">
    <link rel="stylesheet" href="../css/style.css">
</head>

<body>

    <!-- NAVBAR -->
    <nav id="navbar">
        <div class="nav-brand">
    <div class="navbar-logo-icon"></div>
    <a href="?page=home" class="nav-logo">Ur<span>Farm</span></a>
</div>
        <div class="nav-links" id="navLinks">
            <a href="/urfarm/?page=home">Home</a>
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
                        <a href="admin/dashboard.php">Dashboard</a>
                        <?php endif; ?>
                        <a href="../auth/login.php" class="btn-masuk">Masuk</a>
                    </div>
                </div>
            <?php else: ?>
                <a href="../auth/login.php" class="btn-masuk">Masuk</a>
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

    <?php if ($highlight): ?>
    <section class="highlight-wrap">
        <div class="highlight-inner">
            <div class="highlight-card">
                <div class="highlight-img">
                    <?php
                    $hlImg = $highlight['gambar']
                        ? '../assets/publikasi/' . htmlspecialchars($highlight['gambar'])
                        : 'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=600&q=80';
                    ?>
                    <img src="<?= $hlImg ?>" alt="<?= htmlspecialchars($highlight['judul']) ?>">
                </div>
                <div class="highlight-body">
                    <span class="badge-gold">HIGHLIGHT UTAMA</span>
                    <h2 class="highlight-title"><?= htmlspecialchars($highlight['judul']) ?></h2>
                    <p class="highlight-desc"><?= htmlspecialchars(mb_strimwidth($highlight['isi'], 0, 200, '...')) ?></p>
                    <div class="highlight-meta">
                        <span class="meta-info"><?= date('d F Y', strtotime($highlight['tanggal_publikasi'])) ?></span>
                        <span class="meta-info"><?= htmlspecialchars($highlight['nama_event'] ?? 'Tim UrFarm') ?></span>
                        <a href="#" class="btn-green">Baca Selengkapnya</a>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <?php if (!empty($publikasi)): ?>
    <section class="artikel-wrap">
        <div class="artikel-inner">
            <div class="artikel-grid">

                <?php foreach ($publikasi as $artikel): ?>
                <div class="artikel-card">
                    <div class="artikel-img">
                        <?php
                        $artImg = $artikel['gambar']
                            ? '../assets/publikasi/' . htmlspecialchars($artikel['gambar'])
                            : '../assets/img2.jpeg';
                        ?>
                        <img src="<?= $artImg ?>" alt="<?= htmlspecialchars($artikel['judul']) ?>">
                    </div>
                    <div class="artikel-body">
                        <span class="artikel-date"><?= date('d M Y', strtotime($artikel['tanggal_publikasi'])) ?></span>
                        <h3 class="artikel-title"><?= htmlspecialchars($artikel['judul']) ?></h3>
                        <p class="artikel-desc"><?= htmlspecialchars(mb_strimwidth($artikel['isi'], 0, 120, '...')) ?></p>
                        <div class="artikel-footer">
                            <span class="artikel-author"><?= htmlspecialchars($artikel['nama_event'] ?? 'Tim UrFarm') ?></span>
                            <a href="#" class="link-baca">Baca →</a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>

            </div>
        </div>
    </section>
    <?php endif; ?>

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