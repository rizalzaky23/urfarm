<?php
session_start();
require_once '../config/connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../auth/login.php');
    exit;
}

function flash($msg, $type = 'success') { $_SESSION['flash'] = compact('msg', 'type'); }
function getFlash() { $f = $_SESSION['flash'] ?? null; unset($_SESSION['flash']); return $f; }

function genId($conn) {
    $r = $conn->query("SELECT id_alokasi FROM alokasi_dana ORDER BY id_alokasi DESC LIMIT 1")->fetch_assoc();
    $num = $r ? (int)substr($r['id_alokasi'], 1) + 1 : 1;
    return 'A' . str_pad($num, 4, '0', STR_PAD_LEFT);
}

function rupiah($n) {
    return 'Rp ' . number_format((float)$n, 0, ',', '.');
}

function jutaan($n) {
    if ($n >= 1000000) return 'Rp ' . number_format($n / 1000000, 1, ',', '.') . ' Jt';
    return rupiah($n);
}

// ── HANDLE POST ────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $act = $_POST['action'] ?? '';

    if (in_array($act, ['tambah', 'edit'])) {
        $id_donasi    = !empty($_POST['id_donasi']) ? intval($_POST['id_donasi']) : null;
        $id_penanaman = trim($_POST['id_penanaman'] ?? '') ?: null;
        $nominal      = floatval($_POST['nominal'] ?? 0);
        $errors = [];

        if ($nominal <= 0) $errors[] = 'Nominal harus lebih dari 0.';

        if (empty($errors)) {
            if ($act === 'tambah') {
                $id = genId($conn);
                $s = $conn->prepare("INSERT INTO alokasi_dana (id_alokasi, id_donasi, id_penanaman, nominal) VALUES (?, ?, ?, ?)");
                $s->bind_param('sisd', $id, $id_donasi, $id_penanaman, $nominal);
            } else {
                $id = $_POST['id_alokasi'];
                $s = $conn->prepare("UPDATE alokasi_dana SET id_donasi=?, id_penanaman=?, nominal=? WHERE id_alokasi=?");
                $s->bind_param('isds', $id_donasi, $id_penanaman, $nominal, $id);
            }
            $s->execute(); $s->close();
            flash($act === 'tambah' ? 'Alokasi dana berhasil ditambahkan!' : 'Alokasi dana berhasil diperbarui!');
            header('Location: alokasiDana.php'); exit;
        }
    }

    if ($act === 'hapus') {
        $id = $_POST['id_alokasi'];
        $conn->query("DELETE FROM alokasi_dana WHERE id_alokasi='$id'");
        flash('Alokasi dana berhasil dihapus.', 'danger');
        header('Location: alokasiDana.php'); exit;
    }
}

// ── DATA PENDUKUNG ─────────────────────────────────────────────────────────
$donasi_list = $conn->query("SELECT id_donasi, nama_donatur, nominal FROM donasi WHERE status='verified' ORDER BY id_donasi DESC")->fetch_all(MYSQLI_ASSOC);
$penanaman_list = $conn->query("SELECT p.id_penanaman, e.nama_evet, p.lokasi FROM penanaman p LEFT JOIN event e ON p.id_event=e.id_event ORDER BY p.id_penanaman")->fetch_all(MYSQLI_ASSOC);

// ── STATS ──────────────────────────────────────────────────────────────────
$total_donasi_masuk = (float)$conn->query("SELECT COALESCE(SUM(nominal), 0) as t FROM donasi WHERE status='verified'")->fetch_assoc()['t'];
$total_biaya_digunakan = (float)$conn->query("SELECT COALESCE(SUM(nominal), 0) as t FROM alokasi_dana")->fetch_assoc()['t'];
$sisa_dana = $total_donasi_masuk - $total_biaya_digunakan;
$pct_used = $total_donasi_masuk > 0 ? round(($total_biaya_digunakan / $total_donasi_masuk) * 100) : 0;

