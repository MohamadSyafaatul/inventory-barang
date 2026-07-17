<?php
// =====================================================
// HELPER: Format & Durasi Waktu Maintenance
// =====================================================

/**
 * Format datetime ke format bahasa Indonesia yang mudah dibaca.
 * Contoh output: "16 Juli 2026 09:35"
 *
 * @param string|null $datetime  nilai DATETIME dari database (format Y-m-d H:i:s)
 * @return string                waktu terformat, atau '-' jika null/kosong
 */
function formatWaktuIndo(?string $datetime): string
{
    if (!$datetime || $datetime === '0000-00-00 00:00:00') {
        return '-';
    }

    $bulan = [
        1  => 'Januari',  2  => 'Februari', 3  => 'Maret',
        4  => 'April',    5  => 'Mei',       6  => 'Juni',
        7  => 'Juli',     8  => 'Agustus',   9  => 'September',
        10 => 'Oktober',  11 => 'November',  12 => 'Desember',
    ];

    $ts  = strtotime($datetime);
    $tgl = (int) date('j',  $ts);
    $bln = (int) date('n',  $ts);
    $thn =       date('Y',  $ts);
    $jam =       date('H:i', $ts);

    return "$tgl {$bulan[$bln]} $thn $jam";
}

/**
 * Hitung durasi antara start_time dan finish_time.
 * Contoh output: "35 Menit", "2 Jam 15 Menit", "1 Hari 3 Jam 20 Menit"
 *
 * @param string      $start   nilai DATETIME mulai
 * @param string|null $finish  nilai DATETIME selesai (null = belum selesai)
 * @return string              durasi terformat atau label 'Berlangsung'
 */
function hitungDurasi(string $start, ?string $finish): string
{
    if (!$finish || $finish === '0000-00-00 00:00:00') {
        return '<span class="badge bg-warning text-dark">Berlangsung</span>';
    }

    $selisihDetik = strtotime($finish) - strtotime($start);

    if ($selisihDetik <= 0) {
        return '< 1 Menit';
    }

    $hari  = (int) floor($selisihDetik / 86400);
    $sisa  = $selisihDetik % 86400;
    $jam   = (int) floor($sisa / 3600);
    $sisa  = $sisa % 3600;
    $menit = (int) floor($sisa / 60);

    $bagian = [];
    if ($hari  > 0) $bagian[] = "$hari Hari";
    if ($jam   > 0) $bagian[] = "$jam Jam";
    if ($menit > 0) $bagian[] = "$menit Menit";

    // Jika kurang dari 1 menit tapi > 0 detik
    if (empty($bagian)) {
        $bagian[] = '< 1 Menit';
    }

    return implode(' ', $bagian);
}

/**
 * Hitung durasi dalam bentuk teks murni (tanpa badge HTML).
 * Digunakan di dalam tabel atau tempat yang tidak butuh HTML.
 *
 * @param string      $start
 * @param string|null $finish
 * @return string
 */
function hitungDurasiTeks(string $start, ?string $finish): string
{
    if (!$finish || $finish === '0000-00-00 00:00:00') {
        return 'Belum Selesai';
    }

    $selisihDetik = strtotime($finish) - strtotime($start);

    if ($selisihDetik <= 0) {
        return '< 1 Menit';
    }

    $hari  = (int) floor($selisihDetik / 86400);
    $sisa  = $selisihDetik % 86400;
    $jam   = (int) floor($sisa / 3600);
    $sisa  = $sisa % 3600;
    $menit = (int) floor($sisa / 60);

    $bagian = [];
    if ($hari  > 0) $bagian[] = "$hari Hari";
    if ($jam   > 0) $bagian[] = "$jam Jam";
    if ($menit > 0) $bagian[] = "$menit Menit";

    if (empty($bagian)) {
        $bagian[] = '< 1 Menit';
    }

    return implode(' ', $bagian);
}
