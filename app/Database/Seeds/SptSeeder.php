<?php

namespace App\Database\Seeds;

use App\Models\SptModel;
use CodeIgniter\CLI\CLI;
use CodeIgniter\Database\Seeder;

class SptSeeder extends Seeder
{
    public function run()
    {
        helper('cetak');
        $db      = $this->db;
        $model   = model(SptModel::class);
        $hariIni = date('Y-m-d');

        // ---------- data acuan ----------
        $pejabat = $db->table('pejabat')->where('is_active', 1)->orderBy('id')->get()->getRowArray();
        if (! $pejabat) {
            CLI::error('Belum ada pejabat aktif. Tambahkan dulu lewat menu Pejabat.');

            return;
        }

        $pegawai = [];
        foreach ($db->table('pegawai')->get()->getResultArray() as $p) {
            $pegawai[$p['nama']] = $p;
        }

        $cariUser = static function ($db, string $username): ?array {
            return $db->table('users')->where('username', $username)->get()->getRowArray()
                ?: $db->table('users')->orderBy('id')->get()->getRowArray();
        };

        $dasarSK = 'Keputusan Kepala Balai Teknik Perkeretaapian Kelas I Medan Nomor: 00/SK/BTP-MDN/2026 '
            . 'tentang Struktur Organisasi, Tugas dan Fungsi serta Uraian Tugas dan Tanggung Jawab '
            . 'pada Balai Teknik Perkeretaapian Kelas I Medan;';

        // ---------- 5 data SPT (fiktif) ----------
        $daftar = [
            [
                'status' => 'draft', 'pembuat' => 'citra', 'mode' => 'tunggal',
                'kegiatan' => 'Mengikuti Pelatihan Penguji Sarana Perkeretaapian',
                'tempat' => 'Gedung Pelatihan Perkeretaapian, Bandung',
                'mulai' => '2026-12-07', 'selesai' => '2026-12-11',
                'pegawai' => ['Budi Santoso'],
                'dasar2' => 'Surat Direktur Keselamatan Perkeretaapian Nomor: ST-K5/DJKA/501/2026 tanggal 2 November 2026 perihal Pemanggilan Peserta Pelatihan;',
            ],
            [
                'status' => 'diajukan', 'pembuat' => 'citra', 'mode' => 'banyak',
                'kegiatan' => 'Menghadiri Rapat Koordinasi Penanganan Lokasi Rawan Longsor Lintas Medan – Rantauprapat',
                'tempat' => 'Ruang Rapat Hotel Nusantara Indah, Medan',
                'mulai' => '2026-11-10', 'selesai' => null,
                'pegawai' => ['Asep Suryana', 'Citra Lestari', 'Dedi Kurniawan'],
                'dasar2' => 'Surat Direktur Prasarana Perkeretaapian Nomor: UM.207/18/16/K3/DJKA/2026 tanggal 29 Oktober 2026 perihal Undangan Rapat;',
                'rujukan' => 'Surat Direktur Prasarana Perkeretaapian Nomor: UM.207/18/16/K3/DJKA/2026 tanggal 29 Oktober 2026 perihal Undangan Rapat',
                'nota_nomor' => 'ND-0001/2026',
            ],
            [
                'status' => 'ditolak', 'pembuat' => 'citra', 'mode' => 'tunggal',
                'kegiatan' => 'Melaksanakan Pemeriksaan Prasarana Jalur Kereta Api Stasiun Binjai – Stasiun Besitang',
                'tempat' => 'Lintas Binjai – Besitang, Sumatera Utara',
                'mulai' => '2026-11-16', 'selesai' => '2026-11-18',
                'pegawai' => ['Dedi Kurniawan'],
                'dasar2' => 'Program Kerja Pemeriksaan Prasarana Perkeretaapian Tahun Anggaran 2026;',
                'catatan' => 'Tanggal kegiatan belum sesuai jadwal pemeriksaan. Mohon cantumkan surat penugasan dari Direktorat pada bagian Dasar.',
            ],
            [
                'status' => 'disetujui', 'pembuat' => 'citra', 'mode' => 'banyak',
                'kegiatan' => 'Mengikuti Bimbingan Teknis Pemeliharaan Jalur dan Bantalan Rel',
                'tempat' => 'Hotel Nusantara Indah, Medan',
                'mulai' => '2026-11-23', 'selesai' => '2026-11-25',
                'pegawai' => ['Asep Suryana', 'Budi Santoso', 'Citra Lestari', 'Eka Putri'],
                'dasar2' => 'Surat Kepala Balai Teknik Perkeretaapian Kelas I Medan Nomor: UM.207/17/3/BTP-MDN/2026 tanggal 5 November 2026 perihal Undangan Bimbingan Teknis;',
                'rujukan' => 'Surat Kepala Balai Teknik Perkeretaapian Kelas I Medan Nomor: UM.207/17/3/BTP-MDN/2026 tanggal 5 November 2026 perihal Undangan Bimbingan Teknis',
                'nomor' => 'SPT-0001/2026', 'nota_nomor' => 'ND-0002/2026',
            ],
            [
                'status' => 'disetujui', 'pembuat' => 'asep', 'mode' => 'tunggal',
                'kegiatan' => 'Melaksanakan Survei Lokasi Rencana Pembangunan Halte Kereta Api',
                'tempat' => 'Kabupaten Deli Serdang, Sumatera Utara',
                'mulai' => '2026-11-30', 'selesai' => '2026-12-02',
                'pegawai' => ['Fajar Nugroho'],
                'dasar2' => 'Rencana Kerja Balai Teknik Perkeretaapian Kelas I Medan Tahun Anggaran 2026;',
                'nomor' => 'SPT-0002/2026', 'nota_nomor' => 'ND-0003/2026',
            ],
        ];

        // ---------- simpan ----------
        $untukStandar = [
            'Tidak memberikan atau menerima bentuk gratifikasi apapun.',
            'Melaporkan hasilnya secara tertulis kepada Kepala Balai setelah selesai melaksanakan tugas.',
            'Melaksanakan tugas sebaik-baiknya dengan penuh rasa tanggung jawab.',
        ];
        $jumlah = 0;

        foreach ($daftar as $i => $d) {
            $no = $i + 1;

            if ($db->table('spt')->where('kegiatan', $d['kegiatan'])->countAllResults() > 0) {
                CLI::write("#{$no} dilewati: SPT dengan kegiatan yang sama sudah ada.", 'yellow');
                continue;
            }

            $baris = [];
            foreach ($d['pegawai'] as $nama) {
                if (isset($pegawai[$nama])) {
                    $baris[] = $pegawai[$nama];
                }
            }
            if (count($baris) !== count($d['pegawai'])) {
                CLI::write("#{$no} dilewati: ada pegawai yang tidak ditemukan (" . implode(', ', $d['pegawai']) . ').', 'yellow');
                continue;
            }

            $user = $cariUser($db, $d['pembuat']);
            if (! $user) {
                CLI::error('Belum ada pengguna.');

                return;
            }

            $disetujui = $d['status'] === 'disetujui';
            $diajukan  = $d['status'] !== 'draft';

            $header = [
                'nomor'            => $d['nomor'] ?? null,
                'tanggal_surat'    => $disetujui ? $hariIni : null,
                'kegiatan'         => $d['kegiatan'],
                'tempat'           => $d['tempat'],
                'tanggal_mulai'    => $d['mulai'],
                'tanggal_selesai'  => $d['selesai'],
                'mode_penerima'    => $d['mode'],
                'instansi_id'      => (int) $pejabat['instansi_id'],
                'pejabat_id'       => (int) $pejabat['id'],
                'pembuat_id'       => (int) $user['id'],
                'status'           => $d['status'],
                'catatan_revisi'   => $d['catatan'] ?? null,
                'nota_nomor'       => $d['nota_nomor'] ?? null,
                'nota_dari'        => 'Kepala Seksi Prasarana',
                'nota_hal'         => 'Penyampaian Konsep Surat Tugas',
                'nota_pengirim_id' => isset($pegawai['Asep Suryana']) ? (int) $pegawai['Asep Suryana']['id'] : null,
                'nota_rujukan'     => $d['rujukan'] ?? null,
            ];

            $dasar = [$dasarSK, $d['dasar2']];

            $untuk1 = $d['kegiatan'] . ' ' . frasa_waktu($d['mulai'], $d['selesai']) . ' di ' . $d['tempat'] . '.';
            $untuk  = array_merge([$untuk1], $untukStandar);

            $pegawaiRows = [];
            foreach ($baris as $n => $p) {
                $pegawaiRows[] = [
                    'pegawai_id'       => (int) $p['id'],
                    'urutan'           => $n + 1,
                    'pangkat_golongan' => $p['pangkat_golongan'],
                    'jabatan'          => $p['jabatan'],
                ];
            }

            // Paraf: Dikonsep = pegawai pembuat (cadangan: Citra), Diperiksa = Asep
            $konsep  = $user['pegawai_id'] ?: ($pegawai['Citra Lestari']['id'] ?? null);
            $periksa = $pegawai['Asep Suryana']['id'] ?? null;
            $parafRows = [];
            if ($konsep) {
                $parafRows[] = ['uraian' => 'Dikonsep', 'pegawai_id' => (int) $konsep, 'urutan' => 1, 'tanggal' => $diajukan ? $hariIni : null];
            }
            if ($periksa) {
                $parafRows[] = ['uraian' => 'Diperiksa', 'pegawai_id' => (int) $periksa, 'urutan' => 1, 'tanggal' => $disetujui ? $hariIni : null];
            }

            $model->simpanLengkap(null, $header, $dasar, $untuk, $pegawaiRows, $parafRows);
            $jumlah++;
            CLI::write("#{$no} dibuat: [{$d['status']}] " . $d['kegiatan'], 'green');
        }

        CLI::write("Selesai. {$jumlah} SPT ditambahkan.");
    }
}