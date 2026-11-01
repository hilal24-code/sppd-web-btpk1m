<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?php
    $role  = session()->get('role');
    $staf  = $role === 'user';
    $kartu = [
        'diajukan'  => [$staf ? 'Menunggu pemeriksaan' : 'Perlu diperiksa', 'warning'],
        'ditolak'   => ['Perlu revisi', 'danger'],
        'draft'     => ['Draft', 'secondary'],
        'disetujui' => ['Disetujui', 'success'],
    ];
    $badge = ['draft' => 'secondary', 'diajukan' => 'warning', 'disetujui' => 'success', 'ditolak' => 'danger'];
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h3 class="mb-0">Dashboard</h3>
        <span class="text-muted">Selamat datang, <?= esc(session()->get('nama')) ?>.</span>
    </div>
    <a href="<?= site_url('spt/baru') ?>" class="btn btn-primary">+ Buat SPT</a>
</div>

<?php if (! $staf && $jumlah['diajukan'] > 0): ?>
    <div class="alert alert-warning">
        Ada <strong><?= $jumlah['diajukan'] ?></strong> SPT yang menunggu pemeriksaan.
        <a href="<?= site_url('spt?status=diajukan') ?>" class="alert-link">Periksa sekarang</a>
    </div>
<?php endif; ?>
<?php if ($staf && $jumlah['ditolak'] > 0): ?>
    <div class="alert alert-danger">
        Ada <strong><?= $jumlah['ditolak'] ?></strong> SPT yang dikembalikan dan perlu direvisi.
        <a href="<?= site_url('spt?status=ditolak') ?>" class="alert-link">Lihat</a>
    </div>
<?php endif; ?>

<div class="row g-3 mb-4">
    <?php foreach ($kartu as $status => [$label, $warna]): ?>
        <div class="col-6 col-lg-3">
            <a href="<?= site_url('spt?status=' . $status) ?>" class="text-decoration-none">
                <div class="card shadow-sm border-<?= $warna ?> h-100">
                    <div class="card-body">
                        <div class="text-muted small"><?= esc($label) ?></div>
                        <div class="display-6 text-<?= $warna === 'warning' ? 'warning-emphasis' : $warna ?>"><?= $jumlah[$status] ?></div>
                    </div>
                </div>
            </a>
        </div>
    <?php endforeach; ?>
</div>

<div class="card shadow-sm">
    <div class="card-header fw-semibold">SPT Terbaru</div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light"><tr><th>Nomor</th><th>Kegiatan</th><th>Tanggal</th><th>Status</th><th></th></tr></thead>
            <tbody>
            <?php if (empty($terbaru)): ?>
                <tr><td colspan="5" class="text-center text-muted py-4">Belum ada SPT.</td></tr>
            <?php endif; ?>
            <?php foreach ($terbaru as $row): ?>
                <tr>
                    <td><?= esc($row['nomor'] ?? '-') ?></td>
                    <td><?= esc(mb_strimwidth($row['kegiatan'], 0, 90, '...')) ?></td>
                    <td class="text-nowrap"><?= date('d/m/Y', strtotime($row['tanggal_mulai'])) ?></td>
                    <td><span class="badge text-bg-<?= $badge[$row['status']] ?? 'secondary' ?>"><?= esc(ucfirst($row['status'])) ?></span></td>
                    <td class="text-end"><a href="<?= site_url('spt/lihat/' . $row['id']) ?>" class="btn btn-sm btn-outline-dark">Detail</a></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?= $this->endSection() ?>