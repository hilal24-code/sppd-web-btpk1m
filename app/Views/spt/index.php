<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?php
    $badge = [
        'draft'     => 'secondary',
        'diajukan'  => 'warning',
        'disetujui' => 'success',
        'ditolak'   => 'danger',
    ];
    $tgl = static fn (?string $d) => $d ? date('d/m/Y', strtotime($d)) : '';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">Surat Tugas</h3>
    <a href="<?= site_url('spt/baru') ?>" class="btn btn-primary">+ Buat SPT</a>
</div>

<form method="get" action="<?= site_url('spt') ?>" class="row g-2 mb-3">
    <div class="col-md-5">
        <input type="text" name="q" value="<?= esc($q) ?>" class="form-control" placeholder="Cari kegiatan atau nomor...">
    </div>
    <div class="col-md-3">
        <select name="status" class="form-select">
            <option value="">Semua status</option>
            <?php foreach (array_keys($badge) as $s): ?>
                <option value="<?= $s ?>" <?= $status === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-auto">
        <button class="btn btn-outline-secondary">Cari</button>
        <?php if ($q !== '' || $status !== ''): ?><a href="<?= site_url('spt') ?>" class="btn btn-link">Reset</a><?php endif; ?>
    </div>
</form>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
            <tr><th>No</th><th>Nomor</th><th>Kegiatan</th><th>Tanggal</th><th>Pegawai</th><th>Pembuat</th><th>Status</th><th class="text-end">Aksi</th></tr>
            </thead>
            <tbody>
            <?php if (empty($daftar)): ?>
                <tr><td colspan="8" class="text-center text-muted py-4">Belum ada SPT.</td></tr>
            <?php endif; ?>

            <?php $no = 1 + ($pager->getCurrentPage() - 1) * $pager->getPerPage(); ?>
            <?php foreach ($daftar as $row): ?>
                <tr>
                    <td><?= $no++ ?></td>
                    <td><?= esc($row['nomor'] ?? '-') ?></td>
                    <td style="max-width:360px;"><?= esc(mb_strimwidth($row['kegiatan'], 0, 110, '...')) ?></td>
                    <td class="text-nowrap">
                        <?= $tgl($row['tanggal_mulai']) ?>
                        <?= $row['tanggal_selesai'] ? ' - ' . $tgl($row['tanggal_selesai']) : '' ?>
                    </td>
                    <td><?= (int) $row['jml_pegawai'] ?> orang</td>
                    <td><?= esc($row['pembuat']) ?></td>
                    <td><span class="badge text-bg-<?= $badge[$row['status']] ?? 'secondary' ?>"><?= esc(ucfirst($row['status'])) ?></span></td>
                    <td class="text-end text-nowrap">
                        <a href="<?= site_url('spt/lihat/' . $row['id']) ?>" class="btn btn-sm btn-outline-dark">Detail</a>
                        <?php if ($row['status'] === 'disetujui'): ?>
                            <a href="<?= site_url('spt/cetak/' . $row['id'] . '/paket') ?>" target="_blank" class="btn btn-sm btn-outline-success">Cetak</a>
                        <?php endif; ?>
                        <?php if ($row['boleh_ubah']): ?>
                            <a href="<?= site_url('spt/ubah/' . $row['id']) ?>" class="btn btn-sm btn-outline-primary">Ubah</a>
                            <form action="<?= site_url('spt/hapus/' . $row['id']) ?>" method="post" class="d-inline"
                                  onsubmit="return confirm('Hapus SPT ini?');">
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

<div class="mt-3"><?= $pager->links('default', 'bootstrap') ?></div>
<?= $this->endSection() ?>