# SPPD Web – Aplikasi Surat Perintah Tugas (SPT)

Aplikasi web untuk **membuat, memeriksa, menyetujui, dan mencetak Surat Perintah Tugas (SPT)** perjalanan dinas di lingkungan Balai Teknik Perkeretaapian. Dibangun dengan **CodeIgniter 4**.

Satu paket SPT menghasilkan tiga dokumen yang dicetak ke PDF:

- **Surat Tugas** (Dasar, Kepada, Untuk, tanda tangan, tabel paraf)
- **Lampiran** daftar pegawai (khusus penugasan lebih dari satu pegawai)
- **Nota Dinas** pengantar konsep surat tugas

> Seluruh data contoh (nama pegawai, NIP, pejabat, nomor surat, tempat) adalah **fiktif**.

## Fitur

- Login dengan tiga peran: **user**, **admin**, dan **super user**
- Pembuatan SPT dengan butir *Dasar* dan *Untuk* yang bisa ditambah atau dihapus, penugasan satu atau banyak pegawai, pilihan instansi dan penandatangan, tabel paraf, dan data Nota Dinas
- Alur persetujuan: draft → diajukan → disetujui (bernomor dan terkunci) atau ditolak (dengan catatan revisi)
- Cetak PDF (Surat Tugas, Lampiran, Nota Dinas, atau satu paket) dengan format mengikuti contoh surat asli
- **Kop surat dan footer per instansi** (diupload sebagai gambar, otomatis dipasang penuh sampai tepi halaman)
- Master data: pegawai, instansi, pejabat penandatangan, dan pengguna
- Dashboard ringkasan SPT sesuai peran, pencarian, filter status, dan paginasi
- Ganti password sendiri; akun yang dinonaktifkan atau diubah perannya langsung berlaku

## Peran dan hak akses

| Fitur | user | admin | super user |
|---|:---:|:---:|:---:|
| Membuat dan mengubah SPT sendiri (draft atau ditolak) | ✔ | ✔ | ✔ |
| Melihat semua SPT | – | ✔ | ✔ |
| Memeriksa, menyetujui, menolak, memberi nomor surat | – | ✔ | ✔ |
| Mengubah SPT milik orang lain (selama belum disetujui) | – | ✔ | ✔ |
| Mengelola data pegawai | – | ✔ | ✔ |
| Mengelola instansi (kop, footer) dan pejabat penandatangan | – | – | ✔ |
| Mengelola pengguna | – | – | ✔ |
| Mengembalikan SPT yang sudah disetujui ke draft | – | – | ✔ |
| Mencetak PDF (hanya SPT yang sudah disetujui) | ✔ (miliknya) | ✔ | ✔ |

## Alur status SPT

```
draft ──Ajukan──▶ diajukan ──Setujui──▶ disetujui (terkunci, bernomor)
  ▲                  │
  └──── ditolak ◀────┘  Tolak (wajib catatan revisi)
```

## Teknologi

