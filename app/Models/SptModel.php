<?php

namespace App\Models;

use CodeIgniter\Model;

class SptModel extends Model
{
    protected $table         = 'spt';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'nomor', 'tanggal_surat', 'kegiatan', 'tempat', 'tanggal_mulai', 'tanggal_selesai',
        'mode_penerima', 'instansi_id', 'pejabat_id', 'pembuat_id', 'status', 'catatan_revisi',
        'nota_nomor', 'nota_dari', 'nota_hal', 'nota_pengirim_id',
    ];

    /**
     * Simpan SPT beserta semua tabel anaknya dalam satu transaksi.
     * Jika $id null = buat baru; selain itu = perbarui (anak dihapus lalu ditulis ulang).
     */
    public function simpanLengkap(?int $id, array $header, array $dasar, array $untuk, array $pegawaiRows, array $parafRows): int
    {
        $db = $this->db;
        $db->transStart();

        if ($id === null) {
            $id = (int) $this->insert($header, true);
        } else {
            $this->update($id, $header);
        }

        foreach (['spt_dasar', 'spt_untuk', 'spt_pegawai', 'spt_paraf'] as $tabel) {
            $db->table($tabel)->where('spt_id', $id)->delete();
        }

        $this->isiTeks('spt_dasar', $id, $dasar);
        $this->isiTeks('spt_untuk', $id, $untuk);

        if ($pegawaiRows !== []) {
            $db->table('spt_pegawai')->insertBatch(array_map(static fn ($r) => $r + ['spt_id' => $id], $pegawaiRows));
        }
        if ($parafRows !== []) {
            $db->table('spt_paraf')->insertBatch(array_map(static fn ($r) => $r + ['spt_id' => $id], $parafRows));
        }

        $db->transComplete();
        if (! $db->transStatus()) {
            throw new \RuntimeException('Gagal menyimpan SPT.');
        }

        return $id;
    }

    /** Ambil SPT beserta dasar, untuk, pegawai, dan paraf-nya. */
    public function ambilLengkap(int $id): ?array
    {
        $spt = $this->find($id);
        if (! $spt) {
            return null;
        }

        $ambil = fn (string $tabel) => $this->db->table($tabel)->where('spt_id', $id)->orderBy('urutan', 'ASC')->get()->getResultArray();

        $spt['dasar']       = array_column($ambil('spt_dasar'), 'isi');
        $spt['untuk']       = array_column($ambil('spt_untuk'), 'isi');
        $spt['pegawai_ids'] = array_column($ambil('spt_pegawai'), 'pegawai_id');

        $spt['dikonsep_id']   = null;
        $spt['diperiksa_ids'] = [];
        foreach ($ambil('spt_paraf') as $p) {
            if ($p['uraian'] === 'Dikonsep') {
                $spt['dikonsep_id'] = $p['pegawai_id'];
            } else {
                $spt['diperiksa_ids'][] = $p['pegawai_id'];
            }
        }

        return $spt;
    }

    private function isiTeks(string $tabel, int $id, array $daftar): void
    {
        $rows = [];
        foreach (array_values($daftar) as $i => $isi) {
            $rows[] = ['spt_id' => $id, 'urutan' => $i + 1, 'isi' => $isi];
        }
        if ($rows !== []) {
            $this->db->table($tabel)->insertBatch($rows);
        }
    }

        /** Data lengkap untuk halaman detail (sudah digabung dengan nama pejabat, instansi, dll). */
    public function ambilDetail(int $id): ?array
    {
        $spt = $this
            ->select('spt.*, pj.nama AS pejabat_nama, pj.nip AS pejabat_nip, pj.jabatan AS pejabat_jabatan, '
                . 'i.nama AS instansi_nama, i.kota AS instansi_kota, i.kop_file, i.footer_file, '
                . 'COALESCE(pg.nama, u.username) AS pembuat, np.nama AS nota_pengirim_nama, np.nip AS nota_pengirim_nip', false)
            ->join('pejabat pj', 'pj.id = spt.pejabat_id')
            ->join('instansi i', 'i.id = spt.instansi_id')
            ->join('users u', 'u.id = spt.pembuat_id')
            ->join('pegawai pg', 'pg.id = u.pegawai_id', 'left')
            ->join('pegawai np', 'np.id = spt.nota_pengirim_id', 'left')
            ->where('spt.id', $id)
            ->first();

        if (! $spt) {
            return null;
        }

        $teks = fn (string $tabel) => array_column(
            $this->db->table($tabel)->where('spt_id', $id)->orderBy('urutan', 'ASC')->get()->getResultArray(),
            'isi'
        );

        $spt['dasar'] = $teks('spt_dasar');
        $spt['untuk'] = $teks('spt_untuk');

        $spt['pegawai'] = $this->db->table('spt_pegawai sp')
            ->select('sp.urutan, sp.pangkat_golongan, sp.jabatan, p.nama, p.nip')
            ->join('pegawai p', 'p.id = sp.pegawai_id')
            ->where('sp.spt_id', $id)->orderBy('sp.urutan', 'ASC')
            ->get()->getResultArray();

        $spt['paraf'] = $this->db->table('spt_paraf sr')
            ->select('sr.uraian, sr.tanggal, p.nama, p.jabatan')
            ->join('pegawai p', 'p.id = sr.pegawai_id')
            ->where('sr.spt_id', $id)->orderBy('sr.uraian', 'ASC')->orderBy('sr.urutan', 'ASC')
            ->get()->getResultArray();

        return $spt;
    }

    /** Ubah status (dan kolom terkait) sekaligus mengisi tanggal paraf, dalam satu transaksi. */
    public function ubahStatus(int $id, array $data, ?string $parafUraian = null, ?string $parafTanggal = null): void
    {
        $this->db->transStart();

        $this->update($id, $data);
        if ($parafUraian !== null) {
            $this->db->table('spt_paraf')
                ->where('spt_id', $id)->where('uraian', $parafUraian)
                ->update(['tanggal' => $parafTanggal]);
        }

        $this->db->transComplete();
        if (! $this->db->transStatus()) {
            throw new \RuntimeException('Gagal mengubah status SPT.');
        }
    }
}