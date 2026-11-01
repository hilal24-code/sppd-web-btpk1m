<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();

        if (! $session->get('isLoggedIn')) {
            return redirect()->to('/login')->with('error', 'Silakan login terlebih dahulu.');
        }

        // Cocokkan dengan data terbaru di database (akun dinonaktifkan atau peran diubah)
        $user = db_connect()->table('users')
            ->select('role, is_active')
            ->where('id', (int) $session->get('user_id'))
            ->get()->getRowArray();

        if (! $user || ! $user['is_active']) {
            $session->remove('isLoggedIn');

            return redirect()->to('/login')->with('error', 'Akun Anda tidak aktif atau tidak ditemukan.');
        }

        $session->set('role', $user['role']);

        if (! empty($arguments) && ! in_array($user['role'], $arguments, true)) {
            return redirect()->to('/dashboard')->with('error', 'Anda tidak punya akses ke halaman itu.');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}