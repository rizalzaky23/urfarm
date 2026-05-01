<?php
session_start();
require_once '../config/connection.php';
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') { header('Location: ../auth/login.php'); exit; }

$msg = ''; $msgType = 'success';

// ── HANDLE CRUD ────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'tambah') {
        $id     = trim($_POST['id_bibit']);
        $jenis  = trim($_POST['jenis_pohon']);
        $nama   = trim($_POST['nama_pohon']);
        $harga  = (float)$_POST['harga'];
        $stmt = $conn->prepare("INSERT INTO bibit (id_bibit, jenis_pohon, nama_pohon, harga) VALUES (?,?,?,?)");
        $stmt->bind_param('sssd', $id, $jenis, $nama, $harga);
        if ($stmt->execute()) { $msg = 'Bibit berhasil ditambahkan!'; }
        else { $msg = 'Gagal: '.$conn->error; $msgType = 'error'; }
        $stmt->close();
    }

    if ($action === 'edit') {
        $id    = trim($_POST['id_bibit']);
        $jenis = trim($_POST['jenis_pohon']);
        $nama  = trim($_POST['nama_pohon']);
        $harga = (float)$_POST['harga'];
        $stmt = $conn->prepare("UPDATE bibit SET jenis_pohon=?, nama_pohon=?, harga=? WHERE id_bibit=?");
        $stmt->bind_param('ssds', $jenis, $nama, $harga, $id);
        if ($stmt->execute()) { $msg = 'Data bibit berhasil diperbarui!'; }
        else { $msg = 'Gagal: '.$conn->error; $msgType = 'error'; }
        $stmt->close();
    }

    if ($action === 'hapus') {
        $id = trim($_POST['id_bibit']);
        $stmt = $conn->prepare("DELETE FROM bibit WHERE id_bibit=?");
        $stmt->bind_param('s', $id);
        if ($stmt->execute()) { $msg = 'Bibit berhasil dihapus.'; }
        else { $msg = 'Gagal menghapus: '.$conn->error; $msgType = 'error'; }
        $stmt->close();
    }
}

