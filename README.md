# Sistem Informasi Peminjaman Buku

Aplikasi berbasis web yang dibangun menggunakan Laravel untuk membantu pengelolaan koleksi buku dan aktivitas peminjaman pada perpustakaan sekolah. Sistem ini dirancang untuk mempermudah pencatatan buku, pengguna, serta transaksi peminjaman dan pengembalian.

## 1. Informasi Aplikasi

Aplikasi menggunakan tiga tabel utama dalam database, yaitu `users`, `books`, dan `loans`.

Terdapat dua jenis pengguna berdasarkan kolom `role` pada tabel `users`:

- **Admin**: mempunyai hak akses untuk mengelola data buku dan transaksi peminjaman.
- **Siswa**: merupakan pengguna dengan role bawaan yang dapat meminjam buku dan memiliki informasi tambahan berupa `kelas`.

### Alur Kerja Sistem

1. Siswa melakukan peminjaman buku. Sistem menyimpan transaksi pada tabel `loans` dengan informasi `loan_date`, `due_date`, dan status awal `dipinjam`.
2. Ketika buku dikembalikan, sistem mengisi `returned_at` dan memperbarui status transaksi.
3. Apabila pengembalian melewati batas waktu, jumlah denda dicatat pada kolom `fine`.

**Status pengembangan:** Struktur database melalui migration dan model sudah dibuat. Route saat ini masih menampilkan halaman awal, sedangkan controller untuk pengelolaan buku dan peminjaman belum tersedia.

## 2. Modul Aplikasi

| Modul | Keterangan |
|---|---|
| Books | Mengelola informasi buku, seperti judul, penulis, penerbit, tahun terbit, dan stok. |
| Loans | Menyimpan data transaksi peminjaman, tanggal pinjam, batas pengembalian, tanggal kembali, status, dan denda. |
| Users | Menyimpan informasi pengguna, termasuk nama, email, password, role, dan kelas. |

## 3. Teknologi yang Digunakan

- PHP
- Laravel Framework
- MySQL
- XAMPP
- Composer

## 4. Perancangan Database (ERD)

Database terdiri dari tiga tabel yang saling berhubungan. Tabel `users` dan `books` memiliki relasi one-to-many terhadap tabel `loans`.

Artinya, satu pengguna dapat memiliki beberapa transaksi peminjaman dan satu buku dapat tercatat dalam beberapa transaksi.

### Diagram ERD

![ERD Sistem Peminjaman Buku](erd_peminjaman_buku.png)

**Keterangan:**
- **PK (Primary Key):** identitas utama setiap record dalam tabel.
- **FK (Foreign Key):** atribut yang menghubungkan suatu tabel dengan tabel lainnya.
- **UK (Unique Key):** memastikan suatu nilai tidak memiliki duplikasi.

## 5. Dokumentasi Model dan Route

### 5.1 Model Book

Lokasi file: `app/Models/Book.php`

| Elemen | Jenis | Keterangan |
|---|---|---|
| `$fillable` | Properti | Menentukan kolom yang dapat diisi secara massal, yaitu `title`, `author`, `publisher`, `year`, dan `stock`. |

### 5.2 Model Loan

Lokasi file: `app/Models/Loan.php`

| Elemen | Jenis | Parameter | Return | Keterangan |
|---|---|---|---|---|
| `$fillable` | Properti | - | - | Memuat `user_id`, `book_id`, `loan_date`, `due_date`, `returned_at`, `status`, dan `fine`. |
| `$casts` | Properti | - | - | Mengubah atribut tanggal menjadi objek Carbon secara otomatis. |
| `user()` | Relasi | - | `BelongsTo` | Mengambil data pengguna berdasarkan `user_id`. Contoh: `$loan->user->name`. |
| `book()` | Relasi | - | `BelongsTo` | Mengambil informasi buku melalui `book_id`. Contoh: `$loan->book->title`. |

### 5.3 Model User

Lokasi file: `app/Models/User.php`

