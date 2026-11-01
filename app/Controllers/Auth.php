<?php

namespace App\Controllers;

use App\Models\UserModel;

class Auth extends BaseController
{
    public function login()
    {
        if (session()->get('isLoggedIn')) {
            return redirect()->to('/dashboard');
        }
        return view('auth/login');
    }

    public function attempt()
    {
        $rules = ['username' => 'required', 'password' => 'required'];
        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', 'Username dan password wajib diisi.');
        }

        $user = model(UserModel::class)
            ->where('username', $this->request->getPost('username'))
            ->first();

        if (! $user || ! $user['is_active']
            || ! password_verify($this->request->getPost('password'), $user['password'])) {
            return redirect()->back()->withInput()->with('error', 'Username atau password salah.');
        }

        // Nama tampilan: ambil dari tabel pegawai bila ada
        $nama = $user['username'];
        if ($user['pegawai_id']) {
            $pegawai = db_connect()->table('pegawai')->where('id', $user['pegawai_id'])->get()->getRowArray();
            $nama = $pegawai['nama'] ?? $nama;
        }

        session()->regenerate();
        session()->set([
            'isLoggedIn' => true,
            'user_id'    => $user['id'],
            'pegawai_id' => $user['pegawai_id'],
            'username'   => $user['username'],
            'nama'       => $nama,
            'role'       => $user['role'],
        ]);

        return redirect()->to('/dashboard');
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to('/login');
    }
}