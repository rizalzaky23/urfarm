<?php
session_start();
require_once '../config/connection.php';
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') { header('Location: ../auth/login.php'); exit; }

$msg = ''; $msgType = 'success';

// ── HANDLE CRUD ────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'tambah') {
        $id      = trim($_POST['id_event']);
        $nama    = trim($_POST['nama_evet']);
        $jenis   = trim($_POST['jenis_event']);
        $stmt = $conn->prepare("INSERT INTO event (id_event, nama_evet, jenis_event) VALUES (?,?,?)");
        $stmt->bind_param('sss', $id, $nama, $jenis);
        if ($stmt->execute()) { $msg = 'Event berhasil ditambahkan!'; }
        else { $msg = 'Gagal: '.$conn->error; $msgType = 'error'; }
        $stmt->close();
    }

    if ($action === 'edit') {
        $id    = trim($_POST['id_event']);
        $nama  = trim($_POST['nama_evet']);
        $jenis = trim($_POST['jenis_event']);
        $stmt = $conn->prepare("UPDATE event SET nama_evet=?, jenis_event=? WHERE id_event=?");
        $stmt->bind_param('sss', $nama, $jenis, $id);
        if ($stmt->execute()) { $msg = 'Event berhasil diperbarui!'; }
        else { $msg = 'Gagal: '.$conn->error; $msgType = 'error'; }
        $stmt->close();
    }

    if ($action === 'hapus') {
        $id = trim($_POST['id_event']);
        // hapus penanaman dulu (FK)
        $conn->query("DELETE FROM penanaman WHERE id_event='$id'");
        $stmt = $conn->prepare("DELETE FROM event WHERE id_event=?");
        $stmt->bind_param('s', $id);
        if ($stmt->execute()) { $msg = 'Event berhasil dihapus.'; }
        else { $msg = 'Gagal: '.$conn->error; $msgType = 'error'; }
        $stmt->close();
    }
}

