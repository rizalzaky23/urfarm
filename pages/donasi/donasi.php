<?php
session_start();
require_once '../../config/connection.php';

// Hanya user yang sudah login yang bisa donasi
if (!isset($_SESSION['user_id'])) {
    header('Location: /project-urfarm/auth/login.php');
    exit;
}

// Ambil email user dari database (tidak disimpan di session)
$_user_email = '';
$_stmt = $conn->prepare('SELECT email FROM users WHERE id = ? LIMIT 1');
if ($_stmt) {
    $_stmt->bind_param('i', $_SESSION['user_id']);
    $_stmt->execute();
    $_stmt->bind_result($_user_email);
    $_stmt->fetch();
    $_stmt->close();
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donasi - UrFarm</title>
    <meta name="description" content="Donasikan untuk penghijauan Indonesia. Setiap Rp10.000 menghasilkan 1 bibit nyata.">
    <link rel="stylesheet" href="../../css/donasi.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body>

<!-- ===== NAVBAR ===== -->
<nav id="navbar">
    <div class="nav-brand">
        <div class="navbar-logo-icon"></div>
        <a href="/project-urfarm/landing.php" class="nav-logo">Ur<span>Farm</span></a>
    </div>
    <div class="nav-links" id="navLinks">
        <a href="/project-urfarm/landing.php">Home</a>
        <a href="/project-urfarm/pages/program.php">Program</a>
        <a href="/project-urfarm/pages/partner.php">Partner</a>
        <a href="/project-urfarm/pages/publikasi.php">Publikasi</a>
        <div class="dropdown">
            <a href="#">Tentang ▾</a>
            <div class="dropdown-menu">
                <a href="/project-urfarm/pages/about/tentang.php">Tentang Kami</a>
                <a href="/project-urfarm/pages/about/contact.php">Hubungi Kami</a>
                <a href="/project-urfarm/pages/about/faq.php">FAQ</a>
            </div>
        </div>
        <?php if (isset($_SESSION['user_id'])): ?>
            <div class="dropdown">
                <a href="#"><i class="bi bi-person-circle"></i> <?= htmlspecialchars($_SESSION['user_nama']) ?> ▾</a>
                <div class="dropdown-menu">
                    <?php if ($_SESSION['user_role'] === 'admin'): ?>
                        <a href="/project-urfarm/admin/dashboard.php">Dashboard</a>
                    <?php endif; ?>
                    <a href="/project-urfarm/pages/donasi/riwayat.php">Riwayat Donasi</a>
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
<div class="donasi-hero">
    <div class="donasi-hero-content">
        <h1 class="donasi-hero-title">Form Donasi UrFarm</h1>
        <p class="donasi-hero-sub">Setiap Rp10.000 yang kamu donasikan = 1 bibit yang kami tanam</p>
    </div>
</div>

<!-- ===== MAIN PAGE ===== -->
<div class="donasi-page" id="donasiPage">

    <!-- ===== LEFT: FORM ===== -->
    <div class="form-left">

        <!-- PROGRESS BAR -->
        <div class="progress-wrap">
            <div class="progress-step">
                <div class="step-circle done" id="circle-1">✓</div>
                <div class="step-label done" id="label-1">Data</div>
            </div>
            <div class="step-line done" id="line-1"></div>
            <div class="progress-step">
                <div class="step-circle active" id="circle-2">2</div>
                <div class="step-label active" id="label-2">Nominal</div>
            </div>
            <div class="step-line" id="line-2"></div>
            <div class="progress-step">
                <div class="step-circle" id="circle-3">3</div>
                <div class="step-label" id="label-3">Metode</div>
            </div>
            <div class="step-line" id="line-3"></div>
            <div class="progress-step">
                <div class="step-circle" id="circle-4">4</div>
                <div class="step-label" id="label-4">Konfirmasi</div>
            </div>
        </div>

        <!-- CARD 1: DATA DONATUR -->
        <div class="form-card" id="card-data">
            <div class="card-title">Data Donatur</div>
            <div class="field-row">
                <div class="field-group">
                    <label for="nama_donatur">Nama Lengkap</label>
                    <input type="text" id="nama_donatur" name="nama_donatur"
                           placeholder="Your Name"
                           value="<?= htmlspecialchars($_SESSION['user_nama'] ?? '') ?>"
                           oninput="updateSummary()">
                </div>
                <div class="field-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email"
                           placeholder="contoh: nama@email.com"
                           value="<?= htmlspecialchars($_user_email) ?>"
                           oninput="updateSummary()">
                </div>
            </div>
            <div class="field-row full">
                <div class="field-group">
                    <label for="pesan">Pesan <span style="font-weight:400;color:#9caea4;">(opsional)</span></label>
                    <textarea id="pesan" name="pesan" placeholder="Tulis pesanmu disini"></textarea>
                </div>
            </div>
        </div>

        <!-- CARD 2: NOMINAL DONASI -->
        <div class="form-card" id="card-nominal">
            <div class="card-title">Nominal Donasi</div>
            <div class="nominal-presets">
                <button class="preset-btn" onclick="setNominal(10000)" id="btn-10000">Rp 10.000</button>
                <button class="preset-btn selected" onclick="setNominal(50000)" id="btn-50000">Rp 50.000</button>
                <button class="preset-btn" onclick="setNominal(100000)" id="btn-100000">Rp 100.000</button>
                <button class="preset-btn" onclick="setNominal(250000)" id="btn-250000">Rp 250.000</button>
                <button class="preset-btn" onclick="setNominal(500000)" id="btn-500000">Rp 500.000</button>
            </div>
            <p class="or-label">Atau masukkan nominal lain</p>
            <div class="custom-nominal-wrap">
                <span class="rp-prefix">Rp</span>
                <input type="number" id="custom_nominal" min="10000" step="1000"
                       placeholder="50000"
                       value="50000"
                       oninput="handleCustomNominal(this.value)">
            </div>
            <div class="nominal-info" id="nominal-info">Rp 10.000 = 1 bibit</div>
        </div>

        <!-- CARD 3: METODE PEMBAYARAN -->
        <div class="form-card" id="card-metode">
            <div class="card-title">Pilih Metode Pembayaran</div>
            <div class="metode-tabs">
                <button class="metode-btn selected" onclick="setMetode('gopay')" id="btn-gopay">GoPay</button>
                <button class="metode-btn" onclick="setMetode('ovo')" id="btn-ovo">OVO</button>
                <button class="metode-btn" onclick="setMetode('dana')" id="btn-dana">DANA</button>
                <button class="metode-btn" onclick="setMetode('bca')" id="btn-bca">BCA</button>
                <button class="metode-btn" onclick="setMetode('qris')" id="btn-qris">QRIS</button>
            </div>

            <div class="metode-detail-box active" id="detail-gopay">
                <div class="metode-detail-label">GOPAY</div>
                <div class="metode-detail-number">0812-3456-7890</div>
                <div class="metode-detail-name">a/n UrFarm Indonesia</div>
            </div>
            <div class="metode-detail-box" id="detail-ovo">
                <div class="metode-detail-label">OVO</div>
                <div class="metode-detail-number">0812-3456-7890</div>
                <div class="metode-detail-name">a/n UrFarm Indonesia</div>
            </div>
            <div class="metode-detail-box" id="detail-dana">
                <div class="metode-detail-label">DANA</div>
                <div class="metode-detail-number">0812-3456-7890</div>
                <div class="metode-detail-name">a/n UrFarm Indonesia</div>
            </div>
            <div class="metode-detail-box" id="detail-bca">
                <div class="metode-detail-label">BCA</div>
                <div class="metode-detail-number">4567890</div>
                <div class="metode-detail-name">a/n UrFarm Indonesia</div>
            </div>
            <div class="metode-detail-box" id="detail-qris" style="text-align: center;">
                <div class="metode-detail-label">Scan QRIS</div>
                <img src="../../assets/qris1.png" alt="QRIS Code" style="width: 150px; height: 150px; margin: 12px auto; display: block; border-radius: 8px; border: 1px solid #edf2ef;">
                <div class="metode-detail-name">a/n UrFarm Indonesia</div>
            </div>

            <p class="transfer-note">
                Sertakan kode <strong id="kode-transfer">URF-PAY001</strong> sebagai berita transfer
            </p>
        </div>

        <!-- CARD 4: KONFIRMASI PEMBAYARAN -->
        <div class="form-card" id="card-konfirmasi">
            <div class="card-title">Konfirmasi Pembayaran</div>
            <p class="konfirmasi-hint">Setelah transfer, isi form konfirmasi berikut</p>

            <div class="field-row full">
                <div class="field-group">
                    <label for="konfirmasi_metode">Metode yang Digunakan *</label>
                    <select id="konfirmasi_metode" name="konfirmasi_metode">
                        <option value="gopay">GoPay</option>
                        <option value="ovo">OVO</option>
                        <option value="dana">DANA</option>
                        <option value="bca">BCA</option>
                        <option value="qris">QRIS</option>
                    </select>
                </div>
            </div>

            <div class="field-row full">
                <div class="field-group">
                    <label for="link_bukti">Link Bukti Pembayaran *</label>
                    <input type="url" id="link_bukti" name="link_bukti"
                           placeholder="https://imgur.com/...">
                    <span class="field-hint">Upload ke Imgur / Google Drive, lalu paste linknya</span>
                </div>
            </div>
            <div class="field-row full">
                <div class="field-group">
                    <label for="catatan">Catatan <span style="font-weight:400;color:#9caea4;">(opsional)</span></label>
                    <textarea id="catatan" name="catatan" placeholder="Informasi tambahan..."></textarea>
                </div>
            </div>

            <button class="btn-konfirmasi" id="btnSubmit" onclick="submitDonasi()">
                KONFIRMASI PEMBAYARAN
            </button>
            <p class="konfirmasi-note">Halaman berikutnya menampilkan ringkasan donasi kamu</p>
        </div>

    </div><!-- /form-left -->

    <!-- ===== RIGHT: RINGKASAN ===== -->
    <aside>
        <div class="sidebar-card">
            <div class="sidebar-title">Ringkasan Donasi</div>
            <div class="summary-row">
                <span class="summary-label">Donatur</span>
                <span class="summary-value" id="sum-nama">—</span>
            </div>
            <div class="summary-row">
                <span class="summary-label">Metode</span>
                <span class="summary-value" id="sum-metode">GoPay</span>
            </div>
            <div class="summary-row">
                <span class="summary-label">Nominal</span>
                <span class="summary-value" id="sum-nominal">Rp 50.000</span>
            </div>
            <hr class="summary-divider">
            <div class="bibit-box">
                <div class="bibit-label">Estimasi Bibit</div>
                <div class="bibit-count" id="sum-bibit">5</div>
                <div class="bibit-unit">Bibit</div>
                <div class="bibit-unit" style="font-size:11px;color:#9caea4;margin-top:2px;"
                     id="sum-bibit-rate">@ Rp 10.000 / bibit</div>
            </div>
            <p class="bibit-note" id="sum-co2">Setiap bibit berkontribusi pada pengurangan CO₂ sebesar 21 kg per tahun.</p>
        </div>
    </aside>

</div><!-- /donasi-page -->

<!-- SUCCESS PAGE -->
<div id="successSection" style="display:none;max-width:700px;margin:0 auto;padding:36px 24px 80px;">
    <div class="success-page" id="successBox">
        <div class="success-icon"><i class="bi bi-check-circle-fill" style="color: #2d6a4f; font-size: 52px;"></i></div>
        <div class="success-title">Terima Kasih, <span id="suc-nama"></span>!</div>
        <p class="success-sub">
            Donasi kamu sebesar <strong id="suc-nominal"></strong> sedang kami verifikasi.
            Estimasi <strong id="suc-bibit"></strong> bibit akan segera ditanam atas nama kamu.
        </p>
        <a href="/project-urfarm/landing.php" class="btn-back">Kembali ke Beranda</a>
    </div>
</div>

<!-- FOOTER -->
<footer class="donasi-footer">
    © 2026 UrFarm — <a href="/project-urfarm/landing.php">Kembali ke Beranda</a>
</footer>

<script>
// ===== STATE =====
let selectedNominal = 50000;
let selectedMetode  = 'gopay';
const BIBIT_PER_RUPIAH = 10000; // Rp10.000 = 1 bibit

// ===== PROGRESS BAR =====
function updateProgress(step) {
    for (let i = 1; i <= 4; i++) {
        const circle = document.getElementById('circle-' + i);
        const label  = document.getElementById('label-' + i);
        circle.className = 'step-circle';
        label.className  = 'step-label';
        if (i < step) {
            circle.classList.add('done');
            circle.textContent = '✓';
            label.classList.add('done');
        } else if (i === step) {
            circle.classList.add('active');
            circle.textContent = i;
            label.classList.add('active');
        } else {
            circle.textContent = i;
        }
    }
    for (let i = 1; i <= 3; i++) {
        const line = document.getElementById('line-' + i);
        line.className = 'step-line' + (i < step ? ' done' : '');
    }
}

// ===== NOMINAL =====
function setNominal(val) {
    selectedNominal = val;
    document.querySelectorAll('.preset-btn').forEach(b => b.classList.remove('selected'));
    const btn = document.getElementById('btn-' + val);
    if (btn) btn.classList.add('selected');
    document.getElementById('custom_nominal').value = val;
    updateProgress(3);
    updateSummary();
}

function handleCustomNominal(val) {
    selectedNominal = parseInt(val) || 0;
    document.querySelectorAll('.preset-btn').forEach(b => b.classList.remove('selected'));
    const presets = [10000, 50000, 100000, 250000, 500000];
    if (presets.includes(selectedNominal)) {
        const btn = document.getElementById('btn-' + selectedNominal);
        if (btn) btn.classList.add('selected');
    }
    updateSummary();
}

// ===== METODE =====
function setMetode(m) {
    selectedMetode = m;
    document.querySelectorAll('.metode-btn').forEach(b => b.classList.remove('selected'));
    document.getElementById('btn-' + m).classList.add('selected');
    document.querySelectorAll('.metode-detail-box').forEach(b => b.classList.remove('active'));
    document.getElementById('detail-' + m).classList.add('active');

    // sync konfirmasi select
    document.getElementById('konfirmasi_metode').value = m;

    // update kode transfer
    const kode = 'URF-' + m.toUpperCase().substring(0,3) + '001';
    document.getElementById('kode-transfer').textContent = kode;

    updateProgress(4);
    updateSummary();
}

// ===== SUMMARY SIDEBAR =====
function updateSummary() {
    const nama    = document.getElementById('nama_donatur').value || '—';
    const bibit   = selectedNominal >= BIBIT_PER_RUPIAH
                    ? Math.floor(selectedNominal / BIBIT_PER_RUPIAH)
                    : 0;
    const nominal = selectedNominal > 0
                    ? 'Rp ' + selectedNominal.toLocaleString('id')
                    : 'Rp 0';
    const metodeLabel = selectedMetode.toUpperCase();

    document.getElementById('sum-nama').textContent   = nama;
    document.getElementById('sum-metode').textContent = metodeLabel;
    document.getElementById('sum-nominal').textContent = nominal;
    document.getElementById('sum-bibit').textContent  = bibit;

}

// ===== SUBMIT =====
async function submitDonasi() {
    const nama    = document.getElementById('nama_donatur').value.trim();
    const email   = document.getElementById('email').value.trim();
    const link    = document.getElementById('link_bukti').value.trim();
    const jml     = selectedNominal;
    const metode  = document.getElementById('konfirmasi_metode').value;

    if (!nama)  { alert('Nama donatur wajib diisi.'); return; }
    if (!email) { alert('Email wajib diisi.'); return; }
    if (!link)  { alert('Link bukti pembayaran wajib diisi.'); return; }
    if (jml < 10000) { alert('Jumlah transfer minimal Rp 10.000.'); return; }

    const btn = document.getElementById('btnSubmit');
    btn.disabled = true;
    btn.textContent = 'Mengirim...';

    const payload = new FormData();
    payload.append('nama_donatur', nama);
    payload.append('email',        email);
    payload.append('pesan',        document.getElementById('pesan').value.trim());
    payload.append('nominal',      selectedNominal);
    payload.append('metode',       metode);
    payload.append('jumlah_transfer', jml);
    payload.append('link_bukti',   link);
    payload.append('catatan',      document.getElementById('catatan').value.trim());

    try {
        const res  = await fetch('handler.php', { method: 'POST', body: payload });
        const data = await res.json();

        if (data.success) {
            // Show success
            document.getElementById('donasiPage').style.display = 'none';
            const sec = document.getElementById('successSection');
            sec.style.display = 'block';
            document.getElementById('successBox').style.display = 'block';
            document.getElementById('suc-nama').textContent    = nama;
            document.getElementById('suc-nominal').textContent  = 'Rp ' + selectedNominal.toLocaleString('id');
            document.getElementById('suc-bibit').textContent   = Math.floor(selectedNominal / 10000) + ' bibit';
        } else {
            alert('Gagal: ' + (data.message || 'Terjadi kesalahan.'));
            btn.disabled = false;
            btn.textContent = 'KONFIRMASI PEMBAYARAN';
        }
    } catch(e) {
        alert('Koneksi gagal. Silakan coba lagi.');
        btn.disabled = false;
        btn.textContent = 'KONFIRMASI PEMBAYARAN';
    }
}

// ===== INIT =====
window.addEventListener('DOMContentLoaded', () => {
    // Navbar scroll
    window.addEventListener('scroll', () => {
        document.getElementById('navbar').classList.toggle('scrolled', window.scrollY > 10);
    });
    document.getElementById('menuToggle').addEventListener('click', () => {
        document.getElementById('navLinks').classList.toggle('mobile-open');
    });

    // Set initial progress to step 2 (nominal)
    updateProgress(2);
    updateSummary();

    // Watch nama input for progress step 1
    document.getElementById('nama_donatur').addEventListener('input', () => {
        const nama = document.getElementById('nama_donatur').value.trim();
        const email = document.getElementById('email').value.trim();
        if (nama && email) updateProgress(2);
        else updateProgress(1);
        updateSummary();
    });
    document.getElementById('email').addEventListener('input', () => {
        const nama = document.getElementById('nama_donatur').value.trim();
        const email = document.getElementById('email').value.trim();
        if (nama && email) updateProgress(2);
        else updateProgress(1);
        updateSummary();
    });
});
</script>

</body>
</html>
