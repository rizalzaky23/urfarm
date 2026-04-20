<?php
session_start();
require_once '../config/connection.php';

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama    = trim($_POST['nama'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $pass    = $_POST['password'] ?? '';
    $confirm = $_POST['confirm'] ?? '';

    if (!$nama || !$email || !$pass || !$confirm) {
        $error = 'Harap isi semua kolom.';
    } elseif (strlen($pass) < 8) {
        $error = 'Password minimal 8 karakter.';
    } elseif ($pass !== $confirm) {
        $error = 'Konfirmasi password tidak cocok.';
    } else {
        // Cek email duplikat
        $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $check->bind_param('s', $email);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $error = 'Email sudah terdaftar. Silahkan login.';
        } else {
            $hashed = hash('sha256', $pass);
            $stmt   = $conn->prepare("INSERT INTO users (nama, email, password, role) VALUES (?, ?, ?, 'donatur')");
            $stmt->bind_param('sss', $nama, $email, $hashed);
            $stmt->execute();
            $stmt->close();
            $success = 'Akun berhasil dibuat! Silahkan login.';
        }
        $check->close();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar — UrFarm</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/auth.css">
</head>
<body>
<div class="auth-page">
    <div class="auth-wrapper">
        <div class="auth-logo-bar">
            <a href="../index.php" style="text-decoration:none;">
                <span class="logo-text">Ur<span>Farm</span></span>
            </a>
        </div>
        <div class="auth-box">

        <div class="auth-body">
            <h1 class="auth-heading">Buat Akun</h1>
            <p class="auth-subheading">Bergabung dan mulai perjalanan hijau Anda bersama UrFarm.</p>

            <?php if ($error): ?>
                <div class="auth-alert error">⚠️ <?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="auth-alert success">✅ <?= htmlspecialchars($success) ?>
                    <a href="login.php">Masuk sekarang →</a>
                </div>
            <?php endif; ?>

            <?php if (!$success): ?>
            <form method="POST">
                <label class="field-label">Nama Lengkap</label>
                <input class="field-input" type="text" name="nama"
                       value="<?= htmlspecialchars($_POST['nama'] ?? '') ?>"
                       placeholder="Nama lengkap Anda" required>

                <label class="field-label">Email</label>
                <input class="field-input" type="email" name="email"
                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                       placeholder="contoh@email.com" required>

                <label class="field-label">Password</label>
                <input class="field-input" type="password" name="password"
                       placeholder="Min. 8 karakter" required>

                <label class="field-label">Konfirmasi Password</label>
                <input class="field-input" type="password" name="confirm"
                       placeholder="Ulangi password" required>

                <button type="submit" class="btn-auth">Daftar Sekarang</button>
            </form>
            <?php endif; ?>

            <div class="auth-footer-link">
                Sudah punya akun? <a href="login.php">Masuk di sini</a>
            </div>
        </div>

        </div>
    </div>
</div>
</body>
</html>
