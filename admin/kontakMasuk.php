<?php
session_start();
require_once '../config/connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../auth/login.php');
    exit;
}

if (isset($_GET['mark_read']) && is_numeric($_GET['mark_read'])) {
    $id = intval($_GET['mark_read']);
    $conn->query("UPDATE contact SET is_read = 1 WHERE id_contact = $id");
    header('Location: kontak-masuk.php?id=' . $id);
    exit;
}

if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM contact WHERE id_contact = $id");
    header('Location: kontak-masuk.php');
    exit;
}

$active_id = isset($_GET['id']) && is_numeric($_GET['id']) ? intval($_GET['id']) : null;

if ($active_id) {
    $conn->query("UPDATE contact SET is_read = 1 WHERE id_contact = $active_id");
}

$filter = $_GET['filter'] ?? 'all';

$where = '';
if ($filter === 'unread') {
    $where = 'WHERE c.is_read = 0';
} elseif ($filter === 'read') {
    $where = 'WHERE c.is_read = 1';
}

$search = trim($_GET['q'] ?? '');
if ($search !== '') {
    $s = $conn->real_escape_string($search);
    $where = $where === ''
        ? "WHERE (u.nama LIKE '%$s%' OR c.pesan LIKE '%$s%')"
        : "$where AND (u.nama LIKE '%$s%' OR c.pesan LIKE '%$s%')";
}

