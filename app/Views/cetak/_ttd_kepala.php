<?php $tampilTanggal ??= true; ?>
<table class="ttd">
    <tr>
        <td class="kiri"></td>
        <td>
            <?php if ($tampilTanggal): ?>
                <?= esc($spt['instansi_kota']) ?>, <?= esc(tgl_indo($spt['tanggal_surat'])) ?><br>
            <?php endif; ?>
            <?= esc($spt['pejabat_jabatan']) ?>
            <div class="ruang"></div>
            <?= esc($spt['pejabat_nama']) ?><br>
            NIP. <?= esc($spt['pejabat_nip'] ?: '-') ?>
        </td>
    </tr>
</table>