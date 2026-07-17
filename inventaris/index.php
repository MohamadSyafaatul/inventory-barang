<?php
session_start();

if (!isset($_SESSION['login'])) {
    header("Location: ../assets/login.php");
    exit;
}

include "../config/koneksi.php";

$data = mysqli_query($conn, "SELECT * FROM inventaris ORDER BY ip_address DESC");
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Data Inventaris | Portal Pelindo</title>
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
            <div class="text-muted d-flex align-items-center gap-2" style="font-size: 0.9rem;">
                <i class="bi bi-folder-check"></i> Total: <?= mysqli_num_rows($data) ?> Aset
            </div>
        </div>

        <div class="content-body">
            <div class="modern-card">
                <div class="modern-card-header">
                    <h5 class="modern-card-title"><i class="bi bi-hdd-network me-2"></i>Daftar Perangkat & Aset</h5>
                    <a href="tambah.php" class="btn btn-modern-primary btn-sm">
                        <i class="bi bi-plus-circle me-1"></i> Tambah Inventaris
                    </a>
                </div>
                <div class="modern-card-body p-0">
                    <div class="table-responsive">
                        <table class="table modern-table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>IP Address</th>
                                    <th>User</th>
                                    <th>Jenis Barang</th>
                                    <th>Sub Jenis</th>
                                    <th>Harga</th>
                                    <th>Serial Number</th>
                                    <th width="120" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $no = 1; 
                                if (mysqli_num_rows($data) == 0):
                                ?>
                                    <tr>
                                        <td colspan="9" class="text-center py-4 text-muted">Belum ada data inventaris.</td>
                                    </tr>
                                <?php
                                else:
                                    while($d = mysqli_fetch_assoc($data)):
                                ?>
                                    <tr>
                                        <td><?= $no++ ?></td>
                                        <td class="fw-semibold text-primary"><?= htmlspecialchars($d['ip_address']) ?></td>
                                        <td class="fw-medium"><?= htmlspecialchars($d['user']) ?></td>
                                        <td><?= htmlspecialchars($d['jenis_barang']) ?></td>
                                        <td><?= htmlspecialchars($d['sub_jenis_barang']) ?></td>
                                        <td>Rp <?= number_format($d['harga_per_unit']) ?></td>
                                        <td class="text-monospace" style="font-size: 0.88rem;"><?= htmlspecialchars($d['serial_number']) ?></td>
                                        <td>
                                            <div class="d-flex justify-content-center gap-1">
                                                <a href="edit.php?id=<?= urlencode($d['ip_address']) ?>" class="btn btn-sm btn-outline-warning" title="Edit">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <a href="hapus.php?id=<?= urlencode($d['ip_address']) ?>" class="btn btn-sm btn-outline-danger" 
                                                   onclick="return confirm('Apakah Anda yakin ingin menghapus data inventaris dengan IP <?= $d['ip_address'] ?>?')" title="Hapus">
                                                    <i class="bi bi-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php 
                                    endwhile;
                                endif; 
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>