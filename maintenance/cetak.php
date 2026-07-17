<?php
session_start();

if (!isset($_SESSION['login'])) {
    header("Location: ../assets/login.php");
    exit;
}

include "../config/koneksi.php";
include "../config/helper.php";

// ===== FILTER: IDs terpilih atau semua =====
$selectedIds = [];
if (!empty($_GET['ids'])) {
    // Sanitasi: pastikan hanya angka
    foreach ((array)$_GET['ids'] as $rawId) {
        $clean = intval($rawId);
        if ($clean > 0) $selectedIds[] = $clean;
    }
}

$where = "WHERE 1=1";

if (!empty($selectedIds)) {
    $inList = implode(',', $selectedIds);
    $where .= " AND m.id_maintenance IN ($inList)";
}

// Filter tambahan opsional
$status  = isset($_GET['status'])  ? $_GET['status']  : '';
$periode = isset($_GET['periode']) ? $_GET['periode']  : '';
if ($status  !== '') $where .= " AND m.status_maintenance = '" . mysqli_real_escape_string($conn, $status) . "'";
if ($periode !== '') $where .= " AND m.periode_perawatan = '"  . mysqli_real_escape_string($conn, $periode) . "'";

$isCetakSemua = empty($selectedIds);

$data = mysqli_query($conn, "
    SELECT m.*, i.user, i.jenis_barang, i.sub_jenis_barang, i.serial_number
    FROM maintenance m
    JOIN inventaris i ON m.ip_address = i.ip_address
    $where
    ORDER BY m.start_time DESC
");

$total = mysqli_num_rows($data);

// Hitung statistik sesuai filter
$qStats = mysqli_query($conn, "
    SELECT
        COUNT(*) AS total,
        SUM(status_maintenance = 'Selesai') AS selesai,
        SUM(status_maintenance = 'Berlangsung') AS berlangsung
    FROM maintenance m
    JOIN inventaris i ON m.ip_address = i.ip_address
    $where
");
$stats = mysqli_fetch_assoc($qStats);

$rows = [];
while ($d = mysqli_fetch_assoc($data)) {
    $qc = mysqli_query($conn, "SELECT * FROM maintenance_checklist WHERE id_maintenance = '{$d['id_maintenance']}' ORDER BY id_checklist ASC");
    $d['checklist'] = [];
    while ($cl = mysqli_fetch_assoc($qc)) {
        $d['checklist'][] = $cl;
    }
    $rows[] = $d;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Laporan Maintenance - PT Pelindo Multi Terminal</title>
    <style>
        /* ===== BASE RESET ===== */
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            font-size: 11pt;
            color: #1a1a1a;
            background: #f0f4f8;
        }

        /* ===== SCREEN-ONLY CONTROLS ===== */
        .screen-controls {
            position: fixed;
            top: 0; left: 0; right: 0;
            background: #0A2E50;
            color: white;
            padding: 12px 30px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            z-index: 999;
            box-shadow: 0 2px 12px rgba(0,0,0,0.2);
        }
        .screen-controls .ctrl-title {
            font-size: 1rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .screen-controls .ctrl-title span.badge-doc {
            background: rgba(255,255,255,0.15);
            font-size: 0.75rem;
            padding: 3px 10px;
            border-radius: 20px;
            font-weight: 500;
        }
        .ctrl-buttons { display: flex; gap: 10px; }
        .ctrl-btn {
            border: none;
            border-radius: 6px;
            padding: 8px 18px;
            font-size: 0.88rem;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 6px;
            text-decoration: none;
            transition: all 0.2s;
        }
        .ctrl-btn-print { background: #22c55e; color: white; }
        .ctrl-btn-print:hover { background: #16a34a; }
        .ctrl-btn-back  { background: rgba(255,255,255,0.15); color: white; border: 1px solid rgba(255,255,255,0.3); }
        .ctrl-btn-back:hover { background: rgba(255,255,255,0.25); }

        /* ===== PRINT PAGE WRAPPER ===== */
        .print-page {
            max-width: 900px;
            margin: 80px auto 40px;
            background: white;
            box-shadow: 0 8px 40px rgba(0,0,0,0.12);
            border-radius: 4px;
            overflow: hidden;
        }

        /* ===== DOCUMENT HEADER ===== */
        .doc-header {
            background: linear-gradient(135deg, #0A2E50 0%, #005CB9 100%);
            color: white;
            padding: 32px 40px 28px;
            display: flex;
            align-items: center;
            gap: 24px;
        }
        .doc-header-logo {
            display: flex;
            align-items: center;
            flex-shrink: 0;
            background: transparent;
            padding: 0;
        }
        .doc-header-logo img {
            height: 52px;
            display: block;
            /* mix-blend-mode multiply: putih di PNG melebur ke biru header → tampak transparan */
            mix-blend-mode: multiply;
        }
        .doc-header-text h1 {
            font-size: 1.35rem;
            font-weight: 700;
            letter-spacing: 0.02em;
            margin-bottom: 4px;
        }
        .doc-header-text p {
            font-size: 0.88rem;
            opacity: 0.85;
            margin: 0;
        }
        .doc-header-right {
            margin-left: auto;
            text-align: right;
            flex-shrink: 0;
        }
        .doc-header-right .doc-number {
            font-size: 0.78rem;
            opacity: 0.75;
            margin-bottom: 4px;
        }
        .doc-header-right .doc-date {
            font-size: 0.9rem;
            font-weight: 600;
        }

        /* ===== DIVIDER ===== */
        .doc-divider {
            height: 5px;
            background: linear-gradient(90deg, #f59e0b, #ef4444, #005CB9);
        }

        /* ===== META INFO BAR ===== */
        .doc-meta {
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            padding: 18px 40px;
            display: flex;
            gap: 30px;
            align-items: center;
            flex-wrap: wrap;
        }
        .meta-item { display: flex; flex-direction: column; gap: 2px; }
        .meta-label { font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.05em; color: #64748b; font-weight: 600; }
        .meta-value { font-size: 0.92rem; font-weight: 700; color: #0A2E50; }

        /* ===== SUMMARY BOXES ===== */
        .doc-summary {
            display: flex;
            gap: 0;
            border-bottom: 1px solid #e2e8f0;
        }
        .summary-box {
            flex: 1;
            padding: 20px 24px;
            text-align: center;
            border-right: 1px solid #e2e8f0;
        }
        .summary-box:last-child { border-right: none; }
        .summary-box .s-num {
            font-size: 2rem;
            font-weight: 800;
            line-height: 1;
            margin-bottom: 4px;
        }
        .summary-box .s-lbl {
            font-size: 0.78rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #64748b;
            font-weight: 600;
        }
        .s-blue  { color: #005CB9; }
        .s-green { color: #16a34a; }
        .s-amber { color: #d97706; }

        /* ===== SECTION HEADING ===== */
        .section-heading {
            padding: 18px 40px 10px;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            font-weight: 700;
            color: #005CB9;
            border-bottom: 2px solid #e2e8f0;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* ===== MAINTENANCE CARD ===== */
        .mcard {
            margin: 20px 40px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            overflow: hidden;
            page-break-inside: avoid;
        }
        .mcard-header {
            background: linear-gradient(90deg, #0A2E50 0%, #005CB9 100%);
            color: white;
            padding: 12px 18px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .mcard-header .mcard-title {
            font-size: 0.95rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .mcard-header .mcard-ip {
            font-size: 0.78rem;
            opacity: 0.8;
        }
        .mcard-status {
            font-size: 0.75rem;
            font-weight: 700;
            padding: 4px 12px;
            border-radius: 20px;
        }
        .status-selesai    { background: #d1fae5; color: #065f46; }
        .status-berlangsung { background: #fef3c7; color: #92400e; }

        .mcard-body { padding: 16px 18px; }

        /* INFO GRID */
        .info-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px 16px;
            margin-bottom: 14px;
        }
        .info-item {}
        .info-item .i-label {
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #64748b;
            font-weight: 700;
            margin-bottom: 2px;
        }
        .info-item .i-value {
            font-size: 0.88rem;
            color: #1e293b;
            font-weight: 600;
        }
        .info-item.full { grid-column: 1 / -1; }

        /* TIMELINE */
        .timeline-row {
            display: flex;
            gap: 12px;
            margin-bottom: 14px;
        }
        .tl-box {
            flex: 1;
            border-radius: 6px;
            padding: 10px 14px;
            border: 1px solid;
        }
        .tl-box.tl-start  { background: #eff6ff; border-color: #bfdbfe; }
        .tl-box.tl-finish { background: #f0fdf4; border-color: #bbf7d0; }
        .tl-box.tl-duration { background: #fefce8; border-color: #fde68a; }
        .tl-box .tl-label { font-size: 0.68rem; text-transform: uppercase; letter-spacing: 0.05em; color: #64748b; font-weight: 700; margin-bottom: 2px; }
        .tl-box .tl-val   { font-size: 0.88rem; font-weight: 700; color: #1e293b; }

        /* CHECKLIST TABLE */
        .cl-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            font-size: 0.83rem;
        }
        .cl-table th {
            background: #f1f5f9;
            color: #475569;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 0.68rem;
            letter-spacing: 0.04em;
            padding: 8px 12px;
            text-align: left;
            border-bottom: 2px solid #e2e8f0;
        }
        .cl-table td {
            padding: 7px 12px;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
            color: #334155;
        }
        .cl-table tr:last-child td { border-bottom: none; }
        .cl-table .no-col { width: 36px; text-align: center; color: #94a3b8; }
        .cl-status {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-weight: 700;
            font-size: 0.78rem;
            padding: 3px 10px;
            border-radius: 20px;
        }
        .cl-ok     { background: #d1fae5; color: #065f46; }
        .cl-perlu  { background: #fef3c7; color: #92400e; }
        .cl-ganti  { background: #fee2e2; color: #991b1b; }

        .desc-section { margin-top: 12px; }
        .desc-label { font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.05em; color: #64748b; font-weight: 700; margin-bottom: 4px; }
        .desc-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 10px 12px;
            font-size: 0.85rem;
            color: #334155;
            white-space: pre-wrap;
            line-height: 1.6;
        }

        /* ===== DOCUMENT FOOTER ===== */
        .doc-footer {
            background: #0A2E50;
            color: rgba(255,255,255,0.7);
            padding: 20px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.78rem;
            margin-top: 20px;
        }
        .doc-footer strong { color: white; }

        /* ===== EMPTY STATE ===== */
        .empty-state {
            padding: 60px 40px;
            text-align: center;
            color: #94a3b8;
        }
        .empty-state svg { width: 56px; height: 56px; margin-bottom: 12px; opacity: 0.4; }

        /* ===== PRINT-ONLY MINI HEADER INSIDE EACH CARD ===== */
        .print-card-header {
            display: none; /* hidden on screen */
        }
        .print-card-footer {
            display: none; /* hidden on screen */
        }

        /* ===== PRINT STYLES ===== */
        @media print {
            @page {
                size: A4 portrait;
                margin: 8mm 10mm 8mm 10mm;
            }

            * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }

            body {
                background: white !important;
                font-size: 9pt;
                margin: 0;
                padding: 0;
            }

            /* Sembunyikan semua kontrol layar */
            .screen-controls { display: none !important; }

            /* Sembunyikan bagian header utama dokumen saat print */
            /* (tiap card punya mini-header sendiri) */
            .doc-header,
            .doc-divider,
            .doc-meta,
            .doc-summary,
            .section-heading,
            .empty-state,
            .doc-footer {
                display: none !important;
            }

            /* Wrapper print-page: bersih */
            .print-page {
                margin: 0 !important;
                padding: 0 !important;
                box-shadow: none !important;
                border-radius: 0 !important;
                max-width: 100% !important;
                overflow: visible !important;
                background: white !important;
            }

            /* ===== SETIAP MCARD = TEPAT 1 HALAMAN A4 ===== */
            .mcard {
                page-break-before: always;
                page-break-inside: avoid;
                page-break-after: always;
                margin: 0 !important;
                border: none !important;
                border-radius: 0 !important;
                box-shadow: none !important;
                display: flex;
                flex-direction: column;
                min-height: 0;
            }

            /* Tampilkan mini-header dan mini-footer di dalam card */
            .print-card-header { display: flex !important; }
            .print-card-footer { display: flex !important; }

            /* Perkecil elemen card agar muat 1 halaman */
            .mcard-header       { padding: 8px 14px; }
            .mcard-body         { padding: 8px 14px; }
            .info-grid          { gap: 6px 10px; margin-bottom: 8px; grid-template-columns: repeat(3,1fr); }
            .info-item .i-label { font-size: 0.62rem; }
            .info-item .i-value { font-size: 0.8rem; }
            .timeline-row       { gap: 6px; margin-bottom: 8px; }
            .tl-box             { padding: 6px 9px; }
            .tl-box .tl-label   { font-size: 0.6rem; }
            .tl-box .tl-val     { font-size: 0.8rem; }
            .desc-section       { margin-bottom: 8px; }
            .desc-box           { padding: 6px 9px; font-size: 0.78rem; line-height: 1.4; }
            .desc-label         { font-size: 0.62rem; margin-bottom: 2px; }
            .cl-table           { font-size: 0.76rem; margin-top: 6px; }
            .cl-table th        { padding: 4px 9px; }
            .cl-table td        { padding: 4px 9px; }
        }
    </style>
</head>
<body>

<!-- ===== SCREEN CONTROLS BAR ===== -->
<div class="screen-controls">
    <div class="ctrl-title">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
            <path d="M2.5 8a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1z"/>
            <path d="M5 1a2 2 0 0 0-2 2v2H2a2 2 0 0 0-2 2v3a2 2 0 0 0 2 2h1v1a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2v-1h1a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-1V3a2 2 0 0 0-2-2H5zM4 3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2H4V3zm1 5a2 2 0 0 0-2 2v1H2a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h12a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1h-1v-1a2 2 0 0 0-2-2H5zm7 2v3a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1v-3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1z"/>
        </svg>
        Pratinjau Dokumen Cetak
        <?php if ($isCetakSemua): ?>
        <span class="badge-doc">Semua Laporan (<?= $total ?>)</span>
        <?php else: ?>
        <span class="badge-doc" style="background:rgba(245,158,11,0.25); color:#fbbf24;"><?= $total ?> Laporan Terpilih</span>
        <?php endif; ?>
    </div>
    <div class="ctrl-buttons">
        <a href="index.php" class="ctrl-btn ctrl-btn-back">
            ← Kembali
        </a>
        <button onclick="window.print()" class="ctrl-btn ctrl-btn-print">
            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="currentColor" viewBox="0 0 16 16">
                <path d="M2.5 8a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1z"/>
                <path d="M5 1a2 2 0 0 0-2 2v2H2a2 2 0 0 0-2 2v3a2 2 0 0 0 2 2h1v1a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2v-1h1a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-1V3a2 2 0 0 0-2-2H5zM4 3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2H4V3zm1 5a2 2 0 0 0-2 2v1H2a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h12a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1h-1v-1a2 2 0 0 0-2-2H5zm7 2v3a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1v-3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1z"/>
            </svg>
            Cetak / Simpan PDF
        </button>
    </div>
</div>

<!-- ===== PRINT PAGE ===== -->
<div class="print-page">

    <!-- HEADER -->
    <div class="doc-header">
        <div class="doc-header-logo">
            <img src="../assets/logo-pelindo.png" alt="Logo Pelindo">
        </div>
        <div class="doc-header-text">
            <h1>Laporan Rekap Maintenance</h1>
            <p>PT Pelindo Multi Terminal</p>
            <p style="margin-top:4px; opacity:0.7; font-size:0.8rem;">
                Dokumen ini dicetak secara resmi melalui Sistem Inventaris Portal Pelindo
            </p>
        </div>
        <div class="doc-header-right">
            <div class="doc-number">Tanggal Cetak</div>
            <div class="doc-date"><?= date('d F Y, H:i') ?> WIB</div>
        </div>
    </div>

    <!-- DIVIDER -->
    <div class="doc-divider"></div>

    <!-- META INFO -->
    <div class="doc-meta">
        <div class="meta-item">
            <span class="meta-label">Dicetak Oleh</span>
            <span class="meta-value">Administrator</span>
        </div>
        <div class="meta-item">
            <span class="meta-label">Unit</span>
            <span class="meta-value">IT / Teknologi Informasi</span>
        </div>
        <div class="meta-item">
            <span class="meta-label">Jenis Cetak</span>
            <span class="meta-value"><?= $isCetakSemua ? 'Semua Laporan' : 'Laporan Terpilih' ?></span>
        </div>
        <div class="meta-item">
            <span class="meta-label">Total Dicetak</span>
            <span class="meta-value"><?= $total ?> Laporan</span>
        </div>
    </div>

    <!-- SUMMARY -->
    <div class="doc-summary">
        <div class="summary-box">
            <div class="s-num s-blue"><?= $stats['total'] ?></div>
            <div class="s-lbl">Total Laporan</div>
        </div>
        <div class="summary-box">
            <div class="s-num s-green"><?= $stats['selesai'] ?></div>
            <div class="s-lbl">Selesai</div>
        </div>
        <div class="summary-box">
            <div class="s-num s-amber"><?= $stats['berlangsung'] ?></div>
            <div class="s-lbl">Berlangsung</div>
        </div>
    </div>

    <!-- SECTION TITLE -->
    <div class="section-heading">
        &#9632; Detail Laporan Perawatan Perangkat
    </div>

    <!-- MAINTENANCE CARDS -->
    <?php if (count($rows) === 0): ?>
    <div class="empty-state">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
        <p>Tidak ada data laporan maintenance untuk dicetak.</p>
    </div>
    <?php endif; ?>

    <?php foreach ($rows as $i => $d):
        $isBerlangsung = ($d['status_maintenance'] === 'Berlangsung');
        $durasi = hitungDurasiTeks($d['start_time'], $d['finish_time']);
    ?>
    <div class="mcard">
        <!-- ===== PRINT-ONLY MINI COMPANY HEADER ===== -->
        <div class="print-card-header" style="
            background: linear-gradient(135deg, #0A2E50 0%, #005CB9 100%);
            color: white;
            padding: 10px 16px;
            align-items: center;
            gap: 14px;
            justify-content: space-between;
        ">
            <div style="display:flex; align-items:center; gap:12px;">
                <!-- Logo langsung tanpa kotak putih, diubah jadi putih murni -->
                <img src="../assets/logo-pelindo.png" alt="Logo"
                    style="height:36px; display:block; mix-blend-mode:multiply; flex-shrink:0;">
                <div>
                    <div style="font-size:0.95rem; font-weight:700; letter-spacing:0.01em;">Laporan Maintenance Perangkat</div>
                    <div style="font-size:0.75rem; opacity:0.8;">PT Pelindo Multi Terminal</div>
                </div>
            </div>
            <div style="text-align:right; flex-shrink:0;">
                <div style="font-size:0.68rem; opacity:0.7;">Dicetak</div>
                <div style="font-size:0.82rem; font-weight:600;"><?= date('d/m/Y H:i') ?> WIB</div>
            </div>
        </div>

        <!-- Card Header (judul laporan) -->
        <div class="mcard-header">
            <div class="mcard-title">
                &#9660; Laporan #<?= str_pad($d['id_maintenance'], 4, '0', STR_PAD_LEFT) ?>
                &nbsp;&mdash;&nbsp;
                <span style="font-weight:400;"><?= htmlspecialchars($d['ip_address']) ?></span>
            </div>
            <span class="mcard-status <?= $isBerlangsung ? 'status-berlangsung' : 'status-selesai' ?>">
                <?= htmlspecialchars($d['status_maintenance']) ?>
            </span>
        </div>

        <div class="mcard-body">
            <!-- Info Grid -->
            <div class="info-grid">
                <div class="info-item">
                    <div class="i-label">IP Address</div>
                    <div class="i-value"><?= htmlspecialchars($d['ip_address']) ?></div>
                </div>
                <div class="info-item">
                    <div class="i-label">User / Pemilik</div>
                    <div class="i-value"><?= htmlspecialchars($d['user']) ?></div>
                </div>
                <div class="info-item">
                    <div class="i-label">Jenis Barang</div>
                    <div class="i-value"><?= htmlspecialchars($d['jenis_barang']) ?> — <?= htmlspecialchars($d['sub_jenis_barang']) ?></div>
                </div>
                <div class="info-item">
                    <div class="i-label">Serial Number</div>
                    <div class="i-value"><?= htmlspecialchars($d['serial_number']) ?></div>
                </div>
                <div class="info-item">
                    <div class="i-label">Pelaksana</div>
                    <div class="i-value"><?= htmlspecialchars($d['pelaksana_perawatan']) ?></div>
                </div>
                <div class="info-item">
                    <div class="i-label">Teknisi</div>
                    <div class="i-value"><?= htmlspecialchars($d['teknisi']) ?></div>
                </div>
                <div class="info-item">
                    <div class="i-label">Tipe Perawatan</div>
                    <div class="i-value"><?= htmlspecialchars($d['tipe_perawatan']) ?></div>
                </div>
                <div class="info-item">
                    <div class="i-label">Periode</div>
                    <div class="i-value"><?= htmlspecialchars($d['periode_perawatan']) ?></div>
                </div>
            </div>

            <!-- Timeline -->
            <div class="timeline-row">
                <div class="tl-box tl-start">
                    <div class="tl-label">&#9654; Waktu Mulai</div>
                    <div class="tl-val"><?= formatWaktuIndo($d['start_time']) ?></div>
                </div>
                <div class="tl-box tl-finish">
                    <div class="tl-label">&#9646; Waktu Selesai</div>
                    <div class="tl-val">
                        <?= $isBerlangsung
                            ? '<span style="color:#d97706;font-style:italic;font-weight:500;">Masih Berlangsung</span>'
                            : formatWaktuIndo($d['finish_time']) ?>
                    </div>
                </div>
                <div class="tl-box tl-duration">
                    <div class="tl-label">&#8987; Durasi Pengerjaan</div>
                    <div class="tl-val"><?= $isBerlangsung ? '—' : htmlspecialchars($durasi) ?></div>
                </div>
            </div>

            <!-- Deskripsi -->
            <?php if (!empty($d['permasalahan']) || !empty($d['aksi'])): ?>
            <div class="desc-section" style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-bottom:12px;">
                <?php if (!empty($d['permasalahan'])): ?>
                <div>
                    <div class="desc-label">Deskripsi Masalah</div>
                    <div class="desc-box"><?= htmlspecialchars($d['permasalahan']) ?></div>
                </div>
                <?php endif; ?>
                <?php if (!empty($d['aksi'])): ?>
                <div>
                    <div class="desc-label">Aksi / Tindakan</div>
                    <div class="desc-box"><?= htmlspecialchars($d['aksi']) ?></div>
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <?php if (!empty($d['keterangan'])): ?>
            <div class="desc-section" style="margin-bottom:12px;">
                <div class="desc-label">Keterangan Tambahan</div>
                <div class="desc-box"><?= htmlspecialchars($d['keterangan']) ?></div>
            </div>
            <?php endif; ?>

            <!-- Checklist -->
            <?php if (!empty($d['checklist'])): ?>
            <div class="desc-label" style="margin-top:12px;">Hasil Checklist Perangkat</div>
            <table class="cl-table">
                <thead>
                    <tr>
                        <th class="no-col">No</th>
                        <th>Item Checklist</th>
                        <th style="width:130px;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($d['checklist'] as $ci => $cl):
                        if ($cl['status'] === 'OK')               $clCls = 'cl-ok';
                        elseif ($cl['status'] === 'Perlu Perbaikan') $clCls = 'cl-perlu';
                        else                                       $clCls = 'cl-ganti';
                        $symbol = $cl['status'] === 'OK' ? '&#10003;' : ($cl['status'] === 'Perlu Perbaikan' ? '&#9888;' : '&#10007;');
                    ?>
                    <tr>
                        <td class="no-col"><?= $ci + 1 ?></td>
                        <td><?= htmlspecialchars($cl['item']) ?></td>
                        <td>
                            <span class="cl-status <?= $clCls ?>">
                                <?= $symbol ?> <?= htmlspecialchars($cl['status']) ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>

            <!-- Kolom Tanda Tangan -->
            <div style="margin-top: 40px; display: flex; justify-content: space-between; text-align: center; font-size: 0.85rem; page-break-inside: avoid;">
                <div style="width: 30%;">
                    <div style="margin-bottom: 60px;">Pengecek :</div>
                    <div style="font-weight: 600;">( .................................... )</div>
                </div>
                <div style="width: 30%;">
                    <div style="margin-bottom: 60px;">Teknisi :</div>
                    <div style="font-weight: 600;">( <?= htmlspecialchars($d['teknisi']) ?> )</div>
                </div>
                <div style="width: 30%;">
                    <div style="margin-bottom: 60px;">Pemiliki :</div>
                    <div style="font-weight: 600;">( <?= htmlspecialchars($d['user']) ?> )</div>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>

    <!-- FOOTER -->
    <div class="doc-footer">
        <div>
            <strong>PT Pelindo Multi Terminal</strong> &mdash; Sistem Manajemen Inventaris<br>
            Dokumen dicetak pada: <?= date('d F Y, H:i') ?> WIB
        </div>
        <div style="text-align:right;">
            Dicetak oleh: <strong>Administrator</strong><br>
            Portal Inventaris Pelindo &copy; <?= date('Y') ?>
        </div>
    </div>

</div>

</body>
</html>
