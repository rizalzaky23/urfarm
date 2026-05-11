<?php
session_start();

require_once '../config/connection.php';

function h($v){ return htmlspecialchars((string)($v??''), ENT_QUOTES, 'UTF-8'); }
function qrow($c,$s){ $r=$c->query($s); return $r?$r->fetch_assoc():null; }
function qall($c,$s){ $r=$c->query($s); $d=[]; if($r) while($row=$r->fetch_assoc()) $d[]=$row; return $d; }
function badgeStatus($s){
    return match($s){
        'tumbuh'     =>'<span class="badge b-b">Tumbuh</span>',
        'benih baru' =>'<span class="badge b-y">Benih Baru</span>',
        default      =>'<span class="badge b-x">'.h($s).'</span>',
    };
}
function fdate($d){ return $d ? date('d M Y',strtotime($d)) : '—'; }

// ── POST HANDLER ─────────────────────────────────────────────────
$msg = $msg_type = '';

if ($_SERVER['REQUEST_METHOD']==='POST'){
    $action = $_POST['action'] ?? '';

    if ($action==='generate'){
        $id_titik = trim($_POST['id_titik'] ?? '');
        $qty      = min(100, max(1,(int)($_POST['jumlah']??1)));

        if ($id_titik){
            $ok = 0;
            // auto-generate id_kode: K0001, K0002, dst
            $last = qrow($conn, "SELECT id_kode FROM kode_titik ORDER BY id_kode DESC LIMIT 1");
            $lastNum = $last ? (int)substr($last['id_kode'], 1) : 0;

            $stmt = $conn->prepare("INSERT INTO kode_titik(id_kode, id_titik, kode_unik) VALUES(?,?,?)");
            for($i=0; $i<$qty; $i++){
                $lastNum++;
                $id_kode = 'K'.str_pad($lastNum, 4, '0', STR_PAD_LEFT);
                do {
                    $kode_unik = 'UFM-'.strtoupper(substr(md5(uniqid(mt_rand(),true)),0,8));
                    $dup = $conn->query("SELECT 1 FROM kode_titik WHERE kode_unik='".h($kode_unik)."' LIMIT 1");
                } while($dup && $dup->num_rows);
                $stmt->bind_param('sss', $id_kode, $id_titik, $kode_unik);
                if($stmt->execute()) $ok++;
            }
            $stmt->close();
            $msg="$ok kode berhasil di-generate!"; $msg_type='s';
        } else {
            $msg='Pilih titik lokasi terlebih dahulu.'; $msg_type='e';
        }
    }

    if ($action==='hapus'){
        $id = trim($_POST['id_kode'] ?? '');
        if($id){
            $stmt = $conn->prepare("DELETE FROM kode_titik WHERE id_kode=?");
            $stmt->bind_param('s', $id);
            $stmt->execute(); $stmt->close();
        }
        $msg='Kode dihapus.'; $msg_type='e';
    }
}

