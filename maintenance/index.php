<?php
session_start();

if (!isset($_SESSION['login'])) {
    header("Location: ../assets/login.php");
    exit;
}

include "../config/koneksi.php";
include "../config/helper.php";

$data = mysqli_query($conn, "
    SELECT m.*, i.ip_address, i.user, i.jenis_barang
    FROM maintenance m
    JOIN inventaris i ON m.ip_address = i.ip_address
    ORDER BY m.start_time DESC
");
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Data Maintenance | Portal Pelindo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .btn-action-sm {
            padding: 4px 10px;
            font-size: 0.82rem;
            font-weight: 500;
            border-radius: 6px;
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
                Total: <?= mysqli_num_rows($data) ?> Laporan Perawatan
            </div>
        </div>

        <div class="content-body">
            <form id="formCetak" action="cetak.php" method="GET" target="_blank">
            <div class="modern-card">
                <div class="modern-card-header">
                    <h5 class="modern-card-title"><i class="bi bi-tools me-2"></i>Daftar Laporan Maintenance</h5>
                    <div class="d-flex align-items-center gap-2">
                        <!-- Selection counter -->
                        <span id="selectionInfo" class="text-muted" style="font-size:0.85rem; display:none;">
                            <span id="selectionCount">0</span> dipilih
                        </span>
                        <!-- Cetak Hasil: jika tidak ada pilihan = cetak semua, jika ada = cetak yang dipilih -->
                        <button type="submit" id="btnCetak" class="btn btn-modern-light btn-sm">
                            <i class="bi bi-printer me-1"></i> <span id="btnCetakLabel">Cetak Semua</span>
                        </button>
                        <a href="tambah.php" class="btn btn-modern-primary btn-sm">
                            <i class="bi bi-plus-circle me-1"></i> Tambah Maintenance
                        </a>
                    </div>
                </div>
                <div class="modern-card-body p-0">
                    <div class="table-responsive">
                        <table class="table modern-table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th style="width:42px; text-align:center;">
                                        <input type="checkbox" id="checkAll" title="Pilih Semua"
                                            style="width:16px;height:16px;cursor:pointer;accent-color:var(--pelindo-primary);">
                                    </th>
                                    <th>No</th>
                                    <th>IP Address</th>
                                    <th>User</th>
                                    <th>Jenis Barang</th>
                                    <th>Teknisi</th>
                                    <th>Periode</th>
                                    <th>Waktu Mulai</th>
                                    <th>Status</th>
                                    <th>Durasi</th>
                                    <th width="150" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $no = 1; 
                                if (mysqli_num_rows($data) == 0):
                                ?>
                                    <tr>
                                        <td colspan="11" class="text-center py-4 text-muted">Belum ada data maintenance.</td>
                                    </tr>
                                <?php
                                else:
                                    while ($d = mysqli_fetch_assoc($data)):
                                        $isBerlangsung = ($d['status_maintenance'] === 'Berlangsung');
                                        $durasi = hitungDurasiTeks($d['start_time'], $d['finish_time']);
                                ?>
                                    <tr class="row-selectable" data-id="<?= $d['id_maintenance'] ?>">
                                        <td class="text-center">
                                            <input type="checkbox" name="ids[]" value="<?= $d['id_maintenance'] ?>"
                                                class="row-check"
                                                style="width:16px;height:16px;cursor:pointer;accent-color:var(--pelindo-primary);">
                                        </td>
                                        <td><?= $no++ ?></td>
                                        <td class="fw-semibold text-primary"><?= htmlspecialchars($d['ip_address']) ?></td>
                                        <td><?= htmlspecialchars($d['user']) ?></td>
                                        <td><?= htmlspecialchars($d['jenis_barang']) ?></td>
                                        <td class="fw-medium"><?= htmlspecialchars($d['teknisi']) ?></td>
                                        <td><?= htmlspecialchars($d['periode_perawatan']) ?></td>
                                        <td style="font-size: 0.88rem;"><?= formatWaktuIndo($d['start_time']) ?></td>
                                        <td>
                                            <span class="badge-status <?= $isBerlangsung ? 'badge-status-berlangsung' : 'badge-status-selesai' ?>">
                                                <i class="bi <?= $isBerlangsung ? 'bi-hourglass-split' : 'bi-check-circle-fill' ?>"></i>
                                                <?= htmlspecialchars($d['status_maintenance']) ?>
                                            </span>
                                        </td>
                                        <td style="font-size: 0.85rem;">
                                            <?php if ($isBerlangsung): ?>
                                                <span class="text-muted fst-italic">Berlangsung</span>
                                            <?php else: ?>
                                                <span><?= htmlspecialchars($durasi) ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="d-flex justify-content-center gap-1">
                                                <a href="detail.php?id=<?= $d['id_maintenance'] ?>" class="btn btn-outline-primary btn-action-sm">
                                                    <i class="bi bi-eye me-1"></i> Detail
                                                </a>
                                                <?php if ($isBerlangsung): ?>
                                                <button type="button"
                                                    class="btn btn-outline-success btn-action-sm"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#modalSelesaikan"
                                                    data-id="<?= $d['id_maintenance'] ?>">
                                                    <i class="bi bi-check2"></i> Finish
                                                </button>
                                                <?php endif; ?>
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
            </form>
        </div>
    </div>
</div>

<!-- Modal Konfirmasi Selesaikan -->
<div class="modal fade" id="modalSelesaikan" tabindex="-1" aria-labelledby="modalSelesaikanLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 12px; overflow: hidden;">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="modalSelesaikanLabel">
                    <i class="bi bi-check2-circle me-2"></i>Selesaikan Maintenance
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <p class="mb-3 text-dark">Apakah Anda yakin ingin menyelesaikan maintenance ini?</p>
                <div class="alert alert-info py-2.5 px-3 mb-0" style="border-radius: 8px; font-size: 0.9rem;">
                    <i class="bi bi-clock me-1.5"></i>
                    Sistem akan otomatis mencatat <strong>waktu selesai</strong> menggunakan waktu server saat tombol dikonfirmasi.
                </div>
            </div>
            <div class="modal-footer bg-light px-4 py-3">
                <button type="button" class="btn btn-light border" data-bs-dismiss="modal" style="border-radius: 8px;">Batal</button>
                <form method="POST" action="selesaikan.php" class="d-inline" id="formSelesaikan">
                    <input type="hidden" name="id_maintenance" id="inputIdMaintenance" value="">
                    <button type="submit" class="btn btn-success px-4" style="border-radius: 8px;">
                        <i class="bi bi-check2-circle me-1"></i> Ya, Selesaikan
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
const modalSelesaikan = document.getElementById('modalSelesaikan');
modalSelesaikan.addEventListener('show.bs.modal', function (event) {
    const button = event.relatedTarget;
    const id     = button.getAttribute('data-id');
    document.getElementById('inputIdMaintenance').value = id;
});

// ===== CHECKBOX SELECTION LOGIC =====
const checkAll        = document.getElementById('checkAll');
const rowChecks       = document.querySelectorAll('.row-check');
const selectionInfo   = document.getElementById('selectionInfo');
const selectionCount  = document.getElementById('selectionCount');
const btnCetakLabel   = document.getElementById('btnCetakLabel');
const formCetak       = document.getElementById('formCetak');

function updateSelectionUI() {
    const checked = document.querySelectorAll('.row-check:checked');
    const count   = checked.length;

    selectionCount.textContent = count;
    selectionInfo.style.display = count > 0 ? 'inline' : 'none';

    if (count === 0) {
        btnCetakLabel.textContent = 'Cetak Semua';
    } else {
        btnCetakLabel.textContent = 'Cetak ' + count + ' Laporan';
    }

    // Highlight selected rows
    document.querySelectorAll('.row-selectable').forEach(row => {
        const cb = row.querySelector('.row-check');
        if (cb.checked) {
            row.style.background = 'rgba(0, 92, 185, 0.06)';
        } else {
            row.style.background = '';
        }
    });

    // Update checkAll indeterminate state
    checkAll.indeterminate = count > 0 && count < rowChecks.length;
    checkAll.checked = count === rowChecks.length && rowChecks.length > 0;
}

// Select All
checkAll.addEventListener('change', function() {
    rowChecks.forEach(cb => cb.checked = this.checked);
    updateSelectionUI();
});

// Individual row checkbox
rowChecks.forEach(cb => {
    cb.addEventListener('change', updateSelectionUI);
});

// Click on row itself to toggle checkbox (except on buttons/links)
document.querySelectorAll('.row-selectable').forEach(row => {
    row.addEventListener('click', function(e) {
        // Ignore clicks on buttons, links, and the checkbox itself
        if (e.target.closest('a') || e.target.closest('button') || e.target.type === 'checkbox') return;
        const cb = row.querySelector('.row-check');
        cb.checked = !cb.checked;
        updateSelectionUI();
    });
    row.style.cursor = 'pointer';
});

// If no checkbox selected when form submitted → remove ids[] so cetak.php gets all
formCetak.addEventListener('submit', function(e) {
    const checked = document.querySelectorAll('.row-check:checked');
    if (checked.length === 0) {
        // Disable all checkboxes so they don't get submitted (cetak.php will show all)
        rowChecks.forEach(cb => cb.disabled = true);
    }
});
</script>
</body>
</html>
