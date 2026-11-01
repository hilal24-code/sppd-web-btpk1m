<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class AwalSeeder extends Seeder
{
    public function run()
    {
        $now = date('Y-m-d H:i:s');

        // 1. Instansi
        $this->db->table('instansi')->insert([
            'nama'       => 'Balai Teknik Perkeretaapian Kelas I Medan',
            'kota'       => 'Medan',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $instansiId = $this->db->insertID();

        // 2. Pejabat penandatangan (data fiktif)
        $this->db->table('pejabat')->insert([
            'instansi_id' => $instansiId,
            'nama'        => 'Bambang Hartono, S.T., M.T.',
            'nip'         => '19700315 199503 1 001',
            'jabatan'     => 'Kepala Balai Teknik Perkeretaapian Kelas I Medan',
            'is_active'   => 1,
            'created_at'  => $now,
            'updated_at'  => $now,
        ]);

        // 3. Pegawai (semua nama dan NIP fiktif)
        $pegawai = [
            ['nama' => 'Asep Suryana',   'nip' => '19800101 200501 1 001', 'pangkat_golongan' => 'Penata Tk. I (III/d)',  'jabatan' => 'Kepala Seksi Prasarana',  'seksi' => 'Prasarana'],
            ['nama' => 'Budi Santoso',   'nip' => '19780512 200312 1 002', 'pangkat_golongan' => 'Penata Tk. I (III/d)',  'jabatan' => 'Kasubbag Tata Usaha',     'seksi' => 'Tata Usaha'],
            ['nama' => 'Citra Lestari',  'nip' => '19970202 202204 2 001', 'pangkat_golongan' => 'Penata Muda (III/a)',   'jabatan' => 'Staf Seksi Prasarana',    'seksi' => 'Prasarana'],
            ['nama' => 'Dedi Kurniawan', 'nip' => '19810920 200604 1 003', 'pangkat_golongan' => 'Pembina (IV/a)',         'jabatan' => 'Kepala Seksi LLAKA',      'seksi' => 'LLAKA'],
            ['nama' => 'Eka Putri',      'nip' => null,                    'pangkat_golongan' => null,                     'jabatan' => 'Staf Seksi LLAKA',        'seksi' => 'LLAKA'],
            ['nama' => 'Fajar Nugroho',  'nip' => null,                    'pangkat_golongan' => null,                     'jabatan' => 'Staf Tata Usaha',         'seksi' => 'Tata Usaha'],
        ];

        $ids = [];
        foreach ($pegawai as $p) {
            $p['is_active']  = 1;
            $p['created_at'] = $now;
            $p['updated_at'] = $now;
            $this->db->table('pegawai')->insert($p);
            $ids[$p['nama']] = $this->db->insertID();
        }

        // 4. Akun login (password sementara untuk development: password123)
        $hash = password_hash('password123', PASSWORD_DEFAULT);

        $this->db->table('users')->insertBatch([
            ['pegawai_id' => null,                    'username' => 'superuser', 'password' => $hash, 'role' => 'superuser', 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['pegawai_id' => $ids['Asep Suryana'],    'username' => 'asep',      'password' => $hash, 'role' => 'admin',     'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['pegawai_id' => $ids['Citra Lestari'],   'username' => 'citra',     'password' => $hash, 'role' => 'user',      'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
        ]);
    }
}