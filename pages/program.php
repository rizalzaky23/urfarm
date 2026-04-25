<?php
session_start();
$page = $_GET['page'] ?? 'home';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Program - UrFarm</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../css/program.css" rel="stylesheet">
    
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

<section class="hero-section">
    <div class="hero-bg" style="background-image: url('../assets/bg4.png');"></div>
    <div class="hero-content">
        <div class="hero-badge">Kabar Terbaru</div>
        <h1 class="hero-title">Publikasi UrFarm</h1>
        <p class="hero-sub">Ikuti perkembangan program penanaman dan dampak positif yang telah kami ciptakan bersama</p>
    </div>
</section>

<section class="program-section">
    <div class="program-wrapper">

        <div class="program-card">
            <div class="text-center mb-1">
                <span class="category-badge">Konservasi Hutan</span>
            </div>
            <h2 class="program-title text-center">Penghijauan Hutan Lindung</h2>
            <div class="program-inner">
                <div class="program-img-col">
                    <div class="img-placeholder" style="background-image: url('../assets/img1.png');"></div>
                </div>
                <div class="program-text-col">
                    <p class="program-text">Program Penghijauan Hutan Lindung ini dirancang sebagai upaya nyata dalam memulihkan kawasan hutan yang mengalami kerusakan akibat aktivitas deforestasi. Melalui kolaborasi dengan komunitas lokal, relawan, serta pihak terkait, penanaman bibit dilakukan secara terencana dengan mempertimbangkan jenis tanaman yang sesuai dengan kondisi tanah dan ekosistem setempat. Tidak hanya menanam, program ini juga mencakup proses perawatan dan pemantauan pertumbuhan pohon agar keberlanjutan hutan dapat terjaga dalam jangka panjang. Dengan pendekatan ini, diharapkan hutan dapat kembali berfungsi sebagai penyangga kehidupan, menjaga keseimbangan iklim, serta menjadi habitat bagi berbagai flora dan fauna.</p>
                    <p class="program-text">Setiap donasi yang diberikan akan dialokasikan secara transparan untuk pembelian bibit, operasional penanaman, hingga perawatan pasca-tanam. Donatur juga turut berkontribusi dalam menciptakan dampak lingkungan yang lebih luas, seperti mengurangi emisi karbon, mencegah erosi tanah, serta meningkatkan kualitas udara dan sumber air di sekitar kawasan hutan. Program ini bukan hanya tentang menanam pohon, tetapi juga tentang membangun kesadaran bersama akan pentingnya menjaga lingkungan. Dengan partisipasi yang berkelanjutan, kita dapat menciptakan perubahan nyata dan mewariskan hutan yang lebih sehat bagi generasi mendatang.</p>
                </div>
            </div>
        </div>

        <div class="program-card">
            <div class="text-center mb-1">
                <span class="category-badge blue">Pesisir &amp; Laut</span>
            </div>
            <h2 class="program-title text-center">Restorasi Mangrove Nusantara</h2>
            <div class="program-inner">
                <div class="program-img-col">
                    <div class="img-placeholder" style="background-image: url('../assets/img3.jpeg');"></div>
                </div>
                <div class="program-text-col">
                    <p class="program-text">Program Restorasi Mangrove Nusantara merupakan inisiatif pelestarian lingkungan yang berfokus pada penanaman dan pemulihan ekosistem mangrove di berbagai wilayah pesisir Indonesia. Kegiatan ini dilakukan bersama masyarakat pesisir dan relawan dengan pendekatan yang terstruktur, mulai dari pemilihan bibit mangrove yang sesuai, penanaman di area rawan abrasi, hingga pemantauan pertumbuhan secara berkala. Mangrove memiliki peran penting sebagai benteng alami yang mampu meredam gelombang laut, mencegah abrasi, serta menjaga kestabilan ekosistem pesisir yang rentan terhadap perubahan iklim dan aktivitas manusia.</p>
                    <p class="program-text">Setiap donasi yang diberikan akan digunakan untuk mendukung seluruh proses restorasi, termasuk pengadaan bibit, operasional penanaman, serta perawatan pasca-tanam agar mangrove dapat tumbuh optimal. Selain memberikan perlindungan bagi garis pantai, program ini juga berkontribusi dalam menjaga habitat ikan, kepiting, dan berbagai biota laut lainnya yang bergantung pada ekosistem mangrove. Dengan keterlibatan bersama, program ini tidak hanya membantu memulihkan lingkungan, tetapi juga mendukung keberlanjutan ekonomi masyarakat pesisir yang bergantung pada sumber daya laut.</p>
                </div>
            </div>
        </div>

        <div class="program-card">
            <div class="text-center mb-1">
                <span class="category-badge green">Pertanian Berkelanjutan</span>
            </div>
            <h2 class="program-title text-center">Agroforestri Komunitas</h2>
            <div class="program-inner">
                <div class="program-img-col">
                    <div class="img-placeholder" style="background-image: url('../assets/img4.jpeg');"></div>
                </div>
                <div class="program-text-col">
                    <p class="program-text">Program Agroforestri Komunitas merupakan inisiatif pemberdayaan petani lokal melalui penerapan sistem pertanian terpadu yang mengombinasikan tanaman kehutanan dengan tanaman pangan atau komoditas ekonomi. Pendekatan ini dirancang untuk menjaga keseimbangan antara produktivitas lahan dan kelestarian lingkungan, sehingga tanah tetap subur, risiko erosi berkurang, serta keanekaragaman hayati tetap terjaga. Bersama masyarakat setempat, program ini juga mencakup pelatihan, pendampingan teknis, serta pemilihan jenis tanaman yang sesuai dengan kondisi lahan dan kebutuhan pasar.</p>
                    <p class="program-text">Setiap dukungan yang diberikan akan digunakan untuk pengadaan bibit, pelatihan petani, hingga proses pendampingan berkelanjutan agar sistem agroforestri dapat diterapkan secara optimal. Selain meningkatkan hasil panen, program ini juga membuka peluang diversifikasi sumber pendapatan bagi petani, sehingga mereka tidak bergantung pada satu jenis komoditas saja. Dengan model yang berkelanjutan, Agroforestri Komunitas tidak hanya memperkuat ketahanan ekonomi keluarga petani, tetapi juga berkontribusi dalam menjaga ekosistem dan kualitas lingkungan untuk jangka panjang.</p>
                </div>
            </div>
        </div>

    </div>
</section>

<footer class="footer-dark">
    <div class="container">
        <p>&copy; 2025 UrFarm &mdash; <a href="#">Kembali ke Beranda</a></p>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>