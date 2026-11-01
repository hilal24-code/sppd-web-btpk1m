<?php

namespace App\Controllers;

use App\Models\PegawaiModel;
use CodeIgniter\Database\Exceptions\DatabaseException;

class Pegawai extends BaseController
{
    private PegawaiModel $model;

    public function __construct()
    {
        $this->model = model(PegawaiModel::class);
    }

    // Daftar + pencarian + halaman
    public function index()
    {
        $q = trim((string) $this->request->getGet('q'));

        if ($q !== '') {
            $this->model->groupStart()
                ->like('nama', $q)
                ->orLike('nip', $q)
                ->orLike('jabatan', $q)
                ->groupEnd();
        }

        return view('pegawai/index', [
            'title'   => 'Data Pegawai',
            'pegawai' => $this->model->orderBy('nama', 'ASC')->paginate(10),
            'pager'   => $this->model->pager,
            'q'       => $q,
        ]);
    }

    // Form tambah
    public function baru()
    {
        return view('pegawai/form', [
            'title'   => 'Tambah Pegawai',
            'pegawai' => null,
            'action'  => site_url('pegawai/simpan'),
        ]);
    }

    public function simpan()
    {
        if (! $this->validate($this->rules())) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $this->model->insert($this->dataDariForm());

        return redirect()->to('/pegawai')->with('success', 'Pegawai berhasil ditambahkan.');
    }

    // Form ubah
    public function ubah(int $id)
    {
        $pegawai = $this->model->find($id);
        if (! $pegawai) {
            return redirect()->to('/pegawai')->with('error', 'Data pegawai tidak ditemukan.');
        }

        return view('pegawai/form', [
            'title'   => 'Ubah Pegawai',
            'pegawai' => $pegawai,
            'action'  => site_url('pegawai/perbarui/' . $id),
        ]);
    }

    public function perbarui(int $id)
    {
        if (! $this->model->find($id)) {
            return redirect()->to('/pegawai')->with('error', 'Data pegawai tidak ditemukan.');
        }

        if (! $this->validate($this->rules($id))) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $this->model->update($id, $this->dataDariForm());

        return redirect()->to('/pegawai')->with('success', 'Data pegawai berhasil diperbarui.');
    }

    public function hapus(int $id)
    {
        try {
            $this->model->delete($id);
        } catch (DatabaseException $e) {
            return redirect()->to('/pegawai')->with(
                'error',
                'Pegawai tidak bisa dihapus karena sudah tercatat di SPT. Nonaktifkan saja lewat menu Ubah.'
            );
        }

        return redirect()->to('/pegawai')->with('success', 'Pegawai berhasil dihapus.');
    }

    // ---------- helper ----------

    private function rules(?int $id = null): array
    {
        $nipUnik = $id ? "is_unique[pegawai.nip,id,{$id}]" : 'is_unique[pegawai.nip]';

        return [
            'nama'             => 'required|max_length[150]',
            'nip'              => "permit_empty|max_length[30]|{$nipUnik}",
            'pangkat_golongan' => 'permit_empty|max_length[80]',
            'jabatan'          => 'required|max_length[150]',
            'seksi'            => 'permit_empty|max_length[100]',
        ];
    }

    private function dataDariForm(): array
    {
        $post = $this->request->getPost();
        $nullJikaKosong = static fn ($v) => ($v === null || trim($v) === '') ? null : trim($v);

        return [
            'nama'             => trim($post['nama'] ?? ''),
            'nip'              => $nullJikaKosong($post['nip'] ?? null),
            'pangkat_golongan' => $nullJikaKosong($post['pangkat_golongan'] ?? null),
            'jabatan'          => trim($post['jabatan'] ?? ''),
            'seksi'            => $nullJikaKosong($post['seksi'] ?? null),
            'is_active'        => isset($post['is_active']) ? 1 : 0,
        ];
    }
}