// ── DATA ───────────────────────────────────────────────────────────────────
$event_list = $conn->query("
    SELECT e.*, 
           COUNT(DISTINCT p.id_penanaman) as total_lokasi,
           COALESCE(SUM(p.jumlah_bibit),0) as total_bibit,
           MIN(p.tanggal) as tgl_mulai,
           MAX(p.tanggal) as tgl_selesai
    FROM event e
    LEFT JOIN penanaman p ON e.id_event = p.id_event
    GROUP BY e.id_event
    ORDER BY e.id_event DESC
");

$total_event   = $conn->query("SELECT COUNT(*) as c FROM event")->fetch_assoc()['c'];
$total_bibit_e = $conn->query("SELECT COALESCE(SUM(jumlah_bibit),0) as c FROM penanaman")->fetch_assoc()['c'];
$total_kontak  = $conn->query("SELECT COUNT(*) as c FROM contact")->fetch_assoc()['c'];

function statusEvent($tgl_mulai, $tgl_selesai) {
    if (!$tgl_mulai) return ['Belum Mulai', 'gray'];
    $now = date('Y-m-d');
    if ($now < $tgl_mulai) return ['Akan Datang', 'blue'];
    if ($now <= ($tgl_selesai ?: $tgl_mulai)) return ['Berjalan', 'green'];
    return ['Selesai', 'yellow'];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Event – Admin UrFarm</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="css/admin.css">
<style>
.event-cards{display:flex;gap:14px;padding:16px 20px;overflow-x:auto;border-bottom:1px solid var(--border)}
.event-card-mini{min-width:200px;border-radius:10px;border:1px solid var(--border);padding:14px 16px;background:#fafcfb;cursor:pointer;transition:.2s;flex-shrink:0}
.event-card-mini:hover{border-color:var(--accent);background:var(--accent-light)}
.event-card-mini .ecm-date{font-size:11px;color:var(--text-muted);margin-bottom:4px}
.event-card-mini .ecm-name{font-size:13.5px;font-weight:700;color:var(--text)}
.event-card-mini .ecm-loc{font-size:11.5px;color:var(--text-muted);margin-top:2px}
.event-card-mini .ecm-badges{display:flex;gap:6px;margin-top:8px;flex-wrap:wrap}
</style>
</head>
<body>

<?php include 'partials/sidebar.php'; ?>

<div class="main">
  <header class="topbar">
    <div class="topbar-left">
      <h1>Event</h1>
      <p>Kelola event penanaman</p>
    </div>
    <div class="topbar-right">
      <a href="../auth/logout.php" class="logout-btn"><i class="bi bi-box-arrow-right"></i> Keluar</a>
    </div>
  </header>

  <div class="content">

    <!-- Stats -->
    <div class="stats-grid-3">
      <div class="stat-card">
        <div class="stat-icon teal"><i class="bi bi-calendar-event-fill"></i></div>
        <div class="stat-body">
          <div class="stat-value"><?= $total_event ?></div>
          <div class="stat-label">Total Event</div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon orange"><i class="bi bi-tree-fill"></i></div>
        <div class="stat-body">
          <div class="stat-value"><?= number_format($total_bibit_e,0,',','.') ?></div>
          <div class="stat-label">Total Bibit Ditanam</div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon blue"><i class="bi bi-geo-alt-fill"></i></div>
        <div class="stat-body">
          <?php $tot_lok = $conn->query("SELECT COUNT(*) as c FROM penanaman")->fetch_assoc()['c']; ?>
          <div class="stat-value"><?= $tot_lok ?></div>
          <div class="stat-label">Total Lokasi Penanaman</div>
        </div>
      </div>
    </div>

    <!-- Event Mini Cards -->
    <div class="card" style="margin-bottom:20px">
      <div class="card-header">
        <h2>Ringkasan Event</h2>
        <button class="btn btn-primary" onclick="openModal('modalTambah')">
          <i class="bi bi-plus-lg"></i> Tambah Event
        </button>
      </div>
      <div class="event-cards">
        <?php $event_list->data_seek(0); while($ev=$event_list->fetch_assoc()):
          [$stLabel, $stClass] = statusEvent($ev['tgl_mulai'], $ev['tgl_selesai']);
          $tgl = $ev['tgl_mulai'] ? date('d M Y', strtotime($ev['tgl_mulai'])) : '-';
        ?>
        <div class="event-card-mini">
          <div class="ecm-date"><?= $tgl ?></div>
          <div class="ecm-name"><?= htmlspecialchars($ev['nama_evet']) ?></div>
          <div class="ecm-loc"><?= htmlspecialchars($ev['jenis_event']) ?></div>
          <div class="ecm-badges">
            <span class="badge <?= $stClass ?>"><?= $stLabel ?></span>
            <span class="badge gray"><?= number_format($ev['total_bibit'],0,',','.') ?> bibit</span>
          </div>
        </div>
        <?php endwhile; ?>
      </div>
    </div>

    <!-- Table -->
    <div class="card">
      <div class="card-header"><h2>Semua Event</h2></div>
      <div class="toolbar">
        <div class="search-wrap">
          <i class="bi bi-search"></i>
          <input type="text" class="search-input" id="searchEvent" placeholder="Cari event..." oninput="filterTable()">
        </div>
      </div>
      <div class="table-wrap">
        <table id="tblEvent">
          <thead>
            <tr>
              <th>ID</th>
              <th>Nama Event</th>
              <th>Jenis Event</th>
              <th>Tanggal</th>
              <th>Total Bibit</th>
              <th>Status</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
          <?php $event_list->data_seek(0); while($ev=$event_list->fetch_assoc()):
            [$stLabel, $stClass] = statusEvent($ev['tgl_mulai'], $ev['tgl_selesai']);
            $tgl = $ev['tgl_mulai'] ? date('d M Y', strtotime($ev['tgl_mulai'])) : '-';
          ?>
            <tr>
              <td><span style="font-family:monospace;font-size:12px;color:var(--text-muted)">#<?= htmlspecialchars($ev['id_event']) ?></span></td>
              <td><strong><?= htmlspecialchars($ev['nama_evet']) ?></strong></td>
              <td><?= htmlspecialchars($ev['jenis_event']) ?></td>
              <td><?= $tgl ?></td>
              <td><?= number_format($ev['total_bibit'],0,',','.') ?> btg</td>
              <td><span class="badge <?= $stClass ?>"><?= $stLabel ?></span></td>
              <td>
                <div style="display:flex;gap:6px">
                  <button class="btn btn-edit btn-sm"
                    onclick="openEdit('<?= $ev['id_event'] ?>','<?= addslashes($ev['nama_evet']) ?>','<?= addslashes($ev['jenis_event']) ?>')">
                    <i class="bi bi-pencil"></i>
                  </button>
                  <button class="btn btn-danger btn-sm"
                    onclick="openHapus('<?= $ev['id_event'] ?>','<?= addslashes($ev['nama_evet']) ?>')">
                    <i class="bi bi-trash"></i>
                  </button>
                </div>
              </td>
            </tr>
          <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- ── MODAL TAMBAH ── -->
<div class="modal-backdrop" id="modalTambah">
  <div class="modal">
    <div class="modal-header">
      <h3><i class="bi bi-calendar-plus"></i> Tambah Event</h3>
      <button class="modal-close" onclick="closeModal('modalTambah')">&times;</button>
    </div>
    <form method="POST">
      <input type="hidden" name="action" value="tambah">
      <div class="modal-body">
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">ID Event</label>
            <input class="form-control" name="id_event" placeholder="E0006" required maxlength="5">
          </div>
          <div class="form-group">
            <label class="form-label">Jenis Event</label>
            <input class="form-control" name="jenis_event" placeholder="Penanaman Mangrove" required>
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Nama Event</label>
          <input class="form-control" name="nama_evet" placeholder="Penanaman Mangrove Bali 2025" required>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn" style="background:#f3f4f6;color:var(--text)" onclick="closeModal('modalTambah')">Batal</button>
        <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> Simpan</button>
      </div>
    </form>
  </div>
</div>

<!-- ── MODAL EDIT ── -->
<div class="modal-backdrop" id="modalEdit">
  <div class="modal">
    <div class="modal-header">
      <h3><i class="bi bi-pencil-square"></i> Edit Event</h3>
      <button class="modal-close" onclick="closeModal('modalEdit')">&times;</button>
    </div>
    <form method="POST">
      <input type="hidden" name="action" value="edit">
      <input type="hidden" name="id_event" id="edit_id">
      <div class="modal-body">
        <div class="form-group">
          <label class="form-label">ID Event</label>
          <input class="form-control" id="edit_id_show" disabled style="background:#f0f4f2">
        </div>
        <div class="form-group">
          <label class="form-label">Nama Event</label>
          <input class="form-control" name="nama_evet" id="edit_nama" required>
        </div>
        <div class="form-group">
          <label class="form-label">Jenis Event</label>
          <input class="form-control" name="jenis_event" id="edit_jenis" required>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn" style="background:#f3f4f6;color:var(--text)" onclick="closeModal('modalEdit')">Batal</button>
        <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> Perbarui</button>
      </div>
    </form>
  </div>
</div>

<!-- ── MODAL HAPUS ── -->
<div class="modal-backdrop" id="modalHapus">
  <div class="modal" style="max-width:400px">
    <div class="modal-header">
      <h3 style="color:#b91c1c"><i class="bi bi-exclamation-triangle"></i> Hapus Event</h3>
      <button class="modal-close" onclick="closeModal('modalHapus')">&times;</button>
    </div>
    <form method="POST">
      <input type="hidden" name="action" value="hapus">
      <input type="hidden" name="id_event" id="hapus_id">
      <div class="modal-body">
        <p style="font-size:14px;color:var(--text)">Yakin hapus event <strong id="hapus_nama"></strong>? Data penanaman terkait juga akan dihapus.</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn" style="background:#f3f4f6;color:var(--text)" onclick="closeModal('modalHapus')">Batal</button>
        <button type="submit" class="btn" style="background:#b91c1c;color:#fff"><i class="bi bi-trash"></i> Hapus</button>
      </div>
    </form>
  </div>
</div>

<div class="toast" id="toast"></div>

<script>
function openModal(id){ document.getElementById(id).classList.add('open') }
function closeModal(id){ document.getElementById(id).classList.remove('open') }
document.querySelectorAll('.modal-backdrop').forEach(b => b.addEventListener('click', function(e){ if(e.target===this) this.classList.remove('open') }));

function openEdit(id, nama, jenis){
  document.getElementById('edit_id').value = id;
  document.getElementById('edit_id_show').value = id;
  document.getElementById('edit_nama').value = nama;
  document.getElementById('edit_jenis').value = jenis;
  openModal('modalEdit');
}
function openHapus(id, nama){
  document.getElementById('hapus_id').value = id;
  document.getElementById('hapus_nama').textContent = nama;
  openModal('modalHapus');
}
function filterTable(){
  const q = document.getElementById('searchEvent').value.toLowerCase();
  document.querySelectorAll('#tblEvent tbody tr').forEach(r => {
    r.style.display = r.textContent.toLowerCase().includes(q) ? '' : 'none';
  });
}
<?php if($msg): ?>
const t = document.getElementById('toast');
t.textContent = '<?= addslashes($msg) ?>';
<?php if($msgType==='error'): ?>t.classList.add('error');<?php endif; ?>
t.classList.add('show');
setTimeout(()=>t.classList.remove('show'), 3500);
<?php endif; ?>
</script>
</body>
</html>