- PHP (dikembangkan dengan 8.3) dan **CodeIgniter 4.7**
- MySQL / MariaDB
- Bootstrap 5 (via CDN)
- [Dompdf](https://github.com/dompdf/dompdf) untuk pembuatan PDF

## Prasyarat

- PHP 8.x dengan ekstensi: `intl`, `mbstring`, `mysqli`, `dom`, dan `gd` (untuk memproses gambar kop)
- Composer
- MySQL / MariaDB (misalnya dari Laragon atau XAMPP)

## Instalasi

```bash
# 1. Ambil kode
git clone https://github.com/hilal24-code/sppd-web-btpk1m.git
cd sppd-web-btpk1m

# 2. Pasang dependensi
composer install

# 3. Buat file konfigurasi
cp env .env          # Windows (cmd): copy env .env
```

Buka `.env`, lalu sesuaikan:

```ini
CI_ENVIRONMENT = development

app.baseURL = 'http://localhost:8080/'

database.default.hostname = localhost
database.default.database = db_spt
database.default.username = root
database.default.password =
database.default.DBDriver = MySQLi
database.default.port = 3306
```

Lanjutkan:

```bash
# 4. Kunci enkripsi
php spark key:generate

# 5. Buat database (atau buat manual lewat phpMyAdmin/HeidiSQL), lalu tabel
php spark db:create db_spt
php spark migrate

# 6. Isi data awal (instansi, pejabat, pegawai, dan 3 akun contoh)
php spark db:seed AwalSeeder

# 7. (Opsional) 5 contoh SPT dengan status berbeda
php spark db:seed SptSeeder

# 8. Jalankan
php spark serve
```

Buka <http://localhost:8080>.

> Zona waktu aplikasi diatur di `app/Config/App.php` (`$appTimezone`). Proyek ini memakai `Asia/Jakarta`.

## Akun contoh

Dibuat oleh `AwalSeeder`. Semua memakai password sementara **`password123`**.

| Username | Peran |
|---|---|
| `superuser` | super user |
| `asep` | admin |
| `citra` | user |

> ⚠️ **Wajib mengganti semua password ini** (menu *Ganti Password*, atau *Pengguna* sebagai super user) sebelum aplikasi dipakai di luar komputer pengembangan.

## Kop surat dan footer

1. Login sebagai super user, buka menu **Instansi**, lalu **Ubah**.
2. Upload gambar **Kop surat** (dipasang di atas halaman pertama Surat Tugas) dan/atau **Footer** (dipasang di bawah setiap halaman). Format PNG atau JPG, maksimal 3 MB.
3. Di form SPT, pilih **Instansi** lalu **Penandatangan**. Kop, footer, kota, dan nama instansi pada surat otomatis mengikuti instansi yang dipilih.

Gambar disimpan di `writable/uploads/kop/` dan **tidak ikut Git**. Setiap instalasi baru perlu mengupload ulang gambarnya.

## Cetak PDF

Dari halaman **Detail** SPT yang berstatus *disetujui*, klik **Cetak PDF** (paket lengkap) atau pilih salah satu dokumen dari menu di sebelahnya. Penulisan kegiatan sebaiknya diawali kata kerja (misalnya "Menghadiri Rapat ..."), tanpa tanggal dan tempat, karena keduanya ditambahkan otomatis di judul Lampiran dan Nota Dinas.

## Struktur penting

```
app/
├── Controllers/    Auth, Dashboard, Spt, Pegawai, Instansi, Pejabat, Pengguna, Akun
├── Models/         SptModel, PegawaiModel, InstansiModel, PejabatModel, UserModel
├── Filters/        AuthFilter (login dan pembatasan peran)
├── Helpers/        cetak_helper.php (tanggal Indonesia, daftar bernomor, gambar kop)
├── Database/
│   ├── Migrations/ struktur tabel
│   └── Seeds/      AwalSeeder, SptSeeder
└── Views/
    ├── layouts/    kerangka halaman
    ├── spt/        daftar, form, detail
    └── cetak/      template PDF (surat tugas, lampiran, nota dinas)
```

## Catatan keamanan

- `.env`, folder `vendor/`, dan isi `writable/` (log, sesi, cache, upload) **tidak boleh** dimasukkan ke Git.
- Jangan menyimpan data pegawai asli (NIP, nama) di repositori publik. Seeder hanya berisi data fiktif.
- Untuk produksi: set `CI_ENVIRONMENT = production`, isi `app.baseURL` dengan alamat sebenarnya, gunakan user database khusus (bukan `root`) dengan password, arahkan web root ke folder `public/`, dan jadwalkan backup database.

## Rencana pengembangan

- Ekspor ke Word (.docx) agar dokumen bisa diedit sebelum dicetak
- Pegawai terikat ke instansi, sehingga form SPT hanya menampilkan pegawai dari instansi yang dipilih
- Penomoran surat otomatis sesuai format resmi kantor
- Pesan validasi dalam bahasa Indonesia

## Status

Dalam pengembangan.
