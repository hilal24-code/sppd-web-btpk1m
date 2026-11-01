<?php

namespace App\Controllers;

use App\Models\PegawaiModel;
use App\Models\UserModel;
use CodeIgniter\Database\Exceptions\DatabaseException;

class Pengguna extends BaseController
{
    private UserModel $model;

    public function __construct()
    {
        $this->model = model(UserModel::class);
    }

    public function index()
    {
        $daftar = $this->model
            ->select('users.*, pegawai.nama AS pegawai_nama')
            ->join('pegawai', 'pegawai.id = users.pegawai_id', 'left')
            ->orderBy('users.role', 'DESC') // urutan ENUM: superuser, admin, user
            ->orderBy('users.username', 'ASC')
            ->findAll();

        return view('pengguna/index', ['title' => 'Pengguna', 'daftar' => $daftar]);
    }

    public function baru()
    {
        return view('pengguna/form', [
            'title'    => 'Tambah Pengguna',
            'pengguna' => null,
            'pegawai'  => model(PegawaiModel::class)->orderBy('nama', 'ASC')->findAll(),
            'action'   => site_url('pengguna/simpan'),
        ]);
    }

    public function simpan()
    {
        if (! $this->validate($this->rules())) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data             = $this->dataDariForm();
        $data['password'] = password_hash((string) $this->request->getPost('password'), PASSWORD_DEFAULT);
        $this->model->insert($data);

        return redirect()->to('/pengguna')->with('success', 'Pengguna berhasil ditambahkan.');
    }

    public function ubah(int $id)
    {
        $pengguna = $this->model->find($id);
        if (! $pengguna) {
            return redirect()->to('/pengguna')->with('error', 'Pengguna tidak ditemukan.');
        }

        return view('pengguna/form', [
            'title'    => 'Ubah Pengguna',
            'pengguna' => $pengguna,
            'pegawai'  => model(PegawaiModel::class)->orderBy('nama', 'ASC')->findAll(),
            'action'   => site_url('pengguna/perbarui/' . $id),
        ]);
    }

    public function perbarui(int $id)
    {
        $lama = $this->model->find($id);
        if (! $lama) {
            return redirect()->to('/pengguna')->with('error', 'Pengguna tidak ditemukan.');
        }

        if (! $this->validate($this->rules($id))) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = $this->dataDariForm();

        // Akun sendiri: peran dan status dikunci
        if ($id === (int) session('user_id')) {
            $data['role']      = $lama['role'];
            $data['is_active'] = 1;
        }

        $passwordBaru = (string) $this->request->getPost('password');
        if ($passwordBaru !== '') {
            $data['password'] = password_hash($passwordBaru, PASSWORD_DEFAULT);
        }

        $this->model->update($id, $data);

        return redirect()->to('/pengguna')->with('success', 'Data pengguna berhasil diperbarui.');
    }

    public function hapus(int $id)
    {
        if ($id === (int) session('user_id')) {
            return redirect()->to('/pengguna')->with('error', 'Anda tidak bisa menghapus akun yang sedang dipakai.');
        }

        try {
            $this->model->delete($id);
        } catch (DatabaseException $e) {
            return redirect()->to('/pengguna')->with('error', 'Pengguna ini sudah pernah membuat SPT, jadi tidak bisa dihapus. Nonaktifkan saja lewat menu Ubah.');
        }

        return redirect()->to('/pengguna')->with('success', 'Pengguna berhasil dihapus.');
    }

    // ---------- helper ----------

    private function rules(?int $id = null): array
    {
        $unikUsername = $id ? "is_unique[users.username,id,{$id}]" : 'is_unique[users.username]';
        $unikPegawai  = $id ? "is_unique[users.pegawai_id,id,{$id}]" : 'is_unique[users.pegawai_id]';

        return [
            'username' => [
                'label' => 'Username',
                'rules' => "required|min_length[3]|max_length[50]|alpha_dash|{$unikUsername}",
            ],
            'role' => [
                'label' => 'Peran',
                'rules' => 'required|in_list[user,admin,superuser]',
            ],
            'pegawai_id' => [
                'label' => 'Pegawai',
                'rules' => "permit_empty|is_not_unique[pegawai.id]|{$unikPegawai}",
            ],
            'password' => [
                'label' => 'Password',
                'rules' => $id === null ? 'required|min_length[8]' : 'permit_empty|min_length[8]',
            ],
            'password_konfirmasi' => [
                'label' => 'Konfirmasi password',
                'rules' => $id === null ? 'required|matches[password]' : 'matches[password]',
            ],
        ];
    }

    private function dataDariForm(): array
    {
        $post = $this->request->getPost();
        $pid  = (int) ($post['pegawai_id'] ?? 0);

        return [
            'username'   => trim((string) ($post['username'] ?? '')),
            'role'       => (string) ($post['role'] ?? 'user'),
            'pegawai_id' => $pid > 0 ? $pid : null,
            'is_active'  => isset($post['is_active']) ? 1 : 0,
        ];
    }
}