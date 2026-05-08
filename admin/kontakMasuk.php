<?php
session_start();
require_once '../config/connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../auth/login.php');
    exit;
}

if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM contact WHERE id_contact = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute(); $stmt->close();
    header('Location: kontakMasuk.php');
    exit;
}

$search = trim($_GET['q'] ?? '');
$active_id = isset($_GET['id']) && is_numeric($_GET['id']) ? intval($_GET['id']) : null;

$where = '1=1';
$bind_t = ''; $bind_v = [];
if ($search !== '') {
    $like = "%$search%";
    $where = "(u.nama LIKE ? OR c.pesan LIKE ?)";
    $bind_t = 'ss'; $bind_v = [$like, $like];
}

$sql = "SELECT c.id_contact, c.pesan, u.nama, u.email
        FROM contact c
        JOIN users u ON u.id = c.id_users
        WHERE $where
        ORDER BY c.id_contact DESC";

if ($bind_t) {
    $st = $conn->prepare($sql);
    $st->bind_param($bind_t, ...$bind_v);
    $st->execute();
    $result = $st->get_result();
} else {
    $result = $conn->query($sql);
}

$rows = [];
while ($row = $result->fetch_assoc()) $rows[] = $row;

$active_msg = null;
if ($active_id) {
    foreach ($rows as $r) {
        if ((int)$r['id_contact'] === $active_id) { $active_msg = $r; break; }
    }
    if (!$active_msg) {
        $st2 = $conn->prepare("SELECT c.id_contact, c.pesan, u.nama, u.email FROM contact c JOIN users u ON u.id = c.id_users WHERE c.id_contact = ?");
        $st2->bind_param('i', $active_id);
        $st2->execute();
        $r2 = $st2->get_result()->fetch_assoc();
        if ($r2) $active_msg = $r2;
        $st2->close();
    }
}

$total_kontak = count($rows);

$avatar_colors = ['#D4A84B','#2B7FEB','#40916C','#E63946','#7B1FA2','#1B4332','#388E3C','#0288D1','#F57C00','#C2185B'];

function getInitials($name) {
    $parts = explode(' ', trim($name));
    $init = strtoupper(substr($parts[0], 0, 1));
    if (isset($parts[1])) $init .= strtoupper(substr($parts[1], 0, 1));
    return $init;
}

