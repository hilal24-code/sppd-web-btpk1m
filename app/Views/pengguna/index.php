<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?php $warna = ['superuser' => 'dark', 'admin' => 'primary', 'user' => 'secondary']; ?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">Pengguna</h3>
    <a href="<?= site_url('pengguna/baru') ?>" class="btn btn-primary">+ Tambah Pengguna</a>
</div>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
            <tr><th>No</th><th>Username</th><th>Pegawai</th><th>Peran</th><th>Status</th><th class="text-end">Aksi</th></tr>
            </thead>
            <tbody>
            <?php foreach ($daftar as $i => $u): ?>
                <?php $sendiri = (int) $u['id'] === (int) session('user_id'); ?>
                <tr>
                    <td><?= $i + 1 ?></td>
                    <td>
                        <?= esc($u['username']) ?>
                        <?php if ($sendiri): ?><span class="badge text-bg-info">Anda</span><?php endif; ?>
                    </td>
                    <td><?= esc($u['pegawai_nama'] ?? '-') ?></td>
                    <td><span class="badge text-bg-<?= $warna[$u['role']] ?? 'secondary' ?>"><?= esc($u['role']) ?></span></td>
                    <td>
                        <?= $u['is_active']
                            ? '<span class="badge text-bg-success">Aktif</span>'
                            : '<span class="badge text-bg-secondary">Nonaktif</span>' ?>
                    </td>
                    <td class="text-end text-nowrap">
                        <a href="<?= site_url('pengguna/ubah/' . $u['id']) ?>" class="btn btn-sm btn-outline-primary">Ubah</a>
                        <?php if (! $sendiri): ?>
                            <form action="<?= site_url('pengguna/hapus/' . $u['id']) ?>" method="post" class="d-inline"
                                  onsubmit="return confirm('Hapus pengguna ini?');">
                                <?= csrf_field() ?>
                                <button class="btn btn-sm btn-outline-danger">Hapus</button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?= $this->endSection() ?>