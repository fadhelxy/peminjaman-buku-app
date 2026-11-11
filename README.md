Sistem Informasi Peminjaman Buku Perpustakaan

Aplikasi peminjaman buku merupakan sistem berbasis web yang dikembangkan menggunakan Laravel. Aplikasi ini bertujuan untuk membantu proses pengelolaan koleksi buku, data anggota, serta transaksi peminjaman dan pengembalian buku di lingkungan perpustakaan sekolah.

1. Gambaran Umum Aplikasi

Sistem ini memiliki tiga tabel utama, yaitu "users", "books", dan "loans". Setiap pengguna mempunyai hak akses berdasarkan nilai pada kolom "role".

Jenis pengguna dalam aplikasi:

- Admin: bertanggung jawab mengelola informasi buku serta data transaksi peminjaman.
- Siswa: pengguna yang dapat melakukan peminjaman buku. Akun siswa memiliki informasi tambahan berupa "kelas" dan menjadi role bawaan ketika pengguna dibuat.

Mekanisme Peminjaman

1. Siswa memilih buku yang akan dipinjam. Sistem kemudian menyimpan transaksi ke tabel "loans", termasuk tanggal peminjaman ("loan_date"), tanggal batas pengembalian ("due_date"), dan status awal "dipinjam".
2. Ketika buku dikembalikan, sistem mengisi kolom "returned_at" dan mengubah status transaksi.
3. Apabila buku dikembalikan setelah melewati batas waktu, sistem mencatat jumlah denda pada kolom "fine".

Perkembangan pengerjaan: Struktur database melalui migration dan model utama sudah dibuat. Namun, route yang tersedia masih sebatas halaman utama. Controller dan tampilan untuk pengelolaan buku serta transaksi peminjaman masih perlu dikembangkan.

2. Fitur Utama

Modul| Fungsi
Books| Menyimpan dan mengelola informasi buku, meliputi judul, penulis, penerbit, tahun terbit, dan stok.
Loans| Mengelola transaksi peminjaman dan pengembalian, termasuk tanggal pinjam, tenggat pengembalian, status, serta denda.
Users| Menyimpan informasi pengguna, email, password, role, dan kelas siswa.

3. Teknologi yang Digunakan

- PHP sebagai bahasa pemrograman.
- Laravel sebagai framework pengembangan aplikasi.
- MySQL sebagai sistem manajemen database.
- XAMPP sebagai lingkungan server lokal.
- Composer untuk mengelola dependency PHP.

4. Perancangan Database (ERD)

Hubungan antar tabel dalam aplikasi dirancang menggunakan relasi one-to-many. Satu pengguna dapat mempunyai beberapa transaksi peminjaman, begitu pula satu buku dapat tercatat dalam beberapa transaksi.