function getColor($name, $colors) {
    return $colors[abs(crc32($name)) % count($colors)];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kontak Masuk — UrFarm Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;600;700&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/kontak-masuk.css">
</head>
<body>

<aside class="sidebar">
    <div class="sidebar-logo">
        <div class="logo-icon">UF</div>
        <div class="logo-text">Ur<span>Farm</span></div>
    </div>
    <nav class="sidebar-nav">
        <div class="nav-section-label">Utama</div>
        <a href="dashboard.php" class="nav-item"><i class="bi bi-grid-1x2"></i> Dashboard</a>
        <a href="bibit.php" class="nav-item"><i class="bi bi-tree"></i> Bibit</a>
        <a href="event.php" class="nav-item"><i class="bi bi-calendar-event"></i> Event</a>
        <div class="nav-section-label">Keuangan &amp; Lokasi</div>
        <a href="dana.php" class="nav-item"><i class="bi bi-wallet2"></i> Alokasi Dana</a>
        <a href="lokasi.php" class="nav-item"><i class="bi bi-geo-alt"></i> Lokasi &amp; Penanaman</a>
        <div class="nav-section-label">Konten</div>
        <a href="kode.php" class="nav-item"><i class="bi bi-qr-code"></i> Kode</a>
        <a href="publikasi.php" class="nav-item"><i class="bi bi-newspaper"></i> Publikasi</a>
        <a href="kontakMasuk.php" class="nav-item active">
            <i class="bi bi-envelope"></i> Kontak Masuk
            <?php if ($total_kontak > 0): ?>
                <span class="nav-badge"><?= $total_kontak ?></span>
            <?php endif; ?>
        </a>
    </nav>
    <div class="sidebar-footer">
        <div class="admin-info">
            <div class="admin-avatar">AD</div>
            <div>
                <div class="admin-name"><?= htmlspecialchars($_SESSION['user_nama'] ?? 'Admin') ?></div>
                <div class="admin-role">Super Admin</div>
            </div>
        </div>
    </div>
</aside>

<div class="main">
    <header class="topbar">
        <div>
            <div class="topbar-title">Kontak Masuk</div>
            <div class="topbar-subtitle">Pesan masuk dari pengguna</div>
        </div>
        <div class="topbar-actions">
            <a href="../auth/logout.php" class="btn-admin outline"><i class="bi bi-box-arrow-right"></i> Keluar</a>
        </div>
    </header>

    <div class="page-content">
        <div class="inbox-layout">
            <div class="inbox-list">
                <div class="inbox-search">
                    <form method="GET" action="">
                        <div class="search-wrap">
                            <i class="bi bi-search"></i>
                            <input class="search-input" name="q" placeholder="Cari pesan..." value="<?= htmlspecialchars($search) ?>" autocomplete="off">
                        </div>
                    </form>
                </div>

                <?php if (empty($rows)): ?>
                <div style="padding: 40px 20px; text-align: center; color: var(--text-muted);">
                    <i class="bi bi-inbox" style="font-size: 36px; opacity: 0.3; display: block; margin-bottom: 10px;"></i>
                    <p style="font-size: 13px;">Tidak ada pesan ditemukan</p>
                </div>
                <?php else: ?>
                <?php foreach ($rows as $msg):
                    $init  = getInitials($msg['nama']);
                    $color = getColor($msg['nama'], $avatar_colors);
                    $preview = mb_substr($msg['pesan'], 0, 60) . (mb_strlen($msg['pesan']) > 60 ? '...' : '');
                    $is_active = $active_id === (int)$msg['id_contact'];
                ?>
                <a href="?<?= $search ? 'q='.urlencode($search).'&' : '' ?>id=<?= $msg['id_contact'] ?>"
                   class="inbox-item <?= $is_active ? 'active' : '' ?>">
                    <div class="inbox-avatar" style="background: <?= $color ?>"><?= $init ?></div>
                    <div class="inbox-item-info">
                        <div class="inbox-sender"><?= htmlspecialchars($msg['nama']) ?></div>
                        <div class="inbox-preview"><?= htmlspecialchars($preview) ?></div>
                    </div>
                </a>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <div class="inbox-detail">
                <?php if ($active_msg):
                    $init  = getInitials($active_msg['nama']);
                    $color = getColor($active_msg['nama'], $avatar_colors);
                ?>
                <div class="inbox-detail-header">
                    <div style="display: flex; align-items: flex-start; gap: 14px;">
                        <div class="inbox-avatar" style="background: <?= $color ?>; width: 44px; height: 44px; font-size: 16px; flex-shrink: 0;">
                            <?= $init ?>
                        </div>
                        <div style="flex: 1;">
                            <div class="inbox-detail-subject"><?= htmlspecialchars($active_msg['nama']) ?></div>
                            <div class="inbox-detail-meta"><?= htmlspecialchars($active_msg['email']) ?></div>
                            <div class="inbox-detail-meta-row">
                                <button class="btn-admin danger" onclick="confirmDelete(<?= $active_msg['id_contact'] ?>)">
                                    <i class="bi bi-trash"></i> Hapus
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="inbox-detail-body">
                    <?= nl2br(htmlspecialchars($active_msg['pesan'])) ?>
                </div>
                <div class="inbox-reply-bar">
                    <textarea class="inbox-reply-input" placeholder="Balas via email manual ke: <?= htmlspecialchars($active_msg['email']) ?>"></textarea>
                    <button class="btn-admin primary" onclick="showToast('Buka email client Anda untuk membalas ke <?= htmlspecialchars($active_msg['email']) ?>')">
                        <i class="bi bi-send"></i>
                    </button>
                </div>

                <?php else: ?>
                <div class="inbox-empty-state">
                    <i class="bi bi-envelope-open"></i>
                    <p>Pilih pesan untuk membacanya</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="modal-overlay" id="modal-confirm">
    <div class="modal">
        <div class="modal-header">
            <div class="modal-title" style="color: var(--red);">Konfirmasi Hapus</div>
            <button class="modal-close" onclick="closeModal()"><i class="bi bi-x-lg"></i></button>
        </div>
        <p style="font-size: 14px; color: var(--text-muted); margin-bottom: 8px;">Apakah Anda yakin ingin menghapus pesan ini?</p>
        <p style="font-size: 12px; color: var(--red);">Tindakan ini tidak dapat dibatalkan.</p>
        <div class="modal-footer">
            <button class="btn btn-outline" onclick="closeModal()">Batal</button>
            <a id="confirm-delete-btn" href="#" class="btn btn-danger"><i class="bi bi-trash"></i> Ya, Hapus</a>
        </div>
    </div>
</div>

<div class="toast-container" id="toast-container"></div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
function confirmDelete(id) {
    document.getElementById('confirm-delete-btn').href = '?delete=' + id + '<?= $search ? '&q='.urlencode($search) : '' ?>';
    document.getElementById('modal-confirm').classList.add('open');
}
function closeModal() {
    document.getElementById('modal-confirm').classList.remove('open');
}
document.getElementById('modal-confirm').addEventListener('click', function(e) {
    if (e.target === this) closeModal();
});
function showToast(msg) {
    const c = document.getElementById('toast-container');
    const t = document.createElement('div');
    t.className = 'toast';
    t.textContent = '✅ ' + msg;
    c.appendChild(t);
    setTimeout(() => {
        t.style.opacity = '0';
        t.style.transform = 'translateY(20px)';
        t.style.transition = 'all 0.3s';
        setTimeout(() => t.remove(), 300);
    }, 2800);
}
const searchInput = document.querySelector('.search-input');
let searchTimeout;
searchInput.addEventListener('input', function() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => this.closest('form').submit(), 500);
});
</script>
</body>
</html>