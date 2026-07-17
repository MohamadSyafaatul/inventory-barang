<?php
session_start();

if (!isset($_SESSION['login'])) {
    header("Location: ../assets/login.php");
    exit;
}

include "../config/koneksi.php";

if (!isset($_POST['ip_address'])) {
    header("Location: tambah.php");
    exit;
}

$ip           = mysqli_real_escape_string($conn, $_POST['ip_address']);
$pelaksana    = mysqli_real_escape_string($conn, $_POST['pelaksana_perawatan']);
$teknisi      = mysqli_real_escape_string($conn, $_POST['teknisi']);
$tipe         = mysqli_real_escape_string($conn, $_POST['tipe_perawatan']);
$periode      = mysqli_real_escape_string($conn, $_POST['periode_perawatan']);
$permasalahan = mysqli_real_escape_string($conn, $_POST['permasalahan']);
$aksi         = mysqli_real_escape_string($conn, $_POST['aksi']);
$keterangan   = mysqli_real_escape_string($conn, $_POST['keterangan']);

// start_time diambil dari server (NOW()), bukan dari input user
// finish_time = NULL sampai proses selesai
mysqli_query($conn, "INSERT INTO maintenance
    (ip_address, pelaksana_perawatan, teknisi, tipe_perawatan, periode_perawatan,
     permasalahan, aksi, keterangan, start_time, finish_time, status_maintenance)
    VALUES (
    '$ip', '$pelaksana', '$teknisi', '$tipe', '$periode',
    '$permasalahan', '$aksi', '$keterangan', NOW(), NULL, 'Berlangsung'
)");

header("Location: index.php");
exit;