// ── DATA ───────────────────────────────────────────────────────────────────
$bibit_list = $conn->query("SELECT b.*, COALESCE(SUM(p.jumlah_bibit),0) as total_tanam
    FROM bibit b LEFT JOIN penanaman p ON b.id_bibit=p.id_bibit GROUP BY b.id_bibit ORDER BY b.id_bibit");

$total_bibit_jenis = $conn->query("SELECT COUNT(*) as c FROM bibit")->fetch_assoc()['c'];
$total_bibit_tanam = $conn->query("SELECT COALESCE(SUM(jumlah_bibit),0) as c FROM penanaman")->fetch_assoc()['c'];
$total_kontak      = $conn->query("SELECT COUNT(*) as c FROM contact")->fetch_assoc()['c'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Bibit – Admin UrFarm</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="css/admin.css">
</head>
<body>

<?php include 'partials/sidebar.php'; ?>

<div class="main">
  <header class="topbar">
    <div class="topbar-left">
      <h1>Bibit</h1>
      <p>Manajemen inventaris dan data bibit</p>
    </div>
    <div class="topbar-right">
      <a href="../auth/logout.php" class="logout-btn"><i class="bi bi-box-arrow-right"></i> Keluar</a>
    </div>
  </header>

  <div class="content">

    <!-- Stats -->
    <div class="stats-grid-3">
      <div class="stat-card">
        <div class="stat-icon orange"><i class="bi bi-tree-fill"></i></div>
        <div class="stat-body">
          <div class="stat-value"><?= $total_bibit_jenis ?></div>
          <div class="stat-label">Jenis Bibit</div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon green"><i class="bi bi-flower1"></i></div>
        <div class="stat-body">
          <div class="stat-value"><?= number_format($total_bibit_tanam,0,',','.') ?></div>
          <div class="stat-label">Total Ditanam</div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon blue"><i class="bi bi-currency-dollar"></i></div>
        <div class="stat-body">
          <?php $avg = $conn->query("SELECT AVG(harga) as a FROM bibit")->fetch_assoc()['a']; ?>
          <div class="stat-value">Rp <?= number_format($avg,0,',','.') ?></div>
          <div class="stat-label">Rata-rata Harga/Bibit</div>
        </div>
      </div>
    </div>

    <!-- Table Card -->
    <div class="card">
      <div class="card-header">
        <h2>Inventaris Bibit</h2>
        <button class="btn btn-primary" onclick="openModal('modalTambah')">
          <i class="bi bi-plus-lg"></i> Tambah Bibit
        </button>
      </div>
      <div class="toolbar">
        <div class="search-wrap">
          <i class="bi bi-search"></i>
          <input type="text" class="search-input" id="searchBibit" placeholder="Cari bibit..." oninput="filterTable()">
        </div>
        <select class="filter-select" id="filterJenis" onchange="filterTable()">
          <option value="">Semua Jenis</option>
          <?php
          $jenis_list = $conn->query("SELECT DISTINCT jenis_pohon FROM bibit ORDER BY jenis_pohon");
          while($j=$jenis_list->fetch_assoc()): ?>
          <option value="<?= htmlspecialchars($j['jenis_pohon']) ?>"><?= htmlspecialchars($j['jenis_pohon']) ?></option>
          <?php endwhile; ?>
        </select>
      </div>
      <div class="table-wrap">
        <table id="tblBibit">
          <thead>
            <tr>
              <th>ID</th>
              <th>Jenis Pohon</th>
              <th>Nama Ilmiah</th>
              <th>Harga/btg</th>
              <th>Total Ditanam</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
          <?php $bibit_list->data_seek(0); while($b=$bibit_list->fetch_assoc()): ?>
            <tr data-jenis="<?= htmlspecialchars($b['jenis_pohon']) ?>">
              <td><span style="font-family:monospace;font-size:12px;color:var(--text-muted)">#<?= htmlspecialchars($b['id_bibit']) ?></span></td>
              <td>
                <div style="font-weight:600"><?= htmlspecialchars($b['jenis_pohon']) ?></div>
                <div style="font-size:11px;color:var(--text-muted);font-style:italic"><?= htmlspecialchars($b['nama_pohon']) ?></div>
              </td>
              <td><?= htmlspecialchars($b['nama_pohon']) ?></td>
              <td>Rp <?= number_format($b['harga'],0,',','.') ?></td>
              <td><?= number_format($b['total_tanam'],0,',','.') ?> btg</td>
              <td>
                <div style="display:flex;gap:6px">
                  <button class="btn btn-edit btn-sm"
                    onclick="openEdit('<?= $b['id_bibit'] ?>','<?= addslashes($b['jenis_pohon']) ?>','<?= addslashes($b['nama_pohon']) ?>',<?= $b['harga'] ?>)">
                    <i class="bi bi-pencil"></i>
                  </button>
                  <button class="btn btn-danger btn-sm"
                    onclick="openHapus('<?= $b['id_bibit'] ?>','<?= addslashes($b['jenis_pohon']) ?>')">
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

<!-- ── MODAL TAMBAH ─────────────────────────────────── -->
<div class="modal-backdrop" id="modalTambah">
  <div class="modal">
    <div class="modal-header">
      <h3><i class="bi bi-plus-circle"></i> Tambah Bibit</h3>
      <button class="modal-close" onclick="closeModal('modalTambah')">&times;</button>
    </div>
    <form method="POST">
      <input type="hidden" name="action" value="tambah">
      <div class="modal-body">
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">ID Bibit</label>
            <input class="form-control" name="id_bibit" placeholder="B0006" required maxlength="5">
          </div>
          <div class="form-group">
            <label class="form-label">Jenis Pohon</label>
            <input class="form-control" name="jenis_pohon" placeholder="Mangrove" required>
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Nama Ilmiah</label>
          <input class="form-control" name="nama_pohon" placeholder="Rhizophora apiculata" required>
        </div>
        <div class="form-group">
          <label class="form-label">Harga per Batang (Rp)</label>
          <input class="form-control" name="harga" type="number" min="0" placeholder="25000" required>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn" style="background:#f3f4f6;color:var(--text)" onclick="closeModal('modalTambah')">Batal</button>
        <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> Simpan</button>
      </div>
    </form>
  </div>
</div>

<!-- ── MODAL EDIT ───────────────────────────────────── -->
<div class="modal-backdrop" id="modalEdit">
  <div class="modal">
    <div class="modal-header">
      <h3><i class="bi bi-pencil-square"></i> Edit Bibit</h3>
      <button class="modal-close" onclick="closeModal('modalEdit')">&times;</button>
    </div>
    <form method="POST">
      <input type="hidden" name="action" value="edit">
      <input type="hidden" name="id_bibit" id="edit_id">
      <div class="modal-body">
        <div class="form-group">
          <label class="form-label">ID Bibit</label>
          <input class="form-control" id="edit_id_show" disabled style="background:#f0f4f2">
        </div>
        <div class="form-group">
          <label class="form-label">Jenis Pohon</label>
          <input class="form-control" name="jenis_pohon" id="edit_jenis" required>
        </div>
        <div class="form-group">
          <label class="form-label">Nama Ilmiah</label>
          <input class="form-control" name="nama_pohon" id="edit_nama" required>
        </div>
        <div class="form-group">
          <label class="form-label">Harga per Batang (Rp)</label>
          <input class="form-control" name="harga" id="edit_harga" type="number" min="0" required>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn" style="background:#f3f4f6;color:var(--text)" onclick="closeModal('modalEdit')">Batal</button>
        <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> Perbarui</button>
      </div>
    </form>
  </div>
</div>

<!-- ── MODAL HAPUS ──────────────────────────────────── -->
<div class="modal-backdrop" id="modalHapus">
  <div class="modal" style="max-width:400px">
    <div class="modal-header">
      <h3 style="color:#b91c1c"><i class="bi bi-exclamation-triangle"></i> Hapus Bibit</h3>
      <button class="modal-close" onclick="closeModal('modalHapus')">&times;</button>
    </div>
    <form method="POST">
      <input type="hidden" name="action" value="hapus">
      <input type="hidden" name="id_bibit" id="hapus_id">
      <div class="modal-body">
        <p style="font-size:14px;color:var(--text)">Yakin ingin menghapus bibit <strong id="hapus_nama"></strong>? Tindakan ini tidak bisa dibatalkan.</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn" style="background:#f3f4f6;color:var(--text)" onclick="closeModal('modalHapus')">Batal</button>
        <button type="submit" class="btn" style="background:#b91c1c;color:#fff"><i class="bi bi-trash"></i> Hapus</button>
      </div>
    </form>
  </div>
</div>

<!-- TOAST -->
<div class="toast" id="toast"></div>

<script>
function openModal(id){ document.getElementById(id).classList.add('open') }
function closeModal(id){ document.getElementById(id).classList.remove('open') }
document.querySelectorAll('.modal-backdrop').forEach(b => b.addEventListener('click', function(e){ if(e.target===this) this.classList.remove('open') }));

function openEdit(id, jenis, nama, harga){
  document.getElementById('edit_id').value = id;
  document.getElementById('edit_id_show').value = id;
  document.getElementById('edit_jenis').value = jenis;
  document.getElementById('edit_nama').value = nama;
  document.getElementById('edit_harga').value = harga;
  openModal('modalEdit');
}
function openHapus(id, nama){
  document.getElementById('hapus_id').value = id;
  document.getElementById('hapus_nama').textContent = jenis;
  openModal('modalHapus');
}
function openHapus(id, jenis){
  document.getElementById('hapus_id').value = id;
  document.getElementById('hapus_nama').textContent = jenis;
  openModal('modalHapus');
}

function filterTable(){
  const q = document.getElementById('searchBibit').value.toLowerCase();
  const jenis = document.getElementById('filterJenis').value.toLowerCase();
  document.querySelectorAll('#tblBibit tbody tr').forEach(row => {
    const txt = row.textContent.toLowerCase();
    const rowJenis = row.dataset.jenis?.toLowerCase() || '';
    row.style.display = (txt.includes(q) && (!jenis || rowJenis === jenis)) ? '' : 'none';
  });
}

// Show toast if message
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
