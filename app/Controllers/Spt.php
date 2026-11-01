<?php

namespace App\Controllers;

use App\Models\PegawaiModel;
use App\Models\PejabatModel;
use Dompdf\Dompdf;
use Dompdf\Options;
use App\Models\SptModel;

class Spt extends BaseController
{
    private SptModel $model;

    // Isi awal butir "Untuk" pada SPT baru (butir 1 = kegiatan, diisi sendiri).
    private const UNTUK_DEFAULT = [
        '',
        'Tidak memberikan atau menerima bentuk gratifikasi apapun.',
        'Melaporkan hasilnya secara tertulis kepada Kepala Balai setelah selesai melaksanakan tugas.',
        'Melaksanakan tugas sebaik-baiknya dengan penuh rasa tanggung jawab.',
    ];

    public function __construct()
    {
        $this->model = model(SptModel::class);
    }

    // ---------- daftar ----------

    public function index()
    {
        $q      = trim((string) $this->request->getGet('q'));
        $status = (string) $this->request->getGet('status');

        $this->model
            ->select('spt.*, COALESCE(pg.nama, u.username) AS pembuat, '
                . '(SELECT COUNT(*) FROM spt_pegawai sp WHERE sp.spt_id = spt.id) AS jml_pegawai', false)
            ->join('users u', 'u.id = spt.pembuat_id')
            ->join('pegawai pg', 'pg.id = u.pegawai_id', 'left');

        if (session('role') === 'user') {
            $this->model->where('spt.pembuat_id', (int) session('user_id'));
        }
        if ($q !== '') {
            $this->model->groupStart()->like('spt.kegiatan', $q)->orLike('spt.nomor', $q)->groupEnd();
        }
        if (in_array($status, ['draft', 'diajukan', 'disetujui', 'ditolak'], true)) {
            $this->model->where('spt.status', $status);
        }

        $daftar = $this->model->orderBy('spt.id', 'DESC')->paginate(10);
        foreach ($daftar as &$row) {
            $row['boleh_ubah'] = $this->bolehUbah($row);
        }
        unset($row);

        return view('spt/index', [
            'title'  => 'Surat Tugas',
            'daftar' => $daftar,
            'pager'  => $this->model->pager,
            'q'      => $q,
            'status' => $status,
        ]);
    }

    // ---------- buat / ubah ----------

    public function baru()
    {
        return view('spt/form', $this->dataForm(null) + [
            'title'  => 'Buat Surat Tugas',
            'action' => site_url('spt/simpan'),
        ]);
    }

    public function simpan()
    {
        return $this->proses(null);
    }

    public function ubah(int $id)
    {
        $spt = $this->ambil($id);
        if (! $spt || ! $this->bolehUbah($spt)) {
            return redirect()->to('/spt')->with('error', 'SPT tidak ditemukan atau tidak dapat diubah.');
        }

        return view('spt/form', $this->dataForm($spt) + [
            'title'  => 'Ubah Surat Tugas',
            'action' => site_url('spt/perbarui/' . $id),
        ]);
    }

    public function perbarui(int $id)
    {
        $lama = $this->ambil($id);
        if (! $lama || ! $this->bolehUbah($lama)) {
            return redirect()->to('/spt')->with('error', 'SPT tidak ditemukan atau tidak dapat diubah.');
        }

        return $this->proses($lama);
    }

    public function hapus(int $id)
    {
        $spt = $this->ambil($id);
        if (! $spt || ! $this->bolehUbah($spt)) {
            return redirect()->to('/spt')->with('error', 'SPT tidak ditemukan atau tidak dapat dihapus.');
        }

        $this->model->delete($id); // tabel anak ikut terhapus (ON DELETE CASCADE)

        return redirect()->to('/spt')->with('success', 'SPT berhasil dihapus.');
    }

    // ---------- inti: validasi dan simpan ----------

