<?php
session_start();
require_once '../config/connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../auth/login.php'); exit;
}

function flash($msg, $type = 'success') { $_SESSION['flash'] = compact('msg','type'); }
function getFlash() { $f = $_SESSION['flash'] ?? null; unset($_SESSION['flash']); return $f; }
function rupiah($n) { return 'Rp ' . number_format((float)$n, 0, ',', '.'); }

// ── HANDLE POST ────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $act = $_POST['action'] ?? '';

    if ($act === 'update_status' && !empty($_POST['id_donasi']) && !empty($_POST['status'])) {
        $id = intval($_POST['id_donasi']);
        $status = $_POST['status'];
        if (in_array($status, ['verified','rejected','pending'])) {
            $s = $conn->prepare("UPDATE donasi SET status=? WHERE id_donasi=?");
            $s->bind_param('si', $status, $id);
            $s->execute(); $s->close();
            $label = ['verified'=>'diverifikasi','rejected'=>'ditolak','pending'=>'dikembalikan ke pending'];
            flash("Donasi #$id berhasil " . $label[$status] . "!");
        }
        header('Location: donasi.php' . (isset($_GET['status']) ? '?status='.$_GET['status'] : '')); exit;
    }

    if ($act === 'hapus' && !empty($_POST['id_donasi'])) {
        $id = intval($_POST['id_donasi']);
        $conn->query("DELETE FROM alokasi_dana WHERE id_donasi=$id");
        $conn->query("DELETE FROM donasi WHERE id_donasi=$id");
        flash('Donasi #'.$id.' berhasil dihapus.', 'danger');
        header('Location: donasi.php'); exit;
    }

    if ($act === 'update_lokasi' && !empty($_POST['id_donasi'])) {
        $id = intval($_POST['id_donasi']);
        $kode = trim($_POST['kode_lokasi'] ?? '');
        $kode = $kode ?: null;
        $s = $conn->prepare("UPDATE donasi SET kode_lokasi=? WHERE id_donasi=?");
        $s->bind_param('si', $kode, $id);
        $s->execute(); $s->close();
        flash("Kode lokasi untuk donasi #$id berhasil diperbarui!");
        header('Location: donasi.php' . (isset($_GET['status']) ? '?status='.$_GET['status'] : '')); exit;
    }
}

// ── STATS ──────────────────────────────────────────────────────────────
$st_total     = (int)$conn->query("SELECT COUNT(*) c FROM donasi")->fetch_assoc()['c'];
$st_verified  = (int)$conn->query("SELECT COUNT(*) c FROM donasi WHERE status='verified'")->fetch_assoc()['c'];
$st_pending   = (int)$conn->query("SELECT COUNT(*) c FROM donasi WHERE status='pending'")->fetch_assoc()['c'];
$st_rejected  = (int)$conn->query("SELECT COUNT(*) c FROM donasi WHERE status='rejected'")->fetch_assoc()['c'];
$st_nominal   = (float)$conn->query("SELECT COALESCE(SUM(nominal),0) t FROM donasi WHERE status='verified'")->fetch_assoc()['t'];

// ── FILTERS & PAGINATION ──────────────────────────────────────────────
$q       = trim($_GET['q'] ?? '');
$fStatus = $_GET['status'] ?? '';
$perPg   = 10;
$page    = max(1, (int)($_GET['p'] ?? 1));
$offset  = ($page - 1) * $perPg;

$where = []; $params = []; $types = '';
if ($q) {
    $where[] = '(d.nama_donatur LIKE ? OR d.email LIKE ? OR d.id_donasi LIKE ?)';
    $like = "%$q%"; $params = [$like,$like,$like]; $types = 'sss';
}
if ($fStatus && in_array($fStatus, ['pending','verified','rejected'])) {
    $where[] = 'd.status=?'; $params[] = $fStatus; $types .= 's';
}
$wsql = $where ? 'WHERE '.implode(' AND ',$where) : '';

$sc = $conn->prepare("SELECT COUNT(*) c FROM donasi d $wsql");
if ($params) $sc->bind_param($types, ...$params);
$sc->execute();
$total = $sc->get_result()->fetch_assoc()['c'];
$sc->close();
$totalPg = max(1, (int)ceil($total / $perPg));

