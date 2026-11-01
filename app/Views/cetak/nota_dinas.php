<?php
// Tanggal nota = tanggal paraf "Dikonsep" (saat diajukan); cadangan: tanggal surat
$tglNota = $spt['tanggal_surat'];
foreach ($spt['paraf'] as $r) {
    if ($r['uraian'] === 'Dikonsep' && $r['tanggal']) {
        $tglNota = $r['tanggal'];
        break;
    }
}

$kegiatan = huruf_awal_kecil(tanpa_tanda_akhir($spt['kegiatan'])) . ' ' . frasa_waktu($spt['tanggal_mulai'], $spt['tanggal_selesai'])
    . ($spt['tempat'] ? ' di ' . tanpa_tanda_akhir($spt['tempat']) : '');

$pembuka = $spt['nota_rujukan']
    ? 'Menunjuk ' . $spt['nota_rujukan'] . ', bersama ini kami sampaikan konsep surat tugas '
    : 'Bersama ini kami sampaikan konsep surat tugas ';
$kalimat = $pembuka . $kegiatan . '. Apabila Bapak berkenan, mohon persetujuan dan tanda tangan.';
?>
<div class="tengah" style="margin-top:50px; font-weight:bold;">
    NOTA DINAS<br>
    Nomor : <?= esc($spt['nota_nomor'] ?: '') ?>
</div>

<table class="meta" style="margin-top:22px;">
    <tr><td class="k1">Yth</td><td class="k2">:</td><td><?= esc($spt['pejabat_jabatan']) ?></td></tr>
    <tr><td class="k1">Dari</td><td class="k2">:</td><td><?= esc($spt['nota_dari'] ?: '') ?></td></tr>
    <tr><td class="k1">Hal</td><td class="k2">:</td><td><?= esc($spt['nota_hal']) ?></td></tr>
    <tr><td class="k1">Tanggal</td><td class="k2">:</td><td><?= esc(tgl_indo($tglNota)) ?></td></tr>
</table>

<p class="paragraf" style="margin-top:26px;"><?= esc($kalimat) ?></p>
<p class="paragraf">Demikian disampaikan, atas petunjuk dan arahan lanjut Bapak diucapkan terima kasih.</p>

<table class="ttd" style="margin-top:20px;">
    <tr>
        <td class="kiri"></td>
        <td>
            <div class="ruang" style="height:3cm;"></div>
            <?= esc($spt['nota_pengirim_nama'] ?: '....................') ?><br>
            NIP. <?= esc($spt['nota_pengirim_nip'] ?: '-') ?>
        </td>
    </tr>
</table>