// ── FILTER & PAGINATION ───────────────────────────────────────────────────
$q = trim($_GET['q'] ?? '');
$perPg = 10;
$page = max(1, (int)($_GET['p'] ?? 1));
$offset = ($page - 1) * $perPg;

$where = []; $params = []; $types = '';
if ($q) {
    $where[] = '(a.id_alokasi LIKE ? OR COALESCE(d.nama_donatur,"") LIKE ? OR COALESCE(e.nama_evet,"") LIKE ?)';
    $like = "%$q%";
    $params[] = $like; $params[] = $like; $params[] = $like;
    $types .= 'sss';
}
$wsql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$sc = $conn->prepare("SELECT COUNT(*) c FROM alokasi_dana a LEFT JOIN donasi d ON a.id_donasi=d.id_donasi LEFT JOIN penanaman p ON a.id_penanaman=p.id_penanaman LEFT JOIN event e ON p.id_event=e.id_event $wsql");
if ($params) $sc->bind_param($types, ...$params);
$sc->execute();
$total = $sc->get_result()->fetch_assoc()['c'];
$sc->close();
$totalPg = max(1, (int)ceil($total / $perPg));

$sd = $conn->prepare("
    SELECT a.*, d.nama_donatur, d.nominal as nominal_donasi, e.nama_evet, p.lokasi as penanaman_lokasi
    FROM alokasi_dana a
    LEFT JOIN donasi d ON a.id_donasi = d.id_donasi
    LEFT JOIN penanaman p ON a.id_penanaman = p.id_penanaman
    LEFT JOIN event e ON p.id_event = e.id_event
    $wsql
    ORDER BY a.id_alokasi DESC
    LIMIT ? OFFSET ?
");
$allParams = array_merge($params, [$perPg, $offset]);
$sd->bind_param($types . 'ii', ...$allParams);
$sd->execute();
$rows = $sd->get_result()->fetch_all(MYSQLI_ASSOC);
$sd->close();

// ── EDIT PREFILL ───────────────────────────────────────────────────────────
$editRow = null; $showModal = false;
$editId = $_GET['edit'] ?? '';
if ($editId) {
    $editRow = $conn->query("SELECT * FROM alokasi_dana WHERE id_alokasi='$editId'")->fetch_assoc();
    $showModal = (bool)$editRow;
}

$flash = getFlash();
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>Alokasi Dana — UrFarm Admin</title>
  <meta name="description" content="Kelola transparansi dan manajemen alokasi dana donasi UrFarm.">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="css/admin.css">
  <link rel="stylesheet" href="css/alokasi-dana.css">
</head>
<body>

<?php include 'partials/sidebar.php'; ?>

<div class="main">

  <!-- TOPBAR -->
  <header class="topbar">
    <div class="topbar-left">
      <h1>Alokasi Dana</h1>
      <p>Transparansi dan manajemen keuangan</p>
    </div>
    <div class="topbar-right">
      <a href="../auth/logout.php" class="logout-btn"><i class="bi bi-box-arrow-right"></i> Keluar</a>
    </div>
  </header>

  <div class="content">

    <!-- FLASH MESSAGE -->
    <?php if ($flash): ?>
    <div class="alert-flash <?= $flash['type'] === 'danger' ? 'danger' : 'success' ?>" id="flash-msg">
      <i class="bi bi-<?= $flash['type'] === 'danger' ? 'x-circle-fill' : 'check-circle-fill' ?>"></i>
      <?= htmlspecialchars($flash['msg']) ?>
      <button class="close-btn" onclick="this.parentElement.remove()"><i class="bi bi-x-lg"></i></button>
    </div>
    <?php endif; ?>

    <!-- SUMMARY CARDS -->
    <div class="summary-grid">
      <div class="summary-card">
        <div class="label">Total Donasi Masuk</div>
        <div class="amount green"><?= rupiah($total_donasi_masuk) ?></div>
      </div>
      <div class="summary-card">
        <div class="label">Total Biaya Digunakan</div>
        <div class="amount red"><?= rupiah($total_biaya_digunakan) ?></div>
      </div>
      <div class="summary-card">
        <div class="label">Sisa Dana</div>
        <div class="amount dark"><?= rupiah($sisa_dana) ?></div>
      </div>
    </div>

    <!-- PROGRESS BAR -->
    <div class="progress-card">
      <div class="progress-header">
        <div class="progress-title">Penggunaan Dana</div>
        <div class="progress-pct"><?= $pct_used ?>% terpakai</div>
      </div>
      <div class="progress-bar-track">
        <div class="progress-bar-fill" style="width: <?= min($pct_used, 100) ?>%"></div>
      </div>
      <div class="progress-legend">
        <span><span class="legend-dot-used"></span> Terpakai: <?= jutaan($total_biaya_digunakan) ?></span>
        <span><span class="legend-dot-remaining"></span> Sisa: <?= jutaan($sisa_dana) ?></span>
      </div>
    </div>

    <!-- TABLE CARD -->
    <div class="alokasi-table-card">
      <div class="card-header">
        <h2>Rincian Alokasi Dana</h2>
        <div style="display:flex;align-items:center;gap:10px;">
          <form method="GET" style="display:contents;" id="filter-form">
            <div class="search-wrap" style="min-width:220px;">
              <i class="bi bi-search"></i>
              <input class="search-input" name="q" placeholder="Cari alokasi..." value="<?= htmlspecialchars($q) ?>" oninput="debounce(this.form)">
            </div>
          </form>
          <button class="btn btn-primary btn-sm" onclick="openModal('modal-alokasi')">
            <i class="bi bi-plus-lg"></i> Tambah Alokasi
          </button>
        </div>
      </div>

      <?php if (empty($rows)): ?>
      <div class="empty-state">
        <i class="bi bi-wallet2"></i>
        <p>Belum ada data alokasi dana</p>
        <button class="btn btn-primary" onclick="openModal('modal-alokasi')"><i class="bi bi-plus-lg"></i> Tambah Alokasi</button>
      </div>
      <?php else: ?>
      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>ID</th>
              <th>Keterangan</th>
              <th>Event</th>
              <th>Jumlah</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($rows as $row): ?>
            <tr>
              <td><span class="badge gray"><?= htmlspecialchars($row['id_alokasi']) ?></span></td>
              <td>
                <div class="td-main">
                  <?php if ($row['nama_donatur']): ?>
                    Donasi — <?= htmlspecialchars($row['nama_donatur']) ?>
                  <?php elseif ($row['penanaman_lokasi']): ?>
                    Penanaman — <?= htmlspecialchars($row['penanaman_lokasi']) ?>
                  <?php else: ?>
                    Alokasi Dana
                  <?php endif; ?>
                </div>
                <?php if ($row['id_donasi']): ?>
                  <div class="td-sub">Donasi #<?= $row['id_donasi'] ?></div>
                <?php endif; ?>
                <?php if ($row['id_penanaman']): ?>
                  <div class="td-sub">Penanaman: <?= htmlspecialchars($row['id_penanaman']) ?></div>
                <?php endif; ?>
              </td>
              <td><?= htmlspecialchars($row['nama_evet'] ?? '—') ?></td>
              <td>
                <span class="nominal-out">- <?= rupiah($row['nominal']) ?></span>
              </td>
              <td>
                <div class="row-actions">
                  <a href="?edit=<?= $row['id_alokasi'] ?>" class="btn-icon edit" title="Edit"><i class="bi bi-pencil"></i></a>
                  <button class="btn-icon delete" title="Hapus" onclick="confirmDelete('<?= $row['id_alokasi'] ?>')"><i class="bi bi-trash"></i></button>
                </div>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

      <?php if ($totalPg > 1): $qs = $q ? 'q=' . urlencode($q) : ''; ?>
      <div class="pagination-wrap">
        <div class="pagination-info">Menampilkan <?= min(($page-1)*$perPg+1, $total) ?>–<?= min($page*$perPg, $total) ?> dari <?= $total ?></div>
        <div class="pagination">
          <a href="?<?=$qs?>&p=<?=max(1,$page-1)?>" class="page-btn <?=$page<=1?'disabled':''?>"><i class="bi bi-chevron-left"></i></a>
          <?php for($i=1;$i<=$totalPg;$i++): ?>
          <a href="?<?=$qs?>&p=<?=$i?>" class="page-btn <?=$i===$page?'active':''?>"><?=$i?></a>
          <?php endfor; ?>
          <a href="?<?=$qs?>&p=<?=min($totalPg,$page+1)?>" class="page-btn <?=$page>=$totalPg?'disabled':''?>"><i class="bi bi-chevron-right"></i></a>
        </div>
      </div>
      <?php endif; ?>
      <?php endif; ?>

    </div><!-- /alokasi-table-card -->

  </div><!-- /content -->
</div><!-- /main -->

<!-- ══ MODAL TAMBAH / EDIT ══════════════════════════════════════════ -->
<div class="modal-backdrop <?= $showModal ? 'open' : '' ?>" id="modal-alokasi">
  <div class="modal">
    <div class="modal-header">
      <h3><i class="bi bi-<?= $editRow ? 'pencil-square' : 'plus-circle-fill' ?>"></i> <?= $editRow ? 'Edit Alokasi Dana' : 'Tambah Alokasi Dana' ?></h3>
      <button class="modal-close" onclick="closeModal('modal-alokasi')"><i class="bi bi-x-lg"></i></button>
    </div>
    <form method="POST" id="form-alokasi">
      <input type="hidden" name="action" value="<?= $editRow ? 'edit' : 'tambah' ?>">
      <?php if ($editRow): ?>
      <input type="hidden" name="id_alokasi" value="<?= $editRow['id_alokasi'] ?>">
      <?php endif; ?>

      <div class="modal-body">
        <?php if (!empty($errors)): ?>
        <div class="alert-flash danger" style="margin-bottom:14px;">
          <?php foreach ($errors as $e): ?><div><?= htmlspecialchars($e) ?></div><?php endforeach; ?>
        </div>
        <?php endif; ?>

        <div class="form-group">
          <label class="form-label">Sumber Donasi</label>
          <select class="form-control" name="id_donasi">
            <option value="">— Pilih Donasi (opsional) —</option>
            <?php foreach ($donasi_list as $dn): ?>
            <option value="<?= $dn['id_donasi'] ?>" <?= ($editRow['id_donasi'] ?? '') == $dn['id_donasi'] ? 'selected' : '' ?>>
              #<?= $dn['id_donasi'] ?> — <?= htmlspecialchars($dn['nama_donatur']) ?> (<?= rupiah($dn['nominal']) ?>)
            </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="form-group">
          <label class="form-label">Penanaman / Event</label>
          <select class="form-control" name="id_penanaman">
            <option value="">— Pilih Penanaman (opsional) —</option>
            <?php foreach ($penanaman_list as $pn): ?>
            <option value="<?= $pn['id_penanaman'] ?>" <?= ($editRow['id_penanaman'] ?? '') === $pn['id_penanaman'] ? 'selected' : '' ?>>
              <?= $pn['id_penanaman'] ?> — <?= htmlspecialchars($pn['nama_evet'] ?? 'N/A') ?> (<?= htmlspecialchars($pn['lokasi'] ?? '-') ?>)
            </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="form-group">
          <label class="form-label">Nominal (Rp) *</label>
          <input class="form-control" type="number" name="nominal" required min="1" placeholder="Contoh: 500000"
                 value="<?= $editRow['nominal'] ?? '' ?>">
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-sm" style="background:var(--border);color:var(--text);" onclick="closeModal('modal-alokasi')">
          <i class="bi bi-x"></i> Batal
        </button>
        <button type="submit" class="btn btn-primary btn-sm">
          <i class="bi bi-<?= $editRow ? 'check2-circle' : 'send-fill' ?>"></i>
          <?= $editRow ? 'Simpan Perubahan' : 'Tambah Alokasi' ?>
        </button>
      </div>
    </form>
  </div>
</div>

<!-- ══ MODAL HAPUS ══════════════════════════════════════════════════ -->
<div class="modal-backdrop" id="modal-hapus">
  <div class="modal" style="max-width:400px;">
    <div class="modal-header" style="background:linear-gradient(135deg,#991b1b,#dc2626);border-radius:14px 14px 0 0;">
      <h3 style="color:#fff;"><i class="bi bi-trash3-fill"></i> Hapus Alokasi</h3>
      <button class="modal-close" style="color:rgba(255,255,255,.7);" onclick="closeModal('modal-hapus')"><i class="bi bi-x-lg"></i></button>
    </div>
    <div class="modal-body" style="text-align:center;padding:28px 24px 20px;">
      <div style="width:60px;height:60px;background:#fee2e2;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 14px;font-size:26px;color:#dc2626;">
        <i class="bi bi-trash3-fill"></i>
      </div>
      <p style="font-size:15px;font-weight:700;color:var(--text);margin-bottom:6px;">Hapus alokasi ini?</p>
      <p id="del-id-display" style="font-size:13px;font-weight:600;color:var(--accent);margin-bottom:14px;"></p>
      <div style="font-size:12px;color:#dc2626;background:#fef2f2;border-radius:8px;padding:8px 14px;display:flex;align-items:center;justify-content:center;gap:6px;">
        <i class="bi bi-exclamation-triangle-fill"></i> Tindakan ini tidak dapat dibatalkan
      </div>
    </div>
    <div class="modal-footer" style="justify-content:center;gap:12px;">
      <form method="POST" style="display:contents;">
        <input type="hidden" name="action" value="hapus">
        <input type="hidden" name="id_alokasi" id="del-id">
        <button type="button" class="btn btn-sm" style="background:var(--border);color:var(--text);min-width:90px;" onclick="closeModal('modal-hapus')">
          <i class="bi bi-x"></i> Batal
        </button>
        <button type="submit" class="btn btn-danger btn-sm" style="min-width:110px;">
          <i class="bi bi-trash3-fill"></i> Ya, Hapus
        </button>
      </form>
    </div>
  </div>
</div>

<!-- TOAST -->
<div class="toast" id="toast-msg"></div>

<script>
function openModal(id)  { document.getElementById(id).classList.add('open'); document.body.style.overflow='hidden'; }
function closeModal(id) { document.getElementById(id).classList.remove('open'); document.body.style.overflow=''; }

document.querySelectorAll('.modal-backdrop').forEach(o =>
  o.addEventListener('click', e => { if(e.target===o){ o.classList.remove('open'); document.body.style.overflow=''; }})
);
document.addEventListener('keydown', e => {
  if(e.key==='Escape') document.querySelectorAll('.modal-backdrop.open').forEach(o=>{o.classList.remove('open');document.body.style.overflow='';});
});

function confirmDelete(id) {
  document.getElementById('del-id').value = id;
  document.getElementById('del-id-display').textContent = '"' + id + '"';
  openModal('modal-hapus');
}

let dt;
function debounce(form) { clearTimeout(dt); dt=setTimeout(()=>form.submit(),500); }

<?php if($showModal): ?>document.addEventListener('DOMContentLoaded',()=>openModal('modal-alokasi'));<?php endif; ?>
<?php if($flash): ?>setTimeout(()=>{const el=document.getElementById('flash-msg');if(el){el.style.transition='opacity .4s';el.style.opacity='0';setTimeout(()=>el.remove(),400);}},4000);<?php endif; ?>
</script>
</body>
</html>
