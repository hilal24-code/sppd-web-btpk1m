<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?php
    $badge = ['draft' => 'secondary', 'diajukan' => 'warning', 'disetujui' => 'success', 'ditolak' => 'danger'];
    $tgl   = static fn (?string $d) => $d ? date('d/m/Y', strtotime($d)) : '-';
    $errors = session()->getFlashdata('errors') ?? [];
    $kv = static fn (string $label, $nilai) => '<dt class="col-sm-3">' . esc($label) . '</dt><dd class="col-sm-9">' . esc($nilai ?: '-') . '</dd>';
?>

<div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
    <div>
        <h3 class="mb-1">Detail Surat Tugas</h3>
        <span class="badge text-bg-<?= $badge[$spt['status']] ?? 'secondary' ?>"><?= esc(ucfirst($spt['status'])) ?></span>
        <span class="text-muted small ms-2">Dibuat oleh <?= esc($spt['pembuat']) ?></span>
    </div>

    <div class="d-flex gap-2 flex-wrap">
        <a href="<?= site_url('spt') ?>" class="btn btn-outline-secondary">Kembali</a>

        <?php if ($spt['status'] === 'disetujui'): ?>
            <div class="btn-group">
                <a class="btn btn-dark" target="_blank" href="<?= site_url('spt/cetak/' . $spt['id'] . '/paket') ?>">Cetak PDF</a>
                <button type="button" class="btn btn-dark dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown">
                    <span class="visually-hidden">Pilihan cetak</span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" target="_blank" href="<?= site_url('spt/cetak/' . $spt['id'] . '/surat') ?>">Surat Tugas saja</a></li>
                    <?php if ($spt['mode_penerima'] === 'banyak'): ?>
                        <li><a class="dropdown-item" target="_blank" href="<?= site_url('spt/cetak/' . $spt['id'] . '/lampiran') ?>">Lampiran saja</a></li>
                    <?php endif; ?>
                    <li><a class="dropdown-item" target="_blank" href="<?= site_url('spt/cetak/' . $spt['id'] . '/nota') ?>">Nota Dinas saja</a></li>
                </ul>
            </div>
        <?php endif; ?>

        <?php if ($boleh['ubah']): ?>
            <a href="<?= site_url('spt/ubah/' . $spt['id']) ?>" class="btn btn-outline-primary">Ubah</a>
        <?php endif; ?>

        <?php if ($boleh['ajukan']): ?>
            <form action="<?= site_url('spt/ajukan/' . $spt['id']) ?>" method="post"
                  onsubmit="return confirm('Ajukan SPT ini untuk diperiksa?');">
                <?= csrf_field() ?>
                <button class="btn btn-primary">Ajukan</button>
            </form>
        <?php endif; ?>

        <?php if ($boleh['periksa']): ?>
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modal-setujui">Setujui</button>
            <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#modal-tolak">Tolak</button>
        <?php endif; ?>

        <?php if ($boleh['buka']): ?>
            <form action="<?= site_url('spt/buka-kembali/' . $spt['id']) ?>" method="post"
                  onsubmit="return confirm('Kembalikan SPT ini ke draft agar bisa diubah?');">
                <?= csrf_field() ?>
                <button class="btn btn-outline-warning">Kembalikan ke Draft</button>
            </form>
        <?php endif; ?>
    </div>
</div>

<?php if ($errors): ?>
    <div class="alert alert-danger">
        <ul class="mb-0"><?php foreach ($errors as $e): ?><li><?= esc($e) ?></li><?php endforeach; ?></ul>
    </div>
<?php endif; ?>

<?php if ($spt['status'] === 'ditolak' && $spt['catatan_revisi']): ?>
    <div class="alert alert-warning">
        <strong>Catatan revisi dari pemeriksa:</strong><br>
        <?= nl2br(esc($spt['catatan_revisi'])) ?>
    </div>
<?php endif; ?>

<div class="card shadow-sm mb-3">
    <div class="card-header fw-semibold">Informasi Kegiatan</div>
    <div class="card-body">
        <dl class="row mb-0">
            <?= $kv('Nomor surat', $spt['nomor']) ?>
            <?= $kv('Tanggal surat', $tgl($spt['tanggal_surat'])) ?>
            <?= $kv('Instansi', $spt['instansi_nama']) ?>
            <?= $kv('Kegiatan', $spt['kegiatan']) ?>
            <?= $kv('Tempat', $spt['tempat']) ?>
            <?= $kv('Tanggal kegiatan', $tgl($spt['tanggal_mulai']) . ($spt['tanggal_selesai'] ? ' s.d. ' . $tgl($spt['tanggal_selesai']) : '')) ?>
            <?= $kv('Mode penerima', $spt['mode_penerima'] === 'tunggal' ? 'Satu pegawai' : 'Banyak pegawai (dengan Lampiran)') ?>
        </dl>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-md-6">
        <div class="card shadow-sm h-100">
            <div class="card-header fw-semibold">Dasar</div>
            <div class="card-body"><ol class="mb-0 ps-3">
                <?php foreach ($spt['dasar'] as $d): ?><li class="mb-2"><?= nl2br(esc($d)) ?></li><?php endforeach; ?>
            </ol></div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card shadow-sm h-100">
            <div class="card-header fw-semibold">Untuk</div>
            <div class="card-body"><ol class="mb-0 ps-3">
                <?php foreach ($spt['untuk'] as $u): ?><li class="mb-2"><?= nl2br(esc($u)) ?></li><?php endforeach; ?>
            </ol></div>
        </div>
    </div>
