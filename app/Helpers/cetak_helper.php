<?php

if (! function_exists('tgl_indo')) {
    /** 2026-11-01 => "1 November 2026" */
    function tgl_indo(?string $tanggal): string
    {
        if (! $tanggal) {
            return '';
        }
        $bulan = [1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        $t     = strtotime($tanggal);

        return date('j', $t) . ' ' . $bulan[(int) date('n', $t)] . ' ' . date('Y', $t);
    }
}

if (! function_exists('hari_indo')) {
    function hari_indo(string $tanggal): string
    {
        $hari = [1 => 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];

        return $hari[(int) date('N', strtotime($tanggal))];
    }
}

if (! function_exists('frasa_waktu')) {
    /** "hari Rabu tanggal 27 September 2023" atau "tanggal 27 s.d. 30 September 2022" */
    function frasa_waktu(string $mulai, ?string $selesai): string
    {
        if (! $selesai || $selesai === $mulai) {
            return 'hari ' . hari_indo($mulai) . ' tanggal ' . tgl_indo($mulai);
        }

        $m = strtotime($mulai);
        $s = strtotime($selesai);
        if (date('Y-m', $m) === date('Y-m', $s)) {
            return 'tanggal ' . date('j', $m) . ' s.d. ' . tgl_indo($selesai);
        }

        return 'tanggal ' . tgl_indo($mulai) . ' s.d. ' . tgl_indo($selesai);
    }
}

if (! function_exists('daftar_bernomor')) {
    /** Daftar "1. teks" sebagai tabel (rapi di Dompdf). Teks sudah di-escape. */
    function daftar_bernomor(array $items): string
    {
        $html = '<table class="daftar">';
        foreach (array_values($items) as $i => $teks) {
            $html .= '<tr><td class="no">' . ($i + 1) . '.</td><td>' . nl2br(esc($teks)) . '</td></tr>';
        }

        return $html . '</table>';
    }
}

if (! function_exists('gambar_data_uri')) {
    /** Baca gambar kop/footer dari writable/uploads/kop sebagai data URI (aman untuk Dompdf). */
    function gambar_data_uri(?string $nama): ?string
    {
        if (! $nama) {
            return null;
        }
        $path = WRITEPATH . 'uploads/kop/' . basename($nama);
        if (! is_file($path)) {
            return null;
        }
        $mime = str_ends_with(strtolower($path), '.jpg') ? 'image/jpeg' : 'image/png';

        return 'data:' . $mime . ';base64,' . base64_encode((string) file_get_contents($path));
    }
}

if (! function_exists('huruf_awal_kecil')) {
    function huruf_awal_kecil(string $teks): string
    {
        return mb_strtolower(mb_substr($teks, 0, 1)) . mb_substr($teks, 1);
    }
}

if (! function_exists('gambar_path')) {
    /** Path file gambar kop/footer di writable/uploads/kop, atau null bila tidak ada. */
    function gambar_path(?string $nama): ?string
    {
        if (! $nama) {
            return null;
        }
        $path = WRITEPATH . 'uploads/kop/' . basename($nama);

        return is_file($path) ? $path : null;
    }
}

if (! function_exists('tanpa_tanda_akhir')) {
    /** Buang titik/koma di akhir kalimat kegiatan agar tidak muncul ". tanggal ..." */
    function tanpa_tanda_akhir(string $teks): string
    {
        return rtrim($teks, " \t\n\r.,;:");
    }
}