<?php
session_start();

if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

include "../config/koneksi.php";
include "../config/helper.php";

$total_barang = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM inventaris"));
$total_maintenance = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM maintenance"));
$maintenance_hari_ini = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM maintenance WHERE DATE(start_time) = CURDATE()"));
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard | Portal Inventaris Pelindo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .stat-card {
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 24px;
            background: #ffffff;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.06);
        }
        .stat-icon {
            width: 56px;
            height: 56px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.75rem;
        }
        .stat-primary { background-color: rgba(0, 92, 185, 0.08); color: var(--pelindo-primary); }
        .stat-success { background-color: rgba(25, 135, 84, 0.08); color: #198754; }
        .stat-danger { background-color: rgba(220, 53, 69, 0.08); color: #dc3545; }
    </style>
</head>

<body>

<div class="sidebar-layout">
    <!-- SIDEBAR -->
    <div class="sidebar">
        <div class="sidebar-logo">
            <div class="logo-wrapper">
                <img src="logo-pelindo.png" alt="Logo Pelindo">
            </div>
        </div>

        <div class="sidebar-user">
            <div class="sidebar-user-avatar">
                <i class="bi bi-person"></i>
            </div>
            <div class="sidebar-user-email">admin@pelindo.co.id</div>
        </div>

        <div class="sidebar-nav">
            <a href="dashboard.php" class="active"><i class="bi bi-speedometer2"></i> Dashboard</a>
            <a href="../inventaris/index.php"><i class="bi bi-pc-display"></i> Inventory</a>
            <a href="../maintenance/index.php"><i class="bi bi-tools"></i> Maintenance</a>
            <a href="../history/index.php"><i class="bi bi-clock-history"></i> History</a>
        </div>

        <div class="sidebar-footer">
            <a href="logout.php" class="btn-logout"><i class="bi bi-box-arrow-right"></i> Logout</a>
        </div>
    </div>

    <!-- MAIN CONTENT -->
    <div class="main-content">
        <div class="top-navbar">
            <h5 class="top-navbar-title">Dashboard Overview</h5>
            <div class="text-muted d-flex align-items-center gap-2" style="font-size: 0.9rem;">
                <i class="bi bi-calendar3"></i> <?= formatWaktuIndo(date('Y-m-d H:i:s')) ?>
            </div>
        </div>

        <div class="content-body">
            <!-- STATS GRID -->
            <div class="row g-4 mb-4">
                <div class="col-md-4">
                    <div class="stat-card">
                        <div>
                            <span class="text-muted fw-medium d-block mb-1" style="font-size: 0.9rem;">Total Inventaris</span>
                            <h2 class="fw-bold mb-0 text-dark"><?= $total_barang ?></h2>
                        </div>
                        <div class="stat-icon stat-primary">
                            <i class="bi bi-pc-display"></i>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="stat-card">
                        <div>
                            <span class="text-muted fw-medium d-block mb-1" style="font-size: 0.9rem;">Total Perawatan</span>
                            <h2 class="fw-bold mb-0 text-dark"><?= $total_maintenance ?></h2>
                        </div>
                        <div class="stat-icon stat-success">
                            <i class="bi bi-tools"></i>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="stat-card">
                        <div>
                            <span class="text-muted fw-medium d-block mb-1" style="font-size: 0.9rem;">Maintenance Hari Ini</span>
                            <h2 class="fw-bold mb-0 text-dark"><?= $maintenance_hari_ini ?></h2>
                        </div>
                        <div class="stat-icon stat-danger">
                            <i class="bi bi-calendar-check"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TABLE VIEW -->
            <div class="modern-card">
                <div class="modern-card-header">
                    <h5 class="modern-card-title"><i class="bi bi-activity me-2"></i>Perawatan Terakhir</h5>
                    <a href="../maintenance/index.php" class="btn btn-modern-light btn-sm">Lihat Semua</a>
                </div>
                <div class="modern-card-body p-0">
                    <div class="table-responsive">
                        <table class="table modern-table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>IP Address</th>
                                    <th>User</th>
                                    <th>Teknisi</th>
                                    <th>Waktu Mulai</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $no = 1;
                                $query = mysqli_query($conn, "
                                    SELECT m.*, i.ip_address, i.user
                                    FROM maintenance m
                                    JOIN inventaris i ON m.ip_address = i.ip_address
                                    ORDER BY m.start_time DESC
                                    LIMIT 5
                                ");

                                if (mysqli_num_rows($query) == 0):
                                ?>
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted">Belum ada data maintenance.</td>
                                    </tr>
                                <?php
                                else:
                                    while ($data = mysqli_fetch_assoc($query)):
                                ?>
                                    <tr>
                                        <td><?= $no++ ?></td>
                                        <td class="fw-semibold text-primary"><?= htmlspecialchars($data['ip_address']) ?></td>
                                        <td><?= htmlspecialchars($data['user']) ?></td>
                                        <td><?= htmlspecialchars($data['teknisi']) ?></td>
                                        <td><?= formatWaktuIndo($data['start_time']) ?></td>
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