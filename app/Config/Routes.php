<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->addRedirect('/', 'dashboard');

$routes->get('login', 'Auth::login');
$routes->post('login', 'Auth::attempt');
$routes->get('logout', 'Auth::logout');

// Semua yang sudah login
$routes->group('', ['filter' => 'auth'], static function ($routes) {
    $routes->get('dashboard', 'Dashboard::index');
});

// Data Pegawai: admin & superuser
$routes->group('pegawai', ['filter' => 'auth:admin,superuser'], static function ($routes) {
    $routes->get('/', 'Pegawai::index');
    $routes->get('baru', 'Pegawai::baru');
    $routes->post('simpan', 'Pegawai::simpan');
    $routes->get('ubah/(:num)', 'Pegawai::ubah/$1');
    $routes->post('perbarui/(:num)', 'Pegawai::perbarui/$1');
    $routes->post('hapus/(:num)', 'Pegawai::hapus/$1');
});

// Instansi & Pejabat: khusus super user
$routes->group('instansi', ['filter' => 'auth:superuser'], static function ($routes) {
    $routes->get('/', 'Instansi::index');
    $routes->get('baru', 'Instansi::baru');
    $routes->post('simpan', 'Instansi::simpan');
    $routes->get('ubah/(:num)', 'Instansi::ubah/$1');
    $routes->post('perbarui/(:num)', 'Instansi::perbarui/$1');
    $routes->post('hapus/(:num)', 'Instansi::hapus/$1');
    $routes->get('gambar/(:num)/(:segment)', 'Instansi::gambar/$1/$2');
});

$routes->group('pejabat', ['filter' => 'auth:superuser'], static function ($routes) {
    $routes->get('/', 'Pejabat::index');
    $routes->get('baru', 'Pejabat::baru');
    $routes->post('simpan', 'Pejabat::simpan');
    $routes->get('ubah/(:num)', 'Pejabat::ubah/$1');
    $routes->post('perbarui/(:num)', 'Pejabat::perbarui/$1');
    $routes->post('hapus/(:num)', 'Pejabat::hapus/$1');
});

// Surat Tugas: semua role login (hak akses detail dicek di controller)
$routes->group('spt', ['filter' => 'auth'], static function ($routes) {
    $routes->get('/', 'Spt::index');
    $routes->get('baru', 'Spt::baru');
    $routes->post('simpan', 'Spt::simpan');
    $routes->get('ubah/(:num)', 'Spt::ubah/$1');
    $routes->post('perbarui/(:num)', 'Spt::perbarui/$1');
    $routes->post('hapus/(:num)', 'Spt::hapus/$1');

    $routes->get('lihat/(:num)', 'Spt::lihat/$1');
    $routes->post('ajukan/(:num)', 'Spt::ajukan/$1');
    $routes->post('setujui/(:num)', 'Spt::setujui/$1');
    $routes->post('tolak/(:num)', 'Spt::tolak/$1');
    $routes->post('buka-kembali/(:num)', 'Spt::bukaKembali/$1');

    $routes->get('cetak/(:num)/(:segment)', 'Spt::cetak/$1/$2');
});

// Akun sendiri (semua role)
$routes->get('akun/password', 'Akun::password', ['filter' => 'auth']);
$routes->post('akun/password', 'Akun::ubahPassword', ['filter' => 'auth']);

// Pengguna: khusus super user
$routes->group('pengguna', ['filter' => 'auth:superuser'], static function ($routes) {
    $routes->get('/', 'Pengguna::index');
    $routes->get('baru', 'Pengguna::baru');
    $routes->post('simpan', 'Pengguna::simpan');
    $routes->get('ubah/(:num)', 'Pengguna::ubah/$1');
    $routes->post('perbarui/(:num)', 'Pengguna::perbarui/$1');
    $routes->post('hapus/(:num)', 'Pengguna::hapus/$1');
});