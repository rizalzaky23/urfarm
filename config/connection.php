<?php

$hostname = "20.39.192.91";
$username = "urfarm";
$password = "PWDasik123";
$database = "urfarm_db";

$conn = new mysqli($hostname, $username, $password, $database);

if ($conn->connect_error) {
    die('Koneksi database gagal: ' . $conn->connect_error);
}

$conn->set_charset('utf8mb4');
