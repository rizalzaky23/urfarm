<?php
session_start();
require_once '../config/connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../auth/login.php');
    exit;
}
// upload config
define('UPLOAD_DIR', 'uploads/publikasi/');
if (!is_dir(UPLOAD_DIR)) mkdir(UPLOAD_DIR, 0755, true);

function uploadGambar($file) {
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if ($file['error'] || !in_array($ext, ['jpg','jpeg','png','webp']) || $file['size'] > 5*1024*1024) return false;
    $name = 'pub_' . time() . '_' . bin2hex(random_bytes(3)) . '.' . $ext;
    return move_uploaded_file($file['tmp_name'], UPLOAD_DIR . $name) ? $name : false;
}

function flash($msg, $type = 'success') { $_SESSION['flash'] = compact('msg','type'); }
function getFlash() { $f = $_SESSION['flash'] ?? null; unset($_SESSION['flash']); return $f; }

function genId($conn) {
    $r = $conn->query("SELECT id_publikasi FROM publikasi ORDER BY id_publikasi DESC LIMIT 1")->fetch_assoc();
    $num = $r ? (int)substr($r['id_publikasi'], 2) + 1 : 1;
    return 'PB' . str_pad($num, 3, '0', STR_PAD_LEFT);
}

// handle post
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $act = $_POST['action'] ?? '';

    if (in_array($act, ['tambah','edit'])) {
        $judul   = trim($_POST['judul'] ?? '');
        $id_ev   = $_POST['id_event'] ?? '';
        $tgl     = $_POST['tanggal_publikasi'] ?? '';
        $isi     = trim($_POST['isi'] ?? '');
        $errors  = [];

        if (!$judul) $errors[] = 'Judul wajib diisi.';
        if (!$isi)   $errors[] = 'Isi artikel wajib diisi.';
        if (!$tgl)   $errors[] = 'Tanggal wajib diisi.';

        $gambar = $_POST['gambar_lama'] ?? '';
        if (!empty($_FILES['gambar']['name'])) {
            $up = uploadGambar($_FILES['gambar']);
            if (!$up) $errors[] = 'Gagal upload gambar. Format: jpg/png/webp, maks 5MB.';
            else {
                if ($gambar && file_exists(UPLOAD_DIR . $gambar)) unlink(UPLOAD_DIR . $gambar);
                $gambar = $up;
            }
        }

        if (empty($errors)) {
            if ($act === 'tambah') {
                $id = genId($conn);
                $s = $conn->prepare("INSERT INTO publikasi VALUES (?,?,?,?,?,?)");
                $s->bind_param('ssssss', $id, $id_ev, $tgl, $judul, $gambar, $isi);
            } else {
                $id = $_POST['id_publikasi'];
                $s = $conn->prepare("UPDATE publikasi SET id_event=?,tanggal_publikasi=?,judul=?,gambar=?,isi=? WHERE id_publikasi=?");
                $s->bind_param('ssssss', $id_ev, $tgl, $judul, $gambar, $isi, $id);
            }
            $s->execute(); $s->close();
            flash($act === 'tambah' ? 'Publikasi berhasil ditambahkan!' : 'Publikasi berhasil diperbarui!');
            header('Location: publikasi.php'); exit;
        }
    }

    if ($act === 'hapus') {
        $id  = $_POST['id_publikasi'];
        $row = $conn->query("SELECT gambar,judul FROM publikasi WHERE id_publikasi='$id'")->fetch_assoc();
        if ($row) {
            if ($row['gambar'] && file_exists(UPLOAD_DIR . $row['gambar'])) unlink(UPLOAD_DIR . $row['gambar']);
            $conn->query("DELETE FROM publikasi WHERE id_publikasi='$id'");
            flash('Publikasi "' . $row['judul'] . '" dihapus.', 'danger');
        }
        header('Location: publikasi.php'); exit;
    }
}

// data pendukung
$events    = $conn->query("SELECT id_event, nama_evet FROM event ORDER BY nama_evet")->fetch_all(MYSQLI_ASSOC);

