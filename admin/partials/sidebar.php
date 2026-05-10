<?php
// Sidebar partial — include dari semua halaman admin
if (!isset($total_kontak)) {
    $total_kontak = $conn->query("SELECT COUNT(*) as c FROM contact")->fetch_assoc()['c'];
}
if (!isset($pending_donasi)) {
    $pending_donasi = $conn->query("SELECT COUNT(*) as c FROM donasi WHERE status='pending'")->fetch_assoc()['c'];
}
$current = basename($_SERVER['PHP_SELF']);
?>
<aside class="sidebar">
  <div class="sidebar-logo">
    <img src="/project-urfarm/assets/logo.png" alt="UrFarm Logo">
    <div class="logo-text">Ur<span>Farm</span></div>
  </div>

  <div class="sidebar-section">Utama</div>
  <nav class="sidebar-nav">
    <a href="/project-urfarm/admin/dashboard.php" <?= $current==='dashboard.php'?'class="active"':'' ?>>
      <i class="bi bi-grid-1x2-fill"></i> Dashboard
    </a>
    <a href="/project-urfarm/admin/bibit.php" <?= $current==='bibit.php'?'class="active"':'' ?>>
      <i class="bi bi-tree-fill"></i> Bibit
    </a>
    <a href="/project-urfarm/admin/event.php" <?= $current==='event.php'?'class="active"':'' ?>>
      <i class="bi bi-calendar-event-fill"></i> Event
    </a>
  </nav>

  <div class="sidebar-section">Keuangan &amp; Lokasi</div>
  <nav class="sidebar-nav">
    <a href="/project-urfarm/admin/donasi.php" <?= $current==='donasi.php'?'class="active"':'' ?>>
      <i class="bi bi-cash-coin"></i> Donasi
      <?php if($pending_donasi > 0): ?>
      <span class="badge-count"><?= $pending_donasi ?></span>
      <?php endif; ?>
    </a>
    <a href="/project-urfarm/admin/alokasiDana.php" <?= $current==='alokasiDana.php'?'class="active"':'' ?>>
      <i class="bi bi-wallet2"></i> Alokasi Dana
    </a>
    <a href="/project-urfarm/admin/titikLokasi.php" <?= $current==='titikLokasi.php'?'class="active"':'' ?>>
      <i class="bi bi-geo-alt-fill"></i> Titik Lokasi
    </a>
  </nav>

  <div class="sidebar-section">Konten</div>
  <nav class="sidebar-nav">
    <a href="/project-urfarm/admin/kode.php" <?= $current==='kode.php'?'class="active"':'' ?>>
      <i class="bi bi-key"></i> Kode
    </a>
    <a href="/project-urfarm/admin/publikasi.php" <?= $current==='publikasi.php'?'class="active"':'' ?>>
      <i class="bi bi-newspaper"></i> Publikasi
    </a>
    <a href="/project-urfarm/admin/kontakMasuk.php" <?= $current==='kontakMasuk.php'?'class="active"':'' ?>>
      <i class="bi bi-envelope-fill"></i> Kontak Masuk
      <?php if($total_kontak > 0): ?>
      <span class="badge-count"><?= $total_kontak ?></span>
      <?php endif; ?>
    </a>
  </nav>

  <div class="sidebar-footer">
    <div class="sidebar-user">
      <div class="avatar"><?= strtoupper(substr($_SESSION['user_nama'] ?? 'A', 0, 2)) ?></div>
      <div class="user-info">
        <div class="name"><?= htmlspecialchars($_SESSION['user_nama'] ?? 'Admin') ?></div>
        <div class="role">Admin UrFarm</div>
      </div>
    </div>
  </div>
</aside>