| Elemen | Jenis | Parameter | Return | Keterangan |
|---|---|---|---|---|
| `$fillable` | Properti | - | - | Memuat `name`, `email`, `password`, `role`, dan `kelas`. |
| `$hidden` | Properti | - | - | Menyembunyikan `password` dan `remember_token` saat data dikonversi ke array atau JSON. |
| `casts()` | Fungsi | - | `array` | Mengatur `email_verified_at` menjadi datetime dan melakukan hashing otomatis pada password. |
| `isAdmin()` | Fungsi | - | `bool` | Memeriksa apakah role pengguna bernilai `admin`. Menghasilkan `true` jika sesuai dan `false` jika tidak. |

### 5.4 Route Web

File: `routes/web.php`

| Method | URL | Proses | Output |
|---|---|---|---|
| GET | `/` | Menjalankan closure | Menampilkan view `welcome`. |

## 6. Pemeriksaan dan Debugging

Berdasarkan pemeriksaan struktur program, terdapat beberapa bagian yang masih perlu dilengkapi.

### 6.1 Identifikasi Masalah

| No. | Masalah | Dampak | Solusi |
|---|---|---|---|
| 1 | Model `Book` belum memiliki relasi ke `Loan`. | `$book->loans` tidak dapat dipanggil. | Menambahkan relasi `hasMany`. |
| 2 | Model `User` belum mempunyai relasi ke `Loan`. | Riwayat peminjaman belum dapat diakses melalui relasi. | Menambahkan fungsi `loans()`. |
| 3 | Controller dan route books serta loans belum tersedia. | Fitur belum dapat diakses melalui browser. | Membuat `BookController` dan `LoanController`. |
| 4 | Status transaksi masih menggunakan string bebas. | Berpotensi terjadi kesalahan penulisan status. | Menggunakan konstanta atau enum PHP. |
| 5 | Stok belum terintegrasi dengan transaksi. | Jumlah stok dapat menjadi tidak sesuai. | Menggunakan `DB::transaction()` saat peminjaman dan pengembalian. |

### 6.2 Penambahan Relasi Model

Untuk melengkapi hubungan antar-model, berikut kode yang perlu ditambahkan.

**Book.php**

```php
use Illuminate\Database\Eloquent\Relations\HasMany;

public function loans(): HasMany
{
    return $this->hasMany(Loan::class);
}
```

**User.php**

```php
use Illuminate\Database\Eloquent\Relations\HasMany;

public function loans(): HasMany
{
    return $this->hasMany(Loan::class);
}
```

### 6.3 Error yang Umum Ditemukan

| Error | Penyebab | Solusi |
|---|---|---|
| `Base table or view not found` / `Failed to open the referenced table` | Migration `loans` dijalankan sebelum `users` atau `books`. | Atur timestamp migration `loans` agar paling akhir, kemudian jalankan `php artisan migrate:fresh`. |
| `SQLSTATE[23000]: Integrity constraint violation` | `user_id` atau `book_id` tidak ditemukan pada tabel induk. | Pastikan data pengguna dan buku tersedia. |
| `MassAssignmentException` | Atribut belum dimasukkan ke `$fillable`. | Tambahkan atribut yang diperlukan pada model. |
| `Call to undefined relationship` | Relasi belum didefinisikan. | Tambahkan fungsi relasi pada model terkait. |
| `Class "App\Models\Loan" not found` | Namespace atau nama file tidak sesuai. | Periksa nama class dan jalankan `composer dump-autoload`. |

### 6.4 Perintah untuk Melacak Kesalahan

- `dd($loan);` untuk memeriksa isi variabel.
- `dump($request->all());` untuk melihat data request.
- `Log::info('data pinjam', $data);` untuk mencatat informasi ke log.
- `php artisan route:list` untuk melihat daftar route.
- `php artisan migrate:status` untuk memeriksa status migration.
- `php artisan tinker` untuk melakukan pengujian model melalui terminal.

Contoh pengujian relasi menggunakan Tinker:

```php
App\Models\Loan::with('user','book')->first();
```

Untuk menampilkan informasi error secara lengkap selama pengembangan, pengaturan berikut dapat digunakan pada file `.env`:

```env
APP_DEBUG=true
```

## 7. Instalasi dan Konfigurasi

### 7.1 Clone Repository

```bash
git clone https://github.com/kalilasyifazzahra/peminjaman-buku.git
cd peminjaman-buku
```

