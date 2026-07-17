<?php
session_start();

if (!isset($_SESSION['login'])) {
    header("Location: ../assets/login.php");
    exit;
}

include "../config/koneksi.php";

if (isset($_POST['simpan'])) {
    $ip = mysqli_real_escape_string($conn, $_POST['ip_address']);
    $user = mysqli_real_escape_string($conn, $_POST['user']);
    $jenis = mysqli_real_escape_string($conn, $_POST['jenis_barang']);
    $sub = mysqli_real_escape_string($conn, $_POST['sub_jenis_barang']);
    $harga = intval($_POST['harga_per_unit']);
    $serial = mysqli_real_escape_string($conn, $_POST['serial_number']);

    mysqli_query($conn, "INSERT INTO inventaris (ip_address, user, jenis_barang, sub_jenis_barang, harga_per_unit, serial_number) VALUES ('$ip', '$user', '$jenis', '$sub', '$harga', '$serial')");
    header("Location: index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tambah Inventaris | Portal Pelindo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>

<div class="sidebar-layout">
    <!-- SIDEBAR -->
    <div class="sidebar">
        <div class="sidebar-logo">
            <div class="logo-wrapper">
                <img src="../assets/logo-pelindo.png" alt="Logo Pelindo">
            </div>
        </div>

        <div class="sidebar-user">
            <div class="sidebar-user-avatar">
                <i class="bi bi-person"></i>
            </div>
            <div class="sidebar-user-email">admin@pelindo.co.id</div>
        </div>

        <div class="sidebar-nav">
            <a href="../assets/dashboard.php"><i class="bi bi-speedometer2"></i> Dashboard</a>
            <a href="index.php" class="active"><i class="bi bi-pc-display"></i> Inventaris</a>
            <a href="../maintenance/index.php"><i class="bi bi-tools"></i> Maintenance</a>
            <a href="../history/index.php"><i class="bi bi-clock-history"></i> History</a>
        </div>

        <div class="sidebar-footer">
            <a href="../assets/logout.php" class="btn-logout"><i class="bi bi-box-arrow-right"></i> Logout</a>
        </div>
    </div>

    <!-- MAIN CONTENT -->
    <div class="main-content">
        <div class="top-navbar">
            <h5 class="top-navbar-title">Pengelolaan Inventaris</h5>
            <div class="text-muted" style="font-size: 0.9rem;">
                <a href="index.php" class="text-decoration-none text-muted">Inventaris</a> / Tambah Aset
            </div>
        </div>

        <div class="content-body">
            <div class="modern-card">
                <div class="modern-card-header text-primary">
                    <h5 class="modern-card-title"><i class="bi bi-plus-circle-fill me-2"></i>Tambah Aset Baru</h5>
                </div>
                <div class="modern-card-body">
                    <form method="POST">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">IP Address</label>
                                <input type="text" name="ip_address" class="form-control" placeholder="Contoh: 192.168.1.10" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">User / Pemilik</label>
                                <input type="text" name="user" class="form-control" placeholder="Nama penanggung jawab" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Jenis Barang</label>
                                <input type="text" name="jenis_barang" class="form-control" placeholder="Contoh: Laptop, Printer, PC" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Sub Jenis Barang</label>
                                <input type="text" name="sub_jenis_barang" class="form-control" placeholder="Contoh: Thinkpad T480, Epson L3210" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Harga Per Unit (Rp)</label>
                                <input type="number" name="harga_per_unit" class="form-control" placeholder="Contoh: 12000000" required>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Serial Number</label>
                                <input type="text" name="serial_number" class="form-control" placeholder="Contoh: SN-12345ABC" required>
                            </div>
                        </div>
                        
                        <div class="mt-4 d-flex gap-2">
                            <button type="submit" name="simpan" class="btn btn-modern-primary">
                                <i class="bi bi-floppy me-1"></i> Simpan
                            </button>
                            <a href="index.php" class="btn btn-modern-light">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