</div>

<div class="card shadow-sm mb-3">
    <div class="card-header fw-semibold">Pegawai yang Ditugaskan</div>
    <div class="table-responsive">
        <table class="table mb-0">
            <thead class="table-light"><tr><th>No</th><th>Nama</th><th>NIP</th><th>Pangkat/Gol</th><th>Jabatan</th></tr></thead>
            <tbody>
            <?php foreach ($spt['pegawai'] as $i => $p): ?>
                <tr>
                    <td><?= $i + 1 ?></td>
                    <td><?= esc($p['nama']) ?></td>
                    <td><?= esc($p['nip'] ?: '-') ?></td>
                    <td><?= esc($p['pangkat_golongan'] ?: '-') ?></td>
                    <td><?= esc($p['jabatan'] ?: '-') ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-md-6">
        <div class="card shadow-sm h-100">
            <div class="card-header fw-semibold">Penandatangan</div>
            <div class="card-body">
                <strong><?= esc($spt['pejabat_nama']) ?></strong><br>
                <span class="text-muted">NIP. <?= esc($spt['pejabat_nip'] ?: '-') ?></span><br>
                <?= esc($spt['pejabat_jabatan']) ?>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card shadow-sm h-100">
            <div class="card-header fw-semibold">Paraf</div>
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead class="table-light"><tr><th>Uraian</th><th>Nama</th><th>Tanggal</th></tr></thead>
                    <tbody>
                    <?php if (empty($spt['paraf'])): ?>
                        <tr><td colspan="3" class="text-muted">Belum diisi.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($spt['paraf'] as $r): ?>
                        <tr>
                            <td><?= esc($r['uraian']) ?></td>
                            <td><?= esc($r['nama']) ?><br><small class="text-muted"><?= esc($r['jabatan']) ?></small></td>
                            <td><?= $tgl($r['tanggal']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-header fw-semibold">Nota Dinas</div>
    <div class="card-body">
        <dl class="row mb-0">
            <?= $kv('Nomor nota', $spt['nota_nomor']) ?>
            <?= $kv('Dari', $spt['nota_dari']) ?>
            <?= $kv('Hal', $spt['nota_hal']) ?>
            <?= $kv('Ditandatangani oleh', $spt['nota_pengirim_nama']) ?>
        </dl>
    </div>
</div>

<?php if ($boleh['periksa']): ?>
<!-- Modal Setujui -->
<div class="modal fade" id="modal-setujui" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form class="modal-content" action="<?= site_url('spt/setujui/' . $spt['id']) ?>" method="post">
            <?= csrf_field() ?>
            <div class="modal-header"><h5 class="modal-title">Setujui SPT</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Nomor surat <span class="text-danger">*</span></label>
                    <input type="text" name="nomor" class="form-control" value="<?= esc(old('nomor', $spt['nomor'] ?? '', false)) ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label">Tanggal surat <span class="text-danger">*</span></label>
                    <input type="date" name="tanggal_surat" class="form-control"
                           value="<?= esc(old('tanggal_surat', $spt['tanggal_surat'] ?: date('Y-m-d'), false)) ?>">
                </div>
                <div class="mb-1">
                    <label class="form-label">Nomor Nota Dinas</label>
                    <input type="text" name="nota_nomor" class="form-control" value="<?= esc(old('nota_nomor', $spt['nota_nomor'] ?? '', false)) ?>">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-link" data-bs-dismiss="modal">Batal</button>
                <button class="btn btn-success">Setujui</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Tolak -->
<div class="modal fade" id="modal-tolak" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form class="modal-content" action="<?= site_url('spt/tolak/' . $spt['id']) ?>" method="post">
            <?= csrf_field() ?>
            <div class="modal-header"><h5 class="modal-title">Tolak / Minta Revisi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <label class="form-label">Catatan revisi <span class="text-danger">*</span></label>
                <textarea name="catatan_revisi" rows="4" class="form-control" placeholder="Jelaskan bagian yang perlu diperbaiki"></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-link" data-bs-dismiss="modal">Batal</button>
                <button class="btn btn-danger">Kembalikan ke Pembuat</button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>
<?= $this->endSection() ?>