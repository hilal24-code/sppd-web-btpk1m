<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">Instansi</h3>
    <a href="<?= site_url('instansi/baru') ?>" class="btn btn-primary">+ Tambah Instansi</a>
</div>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
            <tr><th>No</th><th>Nama Instansi</th><th>Kota</th><th class="text-end">Aksi</th></tr>
            </thead>
            <tbody>
            <?php if (empty($daftar)): ?>
                <tr><td colspan="4" class="text-center text-muted py-4">Belum ada data.</td></tr>
            <?php endif; ?>
            <?php foreach ($daftar as $i => $row): ?>
                <tr>
                    <td><?= $i + 1 ?></td>
                    <td><?= esc($row['nama']) ?></td>
                    <td><?= esc($row['kota']) ?></td>
                    <td class="text-end">
                        <a href="<?= site_url('instansi/ubah/' . $row['id']) ?>" class="btn btn-sm btn-outline-primary">Ubah</a>
                        <form action="<?= site_url('instansi/hapus/' . $row['id']) ?>" method="post" class="d-inline"
                              onsubmit="return confirm('Hapus instansi ini?');">
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
<?= $this->endSection() ?>