$sd = $conn->prepare("
    SELECT d.*, u.nama as user_nama,
           (SELECT GROUP_CONCAT(a.id_alokasi) FROM alokasi_dana a WHERE a.id_donasi=d.id_donasi) as alokasi_ids,
           (SELECT COALESCE(SUM(a.nominal),0) FROM alokasi_dana a WHERE a.id_donasi=d.id_donasi) as alokasi_total
    FROM donasi d
    LEFT JOIN users u ON d.id_users = u.id
    $wsql
    ORDER BY d.created_at DESC
    LIMIT ? OFFSET ?
");
$allParams = array_merge($params, [$perPg, $offset]);
$sd->bind_param($types . 'ii', ...$allParams);
$sd->execute();
$rows = $sd->get_result()->fetch_all(MYSQLI_ASSOC);
$sd->close();

// ── DETAIL ─────────────────────────────────────────────────────────────
$detailRow = null;
if (isset($_GET['detail']) && is_numeric($_GET['detail'])) {
    $did = intval($_GET['detail']);
    $ds = $conn->prepare("
        SELECT d.*, u.nama as user_nama,
               (SELECT GROUP_CONCAT(a.id_alokasi) FROM alokasi_dana a WHERE a.id_donasi=d.id_donasi) as alokasi_ids,
               (SELECT COALESCE(SUM(a.nominal),0) FROM alokasi_dana a WHERE a.id_donasi=d.id_donasi) as alokasi_total
        FROM donasi d LEFT JOIN users u ON d.id_users=u.id WHERE d.id_donasi=?
    ");
    $ds->bind_param('i', $did); $ds->execute();
    $detailRow = $ds->get_result()->fetch_assoc();
    $ds->close();
}

$flash = getFlash();
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>Donasi Masuk — UrFarm Admin</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="css/admin.css">
  <link rel="stylesheet" href="css/donasi.css">
</head>
<body>

<?php include 'partials/sidebar.php'; ?>

<div class="main">
  <header class="topbar">
    <div class="topbar-left">
      <h1>Donasi Masuk</h1>
      <p>Kelola dan verifikasi donasi dari pengguna</p>
    </div>
    <div class="topbar-right">
      <a href="../auth/logout.php" class="logout-btn"><i class="bi bi-box-arrow-right"></i> Keluar</a>
    </div>
  </header>

  <div class="content">

    <?php if ($flash): ?>
    <div class="alert-flash <?= $flash['type']==='danger'?'danger':'success' ?>" id="flash-msg">
      <i class="bi bi-<?= $flash['type']==='danger'?'x-circle-fill':'check-circle-fill' ?>"></i>
      <?= htmlspecialchars($flash['msg']) ?>
      <button class="close-btn" onclick="this.parentElement.remove()"><i class="bi bi-x-lg"></i></button>
    </div>
    <?php endif; ?>

    <!-- SUMMARY -->
    <div class="summary-grid">
      <div class="summary-card">
        <div class="s-icon green"><i class="bi bi-cash-stack"></i></div>
        <div class="s-body">
          <div class="s-value"><?= rupiah($st_nominal) ?></div>
          <div class="s-label">Total Terverifikasi</div>
        </div>
      </div>
      <div class="summary-card">
        <div class="s-icon blue"><i class="bi bi-people-fill"></i></div>
        <div class="s-body">
          <div class="s-value"><?= $st_total ?></div>
          <div class="s-label">Total Donasi</div>
        </div>
      </div>
      <div class="summary-card">
        <div class="s-icon orange"><i class="bi bi-hourglass-split"></i></div>
        <div class="s-body">
          <div class="s-value"><?= $st_pending ?></div>
          <div class="s-label">Menunggu Verifikasi</div>
        </div>
      </div>
      <div class="summary-card">
        <div class="s-icon purple"><i class="bi bi-check2-all"></i></div>
        <div class="s-body">
          <div class="s-value"><?= $st_verified ?></div>
          <div class="s-label">Terverifikasi</div>
        </div>
      </div>
    </div>

    <!-- FILTER TABS -->
    <div class="filter-tabs">
      <a href="donasi.php" class="filter-tab <?= !$fStatus?'active':'' ?>">Semua <span class="count"><?= $st_total ?></span></a>
      <a href="?status=pending" class="filter-tab <?= $fStatus==='pending'?'active':'' ?>"><i class="bi bi-clock"></i> Pending <span class="count"><?= $st_pending ?></span></a>
      <a href="?status=verified" class="filter-tab <?= $fStatus==='verified'?'active':'' ?>"><i class="bi bi-check-circle"></i> Verified <span class="count"><?= $st_verified ?></span></a>
      <a href="?status=rejected" class="filter-tab <?= $fStatus==='rejected'?'active':'' ?>"><i class="bi bi-x-circle"></i> Rejected <span class="count"><?= $st_rejected ?></span></a>
    </div>

    <!-- TABLE -->
    <div class="donasi-table-card">
      <div class="card-header">
        <h2>Daftar Donasi</h2>
        <form method="GET" style="display:flex;gap:8px;" id="filter-form">
          <?php if ($fStatus): ?><input type="hidden" name="status" value="<?= htmlspecialchars($fStatus) ?>"><?php endif; ?>
          <div class="search-wrap" style="min-width:240px;">
            <i class="bi bi-search"></i>
            <input class="search-input" name="q" placeholder="Cari donatur..." value="<?= htmlspecialchars($q) ?>" oninput="debounce(this.form)">
          </div>
        </form>
      </div>

      <?php if (empty($rows)): ?>
      <div class="empty-state">
        <i class="bi bi-inbox"></i>
        <p>Tidak ada data donasi<?= $fStatus ? ' dengan status '.htmlspecialchars($fStatus) : '' ?></p>
      </div>
      <?php else: ?>
      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>ID</th>
              <th>Donatur</th>
              <th>Nominal</th>
              <th>Metode</th>
              <th>Status</th>
              <th>Alokasi</th>
              <th>Tanggal</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($rows as $row):
              $has_alokasi = $row['alokasi_total'] > 0;
            ?>
            <tr>
              <td><span class="badge gray">#<?= $row['id_donasi'] ?></span></td>
              <td>
                <div class="td-main"><?= htmlspecialchars($row['nama_donatur']) ?></div>
                <div class="td-sub"><?= htmlspecialchars($row['email']) ?></div>
              </td>
              <td><span class="nominal-green"><?= rupiah($row['nominal']) ?></span></td>
              <td><span class="metode-badge"><?= strtoupper($row['metode']) ?></span></td>
              <td><span class="status-badge <?= $row['status'] ?>"><?= ucfirst($row['status']) ?></span></td>
              <td>
                <?php if ($has_alokasi): ?>
                  <span class="status-badge allocated"><i class="bi bi-check2"></i> Dialokasikan</span>
                  <?php if ($row['kode_lokasi']): ?>
                    <div style="font-size:11px;color:var(--text-muted);margin-top:4px;"><i class="bi bi-qr-code"></i> <?= htmlspecialchars($row['kode_lokasi']) ?></div>
                  <?php endif; ?>
                <?php else: ?>
                  <span class="status-badge not-allocated">Belum</span>
                <?php endif; ?>
              </td>
              <td style="white-space:nowrap;font-size:12px;color:var(--text-muted);"><?= date('d M Y', strtotime($row['created_at'])) ?></td>
              <td>
                <div class="row-actions">
                  <a href="?detail=<?= $row['id_donasi'] ?><?= $fStatus?'&status='.$fStatus:'' ?>" class="btn-icon detail" title="Detail"><i class="bi bi-eye"></i></a>
                  <?php if ($has_alokasi): ?>
                    <button class="btn-icon detail" style="color:#2563eb;background:#eff6ff;" title="Atur Kode Unik" onclick="editLokasi(<?= $row['id_donasi'] ?>, '<?= htmlspecialchars($row['kode_lokasi'] ?? '') ?>')"><i class="bi bi-qr-code"></i></button>
                  <?php endif; ?>
                  <?php if ($row['status'] === 'pending'): ?>
                    <button class="btn-icon verify" title="Verifikasi" onclick="quickAction(<?= $row['id_donasi'] ?>,'verified')"><i class="bi bi-check-lg"></i></button>
                    <button class="btn-icon reject" title="Tolak" onclick="quickAction(<?= $row['id_donasi'] ?>,'rejected')"><i class="bi bi-x-lg"></i></button>
                  <?php elseif ($row['status'] === 'rejected'): ?>
                    <button class="btn-icon verify" title="Verifikasi" onclick="quickAction(<?= $row['id_donasi'] ?>,'verified')"><i class="bi bi-check-lg"></i></button>
                  <?php endif; ?>
                  <button class="btn-icon delete" title="Hapus" onclick="confirmDelete(<?= $row['id_donasi'] ?>,'<?= addslashes(htmlspecialchars($row['nama_donatur'])) ?>')"><i class="bi bi-trash"></i></button>
                </div>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

      <?php if ($totalPg > 1): $qs = http_build_query(array_filter(['q'=>$q,'status'=>$fStatus])); ?>
      <div class="pagination-wrap">
        <div class="pagination-info">Menampilkan <?= min(($page-1)*$perPg+1,$total) ?>–<?= min($page*$perPg,$total) ?> dari <?= $total ?></div>
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
    </div>

  </div>
</div>

<!-- DETAIL MODAL -->
<div class="modal-backdrop <?= $detailRow?'open':'' ?>" id="modal-detail">
  <?php if ($detailRow): ?>
  <div class="modal" style="max-width:600px;">
    <div class="modal-header">
      <h3><i class="bi bi-receipt"></i> Detail Donasi #<?= $detailRow['id_donasi'] ?></h3>
      <button class="modal-close" onclick="closeModal('modal-detail')"><i class="bi bi-x-lg"></i></button>
    </div>
    <div class="modal-body">
      <div class="detail-grid">
        <div class="detail-item">
          <div class="detail-label">Donatur</div>
          <div class="detail-value"><?= htmlspecialchars($detailRow['nama_donatur']) ?></div>
        </div>
        <div class="detail-item">
          <div class="detail-label">Email</div>
          <div class="detail-value"><?= htmlspecialchars($detailRow['email']) ?></div>
        </div>
        <div class="detail-item">
          <div class="detail-label">Nominal Donasi</div>
          <div class="detail-value" style="color:#16a34a;font-weight:700;"><?= rupiah($detailRow['nominal']) ?></div>
        </div>
        <div class="detail-item">
          <div class="detail-label">Jumlah Transfer</div>
          <div class="detail-value" style="color:#16a34a;font-weight:700;"><?= rupiah($detailRow['jumlah_transfer']) ?></div>
        </div>
        <div class="detail-item">
          <div class="detail-label">Metode</div>
          <div class="detail-value"><span class="metode-badge"><?= strtoupper($detailRow['metode']) ?></span></div>
        </div>
        <div class="detail-item">
          <div class="detail-label">Estimasi Bibit</div>
          <div class="detail-value"><?= number_format($detailRow['estimasi_bibit'],0,',','.') ?> batang</div>
        </div>
        <div class="detail-item">
          <div class="detail-label">Status</div>
          <div class="detail-value"><span class="status-badge <?= $detailRow['status'] ?>"><?= ucfirst($detailRow['status']) ?></span></div>
        </div>
        <div class="detail-item">
          <div class="detail-label">Tanggal</div>
          <div class="detail-value"><?= date('d M Y, H:i', strtotime($detailRow['created_at'])) ?></div>
        </div>

        <?php if ($detailRow['link_bukti']): ?>
        <div class="detail-item full">
          <div class="detail-label">Bukti Transfer</div>
          <div class="detail-value"><a href="<?= htmlspecialchars($detailRow['link_bukti']) ?>" target="_blank"><i class="bi bi-link-45deg"></i> Lihat Bukti</a></div>
        </div>
        <?php endif; ?>

        <?php if ($detailRow['pesan']): ?>
        <div class="detail-divider"></div>
        <div class="detail-item full">
          <div class="detail-label">Pesan</div>
          <div class="detail-value"><?= nl2br(htmlspecialchars($detailRow['pesan'])) ?></div>
        </div>
        <?php endif; ?>

        <?php if ($detailRow['catatan']): ?>
        <div class="detail-item full">
          <div class="detail-label">Catatan</div>
          <div class="detail-value"><?= nl2br(htmlspecialchars($detailRow['catatan'])) ?></div>
        </div>
        <?php endif; ?>

        <div class="detail-divider"></div>
        <div class="detail-item full">
          <div class="detail-label">Status Alokasi</div>
          <div class="detail-value">
            <?php if ($detailRow['alokasi_total'] > 0): ?>
              <span class="status-badge allocated"><i class="bi bi-check2"></i> Dialokasikan — <?= rupiah($detailRow['alokasi_total']) ?></span>
              <div style="font-size:11px;color:var(--text-muted);margin-top:4px;">ID: <?= htmlspecialchars($detailRow['alokasi_ids']) ?></div>
              <?php if ($detailRow['kode_lokasi']): ?>
                <div style="font-size:12px;color:var(--text);margin-top:4px;font-weight:600;"><i class="bi bi-qr-code"></i> Kode Unik: <?= htmlspecialchars($detailRow['kode_lokasi']) ?></div>
              <?php endif; ?>
            <?php else: ?>
              <span class="status-badge not-allocated">Belum dialokasikan</span>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
    <div class="modal-footer" style="gap:8px;">
      <?php if ($detailRow['status'] === 'pending'): ?>
        <form method="POST" style="display:contents;"><input type="hidden" name="action" value="update_status"><input type="hidden" name="id_donasi" value="<?= $detailRow['id_donasi'] ?>"><input type="hidden" name="status" value="verified">
          <button type="submit" class="btn btn-sm" style="background:#dcfce7;color:#15803d;border:1px solid #bbf7d0;"><i class="bi bi-check-lg"></i> Verifikasi</button>
        </form>
        <form method="POST" style="display:contents;"><input type="hidden" name="action" value="update_status"><input type="hidden" name="id_donasi" value="<?= $detailRow['id_donasi'] ?>"><input type="hidden" name="status" value="rejected">
          <button type="submit" class="btn btn-sm" style="background:#fee2e2;color:#b91c1c;border:1px solid #fecaca;"><i class="bi bi-x-lg"></i> Tolak</button>
        </form>
      <?php endif; ?>
      <button class="btn btn-sm" style="background:var(--border);color:var(--text);" onclick="closeModal('modal-detail')"><i class="bi bi-x"></i> Tutup</button>
    </div>
  </div>
  <?php endif; ?>
</div>

<!-- DELETE MODAL -->
<div class="modal-backdrop" id="modal-hapus">
  <div class="modal" style="max-width:400px;">
    <div class="modal-header" style="background:linear-gradient(135deg,#991b1b,#dc2626);border-radius:14px 14px 0 0;">
      <h3 style="color:#fff;"><i class="bi bi-trash3-fill"></i> Hapus Donasi</h3>
      <button class="modal-close" style="color:rgba(255,255,255,.7);" onclick="closeModal('modal-hapus')"><i class="bi bi-x-lg"></i></button>
    </div>
    <div class="modal-body" style="text-align:center;padding:28px 24px 20px;">
      <div style="width:60px;height:60px;background:#fee2e2;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 14px;font-size:26px;color:#dc2626;"><i class="bi bi-trash3-fill"></i></div>
      <p style="font-size:15px;font-weight:700;color:var(--text);margin-bottom:6px;">Hapus donasi ini?</p>
      <p id="del-nama" style="font-size:13px;font-weight:600;color:var(--accent);margin-bottom:14px;"></p>
      <div style="font-size:12px;color:#dc2626;background:#fef2f2;border-radius:8px;padding:8px 14px;display:flex;align-items:center;justify-content:center;gap:6px;"><i class="bi bi-exclamation-triangle-fill"></i> Data alokasi terkait juga akan dihapus</div>
    </div>
    <div class="modal-footer" style="justify-content:center;gap:12px;">
      <form method="POST" style="display:contents;">
        <input type="hidden" name="action" value="hapus">
        <input type="hidden" name="id_donasi" id="del-id">
        <button type="button" class="btn btn-sm" style="background:var(--border);color:var(--text);min-width:90px;" onclick="closeModal('modal-hapus')"><i class="bi bi-x"></i> Batal</button>
        <button type="submit" class="btn btn-danger btn-sm" style="min-width:110px;"><i class="bi bi-trash3-fill"></i> Ya, Hapus</button>
      </form>
    </div>
  </div>
</div>

<!-- LOKASI MODAL -->
<div class="modal-backdrop" id="modal-lokasi">
  <div class="modal" style="max-width:400px;">
    <div class="modal-header">
      <h3><i class="bi bi-qr-code"></i> Atur Kode Unik</h3>
      <button class="modal-close" onclick="closeModal('modal-lokasi')"><i class="bi bi-x-lg"></i></button>
    </div>
    <form method="POST">
      <input type="hidden" name="action" value="update_lokasi">
      <input type="hidden" name="id_donasi" id="lokasi-id">
      <div class="modal-body">
        <p style="font-size:14px;color:var(--text-muted);margin-bottom:12px;">Masukkan kode unik (dari menu Kode) untuk donasi <strong id="lokasi-nama-donasi"></strong>.</p>
        <div class="form-group" style="margin-bottom:0;">
          <label class="form-label">Kode Unik</label>
          <input type="text" class="form-control" name="kode_lokasi" id="lokasi-kode" placeholder="Contoh: UFM-A1B2C3D4">
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-sm" style="background:var(--border);color:var(--text);" onclick="closeModal('modal-lokasi')"><i class="bi bi-x"></i> Batal</button>
        <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-save"></i> Simpan</button>
      </div>
    </form>
  </div>
</div>

<!-- HIDDEN FORM FOR QUICK STATUS -->
<form method="POST" id="quick-form" style="display:none;">
  <input type="hidden" name="action" value="update_status">
  <input type="hidden" name="id_donasi" id="quick-id">
  <input type="hidden" name="status" id="quick-status">
</form>

<script>
function openModal(id)  { document.getElementById(id).classList.add('open'); document.body.style.overflow='hidden'; }
function closeModal(id) { document.getElementById(id).classList.remove('open'); document.body.style.overflow=''; }
document.querySelectorAll('.modal-backdrop').forEach(o =>
  o.addEventListener('click', e => { if(e.target===o){o.classList.remove('open');document.body.style.overflow='';} })
);
document.addEventListener('keydown', e => {
  if(e.key==='Escape') document.querySelectorAll('.modal-backdrop.open').forEach(o=>{o.classList.remove('open');document.body.style.overflow='';});
});

function quickAction(id, status) {
  const label = {verified:'memverifikasi',rejected:'menolak'};
  if (!confirm('Yakin ingin ' + label[status] + ' donasi #' + id + '?')) return;
  document.getElementById('quick-id').value = id;
  document.getElementById('quick-status').value = status;
  document.getElementById('quick-form').submit();
}

function confirmDelete(id, nama) {
  document.getElementById('del-id').value = id;
  document.getElementById('del-nama').textContent = '#' + id + ' — ' + nama;
  openModal('modal-hapus');
}

function editLokasi(id, currentKode) {
  document.getElementById('lokasi-id').value = id;
  document.getElementById('lokasi-nama-donasi').textContent = '#' + id;
  document.getElementById('lokasi-kode').value = currentKode;
  openModal('modal-lokasi');
}

let dt;
function debounce(form) { clearTimeout(dt); dt=setTimeout(()=>form.submit(),500); }

<?php if($detailRow): ?>document.addEventListener('DOMContentLoaded',()=>openModal('modal-detail'));<?php endif; ?>
<?php if($flash): ?>setTimeout(()=>{const el=document.getElementById('flash-msg');if(el){el.style.transition='opacity .4s';el.style.opacity='0';setTimeout(()=>el.remove(),400);}},4000);<?php endif; ?>
</script>
</body>
</html>
