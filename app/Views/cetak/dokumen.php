<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Surat Tugas <?= esc($spt['nomor']) ?></title>
    <style>
        @page { margin: 0.8cm 1.5cm <?= $marginBawah ?>cm 2cm; }
        <?= view('cetak/_gaya') ?>
    </style>
</head>
<body>
<?php foreach ($bagian as $i => $nama): ?>
    <div class="<?= $i > 0 ? 'baru' : '' ?>">
        <?= view('cetak/' . $nama, ['spt' => $spt]) ?>
    </div>
<?php endforeach; ?>
</body>
</html>