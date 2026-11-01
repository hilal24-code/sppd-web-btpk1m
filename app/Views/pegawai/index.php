<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">Data Pegawai</h3>
    <a href="<?= site_url('pegawai/baru') ?>" class="btn btn-primary">+ Tambah Pegawai</a>
</div>

<form method="get" action="<?= site_url('pegawai') ?>" class="row g-2 mb-3">
    <div class="col-md-6">
        <input type="text" name="q" value="<?= esc($q) ?>" class="form-control" placeholder="Cari nama, NIP, atau jabatan...">
    </div>
    <div class="col-auto">
        <button class="btn btn-outline-secondary">Cari</button>
        <?php if ($q !== ''): ?><a href="<?= site_url('pegawai') ?>" class="btn btn-link">Reset</a><?php endif; ?>
    </div>
</form>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
            <tr>
                <th>No</th><th>Nama</th><th>NIP</th><th>Pangkat/Gol</th><th>Jabatan</th><th>Status</th><th class="text-end">Aksi</th>
            </tr>
            </thead>
            <tbody>
            <?php if (empty($pegawai)): ?>
                <tr><td colspan="7" class="text-center text-muted py-4">Belum ada data.</td></tr>
            <?php endif; ?>

            <?php $no = 1 + ($pager->getCurrentPage() - 1) * $pager->getPerPage(); ?>
            <?php foreach ($pegawai as $p): ?>
                <tr>
                    <td><?= $no++ ?></td>
                    <td><?= esc($p['nama']) ?></td>
                    <td><?= esc($p['nip'] ?? '-') ?></td>
                    <td><?= esc($p['pangkat_golongan'] ?? '-') ?></td>
                    <td><?= esc($p['jabatan']) ?></td>
                    <td>
                        <?php if ($p['is_active']): ?>
                            <span class="badge text-bg-success">Aktif</span>
                        <?php else: ?>
                            <span class="badge text-bg-secondary">Nonaktif</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-end">
                        <a href="<?= site_url('pegawai/ubah/' . $p['id']) ?>" class="btn btn-sm btn-outline-primary">Ubah</a>
                        <form action="<?= site_url('pegawai/hapus/' . $p['id']) ?>" method="post" class="d-inline"
                              onsubmit="return confirm('Hapus pegawai ini?');">
                            <?= csrf_field() ?>
                            <button class="btn btn-sm btn-outline-danger">Hapus</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="mt-3"><?= $pager->links('default', 'bootstrap') ?></div>
<?= $this->endSection() ?>