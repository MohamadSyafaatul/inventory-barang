<?php
session_start();

if (!isset($_SESSION['login'])) {
    header("Location: ../assets/login.php");
    exit;
}

include "../config/koneksi.php";
include "../config/helper.php";

$where = [];
if (!empty($_GET['search'])) {
    $search = mysqli_real_escape_string($conn, $_GET['search']);
    $where[] = "(i.ip_address LIKE '%$search%' OR i.user LIKE '%$search%' OR i.jenis_barang LIKE '%$search%' OR m.teknisi LIKE '%$search%')";
}
if (!empty($_GET['filter_address']) && is_array($_GET['filter_address'])) {
    $fa = array_map(fn($v) => "'" . mysqli_real_escape_string($conn, $v) . "'", $_GET['filter_address']);
    $where[] = "i.ip_address IN (" . implode(',', $fa) . ")";
}
if (!empty($_GET['filter_teknisi']) && is_array($_GET['filter_teknisi'])) {
    $ft = array_map(fn($v) => "'" . mysqli_real_escape_string($conn, $v) . "'", $_GET['filter_teknisi']);
    $where[] = "m.teknisi IN (" . implode(',', $ft) . ")";
}
if (!empty($_GET['filter_status']) && is_array($_GET['filter_status'])) {
    $fs = array_map(fn($v) => "'" . mysqli_real_escape_string($conn, $v) . "'", $_GET['filter_status']);
    $where[] = "m.status_maintenance IN (" . implode(',', $fs) . ")";
}

$whereClause = count($where) > 0 ? "WHERE " . implode(" AND ", $where) : "";

