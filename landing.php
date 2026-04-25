<?php
session_start();
$page = $_GET['page'] ?? 'home';

// Proteksi: harus login untuk mengakses
if (!isset($_SESSION['user_id'])) {
    header('Location: auth/login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UrFarm - Tanam Pohon, Tanam Kebaikan, Selamatkan Bumi</title>
    <meta name="description"
        content="Platform donasi lingkungan terpercaya untuk penghijauan Indonesia. Setiap donasi menjadi bibit nyata yang bisa kamu lacak.">
    <link rel="stylesheet" href="css/landing.css">
    <!-- Leaflet CSS (OpenStreetMap - gratis, no API key) -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
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

    <!-- ===== HERO ===== -->
    <section id="hero">
        <div class="hero-bg"></div>
        <div class="hero-overlay"></div>
        <div class="hero-content">
            <span class="hero-badge">Selamat Datang, UFam!</span>
            <h1 class="hero-title">
                Tanam Pohon,<br>
                <span class="highlight">Tanam Kebaikan,</span><br>
                Selamatkan Bumi
            </h1>
            <p class="hero-sub">
                Bergabunglah bersama ribuan donatur yang telah berkontribusi nyata dalam pelestarian lingkungan
                Indonesia — satu bibit pada satu waktu.
            </p>
            <div class="hero-buttons">
                <a href="#" class="btn-donasi">Donasi Sekarang</a>
            </div>
        </div>
        <!-- Wave bottom -->
        <div class="hero-wave">
            <svg viewBox="0 0 1440 80" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none">
                <path d="M0,80 C480,20 960,60 1440,20 L1440,80 Z" fill="#ffffff" />
            </svg>
        </div>
    </section>

    <!-- ===== STATS ===== -->
    <section id="stats">
        <div class="stats-grid">
            <div class="stat-item">
                <div class="stat-number" id="stat1">0</div>
                <div class="stat-label">Program Aktif</div>
            </div>
            <div class="stat-divider"></div>
            <div class="stat-item">
                <div class="stat-number gold" id="stat2">0</div>
                <div class="stat-label">Total Donatur</div>
            </div>
            <div class="stat-divider"></div>
            <div class="stat-item">
                <div class="stat-number" id="stat3">0</div>
                <div class="stat-label">Dana Terhimpun</div>
            </div>
        </div>
    </section>

    <!-- ===== ABOUT ===== -->
    <section id="about">
        <div class="about-container">
            <!-- Left: Text -->
            <div class="about-text">
                <span class="badge-pill badge-green">Kenapa Penting?</span>
                <h2 class="about-heading">
                    Tentang Penanaman Pohon di <span>Indonesia</span>
                </h2>
                <p class="about-desc">
                    Indonesia merupakan negara dengan keanekaragaman hayati terbesar ke-2 di dunia setelah Brazil. Namun
                    setiap harinya 684 hektar hutan kita hilang akibat deforestasi dan alih fungsi lahan hingga sehingga
                    ekosistem penting kita terus berkurang.
                </p>
                <div class="mini-stats-row">
                    <div class="mini-stat">
                        <div class="mini-num">29,7 Jt</div>
                        <div class="mini-label">Hutan Lindung Ha</div>
                    </div>
                    <div class="mini-stat">
                        <div class="mini-num">29,3 Jt</div>
                        <div class="mini-label">Area Pertanian Ha</div>
                    </div>
                    <div class="mini-stat">
                        <div class="mini-num">27,4 Jt</div>
                        <div class="mini-label">Hutan Pertanian Ha</div>
                    </div>
                </div>
                <a href="#" class="btn-contribute">Mari Berkontribusi</a>
            </div>
            <!-- Right: Image -->
            <div class="about-img-wrap">
                <img src="assets/img5.png" alt="Hutan Indonesia dari atas">
            </div>
        </div>
    </section>

    <!-- ===== WHY PLANT ===== -->
    <section id="why-plant">
        <div class="why-inner">
            <span class="badge-pill badge-outline">Kenapa Harus Menanam Pohon?</span>
            <div class="why-grid">
                <div class="why-card">
                    <div class="why-icon-wrap">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#2d6a4f" stroke-width="2.2"
                            stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 22V12" />
                            <path d="M12 12C12 7 7 4 3 6" />
                            <path d="M12 12C12 7 17 4 21 6" />
                        </svg>
                    </div>
                    <span class="why-text">Meningkatkan Cadangan Air</span>
                </div>
                <div class="why-card">
                    <div class="why-icon-wrap">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#2d6a4f" stroke-width="2.2"
                            stroke-linecap="round" stroke-linejoin="round">
                            <path
                                d="M9.59 4.59A2 2 0 1 1 11 8H2m10.59 11.41A2 2 0 1 0 14 16H2m15.73-8.27A2.5 2.5 0 1 1 19.5 12H2" />
                        </svg>
                    </div>
                    <span class="why-text">Meningkatkan Kualitas Udara</span>
                </div>
                <div class="why-card">
                    <div class="why-icon-wrap">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#2d6a4f" stroke-width="2.2"
                            stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10" />
                            <path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20" />
                            <path d="M2 12h20" />
                        </svg>
                    </div>
                    <span class="why-text">Menjaga Ekosistem Lingkungan</span>
                </div>
                <div class="why-card">
                    <div class="why-icon-wrap">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#2d6a4f" stroke-width="2.2"
                            stroke-linecap="round" stroke-linejoin="round">
                            <path
                                d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z" />
                        </svg>
                    </div>
                    <span class="why-text">Mempertahankan Keanekaragaman Hayati</span>
                </div>
            </div>
        </div>
    </section>

    <!-- ===== TRACK SEED ===== -->
    <section id="track-seed">
        <div class="track-inner">
            <span class="badge-pill badge-outline">Lacak Bibit</span>
            <h2 class="track-heading">Cek Status <span>Benih Kamu</span></h2>
            <p class="track-sub">Masukkan kode benih yang sudah kamu donasikan untuk melacak statusnya</p>
            <div class="track-form-wrap">
                <div class="track-form">
                    <input type="text" id="seed-code" class="track-input" placeholder="KODE-BENIH" autocomplete="off">
                    <button class="btn-lacak" id="btn-lacak">Lacak Benih</button>
                </div>
            </div>
            <div id="track-result" class="track-result" style="display:none;"></div>
        </div>
    </section>

    <!-- ===== MAP ===== -->
    <section id="map-section">
        <div class="map-inner">
            <span class="badge-pill badge-outline">Peta Penanaman</span>
            <h2 class="map-heading">Jadikan Bumi Kita <span>Sejuk &amp; Alami</span> Kembali</h2>
            <p class="map-sub">700.456 penanaman bibit UrFarm di seluruh Indonesia</p>
            <div class="map-wrap">
                <div id="gmap"></div>
            </div>
            <!-- Legend -->
            <div class="map-legend">
                <div class="legend-item">
                    <span class="legend-dot tumbuh"></span> Tumbuh Aktif
                </div>
                <div class="legend-item">
                    <span class="legend-dot benih"></span> Benih Baru
                </div>
            </div>
        </div>
    </section>


    <!-- ===== FOOTER ===== -->
    <footer>
        <div class="footer-grid">
            <div class="footer-brand">
                <a href="landing.php" class="footer-logo">Ur<span>Farm</span></a>
                <p class="footer-desc">Platform donasi lingkungan untuk mendorong penghijauan dan pelestarian alam
                    Indonesia.</p>
                <div class="footer-contact">
                    📧 hello@urfarm.id<br>
                    📱 +62 851-3456-7890
                </div>
            </div>
            <div>
                <div class="footer-heading">Navigasi</div>
                <div class="footer-links">
                    <a href="landing.php">Home</a>
                    <a href="#">Program</a>
                    <a href="#">Partner</a>
                    <a href="pages/publikasi.php">Publikasi</a>
                </div>
            </div>
            <div>
                <div class="footer-heading">Bantuan</div>
                <div class="footer-links">
                    <a href="#">FAQ</a>
                    <a href="pages/about/contact.php">Hubungi Kami</a>
                    <a href="#">Kebijakan Privasi</a>
                    <a href="#">Syarat Penggunaan</a>
                </div>
            </div>
            <div>
                <div class="footer-heading">Ikuti Kami</div>
                <div class="social-links">
                    <a href="#" class="social-btn" title="Facebook">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z" />
                        </svg>
                    </a>
                    <a href="#" class="social-btn" title="Instagram">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="2" y="2" width="20" height="20" rx="5" ry="5" />
                            <path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z" />
                            <line x1="17.5" y1="6.5" x2="17.51" y2="6.5" />
                        </svg>
                    </a>
                    <a href="#" class="social-btn" title="Twitter/X">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor">
                            <path
                                d="M23 3a10.9 10.9 0 0 1-3.14 1.53 4.48 4.48 0 0 0-7.86 3v1A10.66 10.66 0 0 1 3 4s-4 9 5 13a11.64 11.64 0 0 1-7 2c9 5 20 0 20-11.5a4.5 4.5 0 0 0-.08-.83A7.72 7.72 0 0 0 23 3z" />
                        </svg>
                    </a>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <span>© 2026 UrFarm. Hak Cipta Dilindungi.</span>
            <div style="display:flex;gap:20px;">
                <a href="#">Privasi</a>
                <a href="#">Ketentuan</a>
            </div>
        </div>
    </footer>

    <script>
        // Navbar scroll effect
        window.addEventListener('scroll', () => {
            document.getElementById('navbar').classList.toggle('scrolled', window.scrollY > 10);
        });

        // Mobile menu
        document.getElementById('menuToggle').addEventListener('click', () => {
            document.getElementById('navLinks').classList.toggle('mobile-open');
        });

        // Counter animation with Intersection Observer
        function animateCounter(id, target, prefix, suffix, duration) {
            const el = document.getElementById(id);
            if (!el) return;
            const start = performance.now();
            const update = (now) => {
                const progress = Math.min((now - start) / duration, 1);
                const eased = 1 - Math.pow(1 - progress, 3); // ease-out cubic
                const val = Math.floor(eased * target);
                el.textContent = prefix + val.toLocaleString('id') + suffix;
                if (progress < 1) requestAnimationFrame(update);
            };
            requestAnimationFrame(update);
        }

        let statsAnimated = false;
        const statsObserver = new IntersectionObserver((entries) => {
            if (entries[0].isIntersecting && !statsAnimated) {
                statsAnimated = true;
                animateCounter('stat1', 42, '', '', 1400);
                animateCounter('stat2', 1240, '', '+', 1800);
                animateCounter('stat3', 450, 'Rp ', 'Jt', 1800);
            }
        }, { threshold: 0.4 });
        statsObserver.observe(document.getElementById('stats'));

        // Track seed
        function lacakBenih() {
            const code = document.getElementById('seed-code').value.trim().toUpperCase();
            const result = document.getElementById('track-result');

            if (!code) {
                result.style.display = 'block';
                result.className = 'track-result error';
                result.innerHTML = '⚠️ Harap masukkan kode benih terlebih dahulu.';
                return;
            }

            result.style.display = 'block';
            result.className = 'track-result loading';
            result.innerHTML = '<span class="spinner"></span> Memuat data benih...';

            setTimeout(() => {
                result.className = 'track-result success';
                result.innerHTML = `
            <div class="result-row">
                <span class="result-label">Kode Benih</span>
                <strong>${code}</strong>
            </div>
            <div class="result-row">
                <span class="result-label">Lokasi</span>
                <span>Kalimantan Tengah, Kab. Barito Selatan</span>
            </div>
            <div class="result-row">
                <span class="result-label">Status</span>
                <span class="status-badge">🌱 Tumbuh Aktif</span>
            </div>
            <div class="result-row">
                <span class="result-label">Tanggal Tanam</span>
                <span>15 Februari 2026</span>
            </div>
        `;
            }, 1500);
        }

        document.getElementById('btn-lacak').addEventListener('click', lacakBenih);
        document.getElementById('seed-code').addEventListener('keydown', (e) => {
            if (e.key === 'Enter') lacakBenih();
        });

        // Scroll-reveal cards
        const revealEls = document.querySelectorAll('.why-card, .mini-stat, .about-img-wrap');
        const revealObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('revealed');
                    revealObserver.unobserve(entry.target);
                }
            });
        }, { threshold: 0.15 });
        revealEls.forEach(el => revealObserver.observe(el));
    </script>

    <!-- ===== LEAFLET JS + OPENSTREETMAP (GRATIS) ===== -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
    window.addEventListener('load', async function() {
        // Inisialisasi peta Leaflet dengan OpenStreetMap
        const map = L.map('gmap', {
            center: [-2.5, 118.0],
            zoom: 6,
            zoomControl: true,
            attributionControl: true,
        });

        // Layer tile OpenStreetMap (gratis, no API key)
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright" target="_blank">OpenStreetMap</a> contributors',
            maxZoom: 19,
        }).addTo(map);

        // Force Leaflet recalculate ukuran container (fix blank map)
        setTimeout(() => map.invalidateSize(), 100);

        // Fungsi buat custom SVG marker
        function createIcon(isTumbuh) {
            const color  = isTumbuh ? '#2d6a4f' : '#d4a84b';
            const shadow = isTumbuh ? 'rgba(45,106,79,0.35)' : 'rgba(212,168,75,0.35)';
            const svg = `
                <svg xmlns="http://www.w3.org/2000/svg" width="30" height="42" viewBox="0 0 30 42">
                    <filter id="sh"><feDropShadow dx="0" dy="2" stdDeviation="2" flood-color="${shadow}"/></filter>
                    <path filter="url(#sh)"
                        d="M15 1C8.373 1 3 6.373 3 13c0 8.5 12 27 12 27S27 21.5 27 13C27 6.373 21.627 1 15 1z"
                        fill="${color}" stroke="white" stroke-width="2"/>
                    <circle cx="15" cy="13" r="5" fill="white" opacity="0.9"/>
                </svg>`;
            return L.divIcon({
                html: svg,
                className: '',
                iconSize:   [30, 42],
                iconAnchor: [15, 42],
                popupAnchor:[0, -40],
            });
        }

        // Fungsi buat konten popup
        function buildPopup(d) {
            const isTumbuh = d.status === 'tumbuh';
            const tanggal  = d.tanggal
                ? new Date(d.tanggal).toLocaleDateString('id-ID', { day:'numeric', month:'long', year:'numeric' })
                : '—';
            const badge = isTumbuh
                ? `<span style="background:#d8f3dc;color:#1a4a35;padding:2px 10px;border-radius:20px;font-size:11px;font-weight:700;">🌱 Tumbuh Aktif</span>`
                : `<span style="background:#fff3cd;color:#856404;padding:2px 10px;border-radius:20px;font-size:11px;font-weight:700;">🌿 Benih Baru</span>`;

            return `
                <div style="font-family:'DM Sans',sans-serif;min-width:220px;padding:2px;">
                    <div style="display:flex;align-items:center;gap:10px;margin-bottom:10px;padding-bottom:10px;border-bottom:1px solid #eef2f0;">
                        <div style="width:32px;height:32px;border-radius:50%;background:#d8f3dc;display:flex;align-items:center;justify-content:center;font-size:15px;flex-shrink:0;">🌳</div>
                        <div>
                            <div style="font-weight:700;color:#1a4a35;font-size:13px;line-height:1.2;">${d.nama_event || 'Event Penanaman'}</div>
                            <div style="font-size:11px;color:#8a9e94;">${d.jenis_event || ''}</div>
                        </div>
                    </div>
                    <table style="width:100%;font-size:12px;border-collapse:collapse;">
                        <tr><td style="color:#8a9e94;padding:3px 0;width:95px;">ID Titik</td>   <td style="font-weight:600;color:#1e2d27;">${d.id_titik}</td></tr>
                        <tr><td style="color:#8a9e94;padding:3px 0;">Lokasi</td>      <td style="color:#1e2d27;">${d.lokasi || '—'}</td></tr>
                        <tr><td style="color:#8a9e94;padding:3px 0;">Status</td>      <td>${badge}</td></tr>
                        <tr><td style="color:#8a9e94;padding:3px 0;">Jenis Pohon</td><td style="color:#1e2d27;">${d.jenis_pohon || '—'} <em style="color:#8a9e94;font-size:10px;">(${d.nama_pohon || ''})</em></td></tr>
                        <tr><td style="color:#8a9e94;padding:3px 0;">Jumlah Bibit</td><td style="font-weight:600;color:#2d6a4f;">${d.jumlah_bibit ? parseInt(d.jumlah_bibit).toLocaleString('id') + ' bibit' : '—'}</td></tr>
                        <tr><td style="color:#8a9e94;padding:3px 0;">Tgl. Tanam</td> <td style="color:#1e2d27;">${tanggal}</td></tr>
                    </table>
                </div>`;
        }

        // Ambil data dari API dan tambah marker
        try {
            const res  = await fetch('api/titik_lokasi.php');
            const json = await res.json();
            if (!json.success) return;

            json.markers.forEach(d => {
                const lat = parseFloat(d.latitude);
                const lng = parseFloat(d.longitude);
                if (isNaN(lat) || isNaN(lng)) return;

                const isTumbuh = d.status === 'tumbuh';
                const marker   = L.marker([lat, lng], {
                    icon:  createIcon(isTumbuh),
                    title: d.lokasi || d.id_titik,
                });

                // Tooltip saat hover
                marker.bindTooltip(
                    `<strong>${d.id_titik}</strong><br>${d.lokasi || ''}`,
                    { direction: 'top', offset: [0, -38], className: 'urfarm-tooltip' }
                );

                // Popup saat diklik
                marker.bindPopup(buildPopup(d), {
                    maxWidth: 280,
                    className: 'urfarm-popup',
                });

                marker.addTo(map);
            });
        } catch (err) {
            console.error('Gagal memuat titik lokasi:', err);
        }
    });
    </script>

</body>

</html>