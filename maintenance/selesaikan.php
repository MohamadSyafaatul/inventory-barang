<?php
// =====================================================
// selesaikan.php — Mencatat finish_time dari server
// saat user menekan tombol "Selesaikan Maintenance"
// =====================================================
session_start();

if (!isset($_SESSION['login'])) {
    header("Location: ../assets/login.php");
    exit;
}

include "../config/koneksi.php";

// Hanya menerima metode POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php");
    exit;
}

$id = isset($_POST['id_maintenance']) ? (int) $_POST['id_maintenance'] : 0;

if ($id <= 0) {
    header("Location: index.php");
    exit;
}

// Pastikan record ada dan statusnya masih Berlangsung
$cek = mysqli_query($conn, "SELECT id_maintenance, status_maintenance FROM maintenance WHERE id_maintenance = '$id' LIMIT 1");
$row = mysqli_fetch_assoc($cek);

if (!$row) {
    // Record tidak ditemukan
    header("Location: index.php");
    exit;
}

if ($row['status_maintenance'] === 'Selesai') {
    // Sudah selesai, langsung redirect ke detail
    header("Location: detail.php?id=$id&info=sudah_selesai");
    exit;
}

// Update finish_time menggunakan NOW() dari server — bukan dari input user
mysqli_query($conn, "UPDATE maintenance
    SET finish_time        = NOW(),
        status_maintenance = 'Selesai'
    WHERE id_maintenance   = '$id'
    LIMIT 1
");

header("Location: detail.php?id=$id&sukses=selesai");
exit;