erDiagram
  USERS ||--o{ LOANS : meminjam
  BOOKS ||--o{ LOANS : dipinjam
  USERS {
    bigint id PK
    string name
    string email UK
    string role
    string kelas
    string password
    timestamp email_verified_at
  }
  BOOKS {
    bigint id PK
    string title
    string author
    string publisher
    int year
    int stock
  }
  LOANS {
    bigint id PK
    bigint user_id FK
    bigint book_id FK
    date loan_date
    date due_date
    date returned_at
    string status
    int fine
  }

5. Dokumentasi Model dan Fungsi

A. Model Book ("app/Models/Book.php")

Elemen| Jenis| Keterangan
"$fillable"| Properti| Menentukan atribut yang dapat diisi melalui mass assignment, yaitu "title", "author", "publisher", "year", dan "stock".

B. Model Loan ("app/Models/Loan.php")

Elemen| Jenis| Parameter| Return| Keterangan
"$fillable"| Properti| -| -| Memuat kolom "user_id", "book_id", "loan_date", "due_date", "returned_at", "status", dan "fine".
"$casts"| Properti| -| -| Mengatur agar atribut tanggal dikonversi menjadi objek Carbon secara otomatis.
"user()"| Relasi| -| "BelongsTo"| Menghubungkan transaksi peminjaman dengan pengguna berdasarkan "user_id". Contoh: "$loan->user->name".
"book()"| Relasi| -| "BelongsTo"| Menghubungkan transaksi dengan data buku berdasarkan "book_id". Contoh: "$loan->book->title".

C. Model User ("app/Models/User.php")

Elemen| Jenis| Parameter| Return| Keterangan
"$fillable"| Properti| -| -| Memuat atribut "name", "email", "password", "role", dan "kelas".
"$hidden"| Properti| -| -| Menyembunyikan "password" dan "remember_token" ketika data dikonversi ke array atau JSON.
"casts()"| Fungsi| -| "array"| Mengatur konversi "email_verified_at" menjadi datetime dan melakukan hashing otomatis pada password.
"isAdmin()"| Fungsi| -| "bool"| Memeriksa apakah pengguna mempunyai role "admin". Menghasilkan "true" jika sesuai dan "false" jika tidak.

6. Konfigurasi Route

Pada tahap pengerjaan saat ini, file "routes/web.php" masih memiliki satu route utama.

Method| URL| Proses| Hasil
GET| "/"| Menjalankan closure| Memanggil halaman "welcome".

Route tambahan untuk pengelolaan buku dan transaksi peminjaman belum diimplementasikan.

7. Hasil Pemeriksaan dan Debugging

Berdasarkan pemeriksaan struktur program, ditemukan beberapa bagian yang masih perlu disempurnakan.

A. Temuan dan Solusi

No.| Temuan| Pengaruh| Solusi yang Disarankan
1| Model "Book" belum memiliki relasi menuju "Loan".| Pemanggilan "$book->loans" tidak dapat dilakukan.| Menambahkan relasi "hasMany".
2| Model "User" belum mempunyai relasi menuju "Loan".| Riwayat peminjaman pengguna belum dapat diakses melalui relasi.| Menambahkan fungsi "loans()".
3| Controller dan route buku maupun peminjaman belum tersedia.| Pengguna belum dapat mengakses fitur tersebut melalui browser.| Membuat "BookController" dan "LoanController".
4| Status transaksi masih menggunakan teks biasa.| Berpotensi terjadi kesalahan penulisan status.| Menggunakan konstanta atau enum PHP.
5| Pengelolaan stok belum terintegrasi dengan transaksi.| Jumlah stok dapat menjadi tidak sesuai.| Menggunakan "DB::transaction()" saat peminjaman dan pengembalian.

B. Penambahan Relasi Model

Berikut kode yang dapat digunakan untuk melengkapi hubungan antarmodel.

File "app/Models/Book.php"

use Illuminate\Database\Eloquent\Relations\HasMany;

public function loans(): HasMany
{
    return $this->hasMany(Loan::class);
}

File "app/Models/User.php"

use Illuminate\Database\Eloquent\Relations\HasMany;

public function loans(): HasMany
{
    return $this->hasMany(Loan::class);
}

C. Error yang Berpotensi Terjadi

Pesan Error| Kemungkinan Penyebab| Langkah Penanganan
"Base table or view not found" / "Failed to open the referenced table"| Urutan migration tidak sesuai dengan hubungan tabel.| Pastikan migration "loans" dijalankan setelah "users" dan "books", kemudian gunakan "php artisan migrate:fresh".
"SQLSTATE[23000]: Integrity constraint violation"| ID pengguna atau buku yang dimasukkan tidak ditemukan pada tabel induk.| Pastikan data terkait sudah tersedia sebelum transaksi dibuat.
"MassAssignmentException"| Atribut yang akan disimpan belum terdaftar pada "$fillable".| Periksa dan lengkapi "$fillable" pada model terkait.
"Call to undefined relationship"| Fungsi relasi belum didefinisikan pada model.| Tambahkan relasi sesuai kebutuhan.
"Class "App\Models\Loan" not found"| Nama file, class, atau namespace tidak sesuai.| Periksa lokasi file dan jalankan "composer dump-autoload".

D. Teknik Pemeriksaan Kesalahan

Beberapa perintah yang dapat digunakan untuk membantu menemukan sumber masalah:

- "dd($loan);" untuk melihat isi variabel secara langsung.
- "dump($request->all());" untuk memeriksa data yang diterima dari request.
- "Log::info('data pinjam', $data);" untuk menyimpan informasi ke log aplikasi.
- "php artisan route:list" untuk melihat seluruh route yang terdaftar.
- "php artisan migrate:status" untuk memeriksa status migration.
- "php artisan tinker" untuk menjalankan pengujian model melalui console.

Contoh pengujian relasi:

App\Models\Loan::with('user', 'book')->first();

Selama proses pengembangan, "APP_DEBUG=true" pada file ".env" dapat digunakan agar informasi error lebih lengkap ditampilkan.

8. Panduan Instalasi dan Menjalankan Aplikasi

Langkah 1. Mengunduh Project

Clone repository dari GitHub menggunakan perintah:

git clone https://github.com/kalilasyifazzahra/peminjaman-buku.git
cd peminjaman-buku

Langkah 2. Memasang Dependency

Jalankan Composer untuk menginstal seluruh kebutuhan aplikasi.

composer install

Langkah 3. Menyiapkan Environment

Salin file konfigurasi environment dan buat application key.

cp .env.example .env
php artisan key:generate

Langkah 4. Membuat Database

Buat database dengan nama "peminjaman_buku" melalui phpMyAdmin. Setelah itu, sesuaikan konfigurasi database pada ".env".

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=peminjaman_buku
DB_USERNAME=root
DB_PASSWORD=

Langkah 5. Menjalankan Migration

Gunakan perintah berikut untuk membuat tabel berdasarkan file migration.

php artisan migrate

Langkah 6. Menjalankan Server

Setelah konfigurasi selesai, jalankan aplikasi dengan perintah:

php artisan serve

Aplikasi dapat diakses melalui alamat:

"http://127.0.0.1:8000"

9. Pengelolaan Database

Project menyediakan file database dengan nama "database/peminjaman_buku.sql".

Terdapat dua cara untuk menyiapkan database:

1. Melakukan import file SQL melalui menu Import di phpMyAdmin.
2. Menggunakan migration Laravel melalui perintah "php artisan migrate".

Gunakan salah satu metode sesuai kondisi database yang tersedia.

10. Evaluasi Pengerjaan

A. Tujuan Pengembangan

Membangun aplikasi perpustakaan berbasis web menggunakan Laravel yang dapat membantu pengelolaan informasi buku, anggota perpustakaan, dan aktivitas peminjaman.

B. Capaian Pengerjaan

Beberapa bagian yang telah berhasil diselesaikan antara lain:

- Membuat rancangan database dengan tiga tabel utama, yaitu "users", "books", dan "loans".
- Menyusun ERD untuk menggambarkan hubungan antartabel.
- Membuat migration serta model "Book", "Loan", dan "User".
- Menyiapkan relasi "Loan" dengan model "User" dan "Book".
- Menentukan pembagian hak akses pengguna melalui atribut "role".
- Menyusun dokumentasi program, fungsi model, dan panduan penanganan error.
- Menyediakan file database SQL.
- Menyimpan source code project pada repository GitHub.

C. Kendala Selama Pengerjaan

Dalam proses pengembangan, terdapat beberapa kendala teknis, di antaranya:

- Proses push ke GitHub sempat mengalami masalah "Repository not found" dan "rejected".
- Permasalahan Git diselesaikan dengan memperbaiki konfigurasi remote repository dan menjalankan "git pull --rebase".
- Pengaturan urutan migration perlu diperhatikan agar tabel yang saling berhubungan dapat dibuat tanpa kesalahan.

D. Bagian yang Masih Perlu Dikembangkan

Walaupun fondasi aplikasi telah tersedia, masih terdapat beberapa pekerjaan yang belum diselesaikan:

- Pembuatan controller, route, dan halaman untuk modul buku serta peminjaman.
- Penambahan relasi "hasMany" pada model "Book" dan "User".
- Penerapan sistem pengurangan dan penambahan stok secara otomatis.
- Implementasi perhitungan denda keterlambatan.
- Penggunaan konstanta atau enum untuk menjaga konsistensi status transaksi.
- Penerapan autentikasi dan pembatasan hak akses khusus admin.
- Pengujian terhadap fungsi-fungsi aplikasi.

E. Rencana Pengembangan Selanjutnya

Untuk menyempurnakan aplikasi, beberapa langkah yang direncanakan adalah:

1. Melengkapi relasi antar-model menggunakan "hasMany".
2. Mengembangkan "BookController" untuk proses CRUD data buku.
3. Membuat "LoanController" untuk menangani peminjaman, pengembalian, dan perhitungan denda.
4. Mengintegrasikan perubahan stok menggunakan "DB::transaction()".
5. Menyeragamkan status transaksi menggunakan konstanta atau enum.
6. Menambahkan sistem login serta pembatasan akses admin melalui fungsi "isAdmin()".

11. Kesimpulan

Pengembangan aplikasi peminjaman buku telah mencapai tahap penyusunan fondasi utama, meliputi perancangan database, pembuatan migration, model, serta dokumentasi teknis. Struktur tersebut menjadi dasar untuk membangun sistem perpustakaan yang lebih lengkap.

Meskipun demikian, beberapa fitur inti seperti pengelolaan buku, transaksi peminjaman, pengembalian, dan perhitungan denda masih membutuhkan implementasi lanjutan. Dengan menyelesaikan bagian tersebut, aplikasi diharapkan dapat berfungsi secara optimal dan membantu kegiatan administrasi perpustakaan sekolah.

12. Lisensi

Project ini menggunakan lisensi MIT License, yang memungkinkan penggunaan, pengembangan, dan modifikasi kode sesuai ketentuan lisensi.

Informasi lisensi dapat dilihat melalui: https://opensource.org/licenses/MIT
