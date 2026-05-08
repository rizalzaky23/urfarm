<?php
session_start();
// if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit; }

// ── DB ──────────────────────────────────────────────────────────
$conn = new mysqli('localhost', 'root', '', 'urfarm_db');
$conn->set_charset('utf8mb4');

// ── HELPERS ─────────────────────────────────────────────────────
function h($v){ return htmlspecialchars((string)($v??''), ENT_QUOTES, 'UTF-8'); }
function qrow($c,$s){ $r=$c->query($s); return $r?$r->fetch_assoc():null; }
function qall($c,$s){ $r=$c->query($s); $d=[]; if($r) while($row=$r->fetch_assoc()) $d[]=$row; return $d; }
function badge($s){
    return match($s){
        'digunakan'=>'<span class="badge b-b">Digunakan</span>',
        'aktif'    =>'<span class="badge b-y">Aktif</span>',
        'nonaktif' =>'<span class="badge b-r">Nonaktif</span>',
        default    =>'<span class="badge b-x">'.h($s).'</span>',
    };
}
function fdate($d){ return $d ? date('d M Y',strtotime($d)) : '—'; }

// ── POST HANDLER ─────────────────────────────────────────────────
$msg = $msg_type = '';

if ($_SERVER['REQUEST_METHOD']==='POST'){
    $action = $_POST['action'] ?? '';

    if ($action==='generate'){
        $id_b = (int)($_POST['id_bibit']??0);
        $id_e = (int)($_POST['id_event']??0);
        $id_p = (int)($_POST['id_penanaman']??0);
        $qty  = min(100, max(1,(int)($_POST['jumlah']??1)));

        if ($id_b && $id_e && $id_p){
            $ok = 0;
            $stmt = $conn->prepare("INSERT INTO kode(kode,id_bibit,id_event,id_penanaman,status,tgl_generate) VALUES(?,?,?,?,'aktif',NOW())");
            for($i=0;$i<$qty;$i++){
                do {
                    $kode='UFM-'.strtoupper(substr(md5(uniqid(mt_rand(),true)),0,4));
                    $dup=$conn->query("SELECT 1 FROM kode WHERE kode='".h($kode)."' LIMIT 1");
                } while($dup && $dup->num_rows);
                $stmt->bind_param('siii',$kode,$id_b,$id_e,$id_p);
                if($stmt->execute()) $ok++;
            }
            $stmt->close();
            $msg="$ok kode berhasil di-generate!"; $msg_type='s';
        } else {
            $msg='Lengkapi semua field.'; $msg_type='e';
        }
    }

    if ($action==='hapus'){
        $id=(int)($_POST['id_kode']??0);
        if($id){ $conn->query("DELETE FROM kode WHERE id_kode=$id"); }
        $msg='Kode dihapus.'; $msg_type='e';
    }
}

// ── DROPDOWN DATA ────────────────────────────────────────────────
$events   = qall($conn,"SELECT id_event, nama_evet AS nm FROM event ORDER BY nm");
$bibit    = qall($conn,"SELECT id_bibit, jenis_pohon AS nm, nama_pohon FROM bibit ORDER BY nm");
$penaman  = qall($conn,"SELECT p.id_penanaman, p.lokasi, e.nama_evet AS nm_event FROM penanaman p LEFT JOIN event e ON p.id_event=e.id_event ORDER BY p.lokasi");

// ── STATS ────────────────────────────────────────────────────────
$stats = qrow($conn,"SELECT COUNT(*) total, SUM(status='digunakan') dig, SUM(status='aktif') aktif FROM kode") ?? [];

// ── FILTER + PAGINATE ────────────────────────────────────────────
$q      = trim($_GET['q']??'');
$fs     = $_GET['status']??'';
$fe     = (int)($_GET['id_event']??0);
$page   = max(1,(int)($_GET['page']??1));
$limit  = 10;
$offset = ($page-1)*$limit;

$where = ['1=1'];
$bind_t=''; $bind_v=[];

if($q!==''){
    $like="%$q%";
    $where[]="(k.kode LIKE ? OR u.nama LIKE ?)";
    $bind_t.='ss'; $bind_v[]=$like; $bind_v[]=$like;
}
if($fs!==''){
    $where[]='k.status=?'; $bind_t.='s'; $bind_v[]=$fs;
}
if($fe>0){
    $where[]='k.id_event=?'; $bind_t.='i'; $bind_v[]=$fe;
}
$wh=implode(' AND ',$where);

