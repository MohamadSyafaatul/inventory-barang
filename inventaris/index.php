<?php
session_start();

if (!isset($_SESSION['login'])) {
    header("Location: ../assets/login.php");
    exit;
}

include "../config/koneksi.php";

$where = [];
if (!empty($_GET['search'])) {
    $search = mysqli_real_escape_string($conn, $_GET['search']);
    $where[] = "(ip_address LIKE '%$search%' OR jenis_barang LIKE '%$search%')";
}
if (!empty($_GET['filter_address']) && is_array($_GET['filter_address'])) {
    $fa_arr = array_map(function($val) use ($conn) { return "'" . mysqli_real_escape_string($conn, $val) . "'"; }, $_GET['filter_address']);
    $where[] = "ip_address IN (" . implode(",", $fa_arr) . ")";
}
if (!empty($_GET['filter_user']) && is_array($_GET['filter_user'])) {
    $fu_arr = array_map(function($val) use ($conn) { return "'" . mysqli_real_escape_string($conn, $val) . "'"; }, $_GET['filter_user']);
    $where[] = "user IN (" . implode(",", $fu_arr) . ")";
}
if (!empty($_GET['filter_jenis']) && is_array($_GET['filter_jenis'])) {
    $fj_arr = array_map(function($val) use ($conn) { return "'" . mysqli_real_escape_string($conn, $val) . "'"; }, $_GET['filter_jenis']);
    $where[] = "jenis_barang IN (" . implode(",", $fj_arr) . ")";
}

$whereClause = count($where) > 0 ? "WHERE " . implode(" AND ", $where) : "";
$data = mysqli_query($conn, "SELECT * FROM inventaris $whereClause ORDER BY ip_address DESC");