    private function proses(?array $lama)
    {
        $rules = [
            'kegiatan'         => 'required|max_length[1000]',
            'tempat'           => 'permit_empty|max_length[255]',
            'tanggal_mulai'    => 'required|valid_date[Y-m-d]',
            'tanggal_selesai'  => 'permit_empty|valid_date[Y-m-d]',
            'tanggal_surat'    => 'permit_empty|valid_date[Y-m-d]',
            'mode_penerima'    => 'required|in_list[tunggal,banyak]',
            'pejabat_id'       => 'required|is_not_unique[pejabat.id]',
            'dikonsep_id'      => 'permit_empty|is_not_unique[pegawai.id]',
            'nota_dari'        => 'permit_empty|max_length[150]',
            'nota_hal'         => 'permit_empty|max_length[150]',
            'nota_pengirim_id' => 'permit_empty|is_not_unique[pegawai.id]',
            'nota_rujukan'     => 'permit_empty|max_length[1000]',
        ];

        $errors = $this->validate($rules) ? [] : $this->validator->getErrors();

        $dasar        = $this->daftarTeks('dasar');
        $untuk        = $this->daftarTeks('untuk');
        $pegawaiIds   = $this->daftarId('pegawai_ids');
        $diperiksaIds = $this->daftarId('diperiksa');
        $mode         = (string) $this->request->getPost('mode_penerima');
        $mulai        = $this->nullJikaKosong('tanggal_mulai');
        $selesai      = $this->nullJikaKosong('tanggal_selesai');

        if ($dasar === []) {
            $errors['dasar'] = 'Minimal satu butir "Dasar" harus diisi.';
        }
        if ($untuk === []) {
            $errors['untuk'] = 'Minimal satu butir "Untuk" harus diisi.';
        }
        if ($mode === 'tunggal' && count($pegawaiIds) !== 1) {
            $errors['pegawai'] = 'Mode satu pegawai: pilih tepat 1 pegawai.';
        }
        if ($mode === 'banyak' && count($pegawaiIds) < 2) {
            $errors['pegawai'] = 'Mode banyak pegawai: pilih minimal 2 pegawai.';
        }
        if ($mulai && $selesai && ! isset($errors['tanggal_mulai'], $errors['tanggal_selesai']) && $selesai < $mulai) {
            $errors['tanggal_selesai'] = 'Tanggal selesai tidak boleh sebelum tanggal mulai.';
        }

        // Pegawai penerima tugas: harus ada, dan yang nonaktif hanya boleh bila sudah tercatat sebelumnya
        $pegawaiMap = [];
        if ($pegawaiIds !== []) {
            foreach (model(PegawaiModel::class)->whereIn('id', $pegawaiIds)->findAll() as $p) {
                $pegawaiMap[(int) $p['id']] = $p;
            }
            $lamaIds = array_map('intval', $lama['pegawai_ids'] ?? []);
            foreach ($pegawaiIds as $pid) {
                if (! isset($pegawaiMap[$pid])) {
                    $errors['pegawai'] = 'Ada pegawai yang tidak valid.';
                } elseif (! $pegawaiMap[$pid]['is_active'] && ! in_array($pid, $lamaIds, true)) {
                    $errors['pegawai'] = $pegawaiMap[$pid]['nama'] . ' berstatus nonaktif.';
                }
            }
        }

        // Pejabat penandatangan: harus aktif (kecuali tidak diubah dari SPT lama)
        $pejabat = model(PejabatModel::class)->find((int) $this->request->getPost('pejabat_id'));
        if ($pejabat && ! $pejabat['is_active'] && (int) ($lama['pejabat_id'] ?? 0) !== (int) $pejabat['id']) {
            $errors['pejabat_id'] = 'Pejabat penandatangan berstatus nonaktif.';
        }

        if ($errors !== [] || ! $pejabat) {
            return redirect()->back()->withInput()->with('errors', $errors ?: ['pejabat_id' => 'Pilih pejabat penandatangan.']);
        }

        $header = [
            'tanggal_surat'    => $this->nullJikaKosong('tanggal_surat'),
            'kegiatan'         => trim((string) $this->request->getPost('kegiatan')),
            'tempat'           => $this->nullJikaKosong('tempat'),
            'tanggal_mulai'    => $mulai,
            'tanggal_selesai'  => $selesai,
            'mode_penerima'    => $mode,
            'instansi_id'      => (int) $pejabat['instansi_id'],
            'pejabat_id'       => (int) $pejabat['id'],
            'nota_dari'        => $this->nullJikaKosong('nota_dari'),
            'nota_hal'         => $this->nullJikaKosong('nota_hal') ?? 'Penyampaian Konsep Surat Tugas',
            'nota_pengirim_id' => $this->nullJikaKosong('nota_pengirim_id'),
            'nota_rujukan'     => $this->nullJikaKosong('nota_rujukan'),
        ];
        if ($lama === null) {
            $header['pembuat_id'] = (int) session('user_id');
            $header['status']     = 'draft';
        }

        // Baris spt_pegawai (dengan snapshot pangkat dan jabatan)
        $pegawaiRows = [];
        foreach ($pegawaiIds as $i => $pid) {
            $pegawaiRows[] = [
                'pegawai_id'       => $pid,
                'urutan'           => $i + 1,
                'pangkat_golongan' => $pegawaiMap[$pid]['pangkat_golongan'],
                'jabatan'          => $pegawaiMap[$pid]['jabatan'],
            ];
        }

        // Baris spt_paraf
        $parafRows = [];
        $dikonsepId = (int) $this->request->getPost('dikonsep_id');
        if ($dikonsepId > 0) {
            $parafRows[] = ['uraian' => 'Dikonsep', 'pegawai_id' => $dikonsepId, 'urutan' => 1];
        }
        foreach ($diperiksaIds as $i => $pid) {
            $parafRows[] = ['uraian' => 'Diperiksa', 'pegawai_id' => $pid, 'urutan' => $i + 1];
        }

        try {
            $this->model->simpanLengkap($lama['id'] ?? null, $header, $dasar, $untuk, $pegawaiRows, $parafRows);
        } catch (\RuntimeException $e) {
            return redirect()->back()->withInput()->with('errors', ['umum' => $e->getMessage()]);
        }

        return redirect()->to('/spt')->with('success', $lama ? 'SPT berhasil diperbarui.' : 'SPT berhasil dibuat sebagai draft.');
    }