$base_sql="FROM kode k
    LEFT JOIN event     e ON k.id_event=e.id_event
    LEFT JOIN bibit     b ON k.id_bibit=b.id_bibit
    LEFT JOIN penanaman p ON k.id_penanaman=p.id_penanaman
    LEFT JOIN users     u ON k.id_user=u.id_user
    WHERE $wh";

// total rows
$cs=$conn->prepare("SELECT COUNT(*) c $base_sql");
if($bind_t) $cs->bind_param($bind_t,...$bind_v);
$cs->execute(); $total=$cs->get_result()->fetch_assoc()['c']??0; $cs->close();
$pages=max(1,ceil($total/$limit));

// rows
$ds=$conn->prepare("SELECT k.id_kode,k.kode,k.status,k.tgl_generate,b.jenis_pohon,b.nama_pohon,e.nama_evet nm_event,p.lokasi,u.nama nm_user $base_sql ORDER BY k.tgl_generate DESC LIMIT ? OFFSET ?");
$dt=$bind_t.'ii'; $dv=array_merge($bind_v,[$limit,$offset]);
$ds->bind_param($dt,...$dv); $ds->execute();
$rows=$ds->get_result()->fetch_all(MYSQLI_ASSOC); $ds->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>UrFarm — Kode</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="kode.css">
</head>
<body>

<aside class="sidebar">
  <div class="s-logo">
    <div class="s-icon">UF</div>
    <div class="s-brand">Ur<span>Farm</span></div>
  </div>
  <nav class="s-nav">
    <div class="s-sec">Utama</div>
    <a class="nav-item" href="dashboard.php"><i class="bi bi-grid-1x2"></i>Dashboard</a>
    <a class="nav-item" href="bibit.php"><i class="bi bi-tree"></i>Bibit</a>
    <a class="nav-item" href="event.php"><i class="bi bi-calendar-event"></i>Event</a>
    <div class="s-sec">Keuangan &amp; Lokasi</div>
    <a class="nav-item" href="dana.php"><i class="bi bi-wallet2"></i>Alokasi Dana</a>
    <a class="nav-item" href="lokasi.php"><i class="bi bi-geo-alt"></i>Lokasi &amp; Penanaman</a>
    <div class="s-sec">Konten</div>
    <a class="nav-item active" href="kode.php"><i class="bi bi-qr-code"></i>Kode</a>
    <a class="nav-item" href="publikasi.php"><i class="bi bi-newspaper"></i>Publikasi</a>
    <a class="nav-item" href="kontak.php"><i class="bi bi-envelope"></i>Kontak Masuk<span class="nav-badge">5</span></a>
  </nav>
  <div class="s-foot">
    <div class="admin-box">
      <div class="avatar" style="width:34px;height:34px;font-size:13px;">AD</div>
      <div>
        <div style="font-size:13px;font-weight:600;">Admin UrFarm</div>
        <div style="font-size:11px;color:var(--muted);">Super Admin</div>
      </div>
    </div>
  </div>
</aside>

