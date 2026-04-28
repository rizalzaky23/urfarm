<?php
session_start();
require_once '../../config/connection.php';

// Redirect to login if user is not logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['redirect_to'] = 'contact';
    header('Location: ../../auth/login.php');
    exit;
}

$success = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pesan = trim($_POST['pesan'] ?? '');
    $id_users = $_SESSION['user_id'] ?? null;

    if ($pesan !== '') {
        $stmt = $conn->prepare("INSERT INTO contact (id_users, pesan) VALUES (?, ?)");
        $stmt->bind_param('is', $id_users, $pesan);
        if ($stmt->execute()) {
            $success = true;
        } else {
            $error = 'Gagal mengirim pesan. Silakan coba lagi.';
        }
        $stmt->close();
    } else {
        $error = 'Pesan tidak boleh kosong.';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hubungi Kami - UrFarm</title>
    <link rel="stylesheet" href="../../css/style.css">
    <link rel="stylesheet" href="../../css/contact.css">
</head>
<body>

    <nav id="navbar">
        <a href="<?= isset($_SESSION['user_id']) ? '/project-urfarm/landing.php' : '/project-urfarm/index.php' ?>"
            class="nav-logo">Ur<span>Farm</span></a>
        <div class="nav-links" id="navLinks">
            <a href="<?= isset($_SESSION['user_id']) ? '/project-urfarm/landing.php' : '/project-urfarm/index.php' ?>"
                class="nav-a">Home</a>
            <a href="/project-urfarm/pages/program.php" class="nav-a">Program</a>
            <a href="/project-urfarm/pages/partner.php" class="nav-a">Partner</a>
            <a href="/project-urfarm/pages/publikasi.php" class="nav-a">Publikasi</a>
            <div class="dropdown">
                <a href="#" class="nav-a active">Bantuan ▾</a>
                <div class="dropdown-menu">
                    <a href="tentang.php" class="dd-a">Tentang Kami</a>
                    <a href="contact.php" class="dd-a">Hubungi Kami</a>
                    <a href="faq.php" class="dd-a">FAQ</a>
                </div>
            </div>
            <?php if (isset($_SESSION['user_id'])): ?>
                <div class="dropdown">
                    <a href="#" class="nav-a">👤 <?= htmlspecialchars($_SESSION['user_nama']) ?> ▾</a>
                    <div class="dropdown-menu">
                        <?php if ($_SESSION['user_role'] === 'admin'): ?>
                            <a href="/project-urfarm/admin/dashboard.php" class="dd-a">Dashboard</a>
                        <?php endif; ?>
                        <a href="/project-urfarm/pages/donasi/riwayat.php" class="dd-a">Riwayat Donasi</a>
                        <a href="/project-urfarm/auth/logout.php" class="dd-a">Keluar</a>
                    </div>
                </div>
            <?php else: ?>
                <a href="../../auth/login.php" class="btn-masuk">Masuk</a>
            <?php endif; ?>
        </div>
        <button class="menu-toggle" id="menuToggle">☰</button>
    </nav>

    <div class="contact-page-wrap">
        <?php if ($success): ?>
            <div class="notification" id="notif">
                <span class="icon">✅</span> Pesan berhasil dikirim!
                <button class="close-btn" onclick="document.getElementById('notif').style.display='none'">×</button>
            </div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="notification" style="background:#e63946;" id="notif-err">
                <span class="icon">⚠️</span> <?= htmlspecialchars($error) ?>
                <button class="close-btn" onclick="document.getElementById('notif-err').style.display='none'">×</button>
            </div>
        <?php endif; ?>

        <div class="contact-container">
            <!-- Left Info Card -->
            <div class="contact-info-card">
                <div class="badge-title">HUBUNGI KAMI</div>
                
                <div class="info-row">
                    <div class="info-label">No. Telp</div>
                    <div class="info-value">+62 851-3456-7890</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Email</div>
                    <div class="info-value"><a href="mailto:hello@urfarm.id">hello@urfarm.id</a></div>
                </div>
                <div class="info-row">
                    <div class="info-label">Instagram</div>
                    <div class="info-value">urfarm.id</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Facebook</div>
                    <div class="info-value">urfarm.id</div>
                </div>
                <div class="info-row">
                    <div class="info-label">X</div>
                    <div class="info-value">urfarm.id</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Website</div>
                    <div class="info-value"><a href="https://www.urfarm.co.id" target="_blank">www.urfarm.co.id</a></div>
                </div>
            </div>

            <!-- Right Form Card -->
            <div class="contact-form-card">
                <div class="badge-title">PESAN</div>
                <form method="POST" action="contact.php">
                    <textarea name="pesan" placeholder="ketik pesan..." required></textarea>
                    <div class="form-footer">
                        <button type="submit" class="btn-kirim">KIRIM</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <footer class="contact-footer">
        © 2026 UrFarm — <a href="<?= isset($_SESSION['user_id']) ? '/project-urfarm/landing.php' : '/project-urfarm/index.php' ?>">Kembali ke Beranda</a>
    </footer>

    <script>
        window.addEventListener('scroll', () => {
            document.getElementById('navbar').classList.toggle('scrolled', window.scrollY > 10);
        });

        document.getElementById('menuToggle').addEventListener('click', () => {
            document.getElementById('navLinks').classList.toggle('mobile-open');
        });
    </script>
</body>
</html>
