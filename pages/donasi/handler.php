<?php

header('Content-Type: application/json; charset=utf-8');

// Matikan display_errors agar PHP notice/warning tidak masuk ke JSON output
ini_set('display_errors', 0);
error_reporting(E_ALL);

session_start();

// Hanya menerima POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    exit;
}

// Hanya user yang sudah login
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Silakan login terlebih dahulu.']);
    exit;
}

// Koneksi DB — tangkap error sebagai JSON bukan die()
require_once '../../config/connection.php';
if (!$conn || $conn->connect_error) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Koneksi database gagal.']);
    exit;
}

// ===== 1. AMBIL & SANITASI INPUT =====
$id_users        = (int) $_SESSION['user_id'];
$nama_donatur    = trim($_POST['nama_donatur']    ?? '');
$email           = trim($_POST['email']           ?? '');
$pesan           = trim($_POST['pesan']           ?? '');
$nominal         = (int) ($_POST['nominal']       ?? 0);
$metode          = strtolower(trim($_POST['metode'] ?? ''));
$jumlah_transfer = (int) ($_POST['jumlah_transfer'] ?? 0);
$link_bukti      = trim($_POST['link_bukti']      ?? '');
$catatan         = trim($_POST['catatan']         ?? '');

// ===== 2. VALIDASI =====
$errors = [];

if ($nama_donatur === '') {
    $errors[] = 'Nama donatur wajib diisi.';
}

if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Email tidak valid.';
}

if ($nominal < 10000) {
    $errors[] = 'Nominal donasi minimal Rp 10.000.';
}

$allowed_metode = ['gopay', 'ovo', 'dana', 'sca', 'qris'];
if (!in_array($metode, $allowed_metode)) {
    $errors[] = 'Metode pembayaran tidak valid. Pilih salah satu: GoPay, OVO, DANA, SCA, QRIS.';
}

if ($jumlah_transfer < 10000) {
    $errors[] = 'Jumlah yang ditransfer minimal Rp 10.000.';
}

if ($link_bukti === '') {
    $errors[] = 'Link bukti pembayaran wajib diisi.';
}

if (!empty($errors)) {
    echo json_encode([
        'success' => false,
        'message' => implode(' ', $errors),
    ]);
    exit;
}

// ===== 3. HITUNG ESTIMASI BIBIT =====
$estimasi_bibit = (int) floor($nominal / 10000);

// ===== 4. INSERT KE DATABASE =====
$sql = "
    INSERT INTO donasi
        (id_users, nama_donatur, email, pesan, nominal, metode,
         jumlah_transfer, link_bukti, catatan, estimasi_bibit, status)
    VALUES
        (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')
";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    error_log('[Donasi] prepare error: ' . $conn->error);
    echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan server (prepare). Coba lagi.']);
    exit;
}

// i=int, s=string — urutan harus sama dengan parameter di bawah
// id_users(i) nama(s) email(s) pesan(s) nominal(i) metode(s) jumlah(i) link(s) catatan(s) bibit(i)
$stmt->bind_param(
    'isssisissi',
    $id_users,
    $nama_donatur,
    $email,
    $pesan,
    $nominal,
    $metode,
    $jumlah_transfer,
    $link_bukti,
    $catatan,
    $estimasi_bibit
);

if ($stmt->execute()) {
    $id_donasi = $conn->insert_id;
    $stmt->close();
    $conn->close();

    echo json_encode([
        'success' => true,
        'message' => 'Donasi berhasil dikirim. Sedang menunggu verifikasi.',
        'data'    => [
            'id_donasi'      => $id_donasi,
            'nama_donatur'   => $nama_donatur,
            'nominal'        => $nominal,
            'metode'         => strtoupper($metode),
            'estimasi_bibit' => $estimasi_bibit,
            'status'         => 'pending',
        ],
    ]);
} else {
    $err = $stmt->error;
    $stmt->close();
    $conn->close();
    error_log('[Donasi] execute error: ' . $err);
    echo json_encode(['success' => false, 'message' => 'Gagal menyimpan donasi: ' . $err]);
}