<div class="main">
  <header class="topbar">
    <div>
      <div class="topbar-title">Kode</div>
      <div class="topbar-sub">Generate dan kelola kode tracking bibit</div>
    </div>
    <div class="topbar-right">
      <a class="icon-btn" href="notifikasi.php"><i class="bi bi-bell"></i></a>
      <a class="icon-btn" href="pengaturan.php"><i class="bi bi-gear"></i></a>
      <a class="btn btn-o sm" href="logout.php"><i class="bi bi-box-arrow-right"></i>Keluar</a>
    </div>
  </header>

  <div class="content">

    <?php if($msg): ?>
    <div class="alert alert-<?= $msg_type==='s'?'s':'e' ?>">
      <i class="bi bi-<?= $msg_type==='s'?'check-circle':'exclamation-circle' ?>"></i><?= h($msg) ?>
    </div>
    <?php endif; ?>

    <!-- STATS -->
    <div class="stats">
      <div class="stat"><div class="stat-ic ic-g"><i class="bi bi-qr-code"></i></div><div><div class="stat-val"><?= number_format($stats['total']??0) ?></div><div class="stat-lbl">Total Kode</div></div></div>
      <div class="stat"><div class="stat-ic ic-b"><i class="bi bi-check2-circle"></i></div><div><div class="stat-val"><?= number_format($stats['dig']??0) ?></div><div class="stat-lbl">Sudah Digunakan</div></div></div>
      <div class="stat"><div class="stat-ic ic-a"><i class="bi bi-hourglass-split"></i></div><div><div class="stat-val"><?= number_format($stats['aktif']??0) ?></div><div class="stat-lbl">Belum Digunakan</div></div></div>
    </div>

    <!-- TABLE CARD -->
    <div class="card">
      <div class="card-hd">
        <span class="card-title">Manajemen Kode Bibit</span>
        <button class="btn btn-p sm" onclick="openModal('m-gen')"><i class="bi bi-plus-lg"></i>Generate Kode</button>
      </div>

      <form method="GET" id="ff">
        <div class="filter">
          <div class="search">
            <i class="bi bi-search"></i>
            <input name="q" placeholder="Cari kode atau pengguna..." value="<?= h($q) ?>" oninput="dbs()">
          </div>
          <select class="fsel" name="status" onchange="this.form.submit()">
            <option value="">Semua Status</option>
            <option value="aktif"     <?= $fs==='aktif'?'selected':'' ?>>Belum Digunakan</option>
            <option value="digunakan" <?= $fs==='digunakan'?'selected':'' ?>>Sudah Digunakan</option>
            <option value="nonaktif"  <?= $fs==='nonaktif'?'selected':'' ?>>Nonaktif</option>
          </select>
          <select class="fsel" name="id_event" onchange="this.form.submit()">
            <option value="">Semua Event</option>
            <?php foreach($events as $e): ?>
            <option value="<?= $e['id_event'] ?>" <?= $fe===$e['id_event']?'selected':'' ?>><?= h($e['nm']) ?></option>
            <?php endforeach; ?>
          </select>
          <?php if($q||$fs||$fe): ?>
          <a href="kode.php" class="btn btn-o sm"><i class="bi bi-x-lg"></i>Reset</a>
          <?php endif; ?>
        </div>
      </form>

      <?php if(empty($rows)): ?>
      <div class="empty"><i class="bi bi-qr-code"></i><p>Tidak ada kode yang ditemukan.</p></div>
      <?php else: ?>
      <table class="tbl">
        <thead><tr><th>Kode</th><th>Bibit</th><th>Event</th><th>Lokasi</th><th>Pengguna</th><th>Tgl Generate</th><th>Status</th><th>Aksi</th></tr></thead>
        <tbody>
        <?php foreach($rows as $r): ?>
        <tr>
          <td><span class="chip"><?= h($r['kode']) ?></span></td>
          <td>
            <div style="font-weight:600"><?= h($r['jenis_pohon']??'—') ?></div>
            <?php if($r['nama_pohon']): ?><div style="font-size:11px;color:var(--muted)"><?= h($r['nama_pohon']) ?></div><?php endif; ?>
          </td>
          <td><?= h($r['nm_event']??'—') ?></td>
          <td><?= h($r['lokasi']??'—') ?></td>
          <td>
            <?php if($r['nm_user']): ?>
            <div style="display:flex;align-items:center;gap:6px">
              <div class="avatar" style="width:26px;height:26px;font-size:10px;background:var(--blue)"><?= strtoupper(substr($r['nm_user'],0,2)) ?></div>
              <span style="font-size:12px"><?= h($r['nm_user']) ?></span>
            </div>
            <?php else: ?><span style="font-size:12px;color:var(--muted)">— Belum dipakai</span><?php endif; ?>
          </td>
          <td style="font-size:12px;color:var(--muted)"><?= fdate($r['tgl_generate']) ?></td>
          <td><?= badge($r['status']) ?></td>
          <td>
            <div style="display:flex;gap:6px">
              <button class="btn btn-o sm ic" title="Detail" onclick='openDetail(<?= json_encode($r,JSON_HEX_QUOT) ?>)'><i class="bi bi-eye"></i></button>
              <button class="btn btn-o sm ic" title="Salin" onclick="copy('<?= h($r['kode']) ?>')"><i class="bi bi-clipboard"></i></button>
              <button class="btn-del" title="Hapus" onclick="konfHapus(<?= $r['id_kode'] ?>,'<?= h($r['kode']) ?>')"><i class="bi bi-trash"></i></button>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
      </table>

      <!-- PAGINATION -->
      <?php if($pages>1): ?>
      <div class="pag">
        <div class="pag-info">
          <?= $offset+1 ?>–<?= min($offset+$limit,$total) ?> dari <?= number_format($total) ?> kode
        </div>
        <div class="pag-btns">
          <button class="pb" <?= $page<=1?'disabled':'' ?> onclick="go(<?= $page-1 ?>)"><i class="bi bi-chevron-left"></i></button>
          <?php for($p=max(1,$page-2);$p<=min($pages,$page+2);$p++): ?>
          <button class="pb <?= $p===$page?'on':'' ?>" onclick="go(<?= $p ?>)"><?= $p ?></button>
          <?php endfor; ?>
          <button class="pb" <?= $page>=$pages?'disabled':'' ?> onclick="go(<?= $page+1 ?>)"><i class="bi bi-chevron-right"></i></button>
        </div>
      </div>
      <?php endif; ?>
      <?php endif; ?>
    </div>

  </div><!-- .content -->
