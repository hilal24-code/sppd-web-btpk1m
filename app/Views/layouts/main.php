<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= esc($title ?? 'Aplikasi Surat Tugas') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<?php $role = session()->get('role'); ?>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="<?= site_url('dashboard') ?>">Surat Tugas BTP</a>
        <ul class="navbar-nav me-auto">
            <li class="nav-item"><a class="nav-link" href="<?= site_url('dashboard') ?>">Dashboard</a></li>
            <li class="nav-item"><a class="nav-link" href="<?= site_url('spt') ?>">Surat Tugas</a></li>

            <?php if (in_array($role, ['admin', 'superuser'], true)): ?>
                <li class="nav-item"><a class="nav-link" href="<?= site_url('spt?status=diajukan') ?>">Pemeriksaan SPT</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= site_url('pegawai') ?>">Data Pegawai</a></li>
            <?php endif; ?>

            <?php if ($role === 'superuser'): ?>
                <li class="nav-item"><a class="nav-link" href="<?= site_url('pengguna') ?>">Pengguna</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= site_url('instansi') ?>">Instansi</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= site_url('pejabat') ?>">Pejabat</a></li>
            <?php endif; ?>
        </ul>
        <span class="navbar-text me-3">
            <?= esc(session()->get('nama')) ?> <span class="badge text-bg-secondary"><?= esc($role) ?></span>
        </span>
        <a class="btn btn-outline-light btn-sm me-2" href="<?= site_url('akun/password') ?>">Ganti Password</a>
        <a class="btn btn-outline-light btn-sm" href="<?= site_url('logout') ?>">Keluar</a>
    </div>
</nav>

<div class="container py-4">
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger"><?= esc(session()->getFlashdata('error')) ?></div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success"><?= esc(session()->getFlashdata('success')) ?></div>
    <?php endif; ?>
    <?= $this->renderSection('content') ?>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>