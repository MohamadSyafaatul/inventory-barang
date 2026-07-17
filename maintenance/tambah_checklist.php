<?php
session_start();

if (!isset($_SESSION['login'])) {
    http_response_code(401);
    exit;
}

include "../config/koneksi.php";

$ip          = isset($_POST['ip_address'])          ? $_POST['ip_address']          : '';
$pelaksana   = isset($_POST['pelaksana_perawatan'])  ? $_POST['pelaksana_perawatan']  : '';
$teknisi     = isset($_POST['teknisi'])              ? $_POST['teknisi']              : '';
$tipe        = isset($_POST['tipe_perawatan'])       ? $_POST['tipe_perawatan']       : '';
$periode     = isset($_POST['periode_perawatan'])    ? $_POST['periode_perawatan']    : '';
$permasalahan = isset($_POST['permasalahan'])        ? $_POST['permasalahan']         : '';
$aksi        = isset($_POST['aksi'])                 ? $_POST['aksi']                 : '';
$keterangan  = isset($_POST['keterangan'])           ? $_POST['keterangan']           : '';

// Validasi sederhana — tanggal tidak lagi dibutuhkan dari POST
if ($ip === '') {
    header("Location: tambah.php");
    exit;
}

$ip           = mysqli_real_escape_string($conn, $ip);
$pelaksana    = mysqli_real_escape_string($conn, $pelaksana);
$teknisi      = mysqli_real_escape_string($conn, $teknisi);
$tipe         = mysqli_real_escape_string($conn, $tipe);
$periode      = mysqli_real_escape_string($conn, $periode);
$permasalahan = mysqli_real_escape_string($conn, $permasalahan);
$aksi         = mysqli_real_escape_string($conn, $aksi);
$keterangan   = mysqli_real_escape_string($conn, $keterangan);

// start_time diambil langsung dari server menggunakan NOW() — tidak dari input user
// finish_time dibiarkan NULL sampai proses maintenance selesai
mysqli_query($conn, "INSERT INTO maintenance
    (ip_address, pelaksana_perawatan, teknisi, tipe_perawatan, periode_perawatan,
     permasalahan, aksi, keterangan, start_time, finish_time, status_maintenance)
    VALUES
    ('$ip', '$pelaksana', '$teknisi', '$tipe', '$periode',
     '$permasalahan', '$aksi', '$keterangan', NOW(), NULL, 'Berlangsung')
");

$id_maintenance = mysqli_insert_id($conn);

// Checklist default sesuai permintaan
$defaultItems = [
    'Motherboard','Processor','Hard Disk','Power Supply','RAM',
    'Keyboard','Mouse','Monitor','Wifi LAN Card','CD ROM',
     #'Cleaning'
];

foreach ($defaultItems as $item) {
    $statusKey = 'status_' . str_replace([' ', '/'], '_', strtolower($item));
    $status    = isset($_POST[$statusKey]) ? mysqli_real_escape_string($conn, $_POST[$statusKey]) : 'OK';
    mysqli_query($conn, "INSERT INTO maintenance_checklist (id_maintenance, item, status)
        VALUES ('$id_maintenance', '$item', '$status')");
}

header("Location: index.php");
exit;
