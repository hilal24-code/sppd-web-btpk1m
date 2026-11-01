<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?php $errors = session()->getFlashdata('errors') ?? []; ?>
<h3 class="mb-3">Ganti Password</h3>

<?php if ($errors): ?>
    <div class="alert alert-danger">
        <ul class="mb-0"><?php foreach ($errors as $e): ?><li><?= esc($e) ?></li><?php endforeach; ?></ul>
    </div>
<?php endif; ?>

<div class="card shadow-sm" style="max-width:520px;">
    <div class="card-body">
        <form action="<?= site_url('akun/password') ?>" method="post" autocomplete="off">
            <?= csrf_field() ?>
            <div class="mb-3">
                <label class="form-label">Password lama</label>
                <input type="password" name="password_lama" class="form-control" autocomplete="current-password">
            </div>
            <div class="mb-3">
                <label class="form-label">Password baru</label>
                <input type="password" name="password_baru" class="form-control" autocomplete="new-password">
                <div class="form-text">Minimal 8 karakter dan harus berbeda dari password lama.</div>
            </div>
            <div class="mb-4">
                <label class="form-label">Konfirmasi password baru</label>
                <input type="password" name="password_konfirmasi" class="form-control" autocomplete="new-password">
            </div>
            <button class="btn btn-primary">Simpan</button>
            <a href="<?= site_url('dashboard') ?>" class="btn btn-link">Batal</a>
        </form>
    </div>
</div>
<?= $this->endSection() ?>