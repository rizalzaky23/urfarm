<?php
session_start();
$page = $_GET['page'] ?? 'home';
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tentang Kami - UrFarm</title>
    <link rel="stylesheet" href="../../css/tentang.css">
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
    <!-- HERO -->
    <section class="tentang-hero">
        <div class="tentang-hero-overlay"></div>
        <div class="tentang-hero-content">
            <span class="tentang-hero-badge">URFARM</span>
            <h1 class="tentang-hero-title">Tentang Kami</h1>
            <p class="tentang-hero-sub">Mengenal lebih dekat misi dan perjalanan UrFarm dalam melestarikan lingkungan Indonesia</p>
        </div>
    </section>

    <!-- CONTENT -->
    <div class="tentang-content">
        <div class="tentang-card">
            <p>
                UrFarm adalah platform donasi lingkungan berbasis digital yang menghubungkan para donatur dengan program penghijauan nyata di seluruh Indonesia. Didirikan pada tahun 2023, UrFarm lahir dari keprihatinan mendalam terhadap laju deforestasi yang terus meningkat di Indonesia — negara dengan hutan tropis terbesar ketiga di dunia.
            </p>
            <p>
                Setiap donasi yang masuk melalui UrFarm langsung dialokasikan untuk pembelian bibit, proses penanaman, dan pemeliharaan pohon di berbagai lokasi strategis. Kami bekerja sama dengan komunitas lokal, petani, dan relawan untuk memastikan setiap bibit yang ditanam tumbuh dengan baik dan memberikan dampak positif bagi ekosistem sekitarnya.
            </p>
            <p>
                Yang membedakan UrFarm dari platform lainnya adalah transparansi penuh. Setiap donatur dapat melacak bibit yang telah mereka donasikan secara real-time melalui peta interaktif, mengetahui lokasi tanam, jenis pohon, dan status pertumbuhannya. Kami percaya bahwa transparansi adalah kunci kepercayaan, dan kepercayaan adalah fondasi dari gerakan kolektif yang berkelanjutan.
            </p>
            <p>
                Hingga saat ini, UrFarm telah berhasil menanam lebih dari 700.000 bibit pohon di berbagai wilayah Indonesia, bekerja sama dengan lebih dari 120 perusahaan dan organisasi, serta melibatkan ribuan relawan dan donatur dari seluruh penjuru negeri. Visi kami sederhana namun ambisius: menjadikan Indonesia lebih hijau, satu bibit pada satu waktu.
            </p>
        </div>
    </div>

    <!-- GALERI -->
    <section class="galeri-section">
        <div class="galeri-badge">
            <span>GALERI KAMI</span>
        </div>

        <div class="carousel-wrapper">
            <div class="carousel-track" id="carouselTrack">
                <div class="carousel-slide">
                    <img src="../../assets/img1.png" alt="Kegiatan Penanaman 1">
                </div>
                <div class="carousel-slide">
                    <img src="../../assets/img2.jpeg" alt="Kegiatan Penanaman 2">
                </div>
                <div class="carousel-slide">
                    <img src="../../assets/img3.jpeg" alt="Restorasi Mangrove">
                </div>
                <div class="carousel-slide">
                    <img src="../../assets/img4.jpeg" alt="Agroforestri">
                </div>
                <div class="carousel-slide">
                    <img src="../../assets/img5.png" alt="Hutan dari Atas">
                </div>
                <div class="carousel-slide">
                    <img src="../../assets/img6.jpg" alt="Bibit Pohon">
                </div>
            </div>
        </div>

        <div class="carousel-nav">
            <button class="carousel-btn" id="prevBtn">‹</button>
            <div class="carousel-dots" id="carouselDots"></div>
            <button class="carousel-btn" id="nextBtn">›</button>
        </div>
    </section>

    <!-- FOOTER -->
    <footer class="tentang-footer">
        © 2026 UrFarm — <a href="../../landing.php">Kembali ke Beranda</a>
    </footer>

    <script>
        // Navbar scroll
        window.addEventListener('scroll', () => {
            document.getElementById('navbar').classList.toggle('scrolled', window.scrollY > 10);
        });
        document.getElementById('menuToggle').addEventListener('click', () => {
            document.getElementById('navLinks').classList.toggle('mobile-open');
        });

        // Carousel
        const track = document.getElementById('carouselTrack');
        const slides = track.querySelectorAll('.carousel-slide');
        const dotsContainer = document.getElementById('carouselDots');
        const prevBtn = document.getElementById('prevBtn');
        const nextBtn = document.getElementById('nextBtn');

        let currentIndex = 0;
        let slidesPerView = 3;
        let autoSlideInterval;

        function updateSlidesPerView() {
            if (window.innerWidth <= 560) slidesPerView = 1;
            else if (window.innerWidth <= 900) slidesPerView = 2;
            else slidesPerView = 3;
        }

        function getMaxIndex() {
            return Math.max(0, slides.length - slidesPerView);
        }

        function buildDots() {
            dotsContainer.innerHTML = '';
            const totalDots = getMaxIndex() + 1;
            for (let i = 0; i < totalDots; i++) {
                const dot = document.createElement('button');
                dot.className = 'carousel-dot' + (i === currentIndex ? ' active' : '');
                dot.addEventListener('click', () => goTo(i));
                dotsContainer.appendChild(dot);
            }
        }

        function goTo(index) {
            currentIndex = Math.max(0, Math.min(index, getMaxIndex()));
            const slideWidth = slides[0].offsetWidth + 24; // gap
            track.style.transform = `translateX(-${currentIndex * slideWidth}px)`;
            // Update dots
            document.querySelectorAll('.carousel-dot').forEach((d, i) => {
                d.classList.toggle('active', i === currentIndex);
            });
        }

        function next() {
            if (currentIndex >= getMaxIndex()) goTo(0);
            else goTo(currentIndex + 1);
        }

        function prev() {
            if (currentIndex <= 0) goTo(getMaxIndex());
            else goTo(currentIndex - 1);
        }

        function startAutoSlide() {
            stopAutoSlide();
            autoSlideInterval = setInterval(next, 4000);
        }

        function stopAutoSlide() {
            clearInterval(autoSlideInterval);
        }

        prevBtn.addEventListener('click', () => { prev(); startAutoSlide(); });
        nextBtn.addEventListener('click', () => { next(); startAutoSlide(); });

        // Pause on hover
        track.addEventListener('mouseenter', stopAutoSlide);
        track.addEventListener('mouseleave', startAutoSlide);

        // Init
        function init() {
            updateSlidesPerView();
            if (currentIndex > getMaxIndex()) currentIndex = getMaxIndex();
            buildDots();
            goTo(currentIndex);
            startAutoSlide();
        }

        window.addEventListener('resize', init);
        init();
    </script>

</body>

</html>