// filters & pagination
$q      = trim($_GET['q'] ?? '');
$fEvent = $_GET['id_event'] ?? '';
$perPg  = 9;
$page   = max(1, (int)($_GET['p'] ?? 1));
$offset = ($page - 1) * $perPg;

$where = []; $params = []; $types = '';
if ($q)      { $where[] = '(p.judul LIKE ? OR p.isi LIKE ?)'; $like="%$q%"; $params[]=$like; $params[]=$like; $types.='ss'; }
if ($fEvent) { $where[] = 'p.id_event=?'; $params[]=$fEvent; $types.='s'; }
$wsql = $where ? 'WHERE '.implode(' AND ',$where) : '';

$sc = $conn->prepare("SELECT COUNT(*) c FROM publikasi p $wsql");
if ($params) $sc->bind_param($types, ...$params);
$sc->execute();
$total = $sc->get_result()->fetch_assoc()['c'];
$sc->close();
$totalPg = max(1, (int)ceil($total / $perPg));

$sd = $conn->prepare("SELECT p.*,e.nama_evet,e.jenis_event FROM publikasi p LEFT JOIN event e ON p.id_event=e.id_event $wsql ORDER BY p.tanggal_publikasi DESC LIMIT ? OFFSET ?");
$sd->bind_param($types.'ii', ...array_merge($params, [$perPg, $offset]));
$sd->execute();
$rows = $sd->get_result()->fetch_all(MYSQLI_ASSOC);
$sd->close();

// stats
$stTotal = (int)$conn->query("SELECT COUNT(*) c FROM publikasi")->fetch_assoc()['c'];
$stBulan = (int)$conn->query("SELECT COUNT(*) c FROM publikasi WHERE MONTH(tanggal_publikasi)=MONTH(CURDATE()) AND YEAR(tanggal_publikasi)=YEAR(CURDATE())")->fetch_assoc()['c'];
$stEvent = (int)$conn->query("SELECT COUNT(DISTINCT id_event) c FROM publikasi")->fetch_assoc()['c'];

// edit prefill
$editRow = null; $showModal = false;
$editId  = $_GET['edit'] ?? '';
if ($editId) { $editRow = $conn->query("SELECT * FROM publikasi WHERE id_publikasi='$editId'")->fetch_assoc(); $showModal=(bool)$editRow; }

$flash = getFlash();

