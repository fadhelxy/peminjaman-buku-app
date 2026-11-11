Sistem Informasi Peminjaman Buku

Aplikasi berbasis web yang dikembangkan dengan Laravel untuk membantu pengelolaan buku dan aktivitas peminjaman pada perpustakaan sekolah. Sistem ini dirancang agar pencatatan data buku, anggota, serta transaksi dapat dilakukan secara lebih terorganisir.

1. Informasi Aplikasi

Program ini menggunakan tiga tabel utama dalam database, yaitu "users", "books", dan "loans".

Pengguna aplikasi dibagi menjadi dua berdasarkan kolom "role":

- Admin: mempunyai akses untuk mengelola data buku dan transaksi peminjaman.
- Siswa: merupakan role default pengguna yang melakukan peminjaman buku dan memiliki informasi tambahan berupa "kelas".

Alur Kerja Sistem

Proses transaksi dalam aplikasi dirancang melalui beberapa tahapan:

1. Siswa memilih dan meminjam buku. Sistem menyimpan transaksi pada tabel "loans" dengan mengisi "loan_date", "due_date", serta status awal "dipinjam".
2. Ketika buku dikembalikan, sistem memperbarui kolom "returned_at" dan mengubah status transaksi.
3. Jika pengembalian dilakukan setelah tanggal batas, jumlah denda akan disimpan pada kolom "fine".

Status pengembangan: Struktur database melalui migration dan model sudah tersedia. Route saat ini masih menampilkan halaman awal, sedangkan controller untuk pengelolaan buku dan peminjaman belum dibuat.

2. Modul yang Tersedia

Modul| Kegunaan
Books| Mengelola informasi buku yang terdiri dari judul, penulis, penerbit, tahun terbit, dan stok.
Loans| Menyimpan catatan peminjaman, tanggal pinjam, batas pengembalian, tanggal kembali, status, dan denda.
Users| Menyimpan informasi akun pengguna, termasuk nama, email, password, role, dan kelas.

3. Teknologi Pengembangan

Beberapa teknologi yang digunakan dalam project ini adalah:

- PHP
- Laravel Framework
- MySQL
- XAMPP
- Composer

4. Rancangan Entity Relationship Diagram

Database memiliki hubungan one-to-many antara tabel "users" dengan "loans", serta tabel "books" dengan "loans".

Artinya, satu pengguna dapat mempunyai beberapa catatan peminjaman dan satu buku dapat muncul pada beberapa transaksi.

