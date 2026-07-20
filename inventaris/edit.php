<?php
session_start();

if (!isset($_SESSION['login'])) {
    header("Location: ../assets/login.php");
    exit;
}

include "../config/koneksi.php";

$ip_awal = mysqli_real_escape_string($conn, $_GET['id']);

if (isset($_POST['update'])) {
    $ip = mysqli_real_escape_string($conn, $_POST['ip_address']);
    $user = mysqli_real_escape_string($conn, $_POST['user']);
    $jenis = mysqli_real_escape_string($conn, $_POST['jenis_barang']);
    $sub = mysqli_real_escape_string($conn, $_POST['sub_jenis_barang']);
    $harga = intval($_POST['harga_per_unit']);
    $serial = mysqli_real_escape_string($conn, $_POST['serial_number']);

    mysqli_query($conn, "UPDATE inventaris SET ip_address='$ip', user='$user', jenis_barang='$jenis', sub_jenis_barang='$sub', harga_per_unit='$harga', serial_number='$serial' WHERE ip_address='$ip_awal'");
    $_SESSION['success_message'] = "Data inventaris berhasil di update.";
    header("Location: index.php");
    exit;
}

$data = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM inventaris WHERE ip_address='$ip_awal'"));

if (!$data) {
    header("Location: index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit Inventaris | Portal Pelindo</title>
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
                <a href="index.php" class="text-decoration-none text-muted">Inventaris</a> / Edit Aset
            </div>
        </div>

        <div class="content-body">
            <div class="modern-card">
                <div class="modern-card-header text-warning">
                    <h5 class="modern-card-title"><i class="bi bi-pencil-square me-2"></i>Edit Data Aset</h5>
                </div>
                <div class="modern-card-body">
                    <form method="POST" id="editForm">
                        <input type="hidden" name="update" value="1">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">IP Address</label>
                                <input type="text" name="ip_address" class="form-control" value="<?= htmlspecialchars($data['ip_address']) ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">User / Pemilik</label>
                                <input type="text" name="user" class="form-control" value="<?= htmlspecialchars($data['user']) ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Jenis Barang</label>
                                <input type="text" name="jenis_barang" class="form-control" value="<?= htmlspecialchars($data['jenis_barang']) ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Sub Jenis Barang</label>
                                <input type="text" name="sub_jenis_barang" class="form-control" value="<?= htmlspecialchars($data['sub_jenis_barang']) ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Harga Per Unit (Rp)</label>
                                <input type="number" name="harga_per_unit" class="form-control" value="<?= htmlspecialchars($data['harga_per_unit']) ?>" required>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Serial Number</label>
                                <input type="text" name="serial_number" class="form-control" value="<?= htmlspecialchars($data['serial_number']) ?>" required>
                            </div>
                        </div>
                        
                        <div class="mt-4 d-flex gap-2">
                            <button type="button" onclick="confirmUpdate()" class="btn btn-warning fw-semibold px-4 py-2 border-0" style="border-radius: 8px;">
                                <i class="bi bi-check-circle me-1"></i> Update Data
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
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function confirmUpdate() {
    const form = document.getElementById('editForm');
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }

    Swal.fire({
        title: 'Konfirmasi Update',
        text: 'Apakah yakin mengupdate data ini?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#f59e0b',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, Update!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            form.submit();
        }
    });
}
</script>
</body>
</html>
