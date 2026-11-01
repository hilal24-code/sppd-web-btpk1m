<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?php
    $errors = session()->getFlashdata('errors') ?? [];
    $v      = static fn (string $f, $default = '') => old($f, $pengguna[$f] ?? $default, false);
    $sendiri = $pengguna && (int) $pengguna['id'] === (int) session('user_id');
    $aktif  = old('username') !== null ? (old('is_active') !== null) : ($pengguna ? (bool) $pengguna['is_active'] : true);
    $peran  = [
        'user'      => 'user: membuat dan mengajukan SPT miliknya sendiri',
        'admin'     => 'admin: memeriksa, menyetujui/menolak, memberi nomor, mengelola data pegawai',
        'superuser' => 'superuser: semua akses, termasuk instansi, pejabat, dan pengguna',
    ];
?>
<h3 class="mb-3"><?= esc($title) ?></h3>

<?php if ($errors): ?>
    <div class="alert alert-danger">
        <ul class="mb-0"><?php foreach ($errors as $e): ?><li><?= esc($e) ?></li><?php endforeach; ?></ul>
    </div>
<?php endif; ?>

<div class="card shadow-sm">
    <div class="card-body">
        <form action="<?= $action ?>" method="post" autocomplete="off">
            <?= csrf_field() ?>

            <div class="mb-3">
                <label class="form-label">Username <span class="text-danger">*</span></label>
                <input type="text" name="username" class="form-control" value="<?= esc($v('username')) ?>">
                <div class="form-text">Huruf, angka, strip (-) dan garis bawah (_), minimal 3 karakter.</div>
            </div>

            <div class="mb-3">
                <label class="form-label">Pegawai terkait</label>
                <select name="pegawai_id" class="form-select">
                    <option value="">-- Tidak terhubung ke pegawai --</option>
                    <?php foreach ($pegawai as $p): ?>
                        <option value="<?= $p['id'] ?>" <?= (string) $v('pegawai_id') === (string) $p['id'] ? 'selected' : '' ?>>
                            <?= esc($p['nama'] . ' — ' . $p['jabatan']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <div class="form-text">Menentukan nama yang tampil dan otomatis menjadi pihak "Dikonsep oleh" di form SPT.</div>
            </div>

            <div class="mb-3">
                <label class="form-label">Peran <span class="text-danger">*</span></label>
                <?php if ($sendiri): ?>
                    <input type="hidden" name="role" value="<?= esc($pengguna['role']) ?>">
                    <input type="text" class="form-control" value="<?= esc($pengguna['role']) ?>" disabled>
                    <div class="form-text">Peran akun yang sedang Anda pakai tidak dapat diubah.</div>
                <?php else: ?>
                    <select name="role" class="form-select">
                        <?php foreach ($peran as $kode => $ket): ?>
                            <option value="<?= $kode ?>" <?= $v('role', 'user') === $kode ? 'selected' : '' ?>><?= esc($ket) ?></option>
                        <?php endforeach; ?>
                    </select>
                <?php endif; ?>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label">
                        <?= $pengguna ? 'Password baru' : 'Password' ?>
                        <?= $pengguna ? '' : '<span class="text-danger">*</span>' ?>
                    </label>
                    <input type="password" name="password" class="form-control" autocomplete="new-password">
                    <div class="form-text"><?= $pengguna ? 'Kosongkan bila tidak ingin mengganti. ' : '' ?>Minimal 8 karakter.</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Konfirmasi password</label>
                    <input type="password" name="password_konfirmasi" class="form-control" autocomplete="new-password">
                </div>
            </div>

            <div class="form-check mb-4">
                <?php if ($sendiri): ?>
                    <input type="hidden" name="is_active" value="1">
                    <input class="form-check-input" type="checkbox" id="is_active" checked disabled>
                <?php else: ?>
                    <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" <?= $aktif ? 'checked' : '' ?>>
                <?php endif; ?>
                <label class="form-check-label" for="is_active">Akun aktif (bisa login)</label>
            </div>

            <button class="btn btn-primary">Simpan</button>
            <a href="<?= site_url('pengguna') ?>" class="btn btn-link">Batal</a>
        </form>
    </div>
</div>
<?= $this->endSection() ?>