erDiagram
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

    USERS ||--o{ LOANS : memiliki_transaksi
    BOOKS ||--o{ LOANS : tercatat_dalam

Keterangan:

- "PK" merupakan Primary Key sebagai identitas utama setiap tabel.
- "FK" merupakan Foreign Key yang menghubungkan tabel satu dengan lainnya.
- "UK" merupakan Unique Key untuk memastikan email tidak memiliki nilai duplikat.

5. Penjelasan Model dan Route

5.1 Model Book

Lokasi file: "app/Models/Book.php"

Bagian| Jenis| Penjelasan
"$fillable"| Properti| Menentukan atribut yang dapat disimpan secara massal, yaitu "title", "author", "publisher", "year", dan "stock".

5.2 Model Loan

Lokasi file: "app/Models/Loan.php"

Bagian| Jenis| Parameter| Return| Penjelasan
"$fillable"| Properti| -| -| Memuat atribut transaksi yang dapat diisi, meliputi "user_id", "book_id", "loan_date", "due_date", "returned_at", "status", dan "fine".
"$casts"| Properti| -| -| Mengatur atribut tanggal agar dikonversi menjadi objek Carbon.
"user()"| Relasi| -| "BelongsTo"| Mengambil informasi pengguna yang melakukan peminjaman melalui "user_id". Contoh: "$loan->user->name".
"book()"| Relasi| -| "BelongsTo"| Mengambil informasi buku berdasarkan "book_id". Contoh: "$loan->book->title".

5.3 Model User

Lokasi file: "app/Models/User.php"

Bagian| Jenis| Parameter| Return| Penjelasan
"$fillable"| Properti| -| -| Menentukan data yang boleh diisi, yaitu "name", "email", "password", "role", dan "kelas".
"$hidden"| Properti| -| -| Menyembunyikan "password" dan "remember_token" ketika data diubah ke format array atau JSON.
"casts()"| Fungsi| -| "array"| Mengatur "email_verified_at" sebagai datetime dan melakukan hashing otomatis pada password.
"isAdmin()"| Fungsi| -| "bool"| Memeriksa apakah nilai role pengguna adalah "admin". Mengembalikan "true" jika sesuai dan "false" jika tidak.

5.4 Route Web

File yang digunakan: "routes/web.php"

Route yang sudah tersedia pada tahap ini adalah:

Method| Endpoint| Proses| Hasil
GET| "/"| Menjalankan closure| Memanggil halaman "welcome".

6. Pemeriksaan Program dan Debugging

Pada tahap pemeriksaan, terdapat beberapa bagian yang perlu dilengkapi agar seluruh fungsi aplikasi dapat berjalan dengan baik.

6.1 Hasil Identifikasi

No.| Bagian yang Diperiksa| Dampak| Solusi
1| Model "Book" belum mempunyai relasi ke "Loan".| Pemanggilan "$book->loans" tidak dapat digunakan.| Menambahkan relasi "hasMany".
2| Model "User" belum mempunyai relasi ke "Loan".| Riwayat peminjaman siswa belum bisa diakses melalui relasi.| Membuat fungsi "loans()".
3| Controller dan route books serta loans belum tersedia.| Fitur belum dapat diakses melalui browser.| Membuat "BookController" dan "LoanController", kemudian mendaftarkan route-nya.
4| Status transaksi masih berupa string bebas.| Penulisan status berpotensi tidak konsisten dan menyebabkan kesalahan filter.| Menggunakan konstanta atau enum PHP.
5| Pengelolaan stok belum terhubung dengan transaksi.| Jumlah stok dapat menjadi tidak sesuai.| Menggunakan "DB::transaction()" untuk proses peminjaman dan pengembalian.

6.2 Implementasi Relasi Tambahan

Untuk melengkapi hubungan antar tabel, kode berikut dapat ditambahkan ke masing-masing model.

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

6.3 Daftar Error dan Solusinya

Pesan Error| Penyebab| Solusi
"Base table or view not found" / "Failed to open the referenced table"| Migration "loans" dijalankan sebelum tabel "users" atau "books" akibat urutan timestamp yang tidak sesuai.| Atur timestamp migration agar "loans" berjalan paling akhir, kemudian jalankan "php artisan migrate:fresh".
"SQLSTATE[23000]: Integrity constraint violation"| Nilai "user_id" atau "book_id" tidak ditemukan pada tabel yang menjadi referensi.| Pastikan data pengguna dan buku sudah tersedia.
"MassAssignmentException"| Kolom yang akan disimpan belum terdaftar di "$fillable".| Tambahkan atribut yang diperlukan ke model.
"Call to undefined relationship"| Fungsi relasi belum dibuat pada model yang dipanggil.| Tambahkan fungsi relasi sesuai kebutuhan.
"Class "App\Models\Loan" not found"| Nama class, namespace, atau lokasi file model tidak sesuai.| Periksa penamaan file dan jalankan "composer dump-autoload".

6.4 Perintah Pendukung Debugging

Beberapa perintah yang dapat digunakan selama proses pemeriksaan aplikasi:

Perintah| Kegunaan
"dd($loan);"| Melihat isi variabel secara langsung.
"dump($request->all());"| Memeriksa data yang dikirim melalui request.
"Log::info('data pinjam', $data);"| Menyimpan informasi ke file log aplikasi.
"php artisan route:list"| Melihat daftar route yang tersedia.
"php artisan migrate:status"| Mengetahui status seluruh migration.
"php artisan tinker"| Menguji model melalui terminal Laravel.

Contoh pengujian relasi model menggunakan Tinker:

App\Models\Loan::with('user','book')->first();

Untuk membantu menampilkan informasi kesalahan secara lengkap selama pengembangan, konfigurasi berikut dapat digunakan pada file ".env":

APP_DEBUG=true

7. Panduan Menjalankan Project

Berikut langkah-langkah untuk memasang aplikasi pada perangkat lokal.

Langkah 1 - Mengambil Source Code

Clone repository project melalui terminal:

git clone https://github.com/kalilasyifazzahra/peminjaman-buku.git
cd peminjaman-buku

Langkah 2 - Instalasi Library

Pasang dependency yang dibutuhkan oleh aplikasi menggunakan Composer.

composer install

Langkah 3 - Menyiapkan Konfigurasi

Buat file ".env" berdasarkan template yang tersedia dan buat application key.

cp .env.example .env
php artisan key:generate

Langkah 4 - Konfigurasi Database

Buka phpMyAdmin melalui XAMPP, kemudian buat database bernama "peminjaman_buku".

Sesuaikan konfigurasi koneksi database pada file ".env":

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=peminjaman_buku
DB_USERNAME=root
DB_PASSWORD=

Langkah 5 - Membuat Struktur Tabel

Jalankan migration untuk membentuk tabel database.

php artisan migrate

Langkah 6 - Menjalankan Server Lokal

Gunakan perintah berikut untuk mengaktifkan aplikasi:

php artisan serve

Jika berhasil, aplikasi dapat dibuka melalui browser pada alamat:

"http://127.0.0.1:8000"

8. Informasi File Database

Project ini juga menyediakan file database dalam format SQL dengan lokasi:

"database/peminjaman_buku.sql"

Untuk menggunakan database tersebut, tersedia dua pilihan:

1. Import file SQL melalui menu Import di phpMyAdmin.
2. Menjalankan migration menggunakan perintah "php artisan migrate".

Pilih metode yang sesuai dengan kondisi database agar tidak terjadi konflik atau duplikasi tabel.

9. Hasil Evaluasi Pengembangan

9.1 Target Pembuatan

Membuat aplikasi perpustakaan berbasis Laravel yang dapat digunakan untuk mengelola informasi buku, data pengguna, dan transaksi peminjaman secara terstruktur.

9.2 Pekerjaan yang Telah Diselesaikan

Berdasarkan proses pengembangan yang telah dilakukan, berikut hasil yang sudah tersedia:

- Database telah dirancang menggunakan tabel "users", "books", dan "loans".
- ERD telah dibuat untuk menggambarkan hubungan antar tabel dengan relasi one-to-many.
- Migration dan model "Book", "Loan", serta "User" telah disiapkan.
- Model "Loan" memiliki relasi "belongsTo" menuju model "User" dan "Book".
- Pembagian jenis pengguna telah diatur melalui kolom "role", yaitu "admin" dan "siswa".
- Dokumentasi program, penjelasan fungsi, dan panduan debugging telah disusun.
- File database "database/peminjaman_buku.sql" telah disediakan.
- Source code aplikasi telah disimpan pada GitHub.

9.3 Kendala Selama Pengembangan

Dalam proses pembuatan project, beberapa kendala teknis yang ditemukan antara lain:

- Pengiriman source code ke GitHub sempat mengalami error "Repository not found" dan "rejected".
- Masalah tersebut diselesaikan dengan memperbaiki konfigurasi remote dan menjalankan "git pull --rebase".
- Pengurutan migration perlu diperiksa agar tabel "loans" dibuat setelah tabel "users" dan "books".

9.4 Kekurangan yang Masih Ditemukan

Beberapa komponen aplikasi masih perlu disempurnakan, yaitu:

- Controller, route, dan halaman untuk pengelolaan buku serta peminjaman belum tersedia.
- Relasi "loans()" pada model "Book" dan "User" belum ditambahkan.
- Pengaturan stok otomatis belum diterapkan.
- Mekanisme perhitungan denda keterlambatan belum tersedia.
- Pengujian fitur aplikasi belum dilakukan secara menyeluruh.

9.5 Tahapan Pengembangan Berikutnya

Rencana penyempurnaan aplikasi meliputi beberapa hal berikut:

1. Melengkapi relasi "hasMany" pada model "Book" dan "User".
2. Mengembangkan "BookController" untuk menjalankan fungsi CRUD buku.
3. Membuat "LoanController" untuk mengatur proses peminjaman, pengembalian, dan perhitungan denda.
4. Menghubungkan perubahan stok dengan transaksi melalui "DB::transaction()".
5. Menggunakan konstanta atau enum untuk menjaga keseragaman status peminjaman.
6. Menambahkan sistem login dan pembatasan akses admin menggunakan fungsi "isAdmin()".

10. Kesimpulan

Dari hasil pengembangan yang telah dilakukan, aplikasi Sistem Peminjaman Buku sudah mempunyai fondasi utama berupa struktur database, migration, model, dan dokumentasi program.

Hubungan antara data pengguna, buku, dan transaksi juga telah dirancang melalui ERD. Meskipun demikian, masih terdapat beberapa bagian yang membutuhkan implementasi lanjutan, terutama controller, route, tampilan, serta mekanisme peminjaman dan pengembalian.

Pengembangan berikutnya akan difokuskan pada penyelesaian fitur-fitur tersebut agar aplikasi dapat digunakan sebagai sistem pengelolaan perpustakaan sekolah.

11. Lisensi Project

Project ini menggunakan MIT License. Lisensi ini memperbolehkan penggunaan, pengembangan, dan modifikasi source code dengan tetap mengikuti ketentuan lisensi yang berlaku.

Informasi lebih lanjut mengenai lisensi dapat dilihat di:

https://opensource.org/licenses/MIT
