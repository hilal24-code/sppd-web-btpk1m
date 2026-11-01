<?php

namespace App\Controllers;

use App\Models\InstansiModel;
use App\Models\PejabatModel;
use CodeIgniter\Database\Exceptions\DatabaseException;

class Pejabat extends BaseController
{
    private PejabatModel $model;

    private array $rules = [
        'instansi_id' => 'required|is_not_unique[instansi.id]',
        'nama'        => 'required|max_length[150]',
        'nip'         => 'permit_empty|max_length[30]',
        'jabatan'     => 'required|max_length[150]',
    ];

    public function __construct()
    {
        $this->model = model(PejabatModel::class);
    }

    public function index()
    {
        $daftar = $this->model
            ->select('pejabat.*, instansi.nama AS instansi_nama')
            ->join('instansi', 'instansi.id = pejabat.instansi_id')
            ->orderBy('pejabat.is_active', 'DESC')
            ->orderBy('pejabat.nama', 'ASC')
            ->findAll();

        return view('pejabat/index', ['title' => 'Pejabat Penandatangan', 'daftar' => $daftar]);
    }

    public function baru()
    {
        return view('pejabat/form', [
            'title'    => 'Tambah Pejabat',
            'pejabat'  => null,
            'instansi' => model(InstansiModel::class)->orderBy('nama', 'ASC')->findAll(),
            'action'   => site_url('pejabat/simpan'),
        ]);
    }

    public function simpan()
    {
        if (! $this->validate($this->rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $this->model->insert($this->dataDariForm());

        return redirect()->to('/pejabat')->with('success', 'Pejabat berhasil ditambahkan.');
    }

    public function ubah(int $id)
    {
        $pejabat = $this->model->find($id);
        if (! $pejabat) {
            return redirect()->to('/pejabat')->with('error', 'Data pejabat tidak ditemukan.');
        }

        return view('pejabat/form', [
            'title'    => 'Ubah Pejabat',
            'pejabat'  => $pejabat,
            'instansi' => model(InstansiModel::class)->orderBy('nama', 'ASC')->findAll(),
            'action'   => site_url('pejabat/perbarui/' . $id),
        ]);
    }

    public function perbarui(int $id)
    {
        if (! $this->model->find($id)) {
            return redirect()->to('/pejabat')->with('error', 'Data pejabat tidak ditemukan.');
        }

        if (! $this->validate($this->rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $this->model->update($id, $this->dataDariForm());

        return redirect()->to('/pejabat')->with('success', 'Data pejabat berhasil diperbarui.');
    }

    public function hapus(int $id)
    {
        try {
            $this->model->delete($id);
        } catch (DatabaseException $e) {
            return redirect()->to('/pejabat')->with('error', 'Pejabat tidak bisa dihapus karena sudah dipakai di SPT. Nonaktifkan saja lewat menu Ubah.');
        }

        return redirect()->to('/pejabat')->with('success', 'Pejabat berhasil dihapus.');
    }

    private function dataDariForm(): array
    {
        $post = $this->request->getPost();
        $nip  = trim((string) ($post['nip'] ?? ''));

        return [
            'instansi_id' => (int) ($post['instansi_id'] ?? 0),
            'nama'        => trim((string) ($post['nama'] ?? '')),
            'nip'         => $nip === '' ? null : $nip,
            'jabatan'     => trim((string) ($post['jabatan'] ?? '')),
            'is_active'   => isset($post['is_active']) ? 1 : 0,
        ];
    }
}