function style($jenis) {
    return match(strtolower($jenis ?? '')) {
        'penanaman' => ['🌊','linear-gradient(135deg,#D8F3DC,#95D5B2)'],
        'edukasi'   => ['🌿','linear-gradient(135deg,#FFF8E1,#FFE082)'],
        'program'   => ['🌱','linear-gradient(135deg,#F3E5F5,#CE93D8)'],
        default     => ['📰','linear-gradient(135deg,#EBF3FE,#90CAF9)'],
    };
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Publikasi — UrFarm Admin</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;600;700&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="css/sidebar.css?v=<?= time() ?>">
<link rel="stylesheet" href="css/publikasi.css?v=<?= time() ?>">
</head>
<body>

<?php include 'partials/sidebar.php'; ?>

<div class="main">
  <div class="topbar">
    <div>
      <div class="topbar-title">Publikasi</div>
      <div class="topbar-subtitle">Buat dan kelola konten publikasi</div>
    </div>
    <div class="topbar-actions">
      <a href="#" class="topbar-icon-btn"><i class="bi bi-bell"></i></a>
      <a href="#" class="topbar-icon-btn"><i class="bi bi-gear"></i></a>
      <button class="btn btn-primary btn-sm" onclick="openModal('modal-pub')">
        <i class="bi bi-plus-lg"></i> Tulis Publikasi
      </button>
    </div>
  </div>

  <div class="page-content">

    <?php if ($flash): ?>
    <div class="alert alert-<?= $flash['type']==='danger'?'danger':'success' ?> d-flex align-items-center gap-2" id="flash-msg">
      <i class="bi bi-<?= $flash['type']==='danger'?'x-circle-fill':'check-circle-fill' ?>"></i>
      <?= htmlspecialchars($flash['msg']) ?>
      <button class="btn-close ms-auto" onclick="this.parentElement.remove()"></button>
    </div>
    <?php endif; ?>

    <div class="stats-grid mb-4">
      <div class="stat-card">
        <div class="stat-icon green"><i class="bi bi-newspaper"></i></div>
        <div><div class="stat-val"><?= $stTotal ?></div><div class="stat-label">Total Publikasi</div></div>
      </div>
      <div class="stat-card">
        <div class="stat-icon blue"><i class="bi bi-calendar3"></i></div>
        <div><div class="stat-val"><?= $stBulan ?></div><div class="stat-label">Bulan Ini</div></div>
      </div>
      <div class="stat-card">
        <div class="stat-icon gold"><i class="bi bi-diagram-3-fill"></i></div>
        <div><div class="stat-val"><?= $stEvent ?></div><div class="stat-label">Event Terlibat</div></div>
      </div>
    </div>

    <form method="GET" id="filter-form">
      <div class="filter-bar">
        <div class="search-wrap">
          <i class="bi bi-search"></i>
          <input class="search-input" name="q" placeholder="Cari publikasi..."
                 value="<?= htmlspecialchars($q) ?>" oninput="debounce(this.form)">
        </div>
        <select class="filter-select" name="id_event" onchange="this.form.submit()">
          <option value="">Semua Event</option>
          <?php foreach ($events as $ev): ?>
          <option value="<?= $ev['id_event'] ?>" <?= $fEvent===$ev['id_event']?'selected':'' ?>>
            <?= htmlspecialchars($ev['nama_evet']) ?>
          </option>
          <?php endforeach; ?>
        </select>
        <?php if ($q || $fEvent): ?>
        <a href="publikasi.php" class="btn btn-outline btn-sm"><i class="bi bi-x-lg"></i> Reset</a>
        <?php endif; ?>
        <button type="button" class="btn btn-primary btn-sm ms-auto" onclick="openModal('modal-pub')">
          <i class="bi bi-plus-lg"></i> Tulis Publikasi
        </button>
      </div>
    </form>

    <div class="pub-grid">
      <?php if (empty($rows)): ?>
      <div class="empty-state">
        <div style="font-size:48px">📭</div>
        <h5 class="mt-2">Belum ada publikasi</h5>
        <p>Mulai tulis artikel pertama!</p>
        <button class="btn btn-primary mt-2" onclick="openModal('modal-pub')"><i class="bi bi-plus-lg"></i> Tulis Sekarang</button>
      </div>
      <?php else: foreach ($rows as $pub):
        [$emoji, $bg] = style($pub['jenis_event'] ?? '');
        $hasImg = $pub['gambar'] && file_exists(UPLOAD_DIR . $pub['gambar']);
      ?>
      <div class="pub-card">
        <div class="pub-thumb" style="background:<?= $hasImg ? '#f0faf3' : $bg ?>;">
          <?php if ($hasImg): ?>
            <img src="<?= UPLOAD_DIR . htmlspecialchars($pub['gambar']) ?>" alt="thumb">
          <?php else: ?><span style="font-size:40px"><?= $emoji ?></span><?php endif; ?>
        </div>
        <div class="pub-body">
          <div class="pub-category"><?= htmlspecialchars($pub['nama_evet'] ?? $pub['id_event']) ?></div>
          <div class="pub-title"><?= htmlspecialchars($pub['judul']) ?></div>
          <div class="pub-date">
            <i class="bi bi-calendar3"></i> <?= date('d M Y', strtotime($pub['tanggal_publikasi'])) ?>
            &nbsp;•&nbsp; <span class="badge badge-gray"><?= $pub['id_publikasi'] ?></span>
          </div>
          <div class="pub-actions">
            <a href="?edit=<?= $pub['id_publikasi'] ?>" class="btn btn-outline btn-sm">
              <i class="bi bi-pencil"></i> Edit
            </a>
            <button class="btn btn-sm btn-icon-only" style="background:var(--red-light);color:var(--red);border:none;"
                    onclick="confirmDelete('<?= $pub['id_publikasi'] ?>','<?= addslashes(htmlspecialchars($pub['judul'])) ?>')">
              <i class="bi bi-trash"></i>
            </button>
          </div>
        </div>
      </div>
      <?php endforeach; endif; ?>
    </div>

    <?php if ($totalPg > 1): $qs = http_build_query(['q'=>$q,'id_event'=>$fEvent]); ?>
    <div class="pagination-wrap mt-4">
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

  </div>
</div>

<!-- modal tambah/edit -->
<div class="modal-overlay <?= $showModal?'open':'' ?>" id="modal-pub">
  <div class="pub-modal pub-modal-lg">
    <div class="modal-header">
      <div class="modal-title">
        <span class="modal-title-icon"><i class="bi bi-<?= $editRow ? 'pencil-square' : 'plus-circle-fill' ?>"></i></span>
        <?= $editRow ? 'Edit Publikasi' : 'Tulis Publikasi Baru' ?>
      </div>
      <button class="modal-close" onclick="closeModal('modal-pub')"><i class="bi bi-x-lg"></i></button>
    </div>
    <div class="modal-body">
    <form method="POST" id="form-pub">
      <input type="hidden" name="action" value="<?= $editRow?'edit':'tambah' ?>">
      <?php if ($editRow): ?>
      <input type="hidden" name="id_publikasi" value="<?= $editRow['id_publikasi'] ?>">
      <input type="hidden" name="gambar_lama"  value="<?= htmlspecialchars($editRow['gambar']??'') ?>">
      <?php endif; ?>

      <?php if (!empty($errors)): ?>
      <div class="alert alert-danger mb-3">
        <?php foreach($errors as $e): ?><div><?= htmlspecialchars($e) ?></div><?php endforeach; ?>
      </div>
      <?php endif; ?>

      <div class="form-group">
        <label class="form-label">Judul *</label>
        <input class="form-control" name="judul" required placeholder="Judul artikel..."
               value="<?= htmlspecialchars($editRow['judul']??'') ?>">
      </div>

      <div class="form-control-row">
        <div class="form-group">
          <label class="form-label">Event</label>
          <select class="form-control" name="id_event">
            <option value="">— Pilih Event —</option>
            <?php foreach($events as $ev): ?>
            <option value="<?= $ev['id_event'] ?>" <?= ($editRow['id_event']??'')===$ev['id_event']?'selected':'' ?>>
              <?= htmlspecialchars($ev['nama_evet']) ?>
            </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Tanggal Publikasi *</label>
          <input class="form-control" type="date" name="tanggal_publikasi" required
                 value="<?= $editRow['tanggal_publikasi'] ?? date('Y-m-d') ?>">
        </div>
      </div>

      <div class="form-group">
        <label class="form-label">Gambar</label>
        <input class="form-control" type="file" name="gambar" accept="image/*" onchange="previewThumb(this)">
        <div class="thumb-preview" id="thumb-preview">
          <?php if ($editRow && $editRow['gambar'] && file_exists(UPLOAD_DIR.$editRow['gambar'])): ?>
            <img src="<?= UPLOAD_DIR.htmlspecialchars($editRow['gambar']) ?>" alt="preview">
          <?php else: ?><span>🖼️</span><?php endif; ?>
        </div>
      </div>

      <div class="form-group">
        <label class="form-label">Isi Artikel *</label>
        <textarea class="form-control" name="isi" rows="5" required
                  placeholder="Tulis konten di sini..."><?= htmlspecialchars($editRow['isi']??'') ?></textarea>
      </div>
    </div><!-- /.modal-body -->
    <div class="modal-footer">
      <button type="button" class="btn btn-outline" onclick="closeModal('modal-pub')"><i class="bi bi-x"></i> Batal</button>
      <button type="submit" form="form-pub" class="btn btn-primary">
        <i class="bi bi-<?= $editRow ? 'check2-circle' : 'send-fill' ?>"></i>
        <?= $editRow ? 'Simpan Perubahan' : 'Publikasikan' ?>
      </button>
    </div>
  </div><!-- /.pub-modal -->
</div><!-- /.modal-overlay -->

<!-- modal hapus -->
<div class="modal-overlay" id="modal-hapus">
  <div class="pub-modal pub-modal-sm">
    <div class="modal-header" style="background:linear-gradient(135deg,#991b1b,#dc2626)">
      <div class="modal-title"><span class="modal-title-icon"><i class="bi bi-trash3-fill"></i></span>Hapus Publikasi</div>
      <button class="modal-close" onclick="closeModal('modal-hapus')"><i class="bi bi-x-lg"></i></button>
    </div>
    <div class="modal-body" style="text-align:center;padding:28px 24px 20px">
      <div style="width:60px;height:60px;background:#fee2e2;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 14px;font-size:26px;color:#dc2626;"><i class="bi bi-trash3-fill"></i></div>
      <p style="font-size:15px;font-weight:700;color:var(--text);margin-bottom:6px">Hapus artikel ini?</p>
      <p id="del-nama" style="font-size:13px;font-weight:600;color:var(--green-900);margin-bottom:14px"></p>
      <div style="font-size:12px;color:#dc2626;background:#fef2f2;border-radius:8px;padding:8px 14px;display:flex;align-items:center;justify-content:center;gap:6px">
        <i class="bi bi-exclamation-triangle-fill"></i> Tindakan ini tidak dapat dibatalkan
      </div>
    </div>
    <div class="modal-footer" style="justify-content:center;gap:12px">
      <form method="POST" style="display:contents">
        <input type="hidden" name="action" value="hapus">
        <input type="hidden" name="id_publikasi" id="del-id">
        <button type="button" class="btn btn-outline" onclick="closeModal('modal-hapus')" style="min-width:90px"><i class="bi bi-x"></i> Batal</button>
        <button type="submit" class="btn btn-danger" style="min-width:110px"><i class="bi bi-trash3-fill"></i> Ya, Hapus</button>
      </form>
    </div>
  </div>
</div>

<button class="fab" onclick="openModal('modal-pub')" title="Tulis Publikasi"><i class="bi bi-plus-lg"></i></button>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
function openModal(id)  { document.getElementById(id).classList.add('open'); document.body.style.overflow='hidden'; }
function closeModal(id) { document.getElementById(id).classList.remove('open'); document.body.style.overflow=''; }

document.querySelectorAll('.modal-overlay').forEach(o =>
  o.addEventListener('click', e => { if(e.target===o){o.classList.remove('open');document.body.style.overflow='';} })
);
document.addEventListener('keydown', e => {
  if(e.key==='Escape') document.querySelectorAll('.modal-overlay.open').forEach(o=>{o.classList.remove('open');document.body.style.overflow='';});
});

function confirmDelete(id, nama) {
  document.getElementById('del-id').value = id;
  document.getElementById('del-nama').textContent = '"' + nama + '"';
  openModal('modal-hapus');
}
function previewThumb(input) {
  if (!input.files[0]) return;
  const r = new FileReader();
  r.onload = e => document.getElementById('thumb-preview').innerHTML = '<img src="'+e.target.result+'" alt="preview">';
  r.readAsDataURL(input.files[0]);
}
let dt;
function debounce(form) { clearTimeout(dt); dt=setTimeout(()=>form.submit(),500); }

<?php if($showModal): ?>document.addEventListener('DOMContentLoaded',()=>openModal('modal-pub'));<?php endif; ?>
<?php if($flash): ?>setTimeout(()=>{const el=document.getElementById('flash-msg');if(el){el.style.transition='opacity .4s';el.style.opacity='0';setTimeout(()=>el.remove(),400);}},4000);<?php endif; ?>
</script>
</body>
</html>