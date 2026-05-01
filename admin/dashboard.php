<?php
session_start();
require_once '../config/connection.php';

// Proteksi: harus login sebagai admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../auth/login.php');
    exit;
}

// ── STATS ──────────────────────────────────────────────────────────────────
$total_donasi = $conn->query("SELECT COALESCE(SUM(jumlah_transfer),0) as total FROM donasi WHERE status='verified'")->fetch_assoc()['total'];
$total_donatur = $conn->query("SELECT COUNT(*) as c FROM users WHERE role='donatur'")->fetch_assoc()['c'];
$total_bibit = $conn->query("SELECT COALESCE(SUM(jumlah_bibit),0) as c FROM penanaman")->fetch_assoc()['c'];
$total_event = $conn->query("SELECT COUNT(*) as c FROM event")->fetch_assoc()['c'];
$total_pub = $conn->query("SELECT COUNT(*) as c FROM publikasi")->fetch_assoc()['c'];
$total_lokasi = $conn->query("SELECT COUNT(*) as c FROM titik_lokasi")->fetch_assoc()['c'];
$total_kontak = $conn->query("SELECT COUNT(*) as c FROM contact")->fetch_assoc()['c'];
$total_kode = $conn->query("SELECT COUNT(*) as c FROM kode_titik")->fetch_assoc()['c'];

// Donasi bulan ini
$donasi_bulan = $conn->query("SELECT COALESCE(SUM(jumlah_transfer),0) as total FROM donasi WHERE MONTH(created_at)=MONTH(NOW()) AND YEAR(created_at)=YEAR(NOW())")->fetch_assoc()['total'];
$donatur_bulan = $conn->query("SELECT COUNT(*) as c FROM users WHERE role='donatur' AND MONTH(created_at)=MONTH(NOW()) AND YEAR(created_at)=YEAR(NOW())")->fetch_assoc()['c'];

// ── AKTIVITAS TERBARU ──────────────────────────────────────────────────────
$activities = [];
// Donasi terbaru
$r = $conn->query("SELECT 'donasi' as type, nama_donatur as nama, nominal as val, created_at FROM donasi ORDER BY created_at DESC LIMIT 3");
while ($row = $r->fetch_assoc())
    $activities[] = $row;
// Publikasi terbaru
$r = $conn->query("SELECT 'publikasi' as type, judul as nama, 0 as val, tanggal_publikasi as created_at FROM publikasi ORDER BY tanggal_publikasi DESC LIMIT 2");
while ($row = $r->fetch_assoc())
    $activities[] = $row;
// Kontak terbaru
$r = $conn->query("SELECT 'kontak' as type, u.nama as nama, 0 as val, c.id_contact as created_at FROM contact c LEFT JOIN users u ON c.id_users=u.id LIMIT 2");
while ($row = $r->fetch_assoc())
    $activities[] = $row;
usort($activities, fn($a, $b) => strtotime($b['created_at']) - strtotime($a['created_at']));
$activities = array_slice($activities, 0, 6);

// ── DISTRIBUSI BIBIT ───────────────────────────────────────────────────────
$distribusi = $conn->query("SELECT b.jenis_pohon, SUM(p.jumlah_bibit) as total FROM penanaman p LEFT JOIN bibit b ON p.id_bibit=b.id_bibit GROUP BY b.jenis_pohon ORDER BY total DESC");
$dist_data = [];
$dist_total = 0;
while ($row = $distribusi->fetch_assoc()) {
    $dist_data[] = $row;
    $dist_total += $row['total'];
}