        // ---------- detail dan alur persetujuan ----------

    public function lihat(int $id)
    {
        if (! $this->ambil($id)) { // cek hak akses (user hanya boleh miliknya)
            return redirect()->to('/spt')->with('error', 'SPT tidak ditemukan.');
        }

        $spt = $this->model->ambilDetail($id);

        return view('spt/detail', [
            'title' => 'Detail Surat Tugas',
            'spt'   => $spt,
            'boleh' => [
                'ubah'    => $this->bolehUbah($spt),
                'ajukan'  => $this->bolehAjukan($spt),
                'periksa' => $this->bolehPeriksa($spt),
                'buka'    => session('role') === 'superuser' && $spt['status'] === 'disetujui',
            ],
        ]);
    }

    public function ajukan(int $id)
    {
        $spt = $this->ambil($id);
        if (! $spt || ! $this->bolehAjukan($spt)) {
            return $this->aksesDitolak();
        }

        $this->model->ubahStatus($id, ['status' => 'diajukan', 'catatan_revisi' => null], 'Dikonsep', date('Y-m-d'));

        return redirect()->to('/spt/lihat/' . $id)->with('success', 'SPT diajukan dan menunggu pemeriksaan.');
    }

    public function setujui(int $id)
    {
        $spt = $this->ambil($id);
        if (! $spt || ! $this->bolehPeriksa($spt)) {
            return $this->aksesDitolak();
        }

        $rules = [
            'nomor'         => 'required|max_length[100]',
            'tanggal_surat' => 'required|valid_date[Y-m-d]',
            'nota_nomor'    => 'permit_empty|max_length[100]',
        ];
        $pesan = [
            'nomor'         => ['required' => 'Nomor surat wajib diisi.'],
            'tanggal_surat' => ['required' => 'Tanggal surat wajib diisi.', 'valid_date' => 'Format tanggal surat tidak valid.'],
        ];
        if (! $this->validate($rules, $pesan)) {
            return redirect()->to('/spt/lihat/' . $id)->withInput()->with('errors', $this->validator->getErrors());
        }

        $nomor   = trim((string) $this->request->getPost('nomor'));
        $dipakai = $this->model->where('nomor', $nomor)->where('id !=', $id)->countAllResults();
        if ($dipakai > 0) {
            return redirect()->to('/spt/lihat/' . $id)->withInput()
                ->with('errors', ['nomor' => 'Nomor surat "' . $nomor . '" sudah dipakai SPT lain.']);
        }

        $this->model->ubahStatus($id, [
            'status'         => 'disetujui',
            'nomor'          => $nomor,
            'tanggal_surat'  => $this->nullJikaKosong('tanggal_surat'),
            'nota_nomor'     => $this->nullJikaKosong('nota_nomor'),
            'catatan_revisi' => null,
        ], 'Diperiksa', date('Y-m-d'));

        return redirect()->to('/spt/lihat/' . $id)->with('success', 'SPT disetujui dan diberi nomor.');
    }

