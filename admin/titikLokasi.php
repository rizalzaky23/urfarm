<?php
session_start();
require_once '../config/connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../auth/login.php'); exit;
}

function genTitikId($conn) {
    $r = $conn->query("SELECT id_titik FROM titik_lokasi ORDER BY id_titik DESC LIMIT 1")->fetch_assoc();
    $num = $r ? (int)substr($r['id_titik'], 2) + 1 : 1;
    return 'TL' . str_pad($num, 3, '0', STR_PAD_LEFT);
}
function genPenanamanId($conn) {
    $r = $conn->query("SELECT id_penanaman FROM penanaman ORDER BY id_penanaman DESC LIMIT 1")->fetch_assoc();
    $num = $r ? (int)substr($r['id_penanaman'], 2) + 1 : 1;
    return 'PN' . str_pad($num, 3, '0', STR_PAD_LEFT);
}
function flash($msg, $type='success') { $_SESSION['flash'] = compact('msg','type'); }
function getFlash() { $f = $_SESSION['flash'] ?? null; unset($_SESSION['flash']); return $f; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $act = $_POST['action'] ?? '';

    if ($act === 'tambah') {
        $lat    = trim($_POST['latitude']  ?? '');
        $lng    = trim($_POST['longitude'] ?? '');
        $status = in_array($_POST['status'] ?? '', ['benih baru','tumbuh']) ? $_POST['status'] : 'benih baru';
        $id_ev  = !empty($_POST['id_event']) ? $_POST['id_event'] : null;
        if ($lat !== '' && $lng !== '') {
            $id = genTitikId($conn);
            if ($id_ev !== null) {
                $s = $conn->prepare("INSERT INTO titik_lokasi (id_titik, latitude, longtitude, status, id_event) VALUES (?,?,?,?,?)");
                $s->bind_param('sssss', $id, $lat, $lng, $status, $id_ev);
            } else {
                $s = $conn->prepare("INSERT INTO titik_lokasi (id_titik, latitude, longtitude, status) VALUES (?,?,?,?)");
                $s->bind_param('ssss', $id, $lat, $lng, $status);
            }
            $s->execute(); $s->close();

            // Handle Penanaman Details
            $lokasi_pn = trim($_POST['lokasi_penanaman'] ?? '');
            $tgl_pn    = trim($_POST['tanggal_tanam'] ?? '');
            $bibit_pn  = !empty($_POST['id_bibit']) ? $_POST['id_bibit'] : null;
            $jml_pn    = (int)($_POST['jumlah_bibit'] ?? 0);

            if ($id_ev !== null && ($lokasi_pn !== '' || $tgl_pn !== '' || $bibit_pn !== null || $jml_pn > 0)) {
                $cek = $conn->query("SELECT id_penanaman FROM penanaman WHERE id_event='$id_ev'")->fetch_assoc();
                if ($cek) {
                    $sp = $conn->prepare("UPDATE penanaman SET lokasi=?, tanggal=?, id_bibit=?, jumlah_bibit=? WHERE id_event=?");
                    $sp->bind_param('sssss', $lokasi_pn, $tgl_pn, $bibit_pn, $jml_pn, $id_ev);
                    $sp->execute(); $sp->close();
                } else {
                    $id_pn = genPenanamanId($conn);
                    $sp = $conn->prepare("INSERT INTO penanaman (id_penanaman, id_event, lokasi, tanggal, id_bibit, jumlah_bibit) VALUES (?,?,?,?,?,?)");
                    $sp->bind_param('ssssss', $id_pn, $id_ev, $lokasi_pn, $tgl_pn, $bibit_pn, $jml_pn);
                    $sp->execute(); $sp->close();
                }
            }
            flash('Titik lokasi berhasil ditambahkan!');
        } else {
            flash('Latitude dan Longitude wajib diisi.', 'danger');
        }
        header('Location: titikLokasi.php'); exit;
    }

    if ($act === 'edit') {
        $id     = $_POST['id_titik'];
        $lat    = trim($_POST['latitude']  ?? '');
        $lng    = trim($_POST['longitude'] ?? '');
        $status = in_array($_POST['status'] ?? '', ['benih baru','tumbuh']) ? $_POST['status'] : 'benih baru';
        $id_ev  = !empty($_POST['id_event']) ? $_POST['id_event'] : null;
        if ($id_ev !== null) {
            $s = $conn->prepare("UPDATE titik_lokasi SET latitude=?, longtitude=?, status=?, id_event=? WHERE id_titik=?");
            $s->bind_param('sssss', $lat, $lng, $status, $id_ev, $id);
        } else {
            $s = $conn->prepare("UPDATE titik_lokasi SET latitude=?, longtitude=?, status=?, id_event=NULL WHERE id_titik=?");
            $s->bind_param('ssss', $lat, $lng, $status, $id);
        }
        $s->execute(); $s->close();

        // Handle Penanaman Details
        $lokasi_pn = trim($_POST['lokasi_penanaman'] ?? '');
        $tgl_pn    = trim($_POST['tanggal_tanam'] ?? '');
        $bibit_pn  = !empty($_POST['id_bibit']) ? $_POST['id_bibit'] : null;
        $jml_pn    = (int)($_POST['jumlah_bibit'] ?? 0);

        if ($id_ev !== null && ($lokasi_pn !== '' || $tgl_pn !== '' || $bibit_pn !== null || $jml_pn > 0)) {
            $cek = $conn->query("SELECT id_penanaman FROM penanaman WHERE id_event='$id_ev'")->fetch_assoc();
            if ($cek) {
                $sp = $conn->prepare("UPDATE penanaman SET lokasi=?, tanggal=?, id_bibit=?, jumlah_bibit=? WHERE id_event=?");
                $sp->bind_param('sssss', $lokasi_pn, $tgl_pn, $bibit_pn, $jml_pn, $id_ev);
                $sp->execute(); $sp->close();
            } else {
                $id_pn = genPenanamanId($conn);
                $sp = $conn->prepare("INSERT INTO penanaman (id_penanaman, id_event, lokasi, tanggal, id_bibit, jumlah_bibit) VALUES (?,?,?,?,?,?)");
                $sp->bind_param('ssssss', $id_pn, $id_ev, $lokasi_pn, $tgl_pn, $bibit_pn, $jml_pn);
                $sp->execute(); $sp->close();
            }
        }
        flash('Titik lokasi berhasil diperbarui!');
        header('Location: titikLokasi.php'); exit;
    }

    if ($act === 'hapus') {
        $id = $_POST['id_titik'];
        $s = $conn->prepare("DELETE FROM titik_lokasi WHERE id_titik=?");
        $s->bind_param('s', $id); $s->execute(); $s->close();
        flash('Titik lokasi dihapus.', 'danger');
        header('Location: titikLokasi.php'); exit;
    }
}