// ── EVENT MENDATANG ────────────────────────────────────────────────────────
$events_mendatang = $conn->query("
    SELECT p.id_penanaman, e.nama_evet, p.tanggal, p.lokasi, p.jumlah_bibit,
           CASE WHEN p.tanggal <= CURDATE() THEN 'Berjalan' ELSE 'Akan Datang' END as status
    FROM penanaman p LEFT JOIN event e ON p.id_event=e.id_event
    ORDER BY p.tanggal ASC LIMIT 5
");

// Format rupiah
function rupiah($n)
{
    return 'Rp ' . number_format($n, 0, ',', '.');
}
function jutaan($n)
{
    if ($n >= 1000000)
        return 'Rp ' . number_format($n / 1000000, 1, ',', '.') . ' Jt';
    return rupiah($n);
}
function timeAgo($dt)
{
    if (!$dt || $dt == '0')
        return '-';
    $diff = time() - strtotime($dt);
    if ($diff < 60)
        return $diff . ' dtk';
    if ($diff < 3600)
        return floor($diff / 60) . ' mnt';
    if ($diff < 86400)
        return floor($diff / 3600) . ' jam';
    return floor($diff / 86400) . ' hari';
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin – UrFarm</title>
    <meta name="description"
        content="Dashboard admin UrFarm untuk memantau donasi, bibit, event, dan aktivitas terkini.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="css/admin.css">

</head>

<body>

    <?php include 'partials/sidebar.php'; ?>

    <!-- ══ MAIN ═════════════════════════════════════════════ -->
    <div class="main">

        <!-- TOPBAR -->
        <header class="topbar">
            <div class="topbar-left">
                <h1>Dashboard</h1>
                <p>Selamat datang kembali, <?= htmlspecialchars($_SESSION['user_nama'] ?? 'Admin') ?>!</p>
            </div>
            <div class="topbar-right">
                <a href="/project-urfarm/auth/logout.php" class="logout-btn"><i class="bi bi-box-arrow-right"></i> Keluar</a>
            </div>
        </header>

        <!-- CONTENT -->
        <div class="content">

            <!-- ROW 1: 4 stat cards -->
            <div class="stats-grid-1">
                <div class="stat-card">
                    <div class="stat-icon green"><i class="bi bi-cash-stack"></i></div>
                    <div class="stat-body">
                        <div class="stat-value"><?= jutaan($total_donasi) ?></div>
                        <div class="stat-label">Total Donasi</div>
                        <?php if ($donasi_bulan > 0): ?>
                            <div class="stat-sub">↑ <?= jutaan($donasi_bulan) ?> bulan ini</div>
                        <?php else: ?>
                            <div class="stat-sub red">Belum ada donasi terverifikasi</div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon orange"><i class="bi bi-tree-fill"></i></div>
                    <div class="stat-body">
                        <div class="stat-value"><?= number_format($total_bibit, 0, ',', '.') ?></div>
                        <div class="stat-label">Total Bibit Ditanam</div>
                        <div class="stat-sub">↑ <?= $total_bibit ?> total keseluruhan</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon blue"><i class="bi bi-people-fill"></i></div>
                    <div class="stat-body">
                        <div class="stat-value"><?= $total_donatur ?></div>
                        <div class="stat-label">Total Donatur</div>
                        <div class="stat-sub">↑ <?= $donatur_bulan ?> baru bulan ini</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon pink"><i class="bi bi-megaphone-fill"></i></div>
                    <div class="stat-body">
                        <div class="stat-value"><?= $total_pub ?></div>
                        <div class="stat-label">Publikasi Aktif</div>
                        <div class="stat-sub">Artikel diterbitkan</div>
                    </div>
                </div>
            </div>

            <!-- ROW 2: 3 stat cards -->
            <div class="stats-grid-2">
                <div class="stat-card">
                    <div class="stat-icon teal"><i class="bi bi-calendar-check-fill"></i></div>
                    <div class="stat-body">
                        <div class="stat-value"><?= $total_event ?></div>
                        <div class="stat-label">Event Berjalan</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon purple"><i class="bi bi-geo-alt-fill"></i></div>
                    <div class="stat-body">
                        <div class="stat-value"><?= $total_lokasi ?></div>
                        <div class="stat-label">Lokasi Penanaman</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon yellow"><i class="bi bi-envelope-fill"></i></div>
                    <div class="stat-body">
                        <div class="stat-value"><?= $total_kontak ?></div>
                        <div class="stat-label">Pesan Masuk</div>
                    </div>
                </div>
            </div>

            <!-- ROW 3: Activity + Donut -->
            <div class="bottom-grid">
                <!-- Aktivitas Terbaru -->
                <div class="card">
                    <div class="card-header">
                        <h2>Aktivitas Terbaru</h2>
                        <span class="badge-live">Live</span>
                    </div>
                    <div class="activity-list">
                        <?php foreach ($activities as $act):
                            $type = $act['type'];
                            $dotClass = $type;
                            if ($type === 'donasi') {
                                $text = "Donasi baru masuk dari <strong>" . htmlspecialchars($act['nama']) . "</strong> — " . rupiah($act['val']);
                            } elseif ($type === 'publikasi') {
                                $text = "Publikasi <strong>\"" . htmlspecialchars(substr($act['nama'], 0, 40)) . "\"</strong> diterbitkan";
                            } else {
                                $text = "Pesan masuk dari <strong>" . htmlspecialchars($act['nama']) . "</strong> — belum dibaca";
                            }
                            ?>
                            <div class="activity-item">
                                <span class="act-dot <?= $dotClass ?>"></span>
                                <span class="act-text"><?= $text ?></span>
                                <span class="act-time"><?= timeAgo($act['created_at']) ?></span>
                            </div>
                        <?php endforeach; ?>
                        <?php if (empty($activities)): ?>
                            <div style="padding:30px;text-align:center;color:var(--text-muted);font-size:13px">Belum ada
                                aktivitas</div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Distribusi Bibit -->
                <div class="card">
                    <div class="card-header">
                        <h2>Distribusi Bibit</h2>
                    </div>
                    <div class="donut-wrap">
                        <?php
                        $colors = ['#2d8c5f', '#f59e0b', '#3b82f6', '#8b5cf6', '#ef4444'];
                        $total_d = max($dist_total, 1);
                        $offset = 25;
                        $segments = [];
                        foreach ($dist_data as $i => $d) {
                            $pct = $d['total'] / $total_d * 100;
                            $segments[] = ['pct' => $pct, 'color' => $colors[$i % count($colors)], 'label' => $d['jenis_pohon'], 'val' => $d['total'], 'offset' => $offset];
                            $offset += $pct;
                        }
                        // Build SVG donut
                        $svgSegs = '';
                        foreach ($segments as $s) {
                            $dash = $s['pct'] . ' ' . (100 - $s['pct']);
                            $svgSegs .= '<circle cx="21" cy="21" r="15.915" fill="none" stroke="' . $s['color'] . '" stroke-width="6" stroke-dasharray="' . $dash . '" stroke-dashoffset="-' . ($s['offset'] - 25) . '" style="transition:stroke-dasharray .4s"/>';
                        }
                        ?>
                        <div style="position:relative;width:140px;height:140px">
                            <svg viewBox="0 0 42 42" width="140" height="140" style="transform:rotate(-90deg)">
                                <circle cx="21" cy="21" r="15.915" fill="none" stroke="#f0f4f2" stroke-width="6" />
                                <?= $svgSegs ?>
                            </svg>
                            <div
                                style="position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center">
                                <span
                                    style="font-size:22px;font-weight:700;color:var(--text)"><?= number_format($dist_total, 0, ',', '.') ?></span>
                                <span style="font-size:10px;color:var(--text-muted)">bibit</span>
                            </div>
                        </div>
                    </div>
                    <div class="donut-legend">
                        <?php foreach ($segments as $i => $s): ?>
                            <div class="legend-row">
                                <div class="legend-label">
                                    <span class="legend-dot" style="background:<?= $s['color'] ?>"></span>
                                    <?= htmlspecialchars($s['label']) ?>
                                </div>
                                <span class="legend-val"><?= number_format($s['val'], 0, ',', '.') ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Event Mendatang -->
            <div class="card">
                <div class="card-header">
                    <h2>Event Mendatang</h2>
                    <a href="#" class="card-link">Lihat Semua</a>
                </div>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Nama Event</th>
                                <th>Tanggal</th>
                                <th>Lokasi</th>
                                <th>Bibit</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($ev = $events_mendatang->fetch_assoc()): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($ev['nama_evet'] ?? '-') ?></strong></td>
                                    <td><?= $ev['tanggal'] ? date('d M Y', strtotime($ev['tanggal'])) : '-' ?></td>
                                    <td><?= htmlspecialchars($ev['lokasi'] ?? '-') ?></td>
                                    <td><?= number_format($ev['jumlah_bibit'], 0, ',', '.') ?> btg</td>
                                    <td>
                                        <?php if ($ev['status'] === 'Berjalan'): ?>
                                            <span class="status-badge berjalan">Berjalan</span>
                                        <?php else: ?>
                                            <span class="status-badge akan">Akan Datang</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div><!-- /content -->
    </div><!-- /main -->

</body>

</html>