    public function tolak(int $id)
    {
        $spt = $this->ambil($id);
        if (! $spt || ! $this->bolehPeriksa($spt)) {
            return $this->aksesDitolak();
        }

        $rules = ['catatan_revisi' => 'required|min_length[5]|max_length[1000]'];
        $pesan = ['catatan_revisi' => [
            'required'   => 'Catatan revisi wajib diisi agar pembuat tahu apa yang diperbaiki.',
            'min_length' => 'Catatan revisi terlalu singkat (minimal 5 karakter).',
        ]];
        if (! $this->validate($rules, $pesan)) {
            return redirect()->to('/spt/lihat/' . $id)->with('errors', $this->validator->getErrors());
        }

        $this->model->ubahStatus($id, [
            'status'         => 'ditolak',
            'catatan_revisi' => trim((string) $this->request->getPost('catatan_revisi')),
        ]);

        return redirect()->to('/spt/lihat/' . $id)->with('success', 'SPT dikembalikan ke pembuat untuk direvisi.');
    }

    public function bukaKembali(int $id)
    {
        $spt = $this->ambil($id);
        if (session('role') !== 'superuser' || ! $spt || $spt['status'] !== 'disetujui') {
            return $this->aksesDitolak();
        }

        $this->model->ubahStatus($id, ['status' => 'draft']);

        return redirect()->to('/spt/lihat/' . $id)->with('success', 'SPT dikembalikan ke status draft.');
    }

    private function bolehAjukan(array $spt): bool
    {
        return in_array($spt['status'], ['draft', 'ditolak'], true) && $this->bolehUbah($spt);
    }

    private function bolehPeriksa(array $spt): bool
    {
        return in_array(session('role'), ['admin', 'superuser'], true) && $spt['status'] === 'diajukan';
    }

    private function aksesDitolak()
    {
        return redirect()->to('/spt')->with('error', 'Aksi tidak diizinkan atau SPT tidak ditemukan.');
    }

        // ---------- cetak PDF ----------

    public function cetak(int $id, string $jenis)
    {
        $spt = $this->ambil($id); // user biasa hanya boleh mencetak miliknya
        if (! $spt) {
            return $this->aksesDitolak();
        }
        if ($spt['status'] !== 'disetujui') {
            return redirect()->to('/spt/lihat/' . $id)->with('error', 'Dokumen hanya bisa dicetak setelah SPT disetujui.');
        }

        $bagian = match ($jenis) {
            'surat'    => ['surat_tugas'],
            'lampiran' => ['lampiran'],
            'nota'     => ['nota_dinas'],
            'paket'    => ['surat_tugas', 'lampiran', 'nota_dinas'],
            default    => null,
        };
        if ($bagian === null) {
            return $this->aksesDitolak();
        }

        // Lampiran hanya untuk SPT dengan banyak pegawai
        if ($spt['mode_penerima'] !== 'banyak') {
            $bagian = array_values(array_diff($bagian, ['lampiran']));
            if ($bagian === []) {
                return redirect()->to('/spt/lihat/' . $id)->with('error', 'SPT ini hanya untuk satu pegawai, jadi tidak memakai Lampiran.');
            }
        }

        helper('cetak');
        $detail               = $this->model->ambilDetail($id);
        $detail['kop_uri']    = gambar_data_uri($detail['kop_file'] ?? null); // hanya untuk pengganti tak terlihat

        $kop    = gambar_path($detail['kop_file'] ?? null);
        $footer = gambar_path($detail['footer_file'] ?? null);

        // Ruang bawah halaman disisihkan seukuran footer agar teks tidak menimpa gambar
        $marginBawah = 2.4;
        if ($footer) {
            [$fw, $fh]   = getimagesize($footer);
            $marginBawah = max(2.4, round(21.0 * $fh / $fw + 0.3, 1)); // 21 cm = lebar A4
        }

        $html = view('cetak/dokumen', [
            'spt'         => $detail,
            'bagian'      => $bagian,
            'marginBawah' => $marginBawah,
        ]);

        $options = new Options();
        $options->set('defaultFont', 'Helvetica');
        $options->set('isRemoteEnabled', false);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        // Kop dan footer digambar penuh sampai tepi kertas (tanpa margin)
        $denganKop = $bagian[0] === 'surat_tugas';
        $dompdf->getCanvas()->page_script(function (int $halaman, int $total, $canvas) use ($kop, $footer, $denganKop) {
            $lebar  = $canvas->get_width();
            $tinggi = $canvas->get_height();

            if ($kop && $denganKop && $halaman === 1) {
                [$w, $h] = getimagesize($kop);
                $canvas->image($kop, 0, 0, $lebar, $lebar * $h / $w);
            }
            if ($footer) {
                [$w, $h] = getimagesize($footer);
                $tg      = $lebar * $h / $w;
                $canvas->image($footer, 0, $tinggi - $tg, $lebar, $tg);
            }
        });

        $nama = 'SPT-' . trim(preg_replace('/[^A-Za-z0-9._-]+/', '-', (string) $spt['nomor']), '-') . '-' . $jenis . '.pdf';

        return $this->response
            ->setContentType('application/pdf')
            ->setHeader('Content-Disposition', 'inline; filename="' . $nama . '"')
            ->setBody($dompdf->output());
    }

