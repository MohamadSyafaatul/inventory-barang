<?php
session_start();

if (!isset($_SESSION['login'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

include "../config/koneksi.php";

header('Content-Type: application/json; charset=utf-8');

$ip = isset($_GET['ip_address']) ? $_GET['ip_address'] : '';
$ip = mysqli_real_escape_string($conn, $ip);

if ($ip === '') {
    echo json_encode([]);
    exit;
}

$q = mysqli_query($conn, "SELECT jenis_barang, sub_jenis_barang, serial_number FROM inventaris WHERE ip_address='$ip' LIMIT 1");
$row = mysqli_fetch_assoc($q);

if (!$row) {
    echo json_encode([]);
    exit;
}

echo json_encode($row);

