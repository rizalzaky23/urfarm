<?php
// Sidebar partial — include dari semua halaman admin
// Variabel yang dibutuhkan: $total_kontak (optional)
if (!isset($total_kontak)) {
    $total_kontak = $conn->query("SELECT COUNT(*) as c FROM contact")->fetch_assoc()['c'];
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
    <a href="#"><i class="bi bi-wallet2"></i> Alokasi Dana</a>
    <a href="#"><i class="bi bi-geo-alt-fill"></i> Lokasi &amp; Penanaman</a>
  </nav>

  <div class="sidebar-section">Konten</div>
  <nav class="sidebar-nav">
    <a href="#"><i class="bi bi-key-fill"></i> Kode</a>
    <a href="#"><i class="bi bi-newspaper"></i> Publikasi</a>
    <a href="#">
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
