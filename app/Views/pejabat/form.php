
<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?php
    $errors = session()->getFlashdata('errors') ?? [];
    $nilai = static fn (string $f, $default = '') => old($f, $pejabat[$f] ?? $default, false);   // di instansi: $instansi[$f], di pejabat: $pejabat[$f]
    $aktif  = old('nama') !== null ? (old('is_active') !== null) : ($pejabat ? (bool) $pejabat['is_active'] : true);
?>
<h3 class="mb-3"><?= esc($title) ?></h3>

<div class="card shadow-sm">
    <div class="card-body">
        <form action="<?= $action ?>" method="post">
            <?= csrf_field() ?>

            <div class="mb-3">
                <label class="form-label">Instansi <span class="text-danger">*</span></label>
                <select name="instansi_id" class="form-select <?= isset($errors['instansi_id']) ? 'is-invalid' : '' ?>">
                    <option value="">-- Pilih instansi --</option>
                    <?php foreach ($instansi as $ins): ?>
                        <option value="<?= $ins['id'] ?>" <?= (string) $nilai('instansi_id') === (string) $ins['id'] ? 'selected' : '' ?>>
                            <?= esc($ins['nama']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <div class="invalid-feedback"><?= esc($errors['instansi_id'] ?? '') ?></div>
            </div>

            <div class="mb-3">
                <label class="form-label">Nama (beserta gelar) <span class="text-danger">*</span></label>
                <input type="text" name="nama" class="form-control <?= isset($errors['nama']) ? 'is-invalid' : '' ?>" value="<?= esc($nilai('nama')) ?>">
                <div class="invalid-feedback"><?= esc($errors['nama'] ?? '') ?></div>
            </div>

            <div class="mb-3">
                <label class="form-label">NIP</label>
                <input type="text" name="nip" class="form-control" value="<?= esc($nilai('nip')) ?>">
            </div>

            <div class="mb-3">
                <label class="form-label">Jabatan <span class="text-danger">*</span></label>
                <input type="text" name="jabatan" class="form-control <?= isset($errors['jabatan']) ? 'is-invalid' : '' ?>"
                       value="<?= esc($nilai('jabatan')) ?>" placeholder="Kepala Balai Teknik Perkeretaapian Kelas I Medan">
                <div class="invalid-feedback"><?= esc($errors['jabatan'] ?? '') ?></div>
            </div>

            <div class="form-check mb-4">
                <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" <?= $aktif ? 'checked' : '' ?>>
                <label class="form-check-label" for="is_active">Aktif (muncul di pilihan penandatangan SPT)</label>
            </div>

            <button class="btn btn-primary">Simpan</button>
            <a href="<?= site_url('pejabat') ?>" class="btn btn-link">Batal</a>
        </form>
    </div>
</div>
<?= $this->endSection() ?>
