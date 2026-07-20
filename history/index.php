<?php
session_start();

if (!isset($_SESSION['login'])) {
    header("Location: ../assets/login.php");
    exit;
}

include "../config/koneksi.php";
include "../config/helper.php";

// Ambil data maintenance dengan ringkasan status checklist (grouped by id_maintenance)
$data = mysqli_query($conn, "
    SELECT
        m.id_maintenance,
        m.start_time,
        m.finish_time,
        m.status_maintenance,
        m.teknisi,
        m.tipe_perawatan,
        i.ip_address,
        i.user,
        COUNT(CASE WHEN mc.status = 'OK' THEN 1 END) as ok_count,
        COUNT(CASE WHEN mc.status = 'Perlu Perbaikan' THEN 1 END) as repair_count,
        COUNT(CASE WHEN mc.status = 'Diganti' THEN 1 END) as replace_count
    FROM maintenance m
    JOIN inventaris  i ON m.ip_address = i.ip_address
    LEFT JOIN maintenance_checklist mc ON m.id_maintenance = mc.id_maintenance
    GROUP BY m.id_maintenance
    ORDER BY m.start_time DESC
");
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>History Maintenance | Portal Pelindo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .badge-compact {
            padding: 4px 8px;
            font-size: 0.75rem;
            font-weight: 600;
            border-radius: 6px;
            display: inline-flex;
            align-items: center;
            gap: 3px;
        }
    </style>
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
            <a href="../inventaris/index.php"><i class="bi bi-pc-display"></i> Inventory</a>
            <a href="../maintenance/index.php"><i class="bi bi-tools"></i> Maintenance</a>
            <a href="index.php" class="active"><i class="bi bi-clock-history"></i> History</a>
        </div>

        <div class="sidebar-footer">
            <a href="../assets/logout.php" class="btn-logout"><i class="bi bi-box-arrow-right"></i> Logout</a>
        </div>
    </div>

    <!-- MAIN CONTENT -->
    <div class="main-content">
        <div class="top-navbar">
            <h5 class="top-navbar-title">Riwayat Aktivitas</h5>
            <div class="text-muted" style="font-size: 0.9rem;">
                <i class="bi bi-clock"></i> Riwayat Lengkap Perawatan Perangkat
            </div>
        </div>

        <div class="content-body">
            <div class="modern-card">
                <div class="modern-card-header">
                    <h5 class="modern-card-title"><i class="bi bi-clock-history me-2"></i>Log Riwayat Maintenance</h5>
                </div>
                <div class="modern-card-body p-0">
                    <div class="table-responsive">
                        <table class="table modern-table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>IP Address</th>
                                    <th>User</th>
                                    <th>Waktu Mulai</th>
                                    <th>Waktu Selesai</th>
                                    <th>Durasi</th>
                                    <th>Ringkasan Checklist</th>
                                    <th>Status</th>
                                    <th width="100" class="text-center">Detail</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $no = 1; 
                                if (mysqli_num_rows($data) == 0):
                                ?>
                                    <tr>
                                        <td colspan="9" class="text-center py-4 text-muted">Belum ada riwayat aktivitas.</td>
                                    </tr>
                                <?php
                                else:
                                    while ($d = mysqli_fetch_assoc($data)):
                                        $isBerlangsung = ($d['status_maintenance'] === 'Berlangsung');
                                        $durasi = hitungDurasiTeks($d['start_time'], $d['finish_time']);
                                ?>
                                    <tr>
                                        <td><?= $no++ ?></td>
                                        <td class="fw-semibold text-primary"><?= htmlspecialchars($d['ip_address']) ?></td>
                                        <td><?= htmlspecialchars($d['user']) ?></td>
                                        <td style="font-size: 0.88rem;"><?= formatWaktuIndo($d['start_time']) ?></td>
                                        <td style="font-size: 0.88rem;">
                                            <?php if ($isBerlangsung): ?>
                                                <span class="text-warning fst-italic">Belum Selesai</span>
                                            <?php else: ?>
                                                <?= formatWaktuIndo($d['finish_time']) ?>
                                            <?php endif; ?>
                                        </td>
                                        <td style="font-size: 0.85rem;">
                                            <?php if ($isBerlangsung): ?>
                                                <span class="text-muted fst-italic">Berlangsung</span>
                                            <?php else: ?>
                                                <?= htmlspecialchars($durasi) ?>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="d-flex flex-wrap gap-1">
                                                <?php if ($d['ok_count'] > 0): ?>
                                                    <span class="badge-compact bg-success text-white" title="Item status OK"><i class="bi bi-check-circle-fill"></i> <?= $d['ok_count'] ?> OK</span>
                                                <?php endif; ?>
                                                <?php if ($d['repair_count'] > 0): ?>
                                                    <span class="badge-compact bg-warning text-dark" title="Item Perlu Perbaikan"><i class="bi bi-exclamation-triangle-fill"></i> <?= $d['repair_count'] ?> Perbaikan</span>
                                                <?php endif; ?>
                                                <?php if ($d['replace_count'] > 0): ?>
                                                    <span class="badge-compact bg-danger text-white" title="Item Diganti"><i class="bi bi-x-circle-fill"></i> <?= $d['replace_count'] ?> Diganti</span>
                                                <?php endif; ?>
                                                <?php if ($d['ok_count'] == 0 && $d['repair_count'] == 0 && $d['replace_count'] == 0): ?>
                                                    <span class="text-muted fst-italic" style="font-size: 0.85rem;">Tidak ada checklist</span>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge-status <?= $isBerlangsung ? 'badge-status-berlangsung' : 'badge-status-selesai' ?>">
                                                <i class="bi <?= $isBerlangsung ? 'bi-hourglass-split' : 'bi-check-circle-fill' ?>"></i>
                                                <?= htmlspecialchars($d['status_maintenance']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="d-flex justify-content-center">
                                                <a href="../maintenance/detail.php?id=<?= $d['id_maintenance'] ?>" class="btn btn-sm btn-outline-primary" style="padding: 4px 10px; font-size: 0.82rem; font-weight: 500; border-radius: 6px;">
                                                    <i class="bi bi-eye"></i> Detail
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