$events = $conn->query("SELECT id_event, nama_evet FROM event ORDER BY nama_evet")->fetch_all(MYSQLI_ASSOC);
$bibits = $conn->query("SELECT id_bibit, nama_pohon FROM bibit ORDER BY nama_pohon")->fetch_all(MYSQLI_ASSOC);

$q       = trim($_GET['q'] ?? '');
$fStatus = $_GET['status'] ?? '';
$perPg   = 10;
$page    = max(1, (int)($_GET['p'] ?? 1));
$offset  = ($page - 1) * $perPg;

$conds = [];
if ($q)       $conds[] = "(tl.id_titik LIKE '%".mysqli_real_escape_string($conn,$q)."%' OR e.nama_evet LIKE '%".mysqli_real_escape_string($conn,$q)."%')";
if ($fStatus) $conds[] = "tl.status='".mysqli_real_escape_string($conn,$fStatus)."'";
$wsql = $conds ? 'WHERE '.implode(' AND ', $conds) : '';

$total   = $conn->query("SELECT COUNT(*) c FROM titik_lokasi tl LEFT JOIN event e ON tl.id_event=e.id_event $wsql")->fetch_assoc()['c'];
$totalPg = max(1, (int)ceil($total / $perPg));

$rows = $conn->query("SELECT tl.*, e.nama_evet FROM titik_lokasi tl LEFT JOIN event e ON tl.id_event=e.id_event $wsql ORDER BY tl.id_titik DESC LIMIT $perPg OFFSET $offset")->fetch_all(MYSQLI_ASSOC);

