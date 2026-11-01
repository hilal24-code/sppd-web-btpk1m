<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?php
    $errors = session()->getFlashdata('errors') ?? [];
    $nilai  = static fn (string $f, $default = '') =>   old($f, $pegawai[$f] ?? $default, false)   // di instansi: $instansi[$f], di pejabat: $pejabat[$f]
?>
<h3 class="mb-3"><?= esc($title) ?></h3>

<div class="card shadow-sm">
    <div class="card-body">
        <form action="<?= $action ?>" method="post">
            <?= csrf_field() ?>

            <div class="mb-3">
                <label class="form-label">Nama <span class="text-danger">*</span></label>
                <input type="text" name="nama" class="form-control <?= isset($errors['nama']) ? 'is-invalid' : '' ?>" value="<?= esc($nilai('nama')) ?>">
                <div class="invalid-feedback"><?= esc($errors['nama'] ?? '') ?></div>
            </div>

            <div class="mb-3">
                <label class="form-label">NIP</label>
                <input type="text" name="nip" class="form-control <?= isset($errors['nip']) ? 'is-invalid' : '' ?>" value="<?= esc($nilai('nip')) ?>"
                       placeholder="Kosongkan bila tidak ada (contoh: 19800101 200501 1 001)">
                <div class="invalid-feedback"><?= esc($errors['nip'] ?? '') ?></div>
            </div>

            <div class="mb-3">
                <label class="form-label">Pangkat/Golongan</label>
                <input type="text" name="pangkat_golongan" list="daftar-pangkat" class="form-control" value="<?= esc($nilai('pangkat_golongan')) ?>">
                <datalist id="daftar-pangkat">
                    <option value="Pembina (IV/a)">
                    <option value="Penata Tk. I (III/d)">
                    <option value="Penata (III/c)">
                    <option value="Penata Muda Tk. I (III/b)">
                    <option value="Penata Muda (III/a)">
                    <option value="Pengatur (II/c)">
                </datalist>
            </div>

            <div class="mb-3">
                <label class="form-label">Jabatan <span class="text-danger">*</span></label>
                <input type="text" name="jabatan" class="form-control <?= isset($errors['jabatan']) ? 'is-invalid' : '' ?>" value="<?= esc($nilai('jabatan')) ?>">
                <div class="invalid-feedback"><?= esc($errors['jabatan'] ?? '') ?></div>
            </div>

            <div class="mb-3">
                <label class="form-label">Seksi</label>
                <input type="text" name="seksi" class="form-control" value="<?= esc($nilai('seksi')) ?>">
            </div>

            <?php
                $aktif = old('nama') !== null ? (old('is_active') !== null) : ($pegawai ? (bool) $pegawai['is_active'] : true);
            ?>
            <div class="form-check mb-4">
                <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" <?= $aktif ? 'checked' : '' ?>>
                <label class="form-check-label" for="is_active">Pegawai aktif (muncul di pilihan form SPT)</label>
            </div>

            <button class="btn btn-primary">Simpan</button>
            <a href="<?= site_url('pegawai') ?>" class="btn btn-link">Batal</a>
        </form>
    </div>
</div>
<?= $this->endSection() ?>