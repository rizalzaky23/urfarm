<?php
// Simple routing
$page = $_GET['page'] ?? 'home';
$program = $_GET['program'] ?? '1';

// Handle form submissions
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'donasi':
                $amount = $_POST['amount'] ?? '';
                $name = $_POST['name'] ?? '';
                $message = "success:Terima kasih $name! Donasi Rp " . number_format($amount, 0, ',', '.') . " sedang diproses.";
                break;
            case 'partner':
                $company = $_POST['company'] ?? '';
                $message = "success:Terima kasih $company! Tim kami akan menghubungi Anda segera.";
                break;
            case 'contact':
                $email = $_POST['email'] ?? '';
                $message = "success:Pesan dari $email telah kami terima. Kami akan segera membalas!";
                break;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UrFarm - Tanam Bibit, Lestarikan Bumi Kita</title>
    <link
        href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=DM+Sans:wght@300;400;500;600&family=Sora:wght@600;700;800&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">

</head>

<body>

    <?php
    // Show notification from form submission
    if ($message) {
        [$type, $text] = explode(':', $message, 2);
        echo "<div class='notification' id='notif'><span class='icon'>✅</span><span>$text</span><button class='close-btn' onclick=\"document.getElementById('notif').remove()\">×</button></div>";
    }
    ?>

    <!-- NAVBAR -->
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
            <a href="?page=login" class="btn-masuk">Masuk</a>
        </div>
        <button class="menu-toggle" id="menuToggle">☰</button>
    </nav>

    <?php if ($page === 'home'): ?>

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
                    <button class="btn-primary" onclick="openModal('donasi')">Mulai dari Rp 10.000</button>
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
                        <button class="btn-white" onclick="openModal('donasi')">Mulai Donasi</button>
                        <a href="?page=program" class="btn-outline-white">Pelajari Lebih Lanjut</a>
                    </div>
                </div>
            </div>
        </section>

    <?php elseif ($page === 'program'): ?>

        <div class="page-header">
            <h1>Program <span>Kami</span></h1>
            <p>Tiga pilar utama untuk mewujudkan kelestarian alam Indonesia secara nyata dan terukur.</p>
        </div>

        <div class="content-section">
            <div class="program-detail-grid">
                <?php
                $programs = [
                    ['img' => 'https://images.unsplash.com/photo-1448375240586-882707db888b?w=600&q=80', 'tag' => 'KONSERVASI HUTAN', 'title' => 'Penghijauan Hutan Lindung', 'desc' => 'Menanam ribuan pohon di area hutan lindung yang gundul akibat deforestasi bersama komunitas lokal.', 'progress' => 72],
                    ['img' => 'https://images.unsplash.com/photo-1542601906990-b4d3fb778b09?w=600&q=80', 'tag' => 'PESISIR & LAUT', 'title' => 'Restorasi Mangrove Nusantara', 'desc' => 'Penanaman mangrove di sepanjang pesisir Indonesia untuk melindungi habitat dan garis pantai.', 'progress' => 55],
                    ['img' => 'https://images.unsplash.com/photo-1416879595882-3373a0480b5b?w=600&q=80', 'tag' => 'PERTANIAN', 'title' => 'Agroforestri Komunitas', 'desc' => 'Membantu petani lokal menerapkan sistem agroforestri yang ramah lingkungan dan produktif.', 'progress' => 38],
                ];
                foreach ($programs as $i => $p): ?>
                    <div class="program-detail-card">
                        <img src="<?= $p['img'] ?>" alt="<?= $p['title'] ?>">
                        <div class="program-detail-body">
                            <div class="program-detail-tag"><?= $p['tag'] ?></div>
                            <h3><?= $p['title'] ?></h3>
                            <p><?= $p['desc'] ?></p>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width:<?= $p['progress'] ?>%"></div>
                            </div>
                            <div class="progress-label"><span><?= $p['progress'] ?>% terpenuhi</span><span>Target: Rp
                                    150Jt</span></div>
                            <br>
                            <button class="btn-green" onclick="openModal('donasi')"
                                style="font-size:13px;padding:10px 20px;">Donasi Sekarang</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

    <?php elseif ($page === 'partner'): ?>

        <div class="page-header">
            <h1>Menjadi <span>Partner</span></h1>
            <p>Bergabunglah bersama 120+ perusahaan yang telah berkontribusi untuk kelestarian lingkungan Indonesia.</p>
        </div>

        <div class="content-section">
            <div class="partner-form">
                <h2 style="font-family:'Playfair Display',serif;font-size:28px;margin-bottom:8px;">Daftarkan Perusahaan Anda
                </h2>
                <p style="color:var(--gray-400);font-size:14px;margin-bottom:32px;">Isi formulir di bawah ini dan tim kami
                    akan menghubungi Anda dalam 1-2 hari kerja.</p>
                <form method="POST">
                    <input type="hidden" name="action" value="partner">
                    <div class="two-col">
                        <div class="form-group">
                            <label>Nama Perusahaan *</label>
                            <input type="text" name="company" placeholder="PT. Contoh Indonesia" required>
                        </div>
                        <div class="form-group">
                            <label>Industri *</label>
                            <select name="industry" required>
                                <option value="">Pilih Industri</option>
                                <option>Energi & Pertambangan</option>
                                <option>FMCG & Konsumer</option>
                                <option>Perbankan & Keuangan</option>
                                <option>Teknologi</option>
                                <option>Manufaktur</option>
                                <option>Lainnya</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Nama PIC *</label>
                            <input type="text" name="pic" placeholder="Nama lengkap" required>
                        </div>
                        <div class="form-group">
                            <label>Email Perusahaan *</label>
                            <input type="email" name="email" placeholder="pic@perusahaan.com" required>
                        </div>
                        <div class="form-group">
                            <label>Nomor Telepon</label>
                            <input type="tel" name="phone" placeholder="+62 8xx xxxx xxxx">
                        </div>
                        <div class="form-group">
                            <label>Budget CSR (approx.)</label>
                            <select name="budget">
                                <option value="">Pilih Range</option>
                                <option>
                                    < Rp 50 Juta</option>
                                <option>Rp 50 - 200 Juta</option>
                                <option>Rp 200 - 500 Juta</option>
                                <option>> Rp 500 Juta</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Pesan / Pertanyaan</label>
                        <textarea name="message" rows="4"
                            placeholder="Ceritakan tujuan dan harapan kolaborasi Anda..."></textarea>
                    </div>
                    <button type="submit" class="btn-form">Kirim Pendaftaran Partner</button>
                </form>
            </div>
        </div>

    <?php elseif ($page === 'publikasi'): ?>

        <div class="page-header">
            <h1>Publikasi & <span>Berita</span></h1>
            <p>Ikuti perkembangan terbaru program dan dampak nyata UrFarm untuk lingkungan.</p>
        </div>

        <div class="content-section">
            <div class="pub-grid">
                <?php
                $pubs = [
                    ['img' => 'https://images.unsplash.com/photo-1448375240586-882707db888b?w=600&q=80', 'tag' => 'LAPORAN', 'title' => '10.000 Bibit Berhasil Ditanam di Kalimantan Tengah', 'desc' => 'Bersama 200 relawan lokal, program Penghijauan Hutan Lindung mencapai milestone 10.000 bibit.', 'date' => '10 April 2026'],
                    ['img' => 'https://images.unsplash.com/photo-1542601906990-b4d3fb778b09?w=600&q=80', 'tag' => 'BERITA', 'title' => 'UrFarm Raih Penghargaan Lingkungan Nasional 2026', 'desc' => 'Kementerian LHK memberikan penghargaan kepada UrFarm atas kontribusi nyata dalam pelestarian.', 'date' => '5 Maret 2026'],
                    ['img' => 'https://images.unsplash.com/photo-1416879595882-3373a0480b5b?w=600&q=80', 'tag' => 'ARTIKEL', 'title' => 'Mengapa Agroforestri adalah Masa Depan Pertanian Indonesia', 'desc' => 'Petani di Jawa Tengah membuktikan bahwa agroforestri bisa meningkatkan hasil panen 40%.', 'date' => '20 Februari 2026'],
                    ['img' => 'https://images.unsplash.com/photo-1466692476868-aef1dfb1e735?w=600&q=80', 'tag' => 'KOLABORASI', 'title' => 'UrFarm & Pertamina: 5.000 Pohon untuk Sumatra', 'desc' => 'Program kolaborasi CSR Pertamina dan UrFarm berhasil menghijaukan 150 hektar lahan kritis.', 'date' => '15 Januari 2026'],
                    ['img' => 'https://images.unsplash.com/photo-1441974231531-c6227db76b6e?w=600&q=80', 'tag' => 'LAPORAN', 'title' => 'Transparansi Dana Q4 2025 — Rp 450 Juta Tersalurkan', 'desc' => 'Laporan keuangan kuartalan menunjukkan seluruh dana donasi tersalurkan tepat sasaran.', 'date' => '2 Januari 2026'],
                    ['img' => 'https://images.unsplash.com/photo-1518173946687-a4c8892bbd9f?w=600&q=80', 'tag' => 'KEGIATAN', 'title' => 'Webinar: Masa Depan Hutan Tropis Indonesia', 'desc' => 'Bersama pakar lingkungan, kami membahas tantangan dan solusi pelestarian hutan tropis.', 'date' => '10 Desember 2025'],
                ];
                foreach ($pubs as $p): ?>
                    <div class="pub-card">
                        <img src="<?= $p['img'] ?>" alt="<?= $p['title'] ?>">
                        <div class="pub-body">
                            <div class="pub-tag"><?= $p['tag'] ?></div>
                            <h3><?= $p['title'] ?></h3>
                            <p><?= $p['desc'] ?></p>
                            <div class="pub-date">📅 <?= $p['date'] ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

    <?php elseif ($page === 'about'): ?>

        <div class="page-header">
            <h1>Tentang <span>UrFarm</span></h1>
            <p>Platform donasi lingkungan yang transparan, terukur, dan berdampak nyata.</p>
        </div>
        <div class="content-section">
            <div class="about-grid">
                <div class="about-image">
                    <img src="https://images.unsplash.com/photo-1466692476868-aef1dfb1e735?w=800&q=80" alt="UrFarm">
                </div>
                <div>
                    <span class="about-badge">VISI & MISI</span>
                    <h2 class="about-title">Bersama Wujudkan <span>Indonesia Hijau</span></h2>
                    <p class="about-desc">UrFarm didirikan pada 2021 dengan misi menghubungkan donatur dengan program
                        pelestarian lingkungan yang nyata dan terverifikasi. Kami percaya bahwa setiap orang memiliki peran
                        dalam menjaga bumi untuk generasi mendatang.</p>
                    <div class="feature-list">
                        <div class="feature-item">
                            <div class="feature-icon">🎯</div>
                            <div>
                                <div class="feature-title">Visi Kami</div>
                                <div class="feature-desc">Menjadikan Indonesia sebagai negara dengan tutupan hutan terluas
                                    di Asia Tenggara pada 2040.</div>
                            </div>
                        </div>
                        <div class="feature-item">
                            <div class="feature-icon">🚀</div>
                            <div>
                                <div class="feature-title">Misi Kami</div>
                                <div class="feature-desc">Memobilisasi donasi masyarakat dan korporat untuk program
                                    penanaman yang berdampak dan terukur.</div>
                            </div>
                        </div>
                        <div class="feature-item">
                            <div class="feature-icon">💚</div>
                            <div>
                                <div class="feature-title">Nilai Kami</div>
                                <div class="feature-desc">Transparansi, akuntabilitas, dan dampak nyata dalam setiap program
                                    yang kami jalankan.</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    <?php elseif ($page === 'contact'): ?>

        <div class="page-header">
            <h1>Hubungi <span>Kami</span></h1>
            <p>Ada pertanyaan atau ingin berkolaborasi? Kami siap membantu Anda.</p>
        </div>
        <div class="content-section">
            <div class="contact-grid">
                <div class="contact-info">
                    <h2>Mari <span>Terhubung</span></h2>
                    <p>Tim UrFarm siap membantu Anda dengan pertanyaan seputar program donasi, kemitraan, atau informasi
                        lainnya. Respon dalam 1x24 jam kerja.</p>
                    <div class="contact-item">
                        <div class="contact-icon">📧</div>
                        <div>
                            <div class="contact-label">Email</div>
                            <div class="contact-value">hello@urfarm.id</div>
                        </div>
                    </div>
                    <div class="contact-item">
                        <div class="contact-icon">📱</div>
                        <div>
                            <div class="contact-label">WhatsApp</div>
                            <div class="contact-value">+62 851-3456-7890</div>
                        </div>
                    </div>
                    <div class="contact-item">
                        <div class="contact-icon">📍</div>
                        <div>
                            <div class="contact-label">Kantor</div>
                            <div class="contact-value">Jl. Sudirman No. 45, Jakarta Pusat 10220</div>
                        </div>
                    </div>
                </div>
                <div class="partner-form">
                    <form method="POST">
                        <input type="hidden" name="action" value="contact">
                        <div class="form-group"><label>Nama Lengkap</label><input type="text" name="name"
                                placeholder="Nama Anda" required></div>
                        <div class="form-group"><label>Email</label><input type="email" name="email"
                                placeholder="email@anda.com" required></div>
                        <div class="form-group"><label>Subjek</label><input type="text" name="subject"
                                placeholder="Pertanyaan tentang..."></div>
                        <div class="form-group"><label>Pesan</label><textarea name="pesan" rows="5"
                                placeholder="Tulis pesan Anda di sini..." required></textarea></div>
                        <button type="submit" class="btn-form">Kirim Pesan</button>
                    </form>
                </div>
            </div>
        </div>

    <?php elseif ($page === 'faq'): ?>

        <div class="page-header">
            <h1>FAQ — <span>Pertanyaan</span></h1>
            <p>Jawaban untuk pertanyaan yang paling sering ditanyakan.</p>
        </div>
        <div class="content-section" style="max-width:760px;">
            <?php
            $faqs = [
                ['q' => 'Bagaimana cara berdonasi di UrFarm?', 'a' => 'Klik tombol "Mulai Donasi" di mana saja di website ini, pilih nominal donasi mulai Rp 10.000, isi data diri, dan selesaikan pembayaran via transfer bank, e-wallet, atau kartu kredit.'],
                ['q' => 'Apakah donasi saya bisa dilacak?', 'a' => 'Ya! Setiap donasi akan menghasilkan kode unik yang bisa Anda gunakan untuk melacak perkembangan bibit yang ditanam melalui dashboard real-time kami.'],
                ['q' => 'Apakah UrFarm terdaftar secara legal?', 'a' => 'UrFarm adalah yayasan terdaftar di Kementerian Hukum dan HAM dengan nomor AHU-123/2021. Laporan keuangan kami diaudit secara independen setiap tahun.'],
                ['q' => 'Bagaimana cara menjadi partner korporat?', 'a' => 'Kunjungi halaman Partner, isi formulir pendaftaran, dan tim CSR kami akan menghubungi Anda dalam 2 hari kerja untuk mendiskusikan program kolaborasi.'],
                ['q' => 'Berapa minimum donasi yang bisa saya berikan?', 'a' => 'Minimum donasi adalah Rp 10.000, yang setara dengan menanam 1 bibit pohon. Tidak ada maksimum donasi!'],
            ];
            foreach ($faqs as $i => $f): ?>
                <div class="partner-form" style="margin-bottom:16px;cursor:pointer;"
                    onclick="this.querySelector('.faq-ans').style.display=this.querySelector('.faq-ans').style.display==='none'?'block':'none'">
                    <div style="font-weight:600;font-size:16px;display:flex;justify-content:space-between;align-items:center;">
                        <?= $f['q'] ?> <span>▾</span>
                    </div>
                    <div class="faq-ans"
                        style="display:none;margin-top:12px;color:var(--gray-400);font-size:14px;line-height:1.7;">
                        <?= $f['a'] ?></div>
                </div>
            <?php endforeach; ?>
        </div>

    <?php elseif ($page === 'login'): ?>

        <div class="auth-wrapper" style="padding-top:100px;">
            <div class="auth-card">
                <div class="auth-logo">
                    <span
                        style="font-family:'Playfair Display',serif;font-size:28px;font-weight:700;color:var(--green-dark);">Ur<span
                            style="color:var(--green-light)">Farm</span></span>
                </div>
                <h2 class="auth-title">Selamat Datang</h2>
                <p class="auth-subtitle">Masuk untuk melacak donasi dan dampak Anda</p>
                <form method="POST">
                    <input type="hidden" name="action" value="login">
                    <div class="form-group"><label>Email</label><input type="email" name="email"
                            placeholder="email@anda.com" required></div>
                    <div class="form-group"><label>Password</label><input type="password" name="password"
                            placeholder="••••••••" required></div>
                    <button type="submit" class="btn-form" style="margin-top:8px;">Masuk</button>
                </form>
                <div class="auth-switch">Belum punya akun? <a href="?page=register">Daftar sekarang</a></div>
                <div style="text-align:center;margin-top:10px;"><a href="#"
                        style="font-size:13px;color:var(--gray-400);text-decoration:none;">Lupa password?</a></div>
            </div>
        </div>

    <?php elseif ($page === 'register'): ?>

        <div class="auth-wrapper" style="padding-top:100px;">
            <div class="auth-card">
                <div class="auth-logo">
                    <span
                        style="font-family:'Playfair Display',serif;font-size:28px;font-weight:700;color:var(--green-dark);">Ur<span
                            style="color:var(--green-light)">Farm</span></span>
                </div>
                <h2 class="auth-title">Buat Akun</h2>
                <p class="auth-subtitle">Bergabung dan mulai perjalanan hijau Anda</p>
                <form method="POST">
                    <input type="hidden" name="action" value="register">
                    <div class="form-group"><label>Nama Lengkap</label><input type="text" name="name"
                            placeholder="Nama lengkap Anda" required></div>
                    <div class="form-group"><label>Email</label><input type="email" name="email"
                            placeholder="email@anda.com" required></div>
                    <div class="form-group"><label>Password</label><input type="password" name="password"
                            placeholder="Min. 8 karakter" required></div>
                    <div class="form-group"><label>Konfirmasi Password</label><input type="password" name="confirm"
                            placeholder="Ulangi password" required></div>
                    <button type="submit" class="btn-form" style="margin-top:8px;">Daftar Sekarang</button>
                </form>
                <div class="auth-switch">Sudah punya akun? <a href="?page=login">Masuk di sini</a></div>
            </div>
        </div>

    <?php endif; ?>

    <!-- FOOTER -->
    <footer>
        <div class="footer-grid">
            <div class="footer-brand">
                <a href="?page=home" class="nav-logo">Ur<span>Farm</span></a>
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
                    <a href="?page=home">Home</a>
                    <a href="?page=program">Program</a>
                    <a href="?page=partner">Partner</a>
                    <a href="?page=publikasi">Publikasi</a>
                </div>
            </div>
            <div>
                <div class="footer-heading">Bantuan</div>
                <div class="footer-links">
                    <a href="?page=faq">FAQ</a>
                    <a href="?page=contact">Hubungi Kami</a>
                    <a href="#">Kebijakan Privasi</a>
                    <a href="#">Syarat Penggunaan</a>
                </div>
            </div>
            <div>
                <div class="footer-heading">Ikuti Kami</div>
                <div class="social-links">
                    <a href="#" class="social-btn" title="Facebook">f</a>
                    <a href="#" class="social-btn" title="Instagram">ig</a>
                    <a href="#" class="social-btn" title="Twitter">tw</a>
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

    <!-- MODAL DONASI -->
    <div class="modal-overlay" id="modalDonasi">
        <div class="modal modal-wrapper">
            <button class="modal-close" onclick="closeModal('donasi')">×</button>
            <h2 class="modal-title">Mulai Donasi 🌱</h2>
            <p class="modal-subtitle">Setiap donasi menanam nyata. Pilih nominal atau masukkan jumlah lain.</p>
            <form method="POST">
                <input type="hidden" name="action" value="donasi">
                <div class="form-group">
                    <label>Pilih Nominal</label>
                    <div class="amount-grid">
                        <button type="button" class="amount-btn" onclick="setAmount(10000, this)">Rp 10.000</button>
                        <button type="button" class="amount-btn" onclick="setAmount(25000, this)">Rp 25.000</button>
                        <button type="button" class="amount-btn" onclick="setAmount(50000, this)">Rp 50.000</button>
                        <button type="button" class="amount-btn" onclick="setAmount(100000, this)">Rp 100.000</button>
                        <button type="button" class="amount-btn" onclick="setAmount(250000, this)">Rp 250.000</button>
                        <button type="button" class="amount-btn" onclick="setAmount(500000, this)">Rp 500.000</button>
                    </div>
                    <input type="number" name="amount" id="amountInput" placeholder="Atau masukkan nominal lain..."
                        min="10000">
                </div>
                <div class="form-group"><label>Nama Lengkap</label><input type="text" name="name"
                        placeholder="Nama Anda" required></div>
                <div class="form-group"><label>Email</label><input type="email" name="email"
                        placeholder="email@anda.com" required></div>
                <div class="form-group">
                    <label>Program</label>
                    <select name="program">
                        <option>Penghijauan Hutan Lindung</option>
                        <option>Restorasi Mangrove Nusantara</option>
                        <option>Agroforestri Komunitas</option>
                        <option>Donasi Umum (sesuai kebutuhan)</option>
                    </select>
                </div>
                <button type="submit" class="btn-form">Lanjutkan Donasi</button>
            </form>
        </div>
    </div>

    <script>
        // Navbar scroll effect
        window.addEventListener('scroll', () => {
            document.getElementById('navbar').classList.toggle('scrolled', window.scrollY > 10);
        });

        // Mobile menu
        document.getElementById('menuToggle').addEventListener('click', () => {
            document.getElementById('navLinks').classList.toggle('mobile-open');
        });

        // Tab switching
        function switchTab(n) {
            document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
            document.querySelectorAll('.tab-btn').forEach(el => el.classList.remove('active'));
            document.getElementById('tab' + n).classList.add('active');
            document.querySelectorAll('.tab-btn')[n - 1].classList.add('active');
        }

        // Modal
        function openModal(type) {
            document.getElementById('modalDonasi').classList.add('open');
            document.body.style.overflow = 'hidden';
        }
        function closeModal(type) {
            document.getElementById('modalDonasi').classList.remove('open');
            document.body.style.overflow = '';
        }
        document.getElementById('modalDonasi').addEventListener('click', function (e) {
            if (e.target === this) closeModal('donasi');
        });

        // Amount buttons
        function setAmount(val, btn) {
            document.getElementById('amountInput').value = val;
            document.querySelectorAll('.amount-btn').forEach(b => b.classList.remove('selected'));
            btn.classList.add('selected');
        }

        // Counter animation
        function animateCounter(id, target, prefix, suffix, duration) {
            const el = document.getElementById(id);
            if (!el) return;
            const start = performance.now();
            const update = (now) => {
                const progress = Math.min((now - start) / duration, 1);
                const val = Math.floor(progress * target);
                el.textContent = prefix + val.toLocaleString('id') + suffix;
                if (progress < 1) requestAnimationFrame(update);
            };
            requestAnimationFrame(update);
        }

        window.addEventListener('load', () => {
            if (document.getElementById('stat1')) {
                animateCounter('stat1', 42, '', '', 1500);
                animateCounter('stat2', 450, 'Rp ', 'Jt+', 2000);
                animateCounter('stat3', 120, '', '+', 1500);
            }
        });

        // Auto-hide notification
        <?php if ($message): ?>
            setTimeout(() => {
                const n = document.getElementById('notif');
                if (n) n.remove();
            }, 5000);
        <?php endif; ?>
    </script>

</body>

</html>