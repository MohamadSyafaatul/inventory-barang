<?php
session_start();

if (!isset($_SESSION['login'])) {
    header("Location: ../assets/login.php");
    exit;
}

include "../config/koneksi.php";

$id = intval($_GET['id'] ?? 0);
if (!$id) {
    header("Location: index.php");
    exit;
}

if (isset($_POST['update'])) {
    $ip          = mysqli_real_escape_string($conn, $_POST['ip_address']);
    $pelaksana   = mysqli_real_escape_string($conn, $_POST['pelaksana_perawatan']);
    $teknisi     = mysqli_real_escape_string($conn, $_POST['teknisi']);
    $tipe        = mysqli_real_escape_string($conn, $_POST['tipe_perawatan']);
    $periode     = mysqli_real_escape_string($conn, $_POST['periode_perawatan']);
    $permasalahan = mysqli_real_escape_string($conn, $_POST['permasalahan']);
    $aksi        = mysqli_real_escape_string($conn, $_POST['aksi']);
    $keterangan  = mysqli_real_escape_string($conn, $_POST['keterangan']);

    mysqli_query($conn, "UPDATE maintenance SET
        ip_address='$ip',
        pelaksana_perawatan='$pelaksana',
        teknisi='$teknisi',
        tipe_perawatan='$tipe',
        periode_perawatan='$periode',
        permasalahan='$permasalahan',
        aksi='$aksi',
        keterangan='$keterangan'
        WHERE id_maintenance = $id
    ");

    // Update checklist
    $defaultItems = ['Motherboard','Processor','Hard Disk','Power Supply','RAM','Keyboard','Mouse','Monitor','Wifi LAN Card','CD ROM'];
    foreach ($defaultItems as $item) {
        $statusKey = 'status_' . str_replace([' ', '/'], '_', strtolower($item));
        $status    = isset($_POST[$statusKey]) ? mysqli_real_escape_string($conn, $_POST[$statusKey]) : 'Perlu Perbaikan';
        mysqli_query($conn, "UPDATE maintenance_checklist SET status='$status'
            WHERE id_maintenance=$id AND item='" . mysqli_real_escape_string($conn, $item) . "'");
    }

    $_SESSION['success_message'] = "Data maintenance berhasil diperbarui.";
    header("Location: index.php");
    exit;
}

$data = mysqli_fetch_assoc(mysqli_query($conn, "SELECT m.*, i.jenis_barang, i.sub_jenis_barang, i.serial_number FROM maintenance m JOIN inventaris i ON m.ip_address = i.ip_address WHERE m.id_maintenance = $id"));

if (!$data) {
    header("Location: index.php");
    exit;
}

// Ambil data checklist yang sudah ada
$checklistData = [];
$qcl = mysqli_query($conn, "SELECT item, status FROM maintenance_checklist WHERE id_maintenance = $id");
while ($row = mysqli_fetch_assoc($qcl)) {
    $checklistData[$row['item']] = $row['status'];
}

$ips = mysqli_query($conn, "SELECT ip_address FROM inventaris ORDER BY ip_address ASC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit Maintenance | Portal Pelindo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        /* Checklist Style - sama dengan tambah.php */
        .checklist-item {
            cursor: pointer;
            transition: all 0.2s ease-in-out;
            border: 1px solid #dee2e6;
            border-radius: 10px;
            padding: 14px 18px;
            background: #fff;
            display: flex;
            align-items: center;
            justify-content: space-between;
            user-select: none;
        }
        .checklist-item:hover {
            border-color: var(--pelindo-primary);
            background-color: #f8fafc;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }
        .checklist-item.checked {
            border-color: #198754;
            background-color: #f4fbf7;
            box-shadow: 0 2px 8px rgba(25, 135, 84, 0.08);
        }
        .checklist-icon {
            font-size: 1.35rem;
            color: #cbd5e1;
            transition: color 0.2s ease-in-out;
        }
        .checklist-item.checked .checklist-icon {
            color: #198754;
        }
        .checklist-label {
            font-weight: 500;
            color: #334155;
            transition: color 0.2s ease-in-out;
        }
        .checklist-item.checked .checklist-label {
            color: #198754;
        }
    </style>
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
            <div class="sidebar-user-avatar"><i class="bi bi-person"></i></div>
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
                <a href="index.php" class="text-decoration-none text-muted">Maintenance</a> / Edit Laporan
            </div>
        </div>

        <div class="content-body">
            <div class="modern-card">
                <div class="modern-card-header text-warning">
                    <h5 class="modern-card-title"><i class="bi bi-pencil-square me-2"></i>Edit Data Maintenance</h5>
                </div>
                <div class="modern-card-body">
                    <form method="POST" id="editForm">
                        <input type="hidden" name="update" value="1">
                        <div class="row g-3">

                            <div class="col-md-6">
                                <label class="form-label">IP Address</label>
                                <select name="ip_address" class="form-select" required>
                                    <option value="">-- Pilih IP Address --</option>
                                    <?php while($row = mysqli_fetch_assoc($ips)): ?>
                                        <option value="<?= htmlspecialchars($row['ip_address']) ?>"
                                            <?= $row['ip_address'] === $data['ip_address'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($row['ip_address']) ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Pelaksana Perawatan</label>
                                <input type="text" name="pelaksana_perawatan" class="form-control"
                                    value="<?= htmlspecialchars($data['pelaksana_perawatan']) ?>" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Teknisi Lapangan</label>
                                <input type="text" name="teknisi" class="form-control"
                                    value="<?= htmlspecialchars($data['teknisi']) ?>" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Tipe Perawatan</label>
                                <input type="text" name="tipe_perawatan" class="form-control"
                                    value="<?= htmlspecialchars($data['tipe_perawatan']) ?>" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Periode Perawatan</label>
                                <input type="text" name="periode_perawatan" class="form-control"
                                    value="<?= htmlspecialchars($data['periode_perawatan']) ?>" required>
                            </div>

                            <div class="col-md-12">
                                <label class="form-label">Deskripsi Permasalahan</label>
                                <textarea name="permasalahan" class="form-control" rows="3" required><?= htmlspecialchars($data['permasalahan']) ?></textarea>
                            </div>

                            <div class="col-md-12">
                                <label class="form-label">Tindakan / Aksi yang Dilakukan</label>
                                <textarea name="aksi" class="form-control" rows="3" required><?= htmlspecialchars($data['aksi']) ?></textarea>
                            </div>

                            <div class="col-md-12">
                                <label class="form-label">Keterangan Tambahan (Opsional)</label>
                                <textarea name="keterangan" class="form-control" rows="2"><?= htmlspecialchars($data['keterangan']) ?></textarea>
                            </div>

                            <!-- Checklist Perangkat -->
                            <div class="col-12 mt-4">
                                <label class="form-label fw-bold text-primary mb-3"><i class="bi bi-list-check me-1"></i> Checklist Perangkat (Klik untuk mencentang jika kondisi OK)</label>
                                <div class="row g-3">
                                    <?php
                                    $items = ['Motherboard','Processor','Hard Disk','Power Supply','RAM','Keyboard','Mouse','Monitor','Wifi LAN Card','CD ROM'];
                                    foreach ($items as $item) {
                                        $key = 'status_' . str_replace([' ', '/'], '_', strtolower($item));
                                        $currentStatus = $checklistData[$item] ?? 'Perlu Perbaikan';
                                        $isOK = ($currentStatus === 'OK');
                                    ?>
                                        <div class="col-md-4 col-sm-6">
                                            <div class="checklist-item <?= $isOK ? 'checked' : '' ?>" onclick="toggleChecklistItem(this, '<?= $key ?>')">
                                                <span class="checklist-label"><?= htmlspecialchars($item) ?></span>
                                                <span class="checklist-icon">
                                                    <i class="bi <?= $isOK ? 'bi-check-circle-fill' : 'bi-circle' ?>"></i>
                                                </span>
                                                <input type="hidden" name="<?= $key ?>" id="<?= $key ?>" value="<?= $isOK ? 'OK' : 'Perlu Perbaikan' ?>">
                                            </div>
                                        </div>
                                    <?php } ?>
                                </div>
                            </div>

                        </div>

                        <div class="mt-4 d-flex gap-2 border-top pt-4">
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
        text: 'Apakah yakin mengupdate data maintenance ini?',
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

function toggleChecklistItem(element, inputId) {
    const input = document.getElementById(inputId);
    const icon  = element.querySelector('.checklist-icon i');
    if (input.value === 'Perlu Perbaikan') {
        input.value = 'OK';
        element.classList.add('checked');
        icon.className = 'bi bi-check-circle-fill';
    } else {
        input.value = 'Perlu Perbaikan';
        element.classList.remove('checked');
        icon.className = 'bi bi-circle';
    }
}
</script>
</body>
</html>
