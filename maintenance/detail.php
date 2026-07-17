<?php
session_start();

if (!isset($_SESSION['login'])) {
    header("Location: ../assets/login.php");
    exit;
}

include "../config/koneksi.php";
include "../config/helper.php";

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($id <= 0) {
    header("Location: index.php");
    exit;
}

// Ambil data maintenance beserta data inventaris
$q = mysqli_query($conn, "
    SELECT m.*, i.user, i.jenis_barang, i.sub_jenis_barang, i.serial_number, i.harga_per_unit
    FROM maintenance m
    JOIN inventaris i ON m.ip_address = i.ip_address
    WHERE m.id_maintenance = '$id'
    LIMIT 1
");
$m = mysqli_fetch_assoc($q);

if (!$m) {
    header("Location: index.php");
    exit;
}

// Ambil checklist
$qc = mysqli_query($conn, "SELECT * FROM maintenance_checklist WHERE id_maintenance = '$id' ORDER BY id_checklist ASC");

// Pesan notifikasi
$sukses = isset($_GET['sukses']) ? $_GET['sukses'] : '';
$info   = isset($_GET['info'])   ? $_GET['info']   : '';

// Hitung durasi
$durasiTeks = hitungDurasiTeks($m['start_time'], $m['finish_time']);
$isBerlangsung = ($m['status_maintenance'] === 'Berlangsung');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Detail Maintenance #<?= $id ?> | Portal Pelindo</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .info-label { font-size: .8rem; color: #64748b; font-weight: 600; text-transform: uppercase; letter-spacing: .04em; }
        .info-value { font-size: 0.95rem; color: #1e293b; font-weight: 500; }
        .timeline-block { border-left: 3px solid var(--pelindo-primary); padding-left: 18px; }
        .checklist-ok     { color: #198754; font-weight: 600; }
        .checklist-perlu  { color: #fd7e14; font-weight: 600; }
        .checklist-ganti  { color: #dc3545; font-weight: 600; }
        .duration-badge {
            display: inline-flex; align-items: center; gap: 6px;
            background: #e0f2fe; color: #0369a1; border-radius: 20px;
            padding: 5px 14px; font-weight: 600; font-size: .85rem;
        }
        .btn-finish {
            background: linear-gradient(135deg, #198754, #157347);
            border: none; color: #fff; font-weight: 600;
            padding: 10px 24px; border-radius: 8px;
            transition: transform .15s, box-shadow .15s;
        }
        .btn-finish:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(25,135,84,.3);
            color: #fff;
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
            <a href="../inventaris/index.php"><i class="bi bi-pc-display"></i> Inventaris</a>
            <a href="index.php" class="active"><i class="bi bi-tools"></i> Maintenance</a>
            <a href="../history/index.php"><i class="bi bi-clock-history"></i> History</a>
        </div>

        <div class="sidebar-footer">
            <a href="../assets/logout.php" class="btn-logout"><i class="bi bi-box-arrow-right"></i> Logout</a>
        </div>
    </div>

    <!-- MAIN CONTENT -->
    <div class="main-content">
        <div class="top-navbar">
            <h5 class="top-navbar-title">Pemantauan Perawatan</h5>
            <div class="text-muted" style="font-size: 0.9rem;">
                <a href="index.php" class="text-decoration-none text-muted">Maintenance</a> / Laporan #<?= $id ?>
            </div>
        </div>

        <div class="content-body">
            <!-- Notifikasi -->
            <?php if ($sukses === 'selesai'): ?>
            <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2 mb-4" role="alert" style="border-radius: 8px;">
                <i class="bi bi-check-circle-fill fs-5"></i>
                <div><strong>Perawatan Berhasil Diselesaikan!</strong> Waktu selesai tercatat: <strong><?= formatWaktuIndo($m['finish_time']) ?></strong></div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php elseif ($info === 'sudah_selesai'): ?>
            <div class="alert alert-info alert-dismissible fade show d-flex align-items-center gap-2 mb-4" role="alert" style="border-radius: 8px;">
                <i class="bi bi-info-circle-fill fs-5"></i>
                <div>Maintenance ini sudah dalam status <strong>Selesai</strong> sebelumnya.</div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>

            <div class="modern-card">
                <div class="modern-card-header">
                    <h5 class="modern-card-title"><i class="bi bi-file-earmark-text me-2"></i>Detail Laporan Perawatan #<?= $id ?></h5>
                    <a href="index.php" class="btn btn-modern-light btn-sm"><i class="bi bi-arrow-left me-1"></i> Kembali</a>
                </div>
                
                <div class="modern-card-body p-4">
                    <!-- Status & Durasi -->
                    <div class="d-flex align-items-center gap-3 mb-4">
                        <span class="badge-status <?= $isBerlangsung ? 'badge-status-berlangsung' : 'badge-status-selesai' ?> px-3 py-2 fs-6">
                            <i class="bi <?= $isBerlangsung ? 'bi-hourglass-split' : 'bi-check-circle-fill' ?> me-1"></i>
                            Status: <?= htmlspecialchars($m['status_maintenance']) ?>
                        </span>
                        <?php if (!$isBerlangsung): ?>
                        <span class="duration-badge">
                            <i class="bi bi-stopwatch"></i>
                            Durasi Pengerjaan: <?= htmlspecialchars($durasiTeks) ?>
                        </span>
                        <?php endif; ?>
                    </div>

                    <!-- Timeline Waktu -->
                    <div class="row g-3 mb-4 bg-light p-3 rounded-3 border">
                        <div class="col-md-6">
                            <div class="timeline-block">
                                <div class="info-label"><i class="bi bi-play-circle text-primary me-1"></i>Waktu Mulai (Start Time)</div>
                                <div class="info-value fs-5 mt-1"><?= formatWaktuIndo($m['start_time']) ?></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="timeline-block" style="border-color: <?= $isBerlangsung ? '#ffc107' : '#198754' ?>">
                                <div class="info-label">
                                    <i class="bi bi-stop-circle <?= $isBerlangsung ? 'text-warning' : 'text-success' ?> me-1"></i>
                                    Waktu Selesai (Finish Time)
                                </div>
                                <div class="info-value fs-5 mt-1">
                                    <?php if ($isBerlangsung): ?>
                                        <span class="text-warning fst-italic">Belum Selesai (Dalam Pengerjaan)</span>
                                    <?php else: ?>
                                        <?= formatWaktuIndo($m['finish_time']) ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Data Inventaris Terkait -->
                    <h6 class="fw-bold mb-3 text-primary"><i class="bi bi-pc-display me-2"></i>Data Perangkat Terkait</h6>
                    <div class="row g-3 mb-4 border-bottom pb-4">
                        <div class="col-md-4">
                            <div class="info-label">IP Address</div>
                            <div class="info-value mt-1"><?= htmlspecialchars($m['ip_address']) ?></div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-label">User / Pemilik</div>
                            <div class="info-value mt-1"><?= htmlspecialchars($m['user']) ?></div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-label">Jenis Barang</div>
                            <div class="info-value mt-1"><?= htmlspecialchars($m['jenis_barang']) ?> — <?= htmlspecialchars($m['sub_jenis_barang']) ?></div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-label">Serial Number</div>
                            <div class="info-value mt-1"><?= htmlspecialchars($m['serial_number']) ?></div>
                        </div>
                    </div>

                    <!-- Data Maintenance -->
                    <h6 class="fw-bold mb-3 text-primary"><i class="bi bi-tools me-2"></i>Laporan Kerusakan & Tindakan</h6>
                    <div class="row g-3 mb-4 border-bottom pb-4">
                        <div class="col-md-4">
                            <div class="info-label">Pelaksana Perawatan</div>
                            <div class="info-value mt-1"><?= htmlspecialchars($m['pelaksana_perawatan']) ?></div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-label">Teknisi Lapangan</div>
                            <div class="info-value mt-1"><?= htmlspecialchars($m['teknisi']) ?></div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-label">Tipe / Periode</div>
                            <div class="info-value mt-1">
                                <span class="badge bg-secondary text-white px-2 py-1"><?= htmlspecialchars($m['tipe_perawatan']) ?></span>
                                <span class="badge bg-dark text-white px-2 py-1"><?= htmlspecialchars($m['periode_perawatan']) ?></span>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="info-label">Deskripsi Masalah</div>
                            <div class="info-value mt-1" style="white-space: pre-line;"><?= htmlspecialchars($m['permasalahan']) ?></div>
                        </div>
                        <div class="col-md-12">
                            <div class="info-label">Aksi Tindakan yang Diambil</div>
                            <div class="info-value mt-1" style="white-space: pre-line;"><?= htmlspecialchars($m['aksi']) ?></div>
                        </div>
                        <?php if ($m['keterangan']): ?>
                        <div class="col-md-12">
                            <div class="info-label">Keterangan Tambahan</div>
                            <div class="info-value mt-1" style="white-space: pre-line;"><?= htmlspecialchars($m['keterangan']) ?></div>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- Checklist -->
                    <h6 class="fw-bold mb-3 text-primary"><i class="bi bi-list-check me-2"></i>Hasil Checklist Perangkat</h6>
                    <div class="row g-3 mb-4">
                        <div class="col-md-8">
                            <div class="table-responsive rounded-3 border">
                                <table class="table modern-table table-hover align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th style="width:60%">Item Checklist</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($cl = mysqli_fetch_assoc($qc)): ?>
                                        <?php
                                            $cls = '';
                                            if ($cl['status'] === 'OK')               $cls = 'checklist-ok';
                                            elseif ($cl['status'] === 'Perlu Perbaikan') $cls = 'checklist-perlu';
                                            elseif ($cl['status'] === 'Diganti')       $cls = 'checklist-ganti';
                                        ?>
                                        <tr>
                                            <td class="fw-medium"><?= htmlspecialchars($cl['item']) ?></td>
                                            <td class="<?= $cls ?>">
                                                <?php if ($cl['status'] === 'OK'): ?>
                                                    <i class="bi bi-check-circle-fill me-1"></i>
                                                <?php elseif ($cl['status'] === 'Perlu Perbaikan'): ?>
                                                    <i class="bi bi-exclamation-triangle-fill me-1"></i>
                                                <?php else: ?>
                                                    <i class="bi bi-x-circle-fill me-1"></i>
                                                <?php endif; ?>
                                                <?= htmlspecialchars($cl['status']) ?>
                                            </td>
                                        </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Action Footer -->
                    <div class="d-flex gap-2 border-top pt-4">
                        <a href="index.php" class="btn btn-modern-light">
                            <i class="bi bi-arrow-left"></i> Kembali ke Daftar
                        </a>
                        <?php if ($isBerlangsung): ?>
                        <button type="button" class="btn-finish" data-bs-toggle="modal" data-bs-target="#modalSelesaikan">
                            <i class="bi bi-check2-circle me-1.5"></i> Selesaikan Perawatan
                        </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Konfirmasi Selesaikan -->
<?php if ($isBerlangsung): ?>
<div class="modal fade" id="modalSelesaikan" tabindex="-1" aria-labelledby="modalSelesaikanLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 12px; overflow: hidden;">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="modalSelesaikanLabel">
                    <i class="bi bi-check2-circle me-2"></i>Konfirmasi Selesaikan Maintenance
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <p class="mb-3 text-dark">Apakah Anda yakin ingin menyelesaikan laporan perawatan ini?</p>
                <div class="alert alert-info py-2.5 px-3 mb-0" style="border-radius: 8px; font-size: 0.9rem;">
                    <i class="bi bi-clock me-1.5"></i>
                    Sistem akan otomatis mencatat <strong>waktu selesai</strong> menggunakan waktu server saat tombol dikonfirmasi.
                </div>
            </div>
            <div class="modal-footer bg-light px-4 py-3">
                <button type="button" class="btn btn-light border" data-bs-dismiss="modal" style="border-radius: 8px;">Batal</button>
                <form method="POST" action="selesaikan.php" class="d-inline">
                    <input type="hidden" name="id_maintenance" value="<?= $id ?>">
                    <button type="submit" class="btn btn-success px-4" style="border-radius: 8px;">
                        <i class="bi bi-check2-circle me-1"></i> Ya, Selesaikan
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
