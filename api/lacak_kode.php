<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

header('Content-Type: application/json; charset=utf-8');
require_once '../config/connection.php';

$kode = trim($_GET['kode'] ?? '');
if ($kode === '') {
    echo json_encode(['success' => false, 'message' => 'Kode tidak boleh kosong.']);
    exit;
}

$stmt = $conn->prepare("
    SELECT 
        k.kode_unik,
        k.id_titik,
        tl.latitude,
        tl.longtitude AS longitude,
        tl.status,
        tl.id_event,
        e.nama_evet AS nama_event,
        e.jenis_event,
        p.lokasi,
        p.tanggal,
        p.jumlah_bibit,
        b.jenis_pohon,
        b.nama_pohon
    FROM kode_titik k
    LEFT JOIN titik_lokasi tl ON k.id_titik = tl.id_titik
    LEFT JOIN event        e  ON tl.id_event = e.id_event
    LEFT JOIN penanaman    p  ON tl.id_event = p.id_event
    LEFT JOIN bibit        b  ON p.id_bibit  = b.id_bibit
    WHERE k.kode_unik = ?
    LIMIT 1
");
$stmt->bind_param('s', $kode);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$row) {
    echo json_encode(['success' => false, 'message' => 'Kode benih tidak ditemukan.']);
    exit;
}

echo json_encode(['success' => true, 'data' => $row]);
