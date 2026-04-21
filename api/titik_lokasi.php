<?php
session_start();
// Proteksi: harus login
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

header('Content-Type: application/json; charset=utf-8');
require_once '../config/connection.php';

$sql = "
    SELECT
        tl.id_titik,
        tl.latitude,
        tl.longtitude AS longitude,
        tl.status,
        tl.id_event,
        e.nama_evet   AS nama_event,
        e.jenis_event,
        p.id_penanaman,
        p.lokasi,
        p.tanggal,
        p.jumlah_bibit,
        b.jenis_pohon,
        b.nama_pohon
    FROM titik_lokasi tl
    LEFT JOIN event     e ON tl.id_event = e.id_event
    LEFT JOIN penanaman p ON tl.id_event = p.id_event
    LEFT JOIN bibit     b ON p.id_bibit  = b.id_bibit
    WHERE tl.latitude IS NOT NULL AND tl.longtitude IS NOT NULL
    ORDER BY tl.id_titik ASC
";

$result = $conn->query($sql);
if (!$result) {
    echo json_encode(['error' => $conn->error]);
    exit;
}

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode(['success' => true, 'markers' => $data]);