// Get options for filters
$address_q = mysqli_query($conn, "SELECT DISTINCT ip_address FROM inventaris ORDER BY ip_address ASC");
$users_q = mysqli_query($conn, "SELECT DISTINCT user FROM inventaris ORDER BY user ASC");
$jenis_q = mysqli_query($conn, "SELECT DISTINCT jenis_barang FROM inventaris ORDER BY jenis_barang ASC");
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
    <style>
        /* ===== FILTER MODAL MODERN ===== */
        .filter-modal .modal-content {
            border: none;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.15);
        }
        .filter-modal .modal-header {
            background: linear-gradient(135deg, #005cb9 0%, #0077e6 100%);
            color: white;
            border-radius: 16px 16px 0 0;
            padding: 18px 24px;
            border-bottom: none;
        }
        .filter-modal .modal-header .btn-close {
            filter: invert(1);
        }
        .filter-modal .modal-body {
            padding: 24px;
        }
        .filter-section-title {
            font-size: 0.72rem;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: #6c757d;
            margin-bottom: 10px;
        }
        .filter-chip-wrap {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }
        .filter-chip {
            display: flex;
            align-items: center;
            gap: 6px;
            cursor: pointer;
        }
        .filter-chip input[type="checkbox"] {
            display: none;
        }
        .filter-chip .chip-label {
            display: inline-flex;
            align-items: center;
            padding: 5px 14px;
            border-radius: 20px;
            border: 1.5px solid #dee2e6;
            background: #f8f9fa;
            color: #495057;
            font-size: 0.83rem;
            font-weight: 500;
            transition: all 0.18s ease;
            user-select: none;
            cursor: pointer;
        }
        .filter-chip input:checked + .chip-label {
            background: #e8f0fe;
            border-color: #005cb9;
            color: #005cb9;
            font-weight: 600;
        }
        .filter-chip .chip-label:hover {
            border-color: #005cb9;
            background: #f0f5ff;
        }
        .filter-modal .modal-footer {
            border-top: 1px solid #f0f0f0;
            padding: 16px 24px;
            border-radius: 0 0 16px 16px;
        }
        /* Badge filter aktif */
        .filter-btn-wrap { position: relative; display: inline-flex; }
        .filter-active-badge {
            position: absolute;
            top: -6px;
            right: -6px;
            background: #dc3545;
            color: white;
            border-radius: 50%;
            width: 18px;
            height: 18px;
            font-size: 0.65rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            line-height: 1;
        }
        /* Accordion filter */
        .filter-accordion-btn {
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 100%;
            background: none;
            border: none;
            padding: 10px 0;
            cursor: pointer;
            color: #343a40;
            font-size: 0.78rem;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }
        .filter-accordion-btn:focus { outline: none; box-shadow: none; }
        .filter-accordion-btn .chevron-icon {
            font-size: 0.85rem;
            color: #adb5bd;
            transition: transform 0.25s ease;
        }
        .filter-accordion-btn.collapsed .chevron-icon {
            transform: rotate(-90deg);
        }
        .filter-accordion-item {
            border-bottom: 1px solid #f0f0f0;
        }
        .filter-accordion-item:last-child { border-bottom: none; }
        .filter-collapse-body {
            padding-bottom: 14px;
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
            <a href="index.php" class="active"><i class="bi bi-pc-display"></i> Inventory</a>
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
                <div class="modern-card-header d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <h5 class="modern-card-title mb-0"><i class="bi bi-hdd-network me-2"></i>Daftar Perangkat &amp; Aset</h5>
                    
                    <div class="d-flex align-items-center gap-2 flex-wrap">

                        <!-- SEARCH FORM (standalone) -->
                        <form method="GET" action="index.php" class="d-flex align-items-center gap-2 mb-0">
                            <?php 
                            // Carry filter params into search form
                            if (!empty($_GET['filter_address']) && is_array($_GET['filter_address']))
                                foreach($_GET['filter_address'] as $v) echo '<input type="hidden" name="filter_address[]" value="'.htmlspecialchars($v).'">';
                            if (!empty($_GET['filter_user']) && is_array($_GET['filter_user']))
                                foreach($_GET['filter_user'] as $v) echo '<input type="hidden" name="filter_user[]" value="'.htmlspecialchars($v).'">';
                            if (!empty($_GET['filter_jenis']) && is_array($_GET['filter_jenis']))
                                foreach($_GET['filter_jenis'] as $v) echo '<input type="hidden" name="filter_jenis[]" value="'.htmlspecialchars($v).'">';
                            ?>
                            <div class="input-group input-group-sm search-bar-wrap" style="width: 220px;">
                                <input type="text" name="search" class="form-control" placeholder="Cari IP / Jenis..." value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
                                <button class="btn btn-outline-secondary" type="submit"><i class="bi bi-search"></i></button>
                            </div>
                        </form>

                        <!-- FILTER BUTTON -->
                        <?php
                        $activeFilterCount = 0;
                        if (!empty($_GET['filter_address']) && is_array($_GET['filter_address'])) $activeFilterCount += count($_GET['filter_address']);
                        if (!empty($_GET['filter_user']) && is_array($_GET['filter_user'])) $activeFilterCount += count($_GET['filter_user']);
                        if (!empty($_GET['filter_jenis']) && is_array($_GET['filter_jenis'])) $activeFilterCount += count($_GET['filter_jenis']);
                        ?>
                        <div class="filter-btn-wrap">
                            <button type="button" 
                                class="btn btn-sm <?= $activeFilterCount > 0 ? 'btn-primary' : 'btn-outline-secondary' ?>"
                                data-bs-toggle="modal" data-bs-target="#filterModal"
                                style="border-radius: 8px; padding: 5px 12px;">
                                <i class="bi bi-funnel<?= $activeFilterCount > 0 ? '-fill' : '' ?>"></i>
                                Filter
                            </button>
                            <?php if ($activeFilterCount > 0): ?>
                                <span class="filter-active-badge"><?= $activeFilterCount ?></span>
                            <?php endif; ?>
                        </div>

                        <?php if (!empty($_GET)): ?>
                            <a href="index.php" class="btn btn-sm btn-outline-danger" title="Reset Semua" style="border-radius: 8px;">
                                <i class="bi bi-x-lg"></i>
                            </a>
                        <?php endif; ?>

                        <a href="tambah.php" class="btn btn-modern-primary btn-sm text-nowrap">
                            <i class="bi bi-plus-circle me-1"></i> Tambah Inventaris
                        </a>
                    </div>
                </div>

                <!-- FILTER MODAL (outside all forms) -->
                <form method="GET" action="index.php" id="filterForm">
                    <?php if (!empty($_GET['search'])): ?>
                        <input type="hidden" name="search" value="<?= htmlspecialchars($_GET['search']) ?>">
                    <?php endif; ?>
                    <div class="modal fade filter-modal" id="filterModal" tabindex="-1" aria-labelledby="filterModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <div>
                                        <h6 class="modal-title mb-0" id="filterModalLabel">
                                            <i class="bi bi-funnel-fill me-2"></i>Filter Data Inventaris
                                        </h6>
                                        <div style="font-size: 0.78rem; opacity: 0.8; margin-top: 2px;">Pilih satu atau lebih untuk memfilter data</div>
                                    </div>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body" style="padding: 8px 24px 16px;">

                                    <!-- IP Address -->
                                    <?php $hasActiveFA = (!empty($_GET['filter_address']) && is_array($_GET['filter_address'])); ?>
                                    <div class="filter-accordion-item">
                                        <button class="filter-accordion-btn <?= $hasActiveFA ? '' : '' ?>" 
                                                type="button" data-bs-toggle="collapse" data-bs-target="#collapseIP" 
                                                aria-expanded="<?= $hasActiveFA ? 'true' : 'true' ?>">
                                            <span><i class="bi bi-hdd-network me-2" style="color:#005cb9;"></i>IP Address
                                                <?php if ($hasActiveFA): ?>
                                                    <span class="badge bg-primary ms-2" style="font-size:0.65rem;border-radius:10px;"><?= count($_GET['filter_address']) ?></span>
                                                <?php endif; ?>
                                            </span>
                                            <i class="bi bi-chevron-down chevron-icon"></i>
                                        </button>
                                        <div class="collapse show" id="collapseIP">
                                            <div class="filter-collapse-body">
                                                <div class="filter-chip-wrap">
                                                    <?php 
                                                    $address_q2 = mysqli_query($conn, "SELECT DISTINCT ip_address FROM inventaris ORDER BY ip_address ASC");
                                                    while($u = mysqli_fetch_assoc($address_q2)):
                                                        $chk = ($hasActiveFA && in_array($u['ip_address'], $_GET['filter_address'])) ? 'checked' : '';
                                                    ?>
                                                    <label class="filter-chip">
                                                        <input type="checkbox" name="filter_address[]" value="<?= htmlspecialchars($u['ip_address']) ?>" <?= $chk ?>>
                                                        <span class="chip-label"><i class="bi bi-ethernet me-1"></i><?= htmlspecialchars($u['ip_address']) ?></span>
                                                    </label>
                                                    <?php endwhile; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- User -->
                                    <?php $hasActiveUser = (!empty($_GET['filter_user']) && is_array($_GET['filter_user'])); ?>
                                    <div class="filter-accordion-item">
                                        <button class="filter-accordion-btn collapsed" 
                                                type="button" data-bs-toggle="collapse" data-bs-target="#collapseUser" 
                                                aria-expanded="false">
                                            <span><i class="bi bi-person me-2" style="color:#005cb9;"></i>User / Pemilik
                                                <?php if ($hasActiveUser): ?>
                                                    <span class="badge bg-primary ms-2" style="font-size:0.65rem;border-radius:10px;"><?= count($_GET['filter_user']) ?></span>
                                                <?php endif; ?>
                                            </span>
                                            <i class="bi bi-chevron-down chevron-icon"></i>
                                        </button>
                                        <div class="collapse <?= $hasActiveUser ? 'show' : '' ?>" id="collapseUser">
                                            <div class="filter-collapse-body">
                                                <div class="filter-chip-wrap">
                                                    <?php 
                                                    $users_q2 = mysqli_query($conn, "SELECT DISTINCT user FROM inventaris ORDER BY user ASC");
                                                    while($u = mysqli_fetch_assoc($users_q2)):
                                                        $chk = ($hasActiveUser && in_array($u['user'], $_GET['filter_user'])) ? 'checked' : '';
                                                    ?>
                                                    <label class="filter-chip">
                                                        <input type="checkbox" name="filter_user[]" value="<?= htmlspecialchars($u['user']) ?>" <?= $chk ?>>
                                                        <span class="chip-label"><i class="bi bi-person-circle me-1"></i><?= htmlspecialchars($u['user']) ?></span>
                                                    </label>
                                                    <?php endwhile; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Jenis Barang -->
                                    <?php $hasActiveJenis = (!empty($_GET['filter_jenis']) && is_array($_GET['filter_jenis'])); ?>
                                    <div class="filter-accordion-item">
                                        <button class="filter-accordion-btn collapsed" 
                                                type="button" data-bs-toggle="collapse" data-bs-target="#collapseJenis" 
                                                aria-expanded="false">
                                            <span><i class="bi bi-box-seam me-2" style="color:#005cb9;"></i>Jenis Barang
                                                <?php if ($hasActiveJenis): ?>
                                                    <span class="badge bg-primary ms-2" style="font-size:0.65rem;border-radius:10px;"><?= count($_GET['filter_jenis']) ?></span>
                                                <?php endif; ?>
                                            </span>
                                            <i class="bi bi-chevron-down chevron-icon"></i>
                                        </button>
                                        <div class="collapse <?= $hasActiveJenis ? 'show' : '' ?>" id="collapseJenis">
                                            <div class="filter-collapse-body">
                                                <div class="filter-chip-wrap">
                                                    <?php 
                                                    $jenis_q2 = mysqli_query($conn, "SELECT DISTINCT jenis_barang FROM inventaris ORDER BY jenis_barang ASC");
                                                    while($j = mysqli_fetch_assoc($jenis_q2)):
                                                        $chk = ($hasActiveJenis && in_array($j['jenis_barang'], $_GET['filter_jenis'])) ? 'checked' : '';
                                                    ?>
                                                    <label class="filter-chip">
                                                        <input type="checkbox" name="filter_jenis[]" value="<?= htmlspecialchars($j['jenis_barang']) ?>" <?= $chk ?>>
                                                        <span class="chip-label"><i class="bi bi-cpu me-1"></i><?= htmlspecialchars($j['jenis_barang']) ?></span>
                                                    </label>
                                                    <?php endwhile; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="clearFilter()">Reset Filter</button>
                                    <button type="submit" class="btn btn-sm btn-primary px-4">Terapkan Filter</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
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
                                                <a href="javascript:void(0)" class="btn btn-sm btn-outline-danger" 
                                                   onclick="confirmDelete('<?= addslashes($d['ip_address']) ?>')" title="Hapus">
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
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function confirmDelete(id) {
    Swal.fire({
        title: 'Konfirmasi Hapus',
        text: "Apakah anda yakin menghapus inventory ini?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = 'hapus.php?id=' + encodeURIComponent(id);
        }
    });
}

function clearFilter() {
    // Uncheck all checkboxes in filterForm
    document.querySelectorAll('#filterForm input[type="checkbox"]').forEach(cb => cb.checked = false);
    document.getElementById('filterForm').submit();
}

// Auto-open filter modal if filter is active
<?php if ($activeFilterCount > 0): ?>
// Filter aktif — tidak perlu auto-open, hanya tampilkan badge
<?php endif; ?>
</script>

<?php if (isset($_SESSION['success_message'])): ?>
<script>
    Swal.fire({
        icon: 'success',
        title: 'Berhasil',
        text: '<?= $_SESSION['success_message'] ?>',
        timer: 3000,
        showConfirmButton: false
    });
</script>
<?php unset($_SESSION['success_message']); ?>
<?php endif; ?>

</body>
</html>