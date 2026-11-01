<?php

namespace App\Controllers;

class Dashboard extends BaseController
{
    public function index()
    {
        $db   = db_connect();
        $role = session('role');
        $uid  = (int) session('user_id');

        // Hitung SPT per status (user biasa: hanya miliknya)
        $hitung = $db->table('spt')->select('status, COUNT(*) AS jml')->groupBy('status');
        if ($role === 'user') {
            $hitung->where('pembuat_id', $uid);
        }
        $jumlah = array_merge(
            ['draft' => 0, 'diajukan' => 0, 'disetujui' => 0, 'ditolak' => 0],
            array_map('intval', array_column($hitung->get()->getResultArray(), 'jml', 'status'))
        );

        // 5 SPT terbaru
        $terbaru = $db->table('spt')
            ->select('id, nomor, kegiatan, status, tanggal_mulai')
            ->orderBy('id', 'DESC')->limit(5);
        if ($role === 'user') {
            $terbaru->where('pembuat_id', $uid);
        }

        return view('dashboard/index', [
            'title'   => 'Dashboard',
            'jumlah'  => $jumlah,
            'terbaru' => $terbaru->get()->getResultArray(),
        ]);
    }
}