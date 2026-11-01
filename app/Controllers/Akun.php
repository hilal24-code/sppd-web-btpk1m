<?php

namespace App\Controllers;

use App\Models\UserModel;

class Akun extends BaseController
{
    public function password()
    {
        return view('akun/password', ['title' => 'Ganti Password']);
    }

    public function ubahPassword()
    {
        $rules = [
            'password_lama' => ['label' => 'Password lama', 'rules' => 'required'],
            'password_baru' => ['label' => 'Password baru', 'rules' => 'required|min_length[8]|differs[password_lama]'],
            'password_konfirmasi' => ['label' => 'Konfirmasi password baru', 'rules' => 'required|matches[password_baru]'],
        ];
        if (! $this->validate($rules)) {
            return redirect()->back()->with('errors', $this->validator->getErrors());
        }

        $model = model(UserModel::class);
        $user  = $model->find((int) session('user_id'));

        if (! $user || ! password_verify((string) $this->request->getPost('password_lama'), $user['password'])) {
            return redirect()->back()->with('errors', ['password_lama' => 'Password lama salah.']);
        }

        $model->update($user['id'], [
            'password' => password_hash((string) $this->request->getPost('password_baru'), PASSWORD_DEFAULT),
        ]);

        return redirect()->to('/akun/password')->with('success', 'Password berhasil diubah.');
    }
}