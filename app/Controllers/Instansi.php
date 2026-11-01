<?php

namespace App\Controllers;

use App\Models\InstansiModel;
use CodeIgniter\Database\Exceptions\DatabaseException;
use CodeIgniter\Exceptions\PageNotFoundException;

class Instansi extends BaseController
{
    private InstansiModel $model;

    private array $rules = [
        'nama' => 'required|max_length[150]',
        'kota' => 'required|max_length[80]',
    ];

    public function __construct()
    {
        $this->model = model(InstansiModel::class);
    }

    public function index()
    {
        return view('instansi/index', [
            'title'  => 'Instansi',
            'daftar' => $this->model->orderBy('nama', 'ASC')->findAll(),
        ]);
    }

    public function baru()
    {
        return view('instansi/form', [
            'title'    => 'Tambah Instansi',
            'instansi' => null,
            'action'   => site_url('instansi/simpan'),
        ]);
    }

    public function simpan()
    {
        if (! $this->validate($this->rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        [$data, $errors] = $this->dataDenganGambar(null);
        if ($errors !== []) {
            return redirect()->back()->withInput()->with('errors', $errors);
        }

        $this->model->insert($data);

        return redirect()->to('/instansi')->with('success', 'Instansi berhasil ditambahkan.');
    }

    public function ubah(int $id)
    {
        $instansi = $this->model->find($id);
        if (! $instansi) {
            return redirect()->to('/instansi')->with('error', 'Data instansi tidak ditemukan.');
        }

        return view('instansi/form', [
            'title'    => 'Ubah Instansi',
            'instansi' => $instansi,
            'action'   => site_url('instansi/perbarui/' . $id),
        ]);
    }

    public function perbarui(int $id)
    {
        $lama = $this->model->find($id);
        if (! $lama) {
            return redirect()->to('/instansi')->with('error', 'Data instansi tidak ditemukan.');
        }

        if (! $this->validate($this->rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        [$data, $errors] = $this->dataDenganGambar($lama);
        if ($errors !== []) {
            return redirect()->back()->withInput()->with('errors', $errors);
        }

        $this->model->update($id, $data);

        return redirect()->to('/instansi')->with('success', 'Data instansi berhasil diperbarui.');
    }

    public function hapus(int $id)
    {
        $instansi = $this->model->find($id);
        if (! $instansi) {
            return redirect()->to('/instansi')->with('error', 'Data instansi tidak ditemukan.');
        }

        $jumlahPejabat = db_connect()->table('pejabat')->where('instansi_id', $id)->countAllResults();
        if ($jumlahPejabat > 0) {
            return redirect()->to('/instansi')->with('error', 'Instansi masih memiliki data pejabat. Hapus pejabatnya terlebih dahulu.');
        }

        try {
            $this->model->delete($id);
        } catch (DatabaseException $e) {
            return redirect()->to('/instansi')->with('error', 'Instansi tidak bisa dihapus karena sudah dipakai di SPT.');
        }

        foreach (['kop_file', 'footer_file'] as $kolom) {
            if ($instansi[$kolom]) {
                $this->hapusFile($instansi[$kolom]);
            }
        }

        return redirect()->to('/instansi')->with('success', 'Instansi berhasil dihapus.');
    }

    /** Tampilkan gambar kop/footer (untuk pratinjau di form; khusus super user). */
    public function gambar(int $id, string $jenis)
    {
        $row   = $this->model->find($id);
        $kolom = $jenis === 'footer' ? 'footer_file' : 'kop_file';
        $path  = ($row && $row[$kolom]) ? WRITEPATH . 'uploads/kop/' . basename($row[$kolom]) : null;

        if (! $path || ! is_file($path)) {
            throw PageNotFoundException::forPageNotFound();
        }

        $mime = str_ends_with(strtolower($path), '.jpg') ? 'image/jpeg' : 'image/png';

        return $this->response->setContentType($mime)->setBody((string) file_get_contents($path));
    }

    // ---------- helper ----------

    /** Susun data simpan beserta proses upload. Mengembalikan [data, errors]. */
    private function dataDenganGambar(?array $lama): array
    {
        $data = [
            'nama' => trim((string) $this->request->getPost('nama')),
            'kota' => trim((string) $this->request->getPost('kota')),
        ];

        $baru   = [];
        $errors = [];

        foreach (['kop' => 'kop_file', 'footer' => 'footer_file'] as $field => $kolom) {
            [$nama, $err] = $this->simpanGambar($field);

            if ($err !== null) {
                $errors[$field] = $err;
            } elseif ($nama !== null) {
                $data[$kolom] = $nama;
                $baru[]       = $nama;
            } elseif ($lama && $this->request->getPost('hapus_' . $field)) {
                $data[$kolom] = null;
            }
        }

        if ($errors !== []) {
            foreach ($baru as $n) {
                $this->hapusFile($n); // bersihkan upload yang sudah terlanjur tersimpan
            }

            return [null, $errors];
        }

        // File lama yang tergantikan atau dihapus ikut dibersihkan
        if ($lama) {
            foreach (['kop_file', 'footer_file'] as $kolom) {
                if (array_key_exists($kolom, $data) && $lama[$kolom]) {
                    $this->hapusFile($lama[$kolom]);
                }
            }
        }

        return [$data, []];
    }

    /** @return array{0: ?string, 1: ?string} [nama file tersimpan, pesan error] */
    private function simpanGambar(string $field): array
    {
        $file = $this->request->getFile($field);
        if (! $file || $file->getError() === UPLOAD_ERR_NO_FILE) {
            return [null, null]; // tidak ada upload
        }
        if (! $file->isValid()) {
            return [null, 'Upload gagal: ' . $file->getErrorString()];
        }
        if ($file->getSizeByUnit('mb') > 3) {
            return [null, 'Ukuran gambar maksimal 3 MB.'];
        }
        if (! in_array($file->getMimeType(), ['image/png', 'image/jpeg'], true)) {
            return [null, 'Format gambar harus PNG atau JPG.'];
        }

        $tmp  = $file->getTempName();
        $info = @getimagesize($tmp);
        if (! $info) {
            return [null, 'File bukan gambar yang valid.'];
        }

        $dir = WRITEPATH . 'uploads/kop/';
        if (! is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        $acak = $field . '_' . bin2hex(random_bytes(6));

        if (extension_loaded('gd')) {
            $im = $info[2] === IMAGETYPE_PNG ? @imagecreatefrompng($tmp) : @imagecreatefromjpeg($tmp);
            if (! $im) {
                return [null, 'Gambar tidak bisa dibaca.'];
            }

            [$w, $h] = $info;
            $lebar   = min($w, 1600);
            $tinggi  = (int) round($h * $lebar / $w);

            $kanvas = imagecreatetruecolor($lebar, $tinggi);
            imagefill($kanvas, 0, 0, imagecolorallocate($kanvas, 255, 255, 255)); // transparan menjadi putih
            imagecopyresampled($kanvas, $im, 0, 0, 0, 0, $lebar, $tinggi, $w, $h);

            $nama = $acak . '.png';
            imagepng($kanvas, $dir . $nama, 6);

            return [$nama, null];
        }

        // Tanpa ekstensi GD: simpan apa adanya
        $nama = $acak . ($info[2] === IMAGETYPE_PNG ? '.png' : '.jpg');
        copy($tmp, $dir . $nama);

        return [$nama, null];
    }

    private function hapusFile(string $nama): void
    {
        $path = WRITEPATH . 'uploads/kop/' . basename($nama);
        if (is_file($path)) {
            @unlink($path);
        }
    }
}