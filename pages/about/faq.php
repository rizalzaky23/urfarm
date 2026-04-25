<?php
session_start();
$page = $_GET['page'] ?? 'home';
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FAQ - UrFarm</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../css/faq.css">
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
            <a href="/urfarm/pages/program.php" <?= $page == 'program' ? 'class="active"' : '' ?>>Program</a>
        <a href="/urfarm/pages/partner.php" <?= $page == 'partner' ? 'class="active"' : '' ?>>Partner</a>
        <a href="/urfarm/pages/publikasi.php" <?= $page == 'publikasi' ? 'class="active"' : '' ?>>Publikasi</a>
            <div class="dropdown">
                <a href="#">Tentang ▾</a>
                <div class="dropdown-menu">
                    <a href="/urfarm/pages/about/tentang.php">Tentang Kami</a>
                <a href="/urfarm/pages/about/contact.php">Hubungi Kami</a>
                <a href="/urfarm/pages/about/faq.php">FAQ</a>
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

    <div class="faq-page-wrap">

        <div class="faq-title-badge">SERING DITANYAKAN</div>

        <div class="faq-list">

            <div class="faq-item">
                <div class="faq-question" onclick="toggleFaq(this)">
                    <span class="faq-question-text">Apa itu UrFarm?</span>
                    <span class="faq-icon">∧</span>
                </div>
                <div class="faq-answer open">
                    <p>UrFarm hadir sebagai platform donasi lingkungan yang menghubungkan masyarakat dengan program
                        penghijauan dan pelestarian alam di berbagai wilayah di Indonesia. Melalui website ini, setiap
                        donatur dapat menyumbang bibit pohon sekaligus melacak secara digital di mana bibit tersebut
                        ditanam, kondisi lokasi, dan perkembangan pohonnya dari waktu ke waktu. Pendekatan berbasis
                        teknologi digital ini diharapkan mampu meningkatkan transparansi, memperkuat kepercayaan publik,
                        sekaligus mendorong partisipasi aktif masyarakat dalam pelestarian lingkungan melalui pengalaman
                        donasi yang interaktif dan dapat dilacak.</p>
                </div>
            </div>

            <div class="faq-item">
                <div class="faq-question" onclick="toggleFaq(this)">
                    <span class="faq-question-text">Bagaimana cara berdonasi?</span>
                    <span class="faq-icon">∨</span>
                </div>
                <div class="faq-answer">
                    <p>Untuk berdonasi di UrFarm, kamu cukup membuat akun, memilih program yang ingin kamu dukung, lalu
                        menentukan jumlah donasi. Kami menyediakan berbagai metode pembayaran seperti transfer bank,
                        dompet digital, dan kartu kredit untuk kemudahan transaksi kamu.</p>
                </div>
            </div>

            <div class="faq-item">
                <div class="faq-question" onclick="toggleFaq(this)">
                    <span class="faq-question-text">Berapa minimal donasi?</span>
                    <span class="faq-icon">∨</span>
                </div>
                <div class="faq-answer">
                    <p>Minimal donasi di UrFarm adalah Rp 10.000 per transaksi. Dengan nominal tersebut, kamu sudah
                        berkontribusi nyata dalam program penghijauan dan pelestarian lingkungan di Indonesia.</p>
                </div>
            </div>

            <div class="faq-item">
                <div class="faq-question" onclick="toggleFaq(this)">
                    <span class="faq-question-text">Bagaimana cara melacak bibit saya?</span>
                    <span class="faq-icon">∨</span>
                </div>
                <div class="faq-answer">
                    <p>Setelah berdonasi, kamu akan mendapatkan kode unik benih. Gunakan kode tersebut di fitur Lacak
                        Benih pada halaman utama untuk melihat status, lokasi tanam, dan perkembangan bibit yang kamu
                        donasikan secara real-time melalui peta interaktif kami.</p>
                </div>
            </div>

        </div>

    </div>

    <footer class="faq-footer">
        © 2026 UrFarm — <a href="<?= isset($_SESSION['user_id']) ? '../landing.php' : '../index.php' ?>">Kembali ke
            Beranda</a>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        window.addEventListener('scroll', () => {
            document.getElementById('navbar').classList.toggle('scrolled', window.scrollY > 10);
        });

        document.getElementById('menuToggle').addEventListener('click', () => {
            document.getElementById('navLinks').classList.toggle('mobile-open');
        });

        function toggleFaq(questionEl) {
            const item = questionEl.closest('.faq-item');
            const answer = item.querySelector('.faq-answer');
            const icon = questionEl.querySelector('.faq-icon');
            const isOpen = answer.classList.contains('open');

            document.querySelectorAll('.faq-answer').forEach(a => a.classList.remove('open'));
            document.querySelectorAll('.faq-icon').forEach(i => i.textContent = '∨');

            if (!isOpen) {
                answer.classList.add('open');
                icon.textContent = '∧';
            }
        }
    </script>

</body>

</html>