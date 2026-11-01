<?php
$tunggal = $spt['mode_penerima'] === 'tunggal';
$p       = $spt['pegawai'][0] ?? null;
?>
<?php if (! empty($spt['kop_uri'])): ?>
    <?php /* Pengganti tak terlihat: menyisihkan ruang kop. Gambar aslinya digambar penuh di kanvas (lihat Spt::cetak) */ ?>
    <img src="<?= $spt['kop_uri'] ?>" style="width:100%; visibility:hidden;">
<?php endif; ?>

<div class="judul">SURAT TUGAS</div>
<div class="nomor">NOMOR : <?= esc($spt['nomor']) ?></div>

<table class="isi">
    <tr>
        <td class="lbl">Dasar</td><td class="kolon">:</td>
        <td><?= daftar_bernomor($spt['dasar']) ?></td>
    </tr>
</table>

<div class="tengah" style="margin:6px 0 8px;">Memberi Tugas</div>

<table class="isi">
    <tr>
        <td class="lbl">Kepada</td><td class="kolon">:</td>
        <td>
            <?php if ($tunggal && $p): ?>
                <table class="data">
                    <tr><td class="k1">Nama</td><td class="k2">:</td><td><?= esc($p['nama']) ?></td></tr>
                    <tr><td class="k1">NIP</td><td class="k2">:</td><td><?= esc($p['nip'] ?: '-') ?></td></tr>
                    <tr><td class="k1">Pangkat/Gol</td><td class="k2">:</td><td><?= esc($p['pangkat_golongan'] ?: '-') ?></td></tr>
                    <tr><td class="k1">Jabatan</td><td class="k2">:</td><td><?= esc($p['jabatan'] ?: '-') ?></td></tr>
                </table>
            <?php else: ?>
                Pegawai <?= esc($spt['instansi_nama']) ?> yang namanya terlampir.
            <?php endif; ?>
        </td>
    </tr>
    <tr>
        <td class="lbl" style="padding-top:10px;">Untuk</td><td class="kolon" style="padding-top:10px;">:</td>
        <td style="padding-top:10px;"><?= daftar_bernomor($spt['untuk']) ?></td>
    </tr>
</table>

<?= view('cetak/_ttd_kepala', ['spt' => $spt, 'tampilTanggal' => true]) ?>

<table class="grid" style="margin-top:14px;">
    <tr>
        <th style="width:0.9cm;">No</th><th style="width:2.2cm;">Uraian</th><th>Nama</th>
        <th>Jabatan</th><th style="width:2.2cm;">Tanggal</th><th style="width:2cm;">Paraf</th>
    </tr>
    <?php foreach ($spt['paraf'] as $i => $r): ?>
        <tr>
            <td class="tengah"><?= $i + 1 ?></td>
            <td class="tengah"><?= esc($r['uraian']) ?></td>
            <td class="tengah"><?= esc($r['nama']) ?></td>
            <td class="tengah"><?= esc($r['jabatan']) ?></td>
            <td class="tengah"><?= $r['tanggal'] ? date('d/m/Y', strtotime($r['tanggal'])) : '' ?></td>
            <td>&nbsp;</td>
        </tr>
    <?php endforeach; ?>
</table>