$stAll    = (int)$conn->query("SELECT COUNT(*) c FROM titik_lokasi")->fetch_assoc()['c'];
$stTumbuh = (int)$conn->query("SELECT COUNT(*) c FROM titik_lokasi WHERE status='tumbuh'")->fetch_assoc()['c'];
$stBenih  = (int)$conn->query("SELECT COUNT(*) c FROM titik_lokasi WHERE status='benih baru'")->fetch_assoc()['c'];

$editRow   = null;
$showModal = false;
$editId    = $_GET['edit'] ?? '';
if ($editId) {
    $r = $conn->query("
        SELECT tl.*, p.lokasi as lokasi_pn, p.tanggal as tanggal_pn, p.id_bibit as id_bibit_pn, p.jumlah_bibit as jumlah_bibit_pn 
        FROM titik_lokasi tl 
        LEFT JOIN penanaman p ON tl.id_event = p.id_event 
        WHERE tl.id_titik='".mysqli_real_escape_string($conn,$editId)."'
    ")->fetch_assoc();
    if ($r) { $editRow = $r; $showModal = true; }
}

$flash = getFlash();
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Titik Lokasi — UrFarm Admin</title>
<meta name="description" content="Kelola titik lokasi penanaman UrFarm.">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<link rel="stylesheet" href="css/admin.css">
<link rel="stylesheet" href="css/titik-lokasi.css">
</head>
<body>

<?php include 'partials/sidebar.php'; ?>

<div class="main">
  <header class="topbar">
    <div class="topbar-left">
      <h1>Titik Lokasi</h1>
      <p>Kelola titik koordinat penanaman di peta</p>
    </div>
    <div class="topbar-right">
      <button class="btn btn-primary" onclick="openModal('modal-tambah')">
        <i class="bi bi-plus-lg"></i> Tambah Titik
      </button>
      <a href="../auth/logout.php" class="logout-btn"><i class="bi bi-box-arrow-right"></i> Keluar</a>
    </div>
  </header>

  <div class="content">

    <?php if ($flash): ?>
    <div class="toast show" id="flash-toast" style="position:relative;bottom:auto;right:auto;margin-bottom:16px;display:flex;">
      <i class="bi bi-<?= $flash['type']==='danger'?'x-circle-fill':'check-circle-fill' ?>"></i>
      <?= htmlspecialchars($flash['msg']) ?>
    </div>
    <?php endif; ?>

    <!-- Stats -->
    <div class="stats-grid-2">
      <div class="stat-card">
        <div class="stat-icon purple"><i class="bi bi-geo-alt-fill"></i></div>
        <div class="stat-body"><div class="stat-value"><?= $stAll ?></div><div class="stat-label">Total Titik</div></div>
      </div>
      <div class="stat-card">
        <div class="stat-icon green"><i class="bi bi-tree-fill"></i></div>
        <div class="stat-body"><div class="stat-value"><?= $stTumbuh ?></div><div class="stat-label">Tumbuh Aktif</div></div>
      </div>
      <div class="stat-card">
        <div class="stat-icon yellow"><i class="bi bi-flower1"></i></div>
        <div class="stat-body"><div class="stat-value"><?= $stBenih ?></div><div class="stat-label">Benih Baru</div></div>
      </div>
    </div>

    <!-- Map Overview -->
    <div class="card">
      <div class="card-header"><h2><i class="bi bi-map" style="margin-right:6px"></i>Peta Titik Lokasi</h2></div>
      <div id="map-overview" class="map-full"></div>
    </div>

    <!-- Table -->
    <div class="card">
      <div class="card-header">
        <h2>Daftar Titik Lokasi</h2>
        <form method="GET" style="display:flex;gap:8px;align-items:center">
          <div class="search-wrap" style="min-width:220px">
            <i class="bi bi-search"></i>
            <input class="search-input" name="q" placeholder="Cari ID atau event..." value="<?= htmlspecialchars($q) ?>" oninput="clearTimeout(window._dt);window._dt=setTimeout(()=>this.form.submit(),500)">
          </div>
          <select class="filter-select" name="status" onchange="this.form.submit()">
            <option value="">Semua Status</option>
            <option value="tumbuh" <?= $fStatus==='tumbuh'?'selected':'' ?>>Tumbuh Aktif</option>
            <option value="benih baru"  <?= $fStatus==='benih baru' ?'selected':'' ?>>Benih Baru</option>
          </select>
          <?php if ($q||$fStatus): ?><a href="titikLokasi.php" class="btn btn-sm btn-danger"><i class="bi bi-x"></i></a><?php endif; ?>
        </form>
      </div>

      <div class="table-wrap">
        <table>
          <thead><tr>
            <th>ID Titik</th><th>Latitude</th><th>Longitude</th><th>Status</th><th>Event</th><th style="text-align:right">Aksi</th>
          </tr></thead>
          <tbody>
          <?php if (empty($rows)): ?>
            <tr><td colspan="6" style="text-align:center;padding:40px;color:var(--text-muted)">
              <i class="bi bi-geo" style="font-size:32px;display:block;margin-bottom:8px;opacity:.3"></i>
              Belum ada titik lokasi
            </td></tr>
          <?php else: foreach ($rows as $row): ?>
            <tr>
              <td><strong><?= htmlspecialchars($row['id_titik']) ?></strong></td>
              <td><?= number_format((float)$row['latitude'], 6) ?></td>
              <td><?= number_format((float)$row['longtitude'], 6) ?></td>
              <td>
                <?php if ($row['status']==='tumbuh'): ?>
                  <span class="badge-tumbuh"><i class="bi bi-tree-fill"></i> Tumbuh</span>
                <?php else: ?>
                  <span class="badge-benih"><i class="bi bi-flower1"></i> Benih Baru</span>
                <?php endif; ?>
              </td>
              <td><?= htmlspecialchars($row['nama_evet'] ?? '—') ?></td>
              <td style="text-align:right;display:flex;gap:6px;justify-content:flex-end">
                <a href="?edit=<?= $row['id_titik'] ?><?= $q?"&q=".urlencode($q):'' ?><?= $fStatus?"&status=$fStatus":'' ?>" class="btn btn-sm btn-edit">
                  <i class="bi bi-pencil"></i> Edit
                </a>
                <button class="btn btn-sm btn-danger" onclick="confirmHapus('<?= $row['id_titik'] ?>')">
                  <i class="bi bi-trash"></i>
                </button>
              </td>
            </tr>
          <?php endforeach; endif; ?>
          </tbody>
        </table>
      </div>

      <?php if ($totalPg > 1): $qs = http_build_query(['q'=>$q,'status'=>$fStatus]); ?>
      <div class="pagination-bar">
        <span>Menampilkan <?= min(($page-1)*$perPg+1,$total) ?>–<?= min($page*$perPg,$total) ?> dari <?= $total ?></span>
        <div class="pagination-btns">
          <a href="?<?=$qs?>&p=<?=max(1,$page-1)?>" class="pg-btn <?=$page<=1?'disabled':''?>"><i class="bi bi-chevron-left"></i></a>
          <?php for($i=1;$i<=$totalPg;$i++): ?>
          <a href="?<?=$qs?>&p=<?=$i?>" class="pg-btn <?=$i===$page?'active':''?>"><?=$i?></a>
          <?php endfor; ?>
          <a href="?<?=$qs?>&p=<?=min($totalPg,$page+1)?>" class="pg-btn <?=$page>=$totalPg?'disabled':''?>"><i class="bi bi-chevron-right"></i></a>
        </div>
      </div>
      <?php endif; ?>
    </div>

  </div><!-- /content -->
</div><!-- /main -->

<!-- ═══ MODAL TAMBAH / EDIT ═══ -->
<div class="modal-backdrop <?= $showModal?'open':'' ?>" id="modal-tambah">
  <div class="modal" style="max-width:560px;overflow-y:auto;max-height:92vh">
    <div class="modal-header">
      <h3><i class="bi bi-<?= $editRow?'pencil-square':'plus-circle-fill' ?>" style="margin-right:6px"></i>
        <?= $editRow ? 'Edit Titik Lokasi' : 'Tambah Titik Lokasi' ?>
      </h3>
      <button class="modal-close" onclick="closeTambah()">&times;</button>
    </div>
    <form method="POST" id="form-titik">
      <div class="modal-body">
        <input type="hidden" name="action" value="<?= $editRow?'edit':'tambah' ?>">
        <?php if ($editRow): ?>
        <input type="hidden" name="id_titik" value="<?= $editRow['id_titik'] ?>">
        <?php endif; ?>

        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Status</label>
            <select class="form-control" name="status">
              <option value="benih baru" <?= ($editRow['status']??'')==='benih baru'?'selected':'' ?>>🌱 Benih Baru</option>
              <option value="tumbuh" <?= ($editRow['status']??'')==='tumbuh'?'selected':'' ?>>🌳 Tumbuh Aktif</option>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Event</label>
            <select class="form-control" name="id_event" id="event-select">
              <option value="">— Pilih Event —</option>
              <?php foreach($events as $ev): ?>
              <option value="<?= $ev['id_event'] ?>" <?= ($editRow['id_event']??'')===$ev['id_event']?'selected':'' ?>>
                <?= htmlspecialchars($ev['nama_evet']) ?>
              </option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>

        <div class="card" style="padding:14px;background:#f9fafb;box-shadow:none;border:1px dashed var(--border);margin-bottom:14px">
          <h4 style="font-size:13px;margin-bottom:10px;color:var(--text);display:flex;align-items:center;gap:6px">
            <i class="bi bi-card-text"></i> Detail Penanaman (Opsional, perlu pilih Event)
          </h4>
          <div class="form-row">
            <div class="form-group">
              <label class="form-label">Lokasi Detail</label>
              <input class="form-control" name="lokasi_penanaman" type="text" placeholder="Contoh: Area Timur Hutan" value="<?= htmlspecialchars($editRow['lokasi_pn']??'') ?>">
            </div>
            <div class="form-group">
              <label class="form-label">Tanggal Tanam</label>
              <input class="form-control" name="tanggal_tanam" type="date" value="<?= htmlspecialchars($editRow['tanggal_pn']??'') ?>">
            </div>
          </div>
          <div class="form-row" style="margin-bottom:0">
            <div class="form-group" style="margin-bottom:0">
              <label class="form-label">Jenis Pohon (Bibit)</label>
              <select class="form-control" name="id_bibit">
                <option value="">— Pilih Bibit —</option>
                <?php foreach($bibits as $b): ?>
                <option value="<?= $b['id_bibit'] ?>" <?= ($editRow['id_bibit_pn']??'')===$b['id_bibit']?'selected':'' ?>>
                  <?= htmlspecialchars($b['nama_pohon']) ?>
                </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="form-group" style="margin-bottom:0">
              <label class="form-label">Jumlah Bibit</label>
              <input class="form-control" name="jumlah_bibit" type="number" min="0" placeholder="100" value="<?= htmlspecialchars($editRow['jumlah_bibit_pn']??'') ?>">
            </div>
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Latitude *</label>
            <input class="form-control" id="inp-lat" name="latitude" type="number" step="any" required
                   placeholder="-6.200000" value="<?= htmlspecialchars($editRow['latitude']??'') ?>">
          </div>
          <div class="form-group">
            <label class="form-label">Longitude *</label>
            <input class="form-control" id="inp-lng" name="longitude" type="number" step="any" required
                   placeholder="106.816666" value="<?= htmlspecialchars($editRow['longtitude']??'') ?>">
          </div>
        </div>

        <div class="form-group" style="margin-bottom:0">
          <button type="button" id="btn-map-toggle" class="btn btn-sm btn-edit" style="width:100%;justify-content:center" onclick="toggleMap()">
            <i class="bi bi-map" id="map-toggle-icon"></i>
            <span id="map-toggle-text">📍 Pilih Lokasi di Peta</span>
          </button>
          <div id="map-picker-wrap" style="display:none;margin-top:10px">
            <div id="map-picker" style="height:300px;border-radius:10px;border:1px solid var(--border)"></div>
            <p style="font-size:11.5px;color:var(--text-muted);margin-top:6px;display:flex;align-items:center;gap:4px">
              <i class="bi bi-cursor-fill"></i> Klik pada peta untuk menetapkan titik. Drag marker untuk pindahkan.
            </p>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-sm" style="background:#f3f4f6;color:#374151" onclick="closeTambah()">Batal</button>
        <button type="submit" class="btn btn-sm btn-primary">
          <i class="bi bi-<?= $editRow?'check2-circle':'plus-lg' ?>"></i>
          <?= $editRow ? 'Simpan Perubahan' : 'Tambah Titik' ?>
        </button>
      </div>
    </form>
  </div>
</div>

<!-- ═══ MODAL HAPUS ═══ -->
<div class="modal-backdrop" id="modal-hapus">
  <div class="modal" style="max-width:400px">
    <div class="modal-header"><h3 style="color:#b91c1c"><i class="bi bi-trash3-fill" style="margin-right:6px"></i>Hapus Titik Lokasi</h3>
      <button class="modal-close" onclick="closeModal('modal-hapus')">&times;</button>
    </div>
    <div class="modal-body" style="text-align:center;padding:28px">
      <div style="width:56px;height:56px;background:#fee2e2;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 14px;font-size:24px;color:#dc2626"><i class="bi bi-trash3-fill"></i></div>
      <p style="font-weight:700;font-size:15px;margin-bottom:6px">Hapus titik ini?</p>
      <p id="del-id-show" style="font-size:13px;color:#6b7280;margin-bottom:14px"></p>
      <p style="font-size:12px;color:#dc2626;background:#fef2f2;border-radius:8px;padding:8px 14px"><i class="bi bi-exclamation-triangle-fill"></i> Tindakan ini tidak dapat dibatalkan</p>
    </div>
    <div class="modal-footer" style="justify-content:center;gap:10px">
      <form method="POST" style="display:contents">
        <input type="hidden" name="action" value="hapus">
        <input type="hidden" name="id_titik" id="del-id-input">
        <button type="button" class="btn btn-sm" style="background:#f3f4f6;color:#374151" onclick="closeModal('modal-hapus')">Batal</button>
        <button type="submit" class="btn btn-sm btn-danger"><i class="bi bi-trash3-fill"></i> Ya, Hapus</button>
      </form>
    </div>
  </div>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
// ── Modal helpers ──────────────────────────────────────
function openModal(id)  { document.getElementById(id).classList.add('open'); document.body.style.overflow='hidden'; }
function closeModal(id) { document.getElementById(id).classList.remove('open'); document.body.style.overflow=''; }
document.querySelectorAll('.modal-backdrop').forEach(el =>
  el.addEventListener('click', e => { if(e.target===el) { if(el.id==='modal-tambah') closeTambah(); else closeModal(el.id); } })
);
document.addEventListener('keydown', e => {
  if(e.key==='Escape') { closeTambah(); closeModal('modal-hapus'); }
});

function closeTambah() {
  closeModal('modal-tambah');
  hideMap();
  if (pickerMap) { pickerMap.remove(); pickerMap = null; pickerMarker = null; }
}

function confirmHapus(id) {
  document.getElementById('del-id-input').value = id;
  document.getElementById('del-id-show').textContent = 'ID: ' + id;
  openModal('modal-hapus');
}

<?php if($flash): ?>
setTimeout(() => {
  const el = document.getElementById('flash-toast');
  if (el) { el.style.transition='opacity .4s'; el.style.opacity='0'; setTimeout(()=>el.remove(),400); }
}, 4000);
<?php endif; ?>

// ── Overview Map ──────────────────────────────────────
window.addEventListener('load', async function() {
  const mapOv = L.map('map-overview', { center:[-2.5,118], zoom:5 });
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution:'© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>', maxZoom:19
  }).addTo(mapOv);
  setTimeout(() => mapOv.invalidateSize(), 300);
  function mkIcon(tumbuh) {
    const c = tumbuh ? '#1B4D3E' : '#d97706';
    return L.divIcon({
      html:`<svg xmlns="http://www.w3.org/2000/svg" width="26" height="36" viewBox="0 0 30 42"><path d="M15 1C8.373 1 3 6.373 3 13c0 8.5 12 27 12 27S27 21.5 27 13C27 6.373 21.627 1 15 1z" fill="${c}" stroke="white" stroke-width="2"/><circle cx="15" cy="13" r="5" fill="white" opacity="0.9"/></svg>`,
      className:'', iconSize:[26,36], iconAnchor:[13,36], popupAnchor:[0,-34]
    });
  }
  try {
    const res = await fetch('/project-urfarm/api/titik_lokasi.php');
    const json = await res.json();
    if (!json.success) return;
    json.markers.forEach(d => {
      const lat = parseFloat(d.latitude), lng = parseFloat(d.longitude);
      if (isNaN(lat)||isNaN(lng)) return;
      L.marker([lat,lng],{icon:mkIcon(d.status==='tumbuh'),title:d.id_titik})
        .bindPopup(`<strong>${d.id_titik}</strong><br>${d.nama_event||'—'}<br><span style="font-size:11px;color:#6b7280">${lat.toFixed(5)}, ${lng.toFixed(5)}</span>`)
        .addTo(mapOv);
    });
  } catch(e) { console.error(e); }
});