    // ---------- helper ----------

    private function dataForm(?array $spt): array
    {
        // Pejabat: yang aktif, ditambah pejabat yang sedang terpilih di SPT ini
        $pb = db_connect()->table('pejabat p')
            ->select('p.id, p.instansi_id, p.nama, p.jabatan, i.nama AS instansi')
            ->join('instansi i', 'i.id = p.instansi_id')
            ->groupStart()->where('p.is_active', 1);
        if ($spt) {
            $pb->orWhere('p.id', $spt['pejabat_id']);
        }
        $pejabat = $pb->groupEnd()->orderBy('i.nama')->orderBy('p.nama')->get()->getResultArray();

        return [
            'spt'          => $spt,
            'pegawai'      => model(PegawaiModel::class)->orderBy('nama', 'ASC')->findAll(),
            'pejabat'      => $pejabat,
            'untukDefault' => self::UNTUK_DEFAULT,
            'instansi'     => db_connect()->table('instansi')->select('id, nama, kop_file')->orderBy('nama', 'ASC')->get()->getResultArray(),
        ];
    }

    /** Ambil SPT lengkap; user biasa hanya boleh mengakses miliknya. */
    private function ambil(int $id): ?array
    {
        $spt = $this->model->ambilLengkap($id);
        if (! $spt) {
            return null;
        }
        if (session('role') === 'user' && (int) $spt['pembuat_id'] !== (int) session('user_id')) {
            return null;
        }

        return $spt;
    }

    private function bolehUbah(array $spt): bool
    {
        if (session('role') === 'user') {
            return (int) $spt['pembuat_id'] === (int) session('user_id')
                && in_array($spt['status'], ['draft', 'ditolak'], true);
        }

        return $spt['status'] !== 'disetujui'; // admin & superuser
    }

    private function daftarTeks(string $field): array
    {
        $nilai = $this->request->getPost($field);
        $hasil = [];
        foreach (is_array($nilai) ? $nilai : [] as $v) {
            if (is_string($v) && trim($v) !== '') {
                $hasil[] = trim($v);
            }
        }

        return $hasil;
    }

    private function daftarId(string $field): array
    {
        $nilai = $this->request->getPost($field);
        $ids   = [];
        foreach (is_array($nilai) ? $nilai : [] as $v) {
            if (is_scalar($v) && ctype_digit((string) $v) && (int) $v > 0) {
                $ids[] = (int) $v;
            }
        }

        return array_values(array_unique($ids));
    }

    private function nullJikaKosong(string $field): ?string
    {
        $v = trim((string) $this->request->getPost($field));

        return $v === '' ? null : $v;
    }
}