<?php
session_start();
require_once '../config/connection.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $role  = $_POST['role'] ?? 'donatur';
    $email = trim($_POST['email'] ?? '');
    $pass  = $_POST['password'] ?? '';

    if ($email && $pass) {
        $stmt = $conn->prepare("SELECT id, nama, email, password, role FROM users WHERE email = ? AND role = ?");
        $stmt->bind_param('ss', $email, $role);
        $stmt->execute();
        $result = $stmt->get_result();
        $user   = $result->fetch_assoc();
        $stmt->close();

        if ($user && hash('sha256', $pass) === $user['password']) {
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['user_nama'] = $user['nama'];
            $_SESSION['user_role'] = $user['role'];

            if ($user['role'] === 'admin') {
                header('Location: ../admin/dashboard.php');
            } else {
                header('Location: ../index.php'); // donatur redirect
            }
            exit;
        } else {
            $error = 'Email atau password salah.';
        }
    } else {
        $error = 'Harap isi semua kolom.';
    }
}

$active_tab = $_GET['tab'] ?? 'donatur';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk — UrFarm</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/auth.css">
</head>
<body>
<div class="auth-page">
    <div class="auth-wrapper">

        <!-- Logo -->
        <div class="auth-logo-bar">
            <a href="../index.php" style="text-decoration:none;">
                <span class="logo-text">Ur<span>Farm</span></span>
            </a>
        </div>

        <!-- Tabs -->
        <div class="auth-tabs">
            <button class="auth-tab-btn <?= $active_tab === 'donatur' ? 'active' : '' ?>"
                    onclick="switchTab('donatur')">Donatur</button>
            <button class="auth-tab-btn <?= $active_tab === 'admin' ? 'active' : '' ?>"
                    onclick="switchTab('admin')">Admin</button>
        </div>

        <!-- Card -->
        <div class="auth-box">
            <div class="auth-body">
                <h1 class="auth-heading" id="auth-heading">
                    <?= $active_tab === 'admin' ? 'Halo, Admin!' : 'Halo, UFams!' ?>
                </h1>
                <p class="auth-subheading" id="auth-subheading">
                    <?= $active_tab === 'admin'
                        ? 'Silahkan masuk untuk mengelola UrFarm.'
                        : 'Silahkan Sign in untuk melanjutkan ke UrFarm sebagai Donatur.' ?>
                </p>

                <?php if ($error): ?>
                    <div class="auth-alert error">⚠️ <?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <form method="POST" id="login-form">
                    <input type="hidden" name="role" id="role-input" value="<?= htmlspecialchars($active_tab) ?>">

                    <label class="field-label">Email</label>
                    <input class="field-input" type="email" name="email"
                           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                           placeholder="contoh@email.com" required>

                    <label class="field-label">Password</label>
                    <input class="field-input" type="password" name="password"
                           placeholder="••••••••" required>

                    <button type="submit" class="btn-auth">Masuk</button>
                </form>

                <div class="auth-footer-link" id="register-link"
                     style="<?= $active_tab === 'admin' ? 'display:none' : '' ?>">
                    Belum punya akun?
                    <a href="register.php">Daftar sekarang</a>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
    function switchTab(tab) {
        document.querySelectorAll('.auth-tab-btn').forEach(btn => btn.classList.remove('active'));
        event.currentTarget.classList.add('active');

        document.getElementById('role-input').value = tab;

        const heading    = document.getElementById('auth-heading');
        const subheading = document.getElementById('auth-subheading');
        const regLink    = document.getElementById('register-link');

        if (tab === 'admin') {
            heading.textContent    = 'Halo, Admin!';
            subheading.textContent = 'Silahkan masuk untuk mengelola UrFarm.';
            regLink.style.display  = 'none';
        } else {
            heading.textContent    = 'Halo, UFams!';
            subheading.textContent = 'Silahkan Sign in untuk melanjutkan ke UrFarm sebagai Donatur.';
            regLink.style.display  = 'block';
        }
    }
</script>
</body>
</html>
