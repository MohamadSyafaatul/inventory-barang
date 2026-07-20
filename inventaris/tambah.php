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
    <style>
        /* Validation Modal */
        .modal-validation .modal-header {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: white;
            border-radius: 12px 12px 0 0;
            border-bottom: none;
        }
        .modal-validation .modal-header .btn-close {
            filter: brightness(0) invert(1);
        }
        .modal-validation .modal-content {
            border-radius: 12px;
            border: none;
            box-shadow: 0 20px 60px rgba(0,0,0,0.2);
        }
        .modal-validation .modal-body {
            padding: 1.5rem 1.75rem;
        }
        .modal-validation .modal-footer {
            border-top: 1px solid #f1f5f9;
            padding: 1rem 1.75rem;
            border-radius: 0 0 12px 12px;
        }
        .validation-list li {
            padding: 6px 0;
            color: #374151;
            font-size: 0.92rem;
        }
        .validation-list li i {
            color: #ef4444;
        }
        .field-error {
            border-color: #ef4444 !important;
            box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.15) !important;
        }
        .optional-badge {
            font-size: 0.75rem;
            background: #e0f2fe;
            color: #0284c7;
            padding: 2px 8px;
            border-radius: 20px;
            font-weight: 500;
            vertical-align: middle;
            margin-left: 6px;
        }
        .required-badge {
            color: #ef4444;
            font-size: 0.85rem;
            margin-left: 2px;
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
                    <!-- Modal Validasi -->
                    <div class="modal fade modal-validation" id="validationModal" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">
                                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                        Kolom Wajib Belum Diisi
                                    </h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <p class="text-muted mb-3" style="font-size:0.9rem;">Harap lengkapi kolom berikut sebelum menyimpan data:</p>
                                    <ul class="validation-list list-unstyled" id="validationErrors"></ul>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-modern-primary" data-bs-dismiss="modal">
                                        <i class="bi bi-pencil me-1"></i> Lengkapi Data
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <form method="POST" id="formInventaris" novalidate>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">IP Address <span class="required-badge">*</span></label>
                                <input type="text" name="ip_address" id="ip_address" class="form-control" placeholder="Contoh: 192.168.1.10">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">User / Pemilik <span class="required-badge">*</span></label>
                                <input type="text" name="user" id="user" class="form-control" placeholder="Nama penanggung jawab">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Jenis Barang <span class="required-badge">*</span></label>
                                <input type="text" name="jenis_barang" id="jenis_barang" class="form-control" placeholder="Contoh: Laptop, Printer, PC">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Sub Jenis Barang <span class="required-badge">*</span></label>
                                <input type="text" name="sub_jenis_barang" id="sub_jenis_barang" class="form-control" placeholder="Contoh: Thinkpad T480, Epson L3210">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Harga Per Unit (Rp) <span class="optional-badge">Opsional</span></label>
                                <input type="number" name="harga_per_unit" id="harga_per_unit" class="form-control" placeholder="Contoh: 12000000">
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Serial Number <span class="required-badge">*</span></label>
                                <input type="text" name="serial_number" id="serial_number" class="form-control" placeholder="Contoh: SN-12345ABC">
                            </div>
                        </div>
                        
                        <div class="mt-4 d-flex gap-2">
                            <button type="button" onclick="validateForm()" class="btn btn-modern-primary">
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
<script>
    // Konfigurasi field wajib: { id, label }
    const requiredFields = [
        { id: 'ip_address',      label: 'IP Address' },
        { id: 'user',            label: 'User / Pemilik' },
        { id: 'jenis_barang',    label: 'Jenis Barang' },
        { id: 'sub_jenis_barang',label: 'Sub Jenis Barang' },
        { id: 'serial_number',   label: 'Serial Number' },
    ];

    function validateForm() {
        const errors = [];

        // Reset semua highlight error
        requiredFields.forEach(f => {
            document.getElementById(f.id).classList.remove('field-error');
        });

        // Cek setiap field wajib
        requiredFields.forEach(f => {
            const el = document.getElementById(f.id);
            if (!el.value.trim()) {
                errors.push(f.label);
                el.classList.add('field-error');
            }
        });

        if (errors.length > 0) {
            // Tampilkan modal dengan daftar kolom kosong
            const ul = document.getElementById('validationErrors');
            ul.innerHTML = '';
            errors.forEach(label => {
                ul.innerHTML += `<li><i class="bi bi-x-circle-fill me-2"></i>${label}</li>`;
            });
            const modal = new bootstrap.Modal(document.getElementById('validationModal'));
            modal.show();

            // Scroll ke field pertama yang error
            const firstErrorId = requiredFields.find(f => errors.includes(f.label)).id;
            document.getElementById(firstErrorId).scrollIntoView({ behavior: 'smooth', block: 'center' });
        } else {
            // Semua valid, submit form
            document.getElementById('formInventaris').submit();
        }
    }

    // Hapus highlight error saat user mulai mengetik
    requiredFields.forEach(f => {
        document.getElementById(f.id).addEventListener('input', function() {
            this.classList.remove('field-error');
        });
    });
</script>
</body>
</html>