$messages = $conn->query("
    SELECT c.id_contact, c.pesan, c.is_read, c.created_at,
           u.nama, u.email
    FROM contact c
    JOIN users u ON u.id_users = c.id_users
    $where
    ORDER BY c.is_read ASC, c.created_at DESC
");

$unread_count_result = $conn->query("SELECT COUNT(*) as cnt FROM contact WHERE is_read = 0");
$unread_count = $unread_count_result->fetch_assoc()['cnt'];

$active_msg = null;
if ($active_id) {
    $res = $conn->query("
        SELECT c.id_contact, c.pesan, c.is_read, c.created_at,
               u.nama, u.email
        FROM contact c
        JOIN users u ON u.id_users = c.id_users
        WHERE c.id_contact = $active_id
    ");
    if ($res && $res->num_rows > 0) {
        $active_msg = $res->fetch_assoc();
    }
}

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

function timeAgo($datetime) {
    $time = strtotime($datetime);
    $diff = time() - $time;
    if ($diff < 60) return 'Baru saja';
    if ($diff < 3600) return floor($diff / 60) . ' mnt lalu';
    if ($diff < 86400) return floor($diff / 3600) . ' jam lalu';
    if ($diff < 172800) return 'Kemarin';
    return date('d M Y', $time);
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
    <link rel="stylesheet" href="kontak-masuk.css">
</head>
<body>

<aside class="sidebar">
    <div class="sidebar-logo">
        <div class="logo-icon">UF</div>
        <div class="logo-text">Ur<span>Farm</span></div>
    </div>
    <nav class="sidebar-nav">
        <div class="nav-section-label">Utama</div>
        <a href="dashboard.php" class="nav-item">
            <i class="bi bi-grid-1x2"></i> Dashboard
        </a>
        <a href="bibit.php" class="nav-item">
            <i class="bi bi-tree"></i> Bibit
        </a>
        <a href="event.php" class="nav-item">
            <i class="bi bi-calendar-event"></i> Event
        </a>
        <div class="nav-section-label">Keuangan &amp; Lokasi</div>
        <a href="dana.php" class="nav-item">
            <i class="bi bi-wallet2"></i> Alokasi Dana
        </a>
        <a href="lokasi.php" class="nav-item">
            <i class="bi bi-geo-alt"></i> Lokasi &amp; Penanaman
        </a>
        <div class="nav-section-label">Konten</div>
        <a href="kode.php" class="nav-item">
            <i class="bi bi-qr-code"></i> Kode
        </a>
        <a href="publikasi.php" class="nav-item">
            <i class="bi bi-newspaper"></i> Publikasi
        </a>
        <a href="kontak-masuk.php" class="nav-item active">
            <i class="bi bi-envelope"></i> Kontak Masuk
            <?php if ($unread_count > 0): ?>
                <span class="nav-badge" id="unread-badge"><?= $unread_count ?></span>
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
            <a href="#" class="topbar-icon-btn" title="Notifikasi"><i class="bi bi-bell"></i></a>
            <a href="#" class="topbar-icon-btn" title="Pengaturan"><i class="bi bi-gear"></i></a>
            <a href="../auth/logout.php" class="btn-admin outline"><i class="bi bi-box-arrow-right"></i> Keluar</a>
        </div>
    </header>

    <div class="page-content">
        <div class="inbox-layout">
            <div class="inbox-list">
                <div class="inbox-search">
                    <form method="GET" action="">
                        <input type="hidden" name="filter" value="<?= htmlspecialchars($filter) ?>">
                        <div class="search-wrap">
                            <i class="bi bi-search"></i>
                            <input class="search-input" name="q" placeholder="Cari pesan..." value="<?= htmlspecialchars($search) ?>" autocomplete="off">
                        </div>
                    </form>
                </div>

                <div class="inbox-filter-bar">
                    <a href="?filter=all<?= $search ? '&q=' . urlencode($search) : '' ?>" class="filter-btn <?= $filter === 'all' ? 'active' : '' ?>">Semua</a>
                    <a href="?filter=unread<?= $search ? '&q=' . urlencode($search) : '' ?>" class="filter-btn <?= $filter === 'unread' ? 'active' : '' ?>">
                        Belum Dibaca<?php if ($unread_count > 0): ?> (<?= $unread_count ?>)<?php endif; ?>
                    </a>
                    <a href="?filter=read<?= $search ? '&q=' . urlencode($search) : '' ?>" class="filter-btn <?= $filter === 'read' ? 'active' : '' ?>">Sudah Dibaca</a>
                </div>

                <?php
                $rows = [];
                while ($row = $messages->fetch_assoc()) {
                    $rows[] = $row;
                }

                $unread_rows = array_filter($rows, fn($r) => !$r['is_read']);
                $read_rows = array_filter($rows, fn($r) => $r['is_read']);

                if (empty($rows)):
                ?>
                <div style="padding: 40px 20px; text-align: center; color: var(--text-muted);">
                    <i class="bi bi-inbox" style="font-size: 36px; opacity: 0.3; display: block; margin-bottom: 10px;"></i>
                    <p style="font-size: 13px;">Tidak ada pesan ditemukan</p>
                </div>
                <?php else: ?>

                <?php if (!empty($unread_rows) && $filter !== 'read'): ?>
                <?php foreach ($unread_rows as $msg):
                    $init = getInitials($msg['nama']);
                    $color = getColor($msg['nama'], $avatar_colors);
                    $preview = mb_substr($msg['pesan'], 0, 60) . (mb_strlen($msg['pesan']) > 60 ? '...' : '');
                    $is_active = $active_id === (int)$msg['id_contact'];
                ?>
                <a href="?filter=<?= $filter ?>&id=<?= $msg['id_contact'] ?><?= $search ? '&q=' . urlencode($search) : '' ?>"
                   class="inbox-item unread <?= $is_active ? 'active' : '' ?>">
                    <div class="unread-dot"></div>
                    <div class="inbox-avatar" style="background: <?= $color ?>;"><?= $init ?></div>
                    <div class="inbox-item-info">
                        <div class="inbox-sender"><?= htmlspecialchars($msg['nama']) ?></div>
                        <div class="inbox-preview"><?= htmlspecialchars($preview) ?></div>
                    </div>
                    <div class="inbox-time"><?= timeAgo($msg['created_at']) ?></div>
                </a>
                <?php endforeach; ?>
                <?php endif; ?>

                <?php if (!empty($read_rows) && $filter !== 'unread'): ?>
                <?php if (!empty($unread_rows) && $filter === 'all'): ?>
                <div class="inbox-section-label">Sudah Dibaca</div>
                <?php endif; ?>
                <?php foreach ($read_rows as $msg):
                    $init = getInitials($msg['nama']);
                    $color = getColor($msg['nama'], $avatar_colors);
                    $preview = mb_substr($msg['pesan'], 0, 60) . (mb_strlen($msg['pesan']) > 60 ? '...' : '');
                    $is_active = $active_id === (int)$msg['id_contact'];
                ?>
                <a href="?filter=<?= $filter ?>&id=<?= $msg['id_contact'] ?><?= $search ? '&q=' . urlencode($search) : '' ?>"
                   class="inbox-item <?= $is_active ? 'active' : '' ?>">
                    <div class="unread-spacer"></div>
                    <div class="inbox-avatar" style="background: <?= $color ?>;"><?= $init ?></div>
                    <div class="inbox-item-info">
                        <div class="inbox-sender" style="font-weight: 500; color: var(--text-muted);"><?= htmlspecialchars($msg['nama']) ?></div>
                        <div class="inbox-preview"><?= htmlspecialchars($preview) ?></div>
                    </div>
                    <div class="inbox-time"><?= timeAgo($msg['created_at']) ?></div>
                </a>
                <?php endforeach; ?>
                <?php endif; ?>

                <?php endif; ?>
            </div>

            <div class="inbox-detail">
                <?php if ($active_msg): ?>
                <?php
                    $init = getInitials($active_msg['nama']);
                    $color = getColor($active_msg['nama'], $avatar_colors);
                ?>
                <div class="inbox-detail-header">
                    <div style="display: flex; align-items: flex-start; gap: 14px;">
                        <div class="inbox-avatar" style="background: <?= $color ?>; width: 44px; height: 44px; font-size: 16px; flex-shrink: 0;">
                            <?= $init ?>
                        </div>
                        <div style="flex: 1;">
                            <div class="inbox-detail-subject"><?= htmlspecialchars($active_msg['nama']) ?></div>
                            <div class="inbox-detail-meta">
                                <?= htmlspecialchars($active_msg['email']) ?>
                                &nbsp;•&nbsp;
                                <?= date('d M Y, H.i', strtotime($active_msg['created_at'])) ?>
                                &nbsp;•&nbsp;
                                <?php if ($active_msg['is_read']): ?>
                                    <span class="badge badge-green"><i class="bi bi-check2-all"></i> Sudah Dibaca</span>
                                <?php else: ?>
                                    <span class="badge badge-blue"><i class="bi bi-circle-fill" style="font-size: 7px;"></i> Belum Dibaca</span>
                                <?php endif; ?>
                            </div>
                            <div class="inbox-detail-meta-row">
                                <?php if ($active_msg['is_read']): ?>
                                <a href="?filter=<?= $filter ?>&id=<?= $active_msg['id_contact'] ?>&mark_read=<?= $active_msg['id_contact'] ?>" class="btn-admin outline" style="opacity: 0.5; pointer-events: none;">
                                    <i class="bi bi-envelope-open"></i> Sudah Dibaca
                                </a>
                                <?php endif; ?>
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
                    <button class="btn-admin primary" onclick="showToast('Buka email client Anda untuk membalas ke ' + '<?= htmlspecialchars($active_msg['email']) ?>')">
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
        <p style="font-size: 14px; color: var(--text-muted); margin-bottom: 8px;">
            Apakah Anda yakin ingin menghapus pesan ini?
        </p>
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
    document.getElementById('confirm-delete-btn').href = '?delete=' + id + '&filter=<?= $filter ?><?= $search ? '&q=' . urlencode($search) : '' ?>';
    document.getElementById('modal-confirm').classList.add('open');
}

function closeModal() {
    document.getElementById('modal-confirm').classList.remove('open');
}

document.getElementById('modal-confirm').addEventListener('click', function(e) {
    if (e.target === this) closeModal();
});

function showToast(msg, type = 'success') {
    const c = document.getElementById('toast-container');
    const t = document.createElement('div');
    t.className = 'toast' + (type === 'error' ? ' error' : '');
    t.textContent = (type === 'error' ? '🗑️ ' : '✅ ') + msg;
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
    searchTimeout = setTimeout(() => {
        this.closest('form').submit();
    }, 500);
});
</script>

</body>
</html>