// ── DROPDOWN DATA ────────────────────────────────────────────────
$events  = qall($conn, "SELECT id_event, nama_evet AS nm FROM event ORDER BY nm");
$titiks  = qall($conn, "SELECT tl.id_titik, tl.status, e.nama_evet AS nm_event
                         FROM titik_lokasi tl
                         LEFT JOIN event e ON tl.id_event = e.id_event
                         ORDER BY tl.id_titik");

// ── STATS ────────────────────────────────────────────────────────
$stats = qrow($conn, "SELECT COUNT(*) total FROM kode_titik") ?? [];
$stats['total'] = $stats['total'] ?? 0;

// ── FILTER + PAGINATE ────────────────────────────────────────────
$q      = trim($_GET['q']??'');
$fe     = trim($_GET['id_event']??'');
$page   = max(1,(int)($_GET['page']??1));
$limit  = 10;
$offset = ($page-1)*$limit;

$where = ['1=1'];
$bind_t=''; $bind_v=[];

if($q!==''){
    $like="%$q%";
    $where[]="k.kode_unik LIKE ?";
    $bind_t.='s'; $bind_v[]=$like;
}
if($fe!==''){
    $where[]='tl.id_event=?'; $bind_t.='s'; $bind_v[]=$fe;
}
$wh=implode(' AND ',$where);

$base_sql="FROM kode_titik k
    LEFT JOIN titik_lokasi tl ON k.id_titik = tl.id_titik
    LEFT JOIN event        e  ON tl.id_event = e.id_event
    WHERE $wh";

// total rows
$cs=$conn->prepare("SELECT COUNT(*) c $base_sql");
if($bind_t) $cs->bind_param($bind_t,...$bind_v);
$cs->execute(); $total=$cs->get_result()->fetch_assoc()['c']??0; $cs->close();
$pages=max(1,ceil($total/$limit));

// rows
$ds=$conn->prepare("SELECT k.id_kode, k.kode_unik, k.id_titik, tl.status, tl.longtitude, tl.latitude, e.id_event, e.nama_evet nm_event $base_sql ORDER BY k.id_kode DESC LIMIT ? OFFSET ?");
$dt=$bind_t.'ii'; $dv=array_merge($bind_v,[$limit,$offset]);
$ds->bind_param($dt,...$dv); $ds->execute();
$rows=$ds->get_result()->fetch_all(MYSQLI_ASSOC); $ds->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>UrFarm — Kode</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="css/sidebar.css?v=<?= time() ?>">
<link rel="stylesheet" href="css/kode.css?v=<?= time() ?>">
</head>
<body>

<?php include 'partials/sidebar.php'; ?>

<div class="main">
  <header class="topbar">
    <div>
      <div class="topbar-title">Kode</div>
      <div class="topbar-sub">Generate dan kelola kode tracking bibit</div>
    </div>
    <div class="topbar-right">
      <a class="icon-btn" href="notifikasi.php"><i class="bi bi-bell"></i></a>
      <a class="icon-btn" href="pengaturan.php"><i class="bi bi-gear"></i></a>
      <a class="btn btn-o sm" href="../auth/logout.php"><i class="bi bi-box-arrow-right"></i>Keluar</a>
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
      <div class="stat"><div class="stat-ic ic-b"><i class="bi bi-geo-alt"></i></div><div><div class="stat-val"><?= count($titiks) ?></div><div class="stat-lbl">Titik Lokasi</div></div></div>
      <div class="stat"><div class="stat-ic ic-a"><i class="bi bi-calendar-event"></i></div><div><div class="stat-val"><?= count($events) ?></div><div class="stat-lbl">Total Event</div></div></div>
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
            <input name="q" placeholder="Cari kode unik..." value="<?= h($q) ?>" oninput="dbs()">
          </div>
          <select class="fsel" name="id_event" onchange="this.form.submit()">
            <option value="">Semua Event</option>
            <?php foreach($events as $e): ?>
            <option value="<?= $e['id_event'] ?>" <?= $fe===$e['id_event']?'selected':'' ?>><?= h($e['nm']) ?></option>
            <?php endforeach; ?>
          </select>
          <?php if($q||$fe): ?>
          <a href="kode.php" class="btn btn-o sm"><i class="bi bi-x-lg"></i>Reset</a>
          <?php endif; ?>
        </div>
      </form>

      <?php if(empty($rows)): ?>
      <div class="empty"><i class="bi bi-qr-code"></i><p>Tidak ada kode yang ditemukan.</p></div>
      <?php else: ?>
      <table class="tbl">
        <thead><tr><th>ID Kode</th><th>Kode Unik</th><th>Titik</th><th>Event</th><th>Status Titik</th><th>Koordinat</th><th>Aksi</th></tr></thead>
        <tbody>
        <?php foreach($rows as $r): ?>
        <tr>
          <td><span style="font-family:monospace;font-size:12px;color:var(--muted)"><?= h($r['id_kode']) ?></span></td>
          <td><span class="chip"><?= h($r['kode_unik']) ?></span></td>
          <td><?= h($r['id_titik'] ?? '—') ?></td>
          <td><?= h($r['nm_event']??'—') ?></td>
          <td><?= badgeStatus($r['status']??'') ?></td>
          <td style="font-size:11px;color:var(--muted)">
            <?php if($r['latitude'] && $r['longtitude']): ?>
            <?= number_format($r['latitude'],4) ?>, <?= number_format($r['longtitude'],4) ?>
            <?php else: ?>—<?php endif; ?>
          </td>
          <td>
            <div style="display:flex;gap:6px">
              <button class="btn btn-o sm ic" title="Detail" onclick='openDetail(<?= json_encode($r,JSON_HEX_QUOT) ?>)'><i class="bi bi-eye"></i></button>
              <button class="btn btn-o sm ic" title="Salin" onclick="copy('<?= h($r['kode_unik']) ?>')"><i class="bi bi-clipboard"></i></button>
              <button class="btn-del" title="Hapus" onclick="konfHapus('<?= h($r['id_kode']) ?>','<?= h($r['kode_unik']) ?>')"><i class="bi bi-trash"></i></button>
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
      <div class="fg">
        <label class="lbl">Titik Lokasi *</label>
        <select class="fc" name="id_titik" required>
          <option value="">Pilih Titik Lokasi...</option>
          <?php foreach($titiks as $t): ?>
          <option value="<?= h($t['id_titik']) ?>"><?= h($t['id_titik']) ?> — <?= h($t['nm_event']??'Tanpa Event') ?> (<?= h($t['status']) ?>)</option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="fg">
        <label class="lbl">Jumlah Kode</label>
        <input class="fc" type="number" name="jumlah" value="1" min="1" max="100" oninput="prvw()">
        <div style="font-size:11px;color:var(--muted);margin-top:4px">Maks. 100 sekaligus</div>
      </div>
      <div class="fg">
        <label class="lbl">Preview Format</label>
        <div class="preview-box" id="prvw-box">UFM-????????</div>
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
const fdate=d=>d?new Date(d).toLocaleDateString('id-ID',{day:'2-digit',month:'short',year:'numeric'}):'—';

function openDetail(d){
  $('det-chip').textContent=d.kode_unik;
  $('det-copy').onclick=()=>copy(d.kode_unik);
  const rows=[
    ['Kode Unik', d.kode_unik||'—'],
    ['ID Titik', d.id_titik||'—'],
    ['Event', d.nm_event||'—'],
    ['Status Titik', d.status||'—'],
    ['Koordinat', (d.latitude&&d.longtitude) ? d.latitude+', '+d.longtitude : '—'],
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