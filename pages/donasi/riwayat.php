<?php
/**
 * riwayat.php — Riwayat Donasi User
 * Menampilkan semua riwayat donasi milik user yang sedang login
 */
session_start();
require_once '../../config/connection.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /project-urfarm/auth/login.php');
    exit;
}

$id_users = (int) $_SESSION['user_id'];

// Ambil semua donasi milik user ini, beserta info alokasi jika ada
$stmt = $conn->prepare("
    SELECT
        d.id_donasi,
        d.nama_donatur,
        d.nominal,
        d.metode,
        d.jumlah_transfer,
        d.estimasi_bibit,
        d.status,
        d.link_bukti,
        d.pesan,
        d.catatan,
        d.created_at,
        a.id_alokasi,
        a.id_penanaman,
        a.nominal AS nominal_dialokasikan
    FROM donasi d
    LEFT JOIN alokasi_dana a ON a.id_donasi = d.id_donasi
    WHERE d.id_users = ?
    ORDER BY d.created_at DESC
");
$stmt->bind_param('i', $id_users);
$stmt->execute();
$result = $stmt->get_result();
$riwayat = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$conn->close();

// Helper: format rupiah
function rupiah(int $n): string {
    return 'Rp ' . number_format($n, 0, ',', '.');
}

// Helper: badge status
function statusBadge(string $status): string {
    return match($status) {
        'verified'  => '<span class="badge badge-verified"><i class="bi bi-check-circle-fill"></i> Terverifikasi</span>',
        'rejected'  => '<span class="badge badge-rejected"><i class="bi bi-x-circle-fill"></i> Ditolak</span>',
        default     => '<span class="badge badge-pending"><i class="bi bi-clock-fill"></i> Menunggu Verifikasi</span>',
    };
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Donasi — UrFarm</title>
    <meta name="description" content="Lihat semua riwayat donasi kamu di UrFarm.">
    <link rel="stylesheet" href="../../css/donasi.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        /* ===== RIWAYAT-SPECIFIC STYLES ===== */
        .riwayat-wrap {
            max-width: 900px;
            margin: 0 auto;
            padding: 36px 24px 80px;
        }
        .riwayat-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 28px;
            flex-wrap: wrap;
            gap: 12px;
        }
        .riwayat-title {
            font-family: 'Sora', sans-serif;
            font-size: 24px;
            font-weight: 700;
            color: #1a4a35;
        }
        .btn-donasi-new {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 11px 22px;
            background: #2d6a4f;
            color: white;
            border-radius: 10px;
            font-weight: 700;
            font-size: 14px;
            text-decoration: none;
            transition: all 0.2s;
        }
        .btn-donasi-new:hover { background: #1a4a35; transform: translateY(-1px); }

        /* Stats row */
        .riwayat-stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
            margin-bottom: 28px;
        }
        .rstat-card {
            background: white;
            border-radius: 14px;
            padding: 20px 22px;
            border: 1px solid #edf2ef;
            box-shadow: 0 2px 10px rgba(26,74,53,0.05);
        }
        .rstat-label { font-size: 12px; color: #9caea4; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 6px; }
        .rstat-value { font-family: 'Sora', sans-serif; font-size: 26px; font-weight: 700; color: #1a4a35; }
        .rstat-value.gold { color: #c9a84c; }
        .rstat-value.green { color: #2d6a4f; }

        /* Cards */
        .donasi-card {
            background: white;
            border-radius: 16px;
            padding: 24px;
            border: 1px solid #edf2ef;
            box-shadow: 0 2px 12px rgba(26,74,53,0.05);
            margin-bottom: 16px;
            transition: box-shadow 0.2s;
        }
        .donasi-card:hover { box-shadow: 0 4px 20px rgba(26,74,53,0.10); }
        .card-top {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            margin-bottom: 16px;
            gap: 12px;
            flex-wrap: wrap;
        }
        .card-id { font-size: 12px; color: #9caea4; margin-bottom: 4px; }
        .card-nominal { font-family: 'Sora', sans-serif; font-size: 20px; font-weight: 700; color: #1a4a35; }
        .card-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 16px;
            margin-bottom: 16px;
        }
        .meta-item { display: flex; flex-direction: column; gap: 2px; }
        .meta-label { font-size: 11px; color: #9caea4; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600; }
        .meta-value { font-size: 14px; font-weight: 600; color: #3d4f47; }
        .card-divider { border: none; border-top: 1px solid #edf2ef; margin: 14px 0; }

        /* Alokasi info */
        .alokasi-tag {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #edf7f1;
            color: #2d6a4f;
            border-radius: 8px;
            padding: 6px 12px;
            font-size: 12px;
            font-weight: 600;
        }

        /* Badge */
        .badge { display: inline-block; padding: 5px 12px; border-radius: 20px; font-size: 12px; font-weight: 700; }
        .badge-verified { background: #d8f3dc; color: #1a4a35; }
        .badge-rejected { background: #ffe4e4; color: #c0392b; }
        .badge-pending  { background: #fff8e1; color: #b8860b; }

        /* Bukti link */
        .btn-bukti {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: 13px;
            color: #2d6a4f;
            font-weight: 600;
            text-decoration: none;
            transition: color 0.2s;
        }
        .btn-bukti:hover { color: #1a4a35; text-decoration: underline; }

        /* Empty state */
        .empty-state {
            text-align: center;
            padding: 80px 24px;
            background: white;
            border-radius: 16px;
            border: 1px solid #edf2ef;
        }
        .empty-icon { font-size: 52px; margin-bottom: 16px; }
        .empty-title { font-family: 'Sora', sans-serif; font-size: 20px; font-weight: 700; color: #1a4a35; margin-bottom: 10px; }
        .empty-sub { color: #9caea4; font-size: 14px; margin-bottom: 24px; }

        /* Metode chip */
        .metode-chip {
            display: inline-block;
            background: #f0faf5;
            border: 1px solid #c2e8d4;
            color: #2d6a4f;
            border-radius: 6px;
            padding: 3px 10px;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        @media (max-width: 700px) {
            .riwayat-stats { grid-template-columns: 1fr 1fr; }
            .card-top { flex-direction: column; }
        }
        @media (max-width: 480px) {
            .riwayat-stats { grid-template-columns: 1fr; }
        }
    </style>
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
<div class="donasi-hero" style="min-height:150px;">
    <div class="donasi-hero-content" style="padding:44px 24px 36px;">
        <h1 class="donasi-hero-title">Riwayat Donasi</h1>
        <p class="donasi-hero-sub">Pantau seluruh kontribusi hijau kamu bersama UrFarm</p>
    </div>
</div>

<!-- ===== CONTENT ===== -->
<div class="riwayat-wrap">

    <div class="riwayat-header">
        <div class="riwayat-title">
            Hai, <?= htmlspecialchars($_SESSION['user_nama']) ?>! <i class="bi bi-emoji-smile"></i>
        </div>
        <a href="/project-urfarm/pages/donasi/donasi.php" class="btn-donasi-new">
            <i class="bi bi-plus-circle"></i> Donasi Lagi
        </a>
    </div>

    <?php
    // Hitung statistik
    $total_donasi   = count($riwayat);
    $total_nominal  = 0;
    $total_bibit    = 0;
    $verified_count = 0;
    foreach ($riwayat as $r) {
        $total_nominal += $r['nominal'];
        $total_bibit   += $r['estimasi_bibit'];
        if ($r['status'] === 'verified') $verified_count++;
    }
    ?>

    <!-- STATS -->
    <?php if ($total_donasi > 0): ?>
    <div class="riwayat-stats">
        <div class="rstat-card">
            <div class="rstat-label">Total Donasi</div>
            <div class="rstat-value"><?= $total_donasi ?></div>
        </div>
        <div class="rstat-card">
            <div class="rstat-label">Total Nominal</div>
            <div class="rstat-value gold" style="font-size:18px;"><?= rupiah($total_nominal) ?></div>
        </div>
        <div class="rstat-card">
            <div class="rstat-label">Estimasi Bibit</div>
            <div class="rstat-value green"><?= number_format($total_bibit, 0, ',', '.') ?> <i class="bi bi-tree"></i></div>
        </div>
    </div>
    <?php endif; ?>

    <!-- DAFTAR DONASI -->
    <?php if (empty($riwayat)): ?>
        <div class="empty-state">
            <div class="empty-icon"><i class="bi bi-tree" style="font-size: 52px; color: #2d6a4f;"></i></div>
            <div class="empty-title">Belum Ada Riwayat Donasi</div>
            <p class="empty-sub">Kamu belum pernah melakukan donasi. Yuk, mulai berkontribusi untuk bumi yang lebih hijau!</p>
            <a href="/project-urfarm/pages/donasi/donasi.php" class="btn-donasi-new">Donasi Sekarang</a>
        </div>
    <?php else: ?>
        <?php foreach ($riwayat as $r): ?>
        <div class="donasi-card">
            <div class="card-top">
                <div>
                    <div class="card-id">#<?= str_pad($r['id_donasi'], 5, '0', STR_PAD_LEFT) ?> &bull; <?= date('d M Y, H:i', strtotime($r['created_at'])) ?> WIB</div>
                    <div class="card-nominal"><?= rupiah($r['nominal']) ?></div>
                </div>
                <div><?= statusBadge($r['status']) ?></div>
            </div>

            <div class="card-meta">
                <div class="meta-item">
                    <span class="meta-label">Metode</span>
                    <span class="meta-value"><span class="metode-chip"><?= strtoupper(htmlspecialchars($r['metode'])) ?></span></span>
                </div>
                <div class="meta-item">
                    <span class="meta-label">Estimasi Bibit</span>
                    <span class="meta-value"><i class="bi bi-tree-fill"></i> <?= number_format($r['estimasi_bibit'], 0, ',', '.') ?> bibit</span>
                </div>
                <?php if ($r['id_alokasi']): ?>
                <div class="meta-item">
                    <span class="meta-label">Dialokasikan</span>
                    <span class="meta-value">
                        <span class="alokasi-tag"><i class="bi bi-geo-alt-fill"></i> <?= htmlspecialchars($r['id_penanaman'] ?? '—') ?></span>
                    </span>
                </div>
                <?php endif; ?>
            </div>

            <?php if ($r['pesan'] || $r['catatan'] || $r['link_bukti']): ?>
            <hr class="card-divider">
            <div style="display:flex;flex-wrap:wrap;gap:14px;align-items:center;">
                <?php if ($r['pesan']): ?>
                    <span style="font-size:13px;color:#9caea4;"><i class="bi bi-chat-dots-fill"></i> <?= htmlspecialchars($r['pesan']) ?></span>
                <?php endif; ?>
                <?php if ($r['link_bukti']): ?>
                    <a href="<?= htmlspecialchars($r['link_bukti']) ?>" target="_blank" rel="noopener" class="btn-bukti">
                        <i class="bi bi-link-45deg" style="font-size: 18px;"></i> Lihat Bukti Transfer
                    </a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>

</div>

<!-- FOOTER -->
<footer class="donasi-footer">
    © 2026 UrFarm — <a href="/project-urfarm/landing.php">Kembali ke Beranda</a>
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