### 7.2 Instalasi Dependency

```bash
composer install
```

### 7.3 Pengaturan Environment

```bash
cp .env.example .env
php artisan key:generate
```

### 7.4 Membuat Database

Buka phpMyAdmin melalui XAMPP dan buat database dengan nama `peminjaman_buku`.

Sesuaikan konfigurasi pada file `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=peminjaman_buku
DB_USERNAME=root
DB_PASSWORD=
```

### 7.5 Menjalankan Migration

```bash
php artisan migrate
```

### 7.6 Menjalankan Server

```bash
php artisan serve
```

Buka alamat berikut melalui browser:

`http://127.0.0.1:8000`

## 8. File Database

File database tersedia pada direktori `database/peminjaman_buku.sql`.

Database dapat disiapkan melalui dua cara:

1. Melakukan import file SQL melalui menu Import pada phpMyAdmin.
2. Menggunakan migration Laravel dengan menjalankan `php artisan migrate`.

Gunakan metode yang sesuai dengan kondisi database agar tidak terjadi konflik tabel.

## 9. Evaluasi Pengembangan

### 9.1 Tujuan

Membangun aplikasi perpustakaan berbasis Laravel yang dapat membantu pengelolaan informasi buku, data pengguna, dan transaksi peminjaman secara terstruktur.

### 9.2 Hasil yang Telah Dicapai

- Perancangan database menggunakan tabel `users`, `books`, dan `loans`.
- Pembuatan ERD dengan hubungan one-to-many.
- Pembuatan migration dan model `Book`, `Loan`, serta `User`.
- Penerapan relasi `belongsTo` pada model `Loan` menuju `User` dan `Book`.
- Pembagian hak akses menggunakan kolom `role`, yaitu `admin` dan `siswa`.
- Penyusunan dokumentasi program, fungsi, dan panduan debugging.
- Penyediaan file database `database/peminjaman_buku.sql`.
- Penyimpanan source code pada GitHub.

### 9.3 Kendala Pengerjaan

- Push project ke GitHub sempat mengalami error `Repository not found` dan `rejected`.
- Masalah tersebut ditangani dengan memperbaiki remote repository dan menjalankan `git pull --rebase`.
- Urutan migration perlu diperiksa agar tabel `loans` dibuat setelah tabel `users` dan `books`.

### 9.4 Kekurangan Sistem

- Controller, route, dan tampilan untuk books dan loans belum dibuat.
- Relasi `loans()` pada model `Book` dan `User` belum tersedia.
- Logika perubahan stok belum diterapkan.
- Perhitungan denda keterlambatan belum diimplementasikan.
- Pengujian fitur belum dilakukan secara menyeluruh.

### 9.5 Rencana Pengembangan

1. Menambahkan relasi `hasMany` pada model `Book` dan `User`.
2. Membuat `BookController` untuk mengelola data buku melalui CRUD.
3. Mengembangkan `LoanController` untuk proses peminjaman, pengembalian, dan perhitungan denda.
4. Mengintegrasikan perubahan stok menggunakan `DB::transaction()`.
5. Menggunakan konstanta atau enum untuk menjaga konsistensi status transaksi.
6. Menambahkan autentikasi serta pembatasan akses admin menggunakan fungsi `isAdmin()`.

## 10. Kesimpulan

Aplikasi Sistem Peminjaman Buku telah memiliki fondasi pengembangan berupa struktur database, migration, model, serta dokumentasi teknis. Hubungan antara pengguna, buku, dan transaksi juga telah dirancang menggunakan ERD.

Meskipun demikian, beberapa fitur utama seperti pengelolaan buku, peminjaman, pengembalian, dan perhitungan denda masih memerlukan implementasi lebih lanjut. Pengembangan berikutnya akan difokuskan pada penyelesaian fitur tersebut agar aplikasi dapat digunakan untuk mendukung kegiatan administrasi perpustakaan sekolah.

## 11. Lisensi

Project ini menggunakan **MIT License**, yang memperbolehkan penggunaan dan modifikasi source code sesuai ketentuan lisensi.

Informasi lisensi:

https://opensource.org/licenses/MIT