</div><!-- .main -->

<button class="fab" onclick="openModal('m-gen')"><i class="bi bi-plus-lg"></i></button>

<!-- ── MODAL GENERATE ── -->
<div class="overlay" id="m-gen">
  <div class="modal">
    <div class="modal-hd">
      <div class="modal-title">Generate Kode Bibit</div>
      <button class="mx" onclick="closeModal('m-gen')"><i class="bi bi-x-lg"></i></button>
    </div>
    <form method="POST">
      <input type="hidden" name="action" value="generate">
      <div class="row2">
        <div class="fg">
          <label class="lbl">Jenis Bibit *</label>
          <select class="fc" name="id_bibit" required>
            <option value="">Pilih Bibit...</option>
            <?php foreach($bibit as $b): ?>
            <option value="<?= $b['id_bibit'] ?>"><?= h($b['nm']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="fg">
          <label class="lbl">Event *</label>
          <select class="fc" name="id_event" required>
            <option value="">Pilih Event...</option>
            <?php foreach($events as $e): ?>
            <option value="<?= $e['id_event'] ?>"><?= h($e['nm']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
      <div class="fg">
        <label class="lbl">Lokasi Penanaman *</label>
        <select class="fc" name="id_penanaman" required>
          <option value="">Pilih Lokasi...</option>
          <?php foreach($penaman as $p): ?>
          <option value="<?= $p['id_penanaman'] ?>"><?= h($p['lokasi']) ?> — <?= h($p['nm_event']??'') ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="row2">
        <div class="fg">
          <label class="lbl">Jumlah Kode</label>
          <input class="fc" type="number" name="jumlah" value="1" min="1" max="100" oninput="prvw()">
          <div style="font-size:11px;color:var(--muted);margin-top:4px">Maks. 100 sekaligus</div>
        </div>
        <div class="fg">
          <label class="lbl">Preview Format</label>
          <div class="preview-box" id="prvw-box">UFM-????</div>
        </div>
      </div>
      <div class="modal-ft">
        <button type="button" class="btn btn-o" onclick="closeModal('m-gen')">Batal</button>
        <button type="submit" class="btn btn-p"><i class="bi bi-cpu"></i>Generate</button>
      </div>
    </form>
  </div>
</div>

<!-- ── MODAL DETAIL ── -->
<div class="overlay" id="m-det">
  <div class="modal">
    <div class="modal-hd">
      <div class="modal-title">Detail Kode</div>
      <button class="mx" onclick="closeModal('m-det')"><i class="bi bi-x-lg"></i></button>
    </div>
    <div style="text-align:center;margin-bottom:18px">
      <span class="chip" id="det-chip" style="font-size:20px;padding:10px 22px;letter-spacing:5px"></span>
    </div>
    <div id="det-body"></div>
    <div class="modal-ft">
      <button class="btn btn-o" onclick="closeModal('m-det')">Tutup</button>
      <button class="btn btn-o" id="det-copy"><i class="bi bi-clipboard"></i>Salin Kode</button>
    </div>
  </div>
</div>

<!-- ── MODAL HAPUS ── -->
<div class="overlay" id="m-hps">
  <div class="modal" style="max-width:400px">
    <div class="modal-hd">
      <div class="modal-title" style="color:var(--red)">Konfirmasi Hapus</div>
      <button class="mx" onclick="closeModal('m-hps')"><i class="bi bi-x-lg"></i></button>
    </div>
    <div style="width:52px;height:52px;border-radius:50%;background:var(--red-l);color:var(--red);font-size:22px;display:flex;align-items:center;justify-content:center;margin-bottom:14px">
      <i class="bi bi-trash3"></i>
    </div>
    <p style="font-size:14px;color:var(--muted);margin-bottom:6px">Hapus kode <strong id="hps-label"></strong>?</p>
    <p style="font-size:12px;color:var(--red)">Tindakan ini tidak dapat dibatalkan.</p>
    <form method="POST">
      <input type="hidden" name="action" value="hapus">
      <input type="hidden" name="id_kode" id="hps-id">
      <div class="modal-ft">
        <button type="button" class="btn btn-o" onclick="closeModal('m-hps')">Batal</button>
        <button type="submit" class="btn btn-d"><i class="bi bi-trash"></i>Hapus</button>
      </div>
    </form>
  </div>
</div>

<div class="toasts" id="tc"></div>

<script>
const $ = id => document.getElementById(id);

// Modal
function openModal(id){ $(id).classList.add('open') }
function closeModal(id){ $(id).classList.remove('open') }
document.querySelectorAll('.overlay').forEach(o=>o.addEventListener('click',e=>{ if(e.target===o) o.classList.remove('open') }));

// Toast
function toast(msg,err=false){
  const t=document.createElement('div');
  t.className='toast'+(err?' err':'');
  t.textContent=(err?'🗑 ':' ✅ ')+msg;
  $('tc').appendChild(t);
  setTimeout(()=>{ t.style.cssText='opacity:0;transform:translateY(20px);transition:all .3s'; setTimeout(()=>t.remove(),300) },2500);
}

// Salin
function copy(k){
  navigator.clipboard.writeText(k).then(()=>toast('Kode '+k+' disalin!')).catch(()=>toast('Gagal menyalin',true));
}

// Preview kode
function prvw(){
  const s=Math.random().toString(36).substr(2,4).toUpperCase();
  $('prvw-box').textContent='UFM-'+s;
}

// Detail
const statMap={'aktif':'<span class="badge b-y">Aktif</span>','digunakan':'<span class="badge b-b">Digunakan</span>','nonaktif':'<span class="badge b-r">Nonaktif</span>'};
const fdate=d=>d?new Date(d).toLocaleDateString('id-ID',{day:'2-digit',month:'short',year:'numeric'}):'—';

function openDetail(d){
  $('det-chip').textContent=d.kode;
  $('det-copy').onclick=()=>copy(d.kode);
  const rows=[
    ['Bibit', (d.jenis_pohon||'—')+(d.nama_pohon?` <span style="font-size:11px;color:var(--muted)">(${d.nama_pohon})</span>`:'')],
    ['Event', d.nm_event||'—'],
    ['Lokasi', d.lokasi||'—'],
    ['Pengguna', d.nm_user||'— Belum dipakai'],
    ['Tgl Generate', fdate(d.tgl_generate)],
    ['Status', statMap[d.status]||d.status],
  ];
  $('det-body').innerHTML=rows.map(([l,v])=>`<div class="d-row"><div class="d-lbl">${l}</div><div>${v}</div></div>`).join('');
  openModal('m-det');
}

// Hapus
function konfHapus(id,kode){
  $('hps-id').value=id;
  $('hps-label').textContent=kode;
  openModal('m-hps');
}

// Pagination
function go(p){ const u=new URL(location.href); u.searchParams.set('page',p); location.href=u }

// Debounce search
let _t;
function dbs(){ clearTimeout(_t); _t=setTimeout(()=>$('ff').submit(),500) }

// Auto toast dari PHP
<?php if($msg): ?>
document.addEventListener('DOMContentLoaded',()=>toast(<?= json_encode($msg) ?>,<?= $msg_type==='e'?'true':'false' ?>));
<?php endif; ?>
</script>
</body>
</html>