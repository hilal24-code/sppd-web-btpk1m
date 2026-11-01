<?php
$judul = 'DAFTAR NAMA YANG ' . tanpa_tanda_akhir($spt['kegiatan']) . ' ' . frasa_waktu($spt['tanggal_mulai'], $spt['tanggal_selesai'])
    . ($spt['tempat'] ? ' di ' . tanpa_tanda_akhir($spt['tempat']) : '');
?>
<table style="width:100%;">
    <tr>
        <td style="width:50%;"></td>
        <td>
            <table class="data">
                <tr><td colspan="3">Lampiran Surat Tugas</td></tr>
                <tr><td class="k1">Nomor</td><td class="k2">:</td><td><?= esc($spt['nomor']) ?></td></tr>
                <tr>
                    <td class="k1" style="border-bottom:1px solid #000;">Tanggal</td>
                    <td class="k2" style="border-bottom:1px solid #000;">:</td>
                    <td style="border-bottom:1px solid #000;"><?= esc(tgl_indo($spt['tanggal_surat'])) ?></td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<p class="tengah" style="font-weight:bold; margin:26px 0 12px;"><?= esc(mb_strtoupper($judul)) ?></p>

<table class="grid tebal">
    <tr>
        <th style="width:1cm;">NO</th><th>NAMA</th><th style="width:4.2cm;">NIP</th>
        <th style="width:3.4cm;">PANGKAT/GOL</th><th>JABATAN</th>
    </tr>
    <?php foreach ($spt['pegawai'] as $i => $p): ?>
        <tr>
            <td><?= $i + 1 ?>.</td>
            <td><?= esc($p['nama']) ?></td>
            <td class="tengah"><?= esc($p['nip'] ?: '-') ?></td>
            <td class="tengah"><?= esc($p['pangkat_golongan'] ?: '-') ?></td>
            <td class="tengah"><?= esc($p['jabatan'] ?: '-') ?></td>
        </tr>
    <?php endforeach; ?>
</table>

<?= view('cetak/_ttd_kepala', ['spt' => $spt, 'tampilTanggal' => false]) ?>