$data = mysqli_query($conn, "
    SELECT m.*, i.ip_address, i.user, i.jenis_barang
    FROM maintenance m
    JOIN inventaris i ON m.ip_address = i.ip_address
    $whereClause
    ORDER BY m.start_time DESC
");

// Filter options
$opt_address  = mysqli_query($conn, "SELECT DISTINCT ip_address FROM inventaris ORDER BY ip_address ASC");
$opt_teknisi  = mysqli_query($conn, "SELECT DISTINCT teknisi FROM maintenance ORDER BY teknisi ASC");
$opt_status   = ["Berlangsung", "Selesai"];
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
        .btn-action-sm { padding: 4px 10px; font-size: 0.82rem; font-weight: 500; border-radius: 6px; }
        /* Filter modal */
        .filter-modal .modal-content { border: none; border-radius: 16px; box-shadow: 0 20px 60px rgba(0,0,0,0.15); }
        .filter-modal .modal-header { background: linear-gradient(135deg,#005cb9 0%,#0077e6 100%); color: white; border-radius: 16px 16px 0 0; padding: 18px 24px; border-bottom: none; }
        .filter-modal .modal-header .btn-close { filter: invert(1); }
        .filter-modal .modal-body { padding: 8px 24px 16px; }
        .filter-modal .modal-footer { border-top: 1px solid #f0f0f0; padding: 16px 24px; border-radius: 0 0 16px 16px; }
        .filter-accordion-btn { display:flex; justify-content:space-between; align-items:center; width:100%; background:none; border:none; padding:10px 0; cursor:pointer; color:#343a40; font-size:0.78rem; font-weight:700; letter-spacing:0.08em; text-transform:uppercase; }
        .filter-accordion-btn:focus { outline:none; box-shadow:none; }
        .filter-accordion-btn .chevron-icon { font-size:0.85rem; color:#adb5bd; transition:transform 0.25s ease; }
        .filter-accordion-btn.collapsed .chevron-icon { transform:rotate(-90deg); }
        .filter-accordion-item { border-bottom:1px solid #f0f0f0; }
        .filter-accordion-item:last-child { border-bottom:none; }
        .filter-collapse-body { padding-bottom:14px; }
        .filter-chip-wrap { display:flex; flex-wrap:wrap; gap:8px; }
        .filter-chip { display:flex; align-items:center; gap:6px; cursor:pointer; }
        .filter-chip input[type="checkbox"] { display:none; }
        .filter-chip .chip-label { display:inline-flex; align-items:center; padding:5px 14px; border-radius:20px; border:1.5px solid #dee2e6; background:#f8f9fa; color:#495057; font-size:0.83rem; font-weight:500; transition:all 0.18s ease; user-select:none; cursor:pointer; }
        .filter-chip input:checked + .chip-label { background:#e8f0fe; border-color:#005cb9; color:#005cb9; font-weight:600; }
        .filter-chip .chip-label:hover { border-color:#005cb9; background:#f0f5ff; }
        .filter-btn-wrap { position:relative; display:inline-flex; }
        .filter-active-badge { position:absolute; top:-6px; right:-6px; background:#dc3545; color:white; border-radius:50%; width:18px; height:18px; font-size:0.65rem; font-weight:700; display:flex; align-items:center; justify-content:center; }
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
            <div class="modern-card">
                <div class="modern-card-header d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <h5 class="modern-card-title mb-0"><i class="bi bi-tools me-2"></i>Daftar Laporan Maintenance</h5>
                    <div class="d-flex align-items-center gap-2 flex-wrap">

                        <!-- Selection counter -->
                        <span id="selectionInfo" class="text-muted" style="font-size:0.85rem; display:none;">
                            <span id="selectionCount">0</span> dipilih
                        </span>

                        <!-- SEARCH FORM -->
                        <form method="GET" action="index.php" class="d-flex align-items-center gap-2 mb-0">
                            <?php
                            if (!empty($_GET['filter_address']) && is_array($_GET['filter_address']))
                                foreach($_GET['filter_address'] as $v) echo '<input type="hidden" name="filter_address[]" value="'.htmlspecialchars($v).'">';
                            if (!empty($_GET['filter_teknisi']) && is_array($_GET['filter_teknisi']))
                                foreach($_GET['filter_teknisi'] as $v) echo '<input type="hidden" name="filter_teknisi[]" value="'.htmlspecialchars($v).'">';
                            if (!empty($_GET['filter_status']) && is_array($_GET['filter_status']))
                                foreach($_GET['filter_status'] as $v) echo '<input type="hidden" name="filter_status[]" value="'.htmlspecialchars($v).'">';
                            ?>
                            <div class="input-group input-group-sm" style="width:220px;">
                                <input type="text" name="search" class="form-control" placeholder="Cari disini" value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
                                <button class="btn btn-outline-secondary" type="submit"><i class="bi bi-search"></i></button>
                            </div>
                        </form>

                        <!-- FILTER BUTTON -->
                        <?php
                        $activeFC = 0;
                        if (!empty($_GET['filter_address']) && is_array($_GET['filter_address'])) $activeFC += count($_GET['filter_address']);
                        if (!empty($_GET['filter_teknisi']) && is_array($_GET['filter_teknisi'])) $activeFC += count($_GET['filter_teknisi']);
                        if (!empty($_GET['filter_status']) && is_array($_GET['filter_status'])) $activeFC += count($_GET['filter_status']);
                        ?>
                        <div class="filter-btn-wrap">
                            <button type="button"
                                class="btn btn-sm <?= $activeFC > 0 ? 'btn-primary' : 'btn-outline-secondary' ?>"
                                data-bs-toggle="modal" data-bs-target="#filterModalMaint"
                                style="border-radius:8px; padding:5px 12px;">
                                <i class="bi bi-funnel<?= $activeFC > 0 ? '-fill' : '' ?>"></i> Filter
                            </button>
                            <?php if ($activeFC > 0): ?>
                                <span class="filter-active-badge"><?= $activeFC ?></span>
                            <?php endif; ?>
                        </div>

                        <?php if (!empty($_GET)): ?>
                            <a href="index.php" class="btn btn-sm btn-outline-danger" title="Reset" style="border-radius:8px;"><i class="bi bi-x-lg"></i></a>
                        <?php endif; ?>

                        <!-- Cetak -->
                        <button type="submit" form="formCetak" id="btnCetak" class="btn btn-modern-light btn-sm">
                            <i class="bi bi-printer me-1"></i> <span id="btnCetakLabel">Cetak Semua</span>
                        </button>
                        <a href="tambah.php" class="btn btn-modern-primary btn-sm">
                            <i class="bi bi-plus-circle me-1"></i> Tambah Maintenance
                        </a>
                    </div>
                </div>
                <div class="modern-card-body p-0">
                    <form id="formCetak" action="cetak.php" method="GET" target="_blank">
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
                                                <a href="detail.php?id=<?= $d['id_maintenance'] ?>" class="btn btn-outline-primary btn-action-sm" title="Detail">
                                                    <i class="bi bi-eye me-1"></i> Detail
                                                </a>
                                                <a href="edit.php?id=<?= $d['id_maintenance'] ?>" class="btn btn-outline-warning btn-action-sm" title="Edit">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <a href="javascript:void(0)" class="btn btn-outline-danger btn-action-sm"
                                                   onclick="confirmDeleteMaint(<?= $d['id_maintenance'] ?>)" title="Hapus">
                                                    <i class="bi bi-trash"></i>
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
                    </form>
                </div>
            </div>

            <!-- FILTER MODAL -->
            <form method="GET" action="index.php" id="filterFormMaint">
                <?php if (!empty($_GET['search'])): ?>
                    <input type="hidden" name="search" value="<?= htmlspecialchars($_GET['search']) ?>">
                <?php endif; ?>
                <div class="modal fade filter-modal" id="filterModalMaint" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <div>
                                    <h6 class="modal-title mb-0"><i class="bi bi-funnel-fill me-2"></i>Filter Data Maintenance</h6>
                                    <div style="font-size:0.78rem;opacity:0.8;margin-top:2px;">Pilih satu atau lebih untuk memfilter data</div>
                                </div>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">

                                <!-- IP Address -->
                                <?php $hasFA = (!empty($_GET['filter_address']) && is_array($_GET['filter_address'])); ?>
                                <div class="filter-accordion-item">
                                    <button class="filter-accordion-btn" type="button" data-bs-toggle="collapse" data-bs-target="#mCollapseIP" aria-expanded="true">
                                        <span><i class="bi bi-hdd-network me-2" style="color:#005cb9;"></i>IP Address
                                            <?php if ($hasFA): ?><span class="badge bg-primary ms-2" style="font-size:0.65rem;border-radius:10px;"><?= count($_GET['filter_address']) ?></span><?php endif; ?>
                                        </span>
                                        <i class="bi bi-chevron-down chevron-icon"></i>
                                    </button>
                                    <div class="collapse show" id="mCollapseIP">
                                        <div class="filter-collapse-body"><div class="filter-chip-wrap">
                                            <?php while($u = mysqli_fetch_assoc($opt_address)):
                                                $chk = ($hasFA && in_array($u['ip_address'], $_GET['filter_address'])) ? 'checked' : ''; ?>
                                            <label class="filter-chip">
                                                <input type="checkbox" name="filter_address[]" value="<?= htmlspecialchars($u['ip_address']) ?>" <?= $chk ?>>
                                                <span class="chip-label"><i class="bi bi-ethernet me-1"></i><?= htmlspecialchars($u['ip_address']) ?></span>
                                            </label>
                                            <?php endwhile; ?>
                                        </div></div>
                                    </div>
                                </div>

                                <!-- Teknisi -->
                                <?php $hasFT = (!empty($_GET['filter_teknisi']) && is_array($_GET['filter_teknisi'])); ?>
                                <div class="filter-accordion-item">
                                    <button class="filter-accordion-btn collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#mCollapseTeknisi" aria-expanded="false">
                                        <span><i class="bi bi-person-gear me-2" style="color:#005cb9;"></i>Teknisi
                                            <?php if ($hasFT): ?><span class="badge bg-primary ms-2" style="font-size:0.65rem;border-radius:10px;"><?= count($_GET['filter_teknisi']) ?></span><?php endif; ?>
                                        </span>
                                        <i class="bi bi-chevron-down chevron-icon"></i>
                                    </button>
                                    <div class="collapse <?= $hasFT ? 'show' : '' ?>" id="mCollapseTeknisi">
                                        <div class="filter-collapse-body"><div class="filter-chip-wrap">
                                            <?php while($t = mysqli_fetch_assoc($opt_teknisi)):
                                                $chk = ($hasFT && in_array($t['teknisi'], $_GET['filter_teknisi'])) ? 'checked' : ''; ?>
                                            <label class="filter-chip">
                                                <input type="checkbox" name="filter_teknisi[]" value="<?= htmlspecialchars($t['teknisi']) ?>" <?= $chk ?>>
                                                <span class="chip-label"><i class="bi bi-person-circle me-1"></i><?= htmlspecialchars($t['teknisi']) ?></span>
                                            </label>
                                            <?php endwhile; ?>
                                        </div></div>
                                    </div>
                                </div>

                                <!-- Status -->
                                <?php $hasFS = (!empty($_GET['filter_status']) && is_array($_GET['filter_status'])); ?>
                                <div class="filter-accordion-item">
                                    <button class="filter-accordion-btn collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#mCollapseStatus" aria-expanded="false">
                                        <span><i class="bi bi-activity me-2" style="color:#005cb9;"></i>Status
                                            <?php if ($hasFS): ?><span class="badge bg-primary ms-2" style="font-size:0.65rem;border-radius:10px;"><?= count($_GET['filter_status']) ?></span><?php endif; ?>
                                        </span>
                                        <i class="bi bi-chevron-down chevron-icon"></i>
                                    </button>
                                    <div class="collapse <?= $hasFS ? 'show' : '' ?>" id="mCollapseStatus">
                                        <div class="filter-collapse-body"><div class="filter-chip-wrap">
                                            <?php foreach($opt_status as $s):
                                                $chk = ($hasFS && in_array($s, $_GET['filter_status'])) ? 'checked' : ''; ?>
                                            <label class="filter-chip">
                                                <input type="checkbox" name="filter_status[]" value="<?= $s ?>" <?= $chk ?>>
                                                <span class="chip-label"><?= $s === 'Berlangsung' ? '<i class="bi bi-hourglass-split me-1"></i>' : '<i class="bi bi-check-circle me-1"></i>' ?><?= $s ?></span>
                                            </label>
                                            <?php endforeach; ?>
                                        </div></div>
                                    </div>
                                </div>

                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="clearMaintFilter()">Reset Filter</button>
                                <button type="submit" class="btn btn-sm btn-primary px-4">Terapkan Filter</button>
                            </div>
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
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function confirmDeleteMaint(id) {
    Swal.fire({
        title: 'Konfirmasi Hapus',
        text: 'Apakah anda yakin menghapus data maintenance ini?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = 'hapus.php?id=' + id;
        }
    });
}

<?php if (!empty($_SESSION['success_message'])): ?>
Swal.fire({
    icon: 'success',
    title: 'Berhasil!',
    text: '<?= addslashes($_SESSION['success_message']) ?>',
    timer: 2500,
    showConfirmButton: false
});
<?php unset($_SESSION['success_message']); endif; ?>
function clearMaintFilter() {
    document.querySelectorAll('#filterFormMaint input[type="checkbox"]').forEach(cb => cb.checked = false);
    document.getElementById('filterFormMaint').submit();
}

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
