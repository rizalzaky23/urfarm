<?php

$hostname = "localhost";
$username = "root";
$password = "";
$database = "urfarm_db";

$conn = new mysqli($hostname, $username, $password, $database);

if ($conn->connect_error) {
    die('Koneksi database gagal: ' . $conn->connect_error);
}

$conn->set_charset('utf8mb4');
