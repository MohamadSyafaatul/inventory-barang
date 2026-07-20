<?php
session_start();

if (!isset($_SESSION['login'])) {
    header("Location: ../assets/login.php");
    exit;
}

include "../config/koneksi.php";

// Ambil daftar IP address inventaris untuk dropdown
$ips = mysqli_query($conn, "SELECT ip_address FROM inventaris ORDER BY ip_address ASC");
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tambah Maintenance | Portal Pelindo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        /* Checklist Style */
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
        .required-badge {
            color: #ef4444;
            font-size: 0.85rem;
            margin-left: 2px;
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
                <a href="index.php" class="text-decoration-none text-muted">Maintenance</a> / Tambah Laporan
            </div>
        </div>

        <div class="content-body">
            <div class="modern-card">
                <div class="modern-card-header">
                    <h5 class="modern-card-title"><i class="bi bi-file-earmark-plus me-2"></i>Tambah Laporan Perawatan</h5>
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

                    <form method="POST" action="tambah_checklist.php" id="maintenanceForm" novalidate>
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label">IP Address <span class="required-badge">*</span></label>
                                <select name="ip_address" id="ip_address" class="form-select">
                                    <option value="">-- Pilih IP Address Perangkat --</option>
                                    <?php while($d = mysqli_fetch_assoc($ips)) { ?>
                                        <option value="<?= htmlspecialchars($d['ip_address']) ?>"><?= htmlspecialchars($d['ip_address']) ?></option>
                                    <?php } ?>
                                </select>
                                <div class="form-text text-muted" style="font-size: 0.82rem;">IP akan meload info referensi di bawah secara otomatis.</div>
                            </div>

                            <div class="col-md-6">
                                <div class="alert alert-info d-flex align-items-center gap-3 py-3 mb-0 h-100" role="alert" style="border-radius: 8px;">
                                    <i class="bi bi-clock-fill fs-4 text-primary"></i>
                                    <div>
                                        <strong class="d-block mb-0.5">Pencatatan Start Time Otomatis</strong>
                                        <span class="text-muted" style="font-size: 0.85rem;">Sistem mencatat waktu server secara realtime saat menekan tombol Simpan.</span>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Pelaksana Perawatan <span class="required-badge">*</span></label>
                                <input type="text" name="pelaksana_perawatan" id="pelaksana_perawatan" class="form-control" placeholder="Contoh: Admin IT, Bidang Teknik">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Teknisi Lapangan <span class="required-badge">*</span></label>
                                <input type="text" name="teknisi" id="teknisi" class="form-control" placeholder="Nama teknisi penanggung jawab">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Tipe Perawatan <span class="required-badge">*</span></label>
                                <input type="text" name="tipe_perawatan" id="tipe_perawatan" class="form-control" placeholder="Contoh: Preventive, Corrective">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Periode Perawatan <span class="required-badge">*</span></label>
                                <input type="text" name="periode_perawatan" id="periode_perawatan" class="form-control" placeholder="Contoh: Bulanan, 3 Bulan, 6 Bulan, Tahunan">
                            </div>

                            <!-- Data referensi (ditampilkan, tidak disimpan) -->
                            <div class="col-md-4">
                                <label class="form-label">Jenis Barang (Referensi)</label>
                                <input type="text" id="jenis_barang" class="form-control bg-light" readonly placeholder="-">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Sub Jenis Barang (Referensi)</label>
                                <input type="text" id="sub_jenis_barang" class="form-control bg-light" readonly placeholder="-">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Serial Number (Referensi)</label>
                                <input type="text" id="serial_number" class="form-control bg-light" readonly placeholder="-">
                            </div>

                            <!-- Data maintenance (disimpan) -->
                            <div class="col-md-12">
                                <label class="form-label">Deskripsi Permasalahan <span class="required-badge">*</span></label>
                                <textarea name="permasalahan" id="permasalahan" class="form-control" rows="3" placeholder="Jelaskan kendala atau status perangkat saat ini..."></textarea>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Tindakan / Aksi yang Dilakukan <span class="required-badge">*</span></label>
                                <textarea name="aksi" id="aksi" class="form-control" rows="3" placeholder="Langkah penanganan yang diambil..."></textarea>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Keterangan Tambahan <span class="optional-badge">Opsional</span></label>
                                <textarea name="keterangan" class="form-control" rows="2" placeholder="Catatan tambahan bila ada..."></textarea>
                            </div>

                            <!-- Checklist maintenance (Dipindah ke bawah deskripsi dengan gaya interaktif lingkaran/centang) -->
                            <div class="col-12 mt-4">
                                <label class="form-label fw-bold text-primary mb-3"><i class="bi bi-list-check me-1"></i> Checklist Perangkat (Klik untuk mencentang jika kondisi OK)</label>
                                <div class="row g-3">
                                    <?php
                                    $items = ['Motherboard','Processor','Hard Disk','Power Supply','RAM','Keyboard','Mouse','Monitor','Wifi LAN Card','CD ROM'];#'Cleaning'
                                    foreach ($items as $item) {
                                        $key = 'status_' . str_replace([' ', '/'], '_', strtolower($item));
                                    ?>
                                        <div class="col-md-4 col-sm-6">
                                            <div class="checklist-item" onclick="toggleChecklistItem(this, '<?= $key ?>')">
                                                <span class="checklist-label"><?= htmlspecialchars($item) ?></span>
                                                <span class="checklist-icon">
                                                    <i class="bi bi-circle"></i>
                                                </span>
                                                <!-- Hidden input to submit the value -->
                                                <input type="hidden" name="<?= $key ?>" id="<?= $key ?>" value="Perlu Perbaikan">
                                            </div>
                                        </div>
                                    <?php } ?>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 d-flex gap-2 border-top pt-4">
                            <button type="button" onclick="validateMaintenanceForm()" class="btn btn-modern-primary py-2.5 px-4">
                                <i class="bi bi-floppy me-1.5"></i> Simpan Laporan
                            </button>
                            <a href="index.php" class="btn btn-modern-light py-2.5 px-4">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const ipSelect = document.getElementById('ip_address');
    const jenisBarang = document.getElementById('jenis_barang');
    const subJenisBarang = document.getElementById('sub_jenis_barang');
    const serialNumber = document.getElementById('serial_number');

    ipSelect.addEventListener('change', async () => {
        const ip = ipSelect.value;
        if (!ip) {
            jenisBarang.value = '';
            subJenisBarang.value = '';
            serialNumber.value = '';
            return;
        }

        try {
            const res = await fetch('get_inventaris.php?ip_address=' + encodeURIComponent(ip));
            if (!res.ok) throw new Error('Request failed');
            const data = await res.json();

            jenisBarang.value = data.jenis_barang || '';
            subJenisBarang.value = data.sub_jenis_barang || '';
            serialNumber.value = data.serial_number || '';
        } catch (e) {
            alert('Gagal mengambil data inventaris untuk IP tersebut.');
        }
    });

    // Toggle checklist item status between 'OK' and 'Perlu Perbaikan'
    function toggleChecklistItem(element, inputId) {
        const input = document.getElementById(inputId);
        const icon = element.querySelector('.checklist-icon i');
        
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

    // Konfigurasi field wajib maintenance
    const requiredFieldsMaintenance = [
        { id: 'ip_address',          label: 'IP Address Perangkat',       type: 'select' },
        { id: 'pelaksana_perawatan', label: 'Pelaksana Perawatan',         type: 'input' },
        { id: 'teknisi',             label: 'Teknisi Lapangan',            type: 'input' },
        { id: 'tipe_perawatan',      label: 'Tipe Perawatan',              type: 'input' },
        { id: 'periode_perawatan',   label: 'Periode Perawatan',           type: 'input' },
        { id: 'permasalahan',        label: 'Deskripsi Permasalahan',      type: 'textarea' },
        { id: 'aksi',                label: 'Tindakan / Aksi yang Dilakukan', type: 'textarea' },
    ];

    function validateMaintenanceForm() {
        const errors = [];

        // Reset highlight error
        requiredFieldsMaintenance.forEach(f => {
            document.getElementById(f.id).classList.remove('field-error');
        });

        // Cek setiap field wajib
        requiredFieldsMaintenance.forEach(f => {
            const el = document.getElementById(f.id);
            if (!el.value.trim()) {
                errors.push(f.label);
                el.classList.add('field-error');
            }
        });

        if (errors.length > 0) {
            const ul = document.getElementById('validationErrors');
            ul.innerHTML = '';
            errors.forEach(label => {
                ul.innerHTML += `<li><i class="bi bi-x-circle-fill me-2"></i>${label}</li>`;
            });
            const modal = new bootstrap.Modal(document.getElementById('validationModal'));
            modal.show();

            // Scroll ke field pertama yang error
            const firstErrorId = requiredFieldsMaintenance.find(f => errors.includes(f.label)).id;
            document.getElementById(firstErrorId).scrollIntoView({ behavior: 'smooth', block: 'center' });
        } else {
            document.getElementById('maintenanceForm').submit();
        }
    }

    // Hapus highlight error saat user mulai mengisi
    requiredFieldsMaintenance.forEach(f => {
        const el = document.getElementById(f.id);
        const eventType = f.type === 'select' ? 'change' : 'input';
        el.addEventListener(eventType, function() {
            this.classList.remove('field-error');
        });
    });
</script>

</body>
</html>