// ── Picker Map (lazy — only init when visible) ────────
let pickerMap = null, pickerMarker = null, mapShown = false;

function toggleMap() { mapShown ? hideMap() : showMap(); }

function showMap() {
  mapShown = true;
  document.getElementById('map-picker-wrap').style.display = 'block';
  document.getElementById('map-toggle-text').textContent  = 'Sembunyikan Peta';
  document.getElementById('map-toggle-icon').className    = 'bi bi-x-circle';
  setTimeout(initPickerMap, 150);
}

function hideMap() {
  mapShown = false;
  document.getElementById('map-picker-wrap').style.display = 'none';
  document.getElementById('map-toggle-text').textContent   = '📍 Pilih Lokasi di Peta';
  document.getElementById('map-toggle-icon').className     = 'bi bi-map';
}

function initPickerMap() {
  if (pickerMap) { pickerMap.invalidateSize(); return; }
  const lat0 = parseFloat(document.getElementById('inp-lat').value) || -2.5;
  const lng0 = parseFloat(document.getElementById('inp-lng').value) || 118;
  const z0   = document.getElementById('inp-lat').value ? 12 : 5;

  pickerMap = L.map('map-picker', { center:[lat0,lng0], zoom:z0, zoomControl:true });
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution:'© OpenStreetMap', maxZoom:19
  }).addTo(pickerMap);

  if (document.getElementById('inp-lat').value && document.getElementById('inp-lng').value) {
    pickerMarker = L.marker([lat0,lng0],{draggable:true}).addTo(pickerMap);
    pickerMarker.on('dragend', syncFromMarker);
  }

  pickerMap.on('click', e => setCoords(e.latlng.lat, e.latlng.lng));
  pickerMap.invalidateSize();
}

function setCoords(lat, lng) {
  document.getElementById('inp-lat').value = lat.toFixed(6);
  document.getElementById('inp-lng').value = lng.toFixed(6);
  if (pickerMarker) pickerMap.removeLayer(pickerMarker);
  pickerMarker = L.marker([lat,lng],{draggable:true}).addTo(pickerMap);
  pickerMarker.on('dragend', syncFromMarker);
  pickerMap.panTo([lat,lng]);
}

function syncFromMarker(e) {
  const p = e.target.getLatLng();
  document.getElementById('inp-lat').value = p.lat.toFixed(6);
  document.getElementById('inp-lng').value = p.lng.toFixed(6);
}

['inp-lat','inp-lng'].forEach(id => {
  document.getElementById(id).addEventListener('change', () => {
    if (!pickerMap) return;
    const la = parseFloat(document.getElementById('inp-lat').value);
    const ln = parseFloat(document.getElementById('inp-lng').value);
    if (!isNaN(la) && !isNaN(ln)) setCoords(la, ln);
  });
});

<?php if($showModal): ?>
document.addEventListener('DOMContentLoaded', () => {
  openModal('modal-tambah');
  setTimeout(showMap, 300);
});
<?php endif; ?>
</script>
</body>
</html>

