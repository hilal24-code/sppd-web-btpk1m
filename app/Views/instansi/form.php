<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?php
    $errors = session()->getFlashdata('errors') ?? [];
    $nilai  = static fn (string $f, $default = '') => old($f, $instansi[$f] ?? $default, false);
?>
<h3 class="mb-3"><?= esc($title) ?></h3>

<div class="card shadow-sm">
    <div class="card-body">
        <form action="<?= $action ?>" method="post" enctype="multipart/form-data">
            <?= csrf_field() ?>

            <div class="mb-3">
                <label class="form-label">Nama Instansi <span class="text-danger">*</span></label>
                <input type="text" name="nama" class="form-control <?= isset($errors['nama']) ? 'is-invalid' : '' ?>"
                       value="<?= esc($nilai('nama')) ?>" placeholder="Balai Teknik Perkeretaapian Kelas I Medan">
                <div class="invalid-feedback"><?= esc($errors['nama'] ?? '') ?></div>
            </div>

            <div class="mb-4">
                <label class="form-label">Kota (untuk "Medan, tanggal...") <span class="text-danger">*</span></label>
                <input type="text" name="kota" class="form-control <?= isset($errors['kota']) ? 'is-invalid' : '' ?>"
                       value="<?= esc($nilai('kota', 'Medan')) ?>">
                <div class="invalid-feedback"><?= esc($errors['kota'] ?? '') ?></div>
            </div>

            <hr>
            <h6 class="mb-3">Gambar untuk cetak PDF <span class="text-muted fw-normal">(opsional)</span></h6>

            <?php foreach (['kop' => ['Kop surat', 'Dipasang di bagian atas halaman pertama Surat Tugas, lebar mengikuti halaman.'],
                            'footer' => ['Footer / slogan', 'Dipasang di bagian bawah setiap halaman.']] as $f => [$label, $bantuan]): ?>
                <?php $ada = $instansi && $instansi[$f . '_file']; ?>
                <div class="mb-4">
                    <label class="form-label"><?= $label ?></label>
                    <?php if ($ada): ?>
                        <div class="mb-2">
                            <img src="<?= site_url('instansi/gambar/' . $instansi['id'] . '/' . $f) ?>" class="img-fluid border" style="max-width:520px;" alt="<?= $label ?>">
                        </div>
                    <?php endif; ?>
                    <input type="file" name="<?= $f ?>" accept="image/png,image/jpeg"
                           class="form-control <?= isset($errors[$f]) ? 'is-invalid' : '' ?>">
                    <div class="invalid-feedback"><?= esc($errors[$f] ?? '') ?></div>
                    <div class="form-text"><?= $bantuan ?> PNG atau JPG, maksimal 3 MB.<?= $ada ? ' Pilih file baru untuk mengganti.' : '' ?></div>
                    <?php if ($ada): ?>
                        <div class="form-check mt-1">
                            <input class="form-check-input" type="checkbox" name="hapus_<?= $f ?>" id="hapus_<?= $f ?>" value="1">
                            <label class="form-check-label" for="hapus_<?= $f ?>">Hapus gambar ini</label>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>

            <button class="btn btn-primary">Simpan</button>
            <a href="<?= site_url('instansi') ?>" class="btn btn-link">Batal</a>
        </form>
    </div>
</div>
<?= $this->endSection() ?>