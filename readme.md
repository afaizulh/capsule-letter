# Capsule Letter

Website **time capsule** berbasis **PHP Native** yang memungkinkan pengguna menulis surat untuk diri sendiri di masa depan. Surat akan terkunci sampai tanggal pembukaan yang ditentukan. Saat dibuka, pengguna dapat membaca kembali harapan, target, dan pesan yang pernah ditulis, lalu merenungkan apa yang sudah tercapai.

Project ini merupakan tugas **CCIT FTUI Semester 3**.

---

## Daftar Isi

- [Tentang Proyek](#tentang-proyek)
- [Konsep & Cara Kerja](#konsep--cara-kerja)
- [Fitur Utama](#fitur-utama)
- [Tech Stack](#tech-stack)
- [Struktur Direktori](#struktur-direktori)
- [Alur Aplikasi](#alur-aplikasi)
- [Skema Database](#skema-database)
- [Instalasi & Menjalankan](#instalasi--menjalankan)
- [Konfigurasi](#konfigurasi)
- [Aturan Bisnis](#aturan-bisnis)
- [Keamanan](#keamanan)
- [CI / Workflow](#ci--workflow)
- [Catatan & Keterbatasan](#catatan--keterbatasan)

---

## Tentang Proyek

**Capsule Letter** adalah aplikasi web untuk membuat "surat kapsul waktu". Pengguna menulis surat, memilih tanggal kapan surat boleh dibuka, lalu menyimpannya. Selama tanggal tersebut belum tiba, isi surat dan lampirannya tidak dapat diakses.

Surat dapat disimpan sebagai:

- **Private** — hanya pemilik yang bisa membaca.
- **Public** — dapat dibaca siapa saja, tetapi identitas penulis tetap **anonim**.

Surat publik yang sudah terbuka dapat diberi **like** dan **komentar**. Komentar akan difilter otomatis terhadap daftar kata terlarang yang dikelola admin.

---

## Konsep & Cara Kerja

1. Pengguna mendaftar dan login.
2. Pengguna membuat surat: judul, kategori (opsional), isi (rich text), lampiran (opsional), tanggal pembukaan, dan visibilitas.
3. Surat disimpan dengan status **terkunci** sampai `unlock_date` terlewati.
4. Setelah tanggal tiba, isi surat dan lampiran dapat dibuka.
5. Surat publik muncul di feed beranda dan bisa di-like/dikomentari.
6. Admin mengelola kategori dan daftar kata terlarang untuk moderasi komentar.

---

## Fitur Utama

### Autentikasi
- Registrasi dengan nama depan, nama belakang, email, dan password.
- Password di-hash menggunakan `password_hash()`.
- Login dengan verifikasi `password_verify()`.
- Session-based authentication.
- Role pengguna: `user` dan `admin`.
- Logout dan proteksi halaman (redirect ke login jika belum masuk).

### Manajemen Surat
- **Create** surat dengan editor rich text (TinyMCE).
- Kategori surat (opsional).
- Lampiran gambar/dokumen: `jpg`, `jpeg`, `png`, `pdf`, `doc`, `docx` (maks. 2 MB).
- **Home dashboard** menampilkan statistik surat, feed surat publik, dan daftar surat milik pengguna.
- **My Letters** menampilkan total, jumlah terkunci, dan jumlah terbuka, serta aksi edit/detail/hapus.
- **Detail surat** dengan status lock/unlock, isi surat, lampiran, like, dan komentar.
- **Update** surat selama 3 hari sejak dibuat.
- **Delete** surat selama 7 hari sejak dibuat.
- Modal konfirmasi untuk update dan delete.

### Interaksi Sosial
- **Like** khusus surat publik (toggle like/unlike).
- **Komentar** khusus surat publik yang sudah terbuka.
- **Filter kata terlarang**: kata terlarang disensor menjadi `***` sebelum disimpan.
- Nama komentator ditampilkan dari bagian lokal email (sebelum `@`).

### Admin Panel
- Menambah dan menghapus **kategori** (slug dibuat otomatis).
- Menambah dan menghapus **kata terlarang** untuk moderasi komentar.
- Hanya dapat diakses oleh user dengan role `admin`.

### UI/UX
- Bootstrap 5.3 + Bootstrap Icons.
- Sidebar responsif (collapse di mobile).
- Font Space Grotesk + TinyMCE untuk pengalaman menulis.
- Tema minimalis hitam-putih dengan aksen oranye.
- Tampilan tanggal menggunakan zona waktu **Asia/Jakarta (WIB)**.

---

## Tech Stack

| Komponen | Teknologi |
|---|---|
| Bahasa | PHP 8+ (PHP Native, tanpa framework) |
| Database | MySQL / MariaDB (`mysqli`) |
| Frontend | HTML5, CSS3, JavaScript vanilla |
| CSS Framework | Bootstrap 5.3.3 (CDN) |
| Ikon | Bootstrap Icons 1.11.3 (CDN) |
| Rich Text Editor | TinyMCE 6.8.2 (CDN) |
| Font | Space Grotesk (Google Fonts) |
| Web Server | Apache (Laragon / XAMPP) |
| CI | GitHub Actions (self-hosted runner) |

---

## Struktur Direktori

```text
capsule-letter/
├── index.php                          # Redirect ke halaman home
├── readme.md
├── .github/
│   └── workflows/
│       └── ci.yml                     # CI: cek sintaks PHP & Docker
├── config/
│   ├── app.php                        # Base URL aplikasi
│   └── database.php                   # Koneksi database (mysqli)
├── controller/
│   ├── admin/
│   │   ├── process-category.php       # CRUD kategori (admin)
│   │   └── process-word.php           # CRUD kata terlarang (admin)
│   ├── auth/
│   │   ├── process-login.php          # Proses login
│   │   ├── process-logout.php         # Proses logout
│   │   └── process-register.php       # Proses registrasi
│   ├── comment/
│   │   └── process-comment.php        # Simpan komentar + filter kata
│   ├── create-letter/
│   │   └── process-create.php         # Simpan surat baru + upload
│   ├── delete-letter/
│   │   └── process-delete.php         # Hapus surat (batas 7 hari)
│   ├── edit-letter/
│   │   └── process-edit.php           # (masih kosong)
│   └── update-letter/
│       └── process-update.php         # Update surat (batas 3 hari)
├── resources/
│   ├── layout-page.php                # Template layout dasar
│   ├── css/
│   │   ├── home-my-letters.css
│   │   ├── my-letter.css
│   │   ├── public-letter-feed.css
│   │   ├── sidebar.css
│   │   └── update-letter.css
│   ├── includes/
│   │   ├── head.php                   # Meta, CDN, style global
│   │   ├── sidebar.php                # Navigasi sidebar
│   │   ├── profile-badge.php          # Badge user + logout
│   │   ├── footer.php                 # Footer
│   │   ├── scripts.php                # Bootstrap JS + init TinyMCE
│   │   └── script-eye.php             # Toggle show/hide password
│   ├── uploads/                       # Penyimpanan lampiran
│   └── views/
│       ├── home.php                   # Dashboard utama
│       ├── create.php                 # Form buat surat
│       ├── my-letter.php              # Daftar surat pengguna
│       ├── detail-letter.php          # Detail surat + like + komentar
│       ├── update-letter.php          # Form edit surat
│       ├── admin/
│       │   └── dashboard.php          # Panel admin
│       └── auth/
│           ├── login.php
│           └── register.php
└── ...
```

---

## Alur Aplikasi

```text
index.php
   └── redirect → resources/views/home.php
                      │
                      ├── belum login → auth/login.php
                      │                     └── process-login.php
                      │                           ├── admin → admin/dashboard.php
                      │                           └── user  → home.php
                      │
                      ├── Create  → create.php → process-create.php
                      ├── My Letter → my-letter.php
                      │                 ├── Update → update-letter.php → process-update.php
                      │                 └── Delete → process-delete.php
                      └── Detail → detail-letter.php
                                      ├── Like (POST)
                                      └── Comment → process-comment.php
```

### Alur pembuatan surat
`create.php` (form + TinyMCE) → `process-create.php` (validasi, upload lampiran, insert ke tabel `letters`) → redirect kembali ke `create.php` dengan pesan sukses/gagal.

### Alur pembukaan surat
`detail-letter.php` mengambil surat berdasarkan `id`. Surat hanya bisa diakses jika milik user **atau** `is_public = 1`. Isi surat hanya ditampilkan jika `unlock_date <= sekarang`. Lampiran hanya bisa diunduh jika surat sudah terbuka.

---

## Skema Database

Nama database: **`db_capsule_letter`**.

> Skema di bawah disusun dari query yang dipakai di kode (tidak ada file `.sql` di repo). Sesuaikan dengan database yang sebenarnya.

### Tabel `users`
| Kolom | Tipe | Keterangan |
|---|---|---|
| `id` | INT PK AUTO_INCREMENT | ID user |
| `name` | VARCHAR(255) | Nama lengkap |
| `email` | VARCHAR(255) UNIQUE | Email login |
| `password` | VARCHAR(255) | Hash password |
| `role` | VARCHAR(20) | `user` / `admin` |
| `created_at` | TIMESTAMP | Waktu registrasi |

### Tabel `letters`
| Kolom | Tipe | Keterangan |
|---|---|---|
| `id` | INT PK AUTO_INCREMENT | ID surat |
| `user_id` | INT FK → users.id | Pemilik surat |
| `category_id` | INT NULL FK → categories.id | Kategori (opsional) |
| `title` | VARCHAR(255) | Judul surat |
| `content` | LONGTEXT | Isi surat (HTML TinyMCE) |
| `attachment` | VARCHAR(255) NULL | Nama file lampiran |
| `unlock_date` | DATETIME | Tanggal surat dapat dibuka |
| `is_public` | TINYINT(1) | `0` private, `1` public |
| `created_at` | TIMESTAMP | Waktu pembuatan |
| `opened_at` | DATETIME NULL | (disebut di komentar kode) |

### Tabel `categories`
| Kolom | Tipe | Keterangan |
|---|---|---|
| `id` | INT PK AUTO_INCREMENT | ID kategori |
| `name` | VARCHAR(100) UNIQUE | Nama kategori |
| `slug` | VARCHAR(120) UNIQUE | Slug otomatis |

### Tabel `forbidden_words`
| Kolom | Tipe | Keterangan |
|---|---|---|
| `id` | INT PK AUTO_INCREMENT | ID kata |
| `word` | VARCHAR(100) UNIQUE | Kata yang difilter |

### Tabel `comments`
| Kolom | Tipe | Keterangan |
|---|---|---|
| `id` | INT PK AUTO_INCREMENT | ID komentar |
| `letter_id` | INT FK → letters.id | Surat terkait |
| `user_id` | INT FK → users.id | Penulis komentar |
| `comment` | TEXT | Isi komentar (sudah difilter) |
| `created_at` | DATETIME | Waktu komentar |

### Tabel `letter_likes`
| Kolom | Tipe | Keterangan |
|---|---|---|
| `id` | INT PK AUTO_INCREMENT | ID like |
| `letter_id` | INT FK → letters.id | Surat yang di-like |
| `user_id` | INT FK → users.id | User yang memberi like |

Disarankan ada constraint unik `(letter_id, user_id)` agar satu user hanya bisa memberi satu like per surat.

### Contoh SQL (saran)

```sql
CREATE DATABASE IF NOT EXISTS db_capsule_letter
  DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE db_capsule_letter;

CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  email VARCHAR(255) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role VARCHAR(20) NOT NULL DEFAULT 'user',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE categories (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL UNIQUE,
  slug VARCHAR(120) NOT NULL UNIQUE
);

CREATE TABLE forbidden_words (
  id INT AUTO_INCREMENT PRIMARY KEY,
  word VARCHAR(100) NOT NULL UNIQUE
);

CREATE TABLE letters (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  category_id INT NULL,
  title VARCHAR(255) NOT NULL,
  content LONGTEXT NOT NULL,
  attachment VARCHAR(255) NULL,
  unlock_date DATETIME NOT NULL,
  is_public TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  opened_at DATETIME NULL,
  CONSTRAINT fk_letters_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_letters_category FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
);

CREATE TABLE comments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  letter_id INT NOT NULL,
  user_id INT NOT NULL,
  comment TEXT NOT NULL,
  created_at DATETIME NOT NULL,
  CONSTRAINT fk_comments_letter FOREIGN KEY (letter_id) REFERENCES letters(id) ON DELETE CASCADE,
  CONSTRAINT fk_comments_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE letter_likes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  letter_id INT NOT NULL,
  user_id INT NOT NULL,
  UNIQUE KEY uniq_letter_user (letter_id, user_id),
  CONSTRAINT fk_likes_letter FOREIGN KEY (letter_id) REFERENCES letters(id) ON DELETE CASCADE,
  CONSTRAINT fk_likes_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

---

## Instalasi & Menjalankan

### Prasyarat
- PHP **8.0+** dengan ekstensi `mysqli`, `fileinfo`, `mbstring`, `dom`.
- MySQL atau MariaDB.
- Apache (Laragon / XAMPP) atau web server lain.
- Browser modern.

### Langkah

1. **Clone repositori**
   ```bash
   git clone https://github.com/afaizulh/capsule-letter.git
   ```

2. **Letakkan di web root**
   - Laragon: `C:\laragon\www\capsule-letter`
   - XAMPP: `C:\xampp\htdocs\capsule-letter`
   - Docker: mount project ke document root container.

   Nama folder **bebas**. Semua path file memakai `__DIR__`, dan URL memakai `APP_URL`, jadi tidak lagi bergantung pada nama folder `capsule-letter`.

3. **Buat database**
   - Buat database `db_capsule_letter`.
   - Import skema (lihat [Skema Database](#skema-database)).
   - Buat akun admin secara manual:
     ```sql
     INSERT INTO users (name, email, password, role)
     VALUES ('Admin', 'admin@example.com', '<hash>', 'admin');
     ```
     Hash dibuat dengan:
     ```bash
     php -r "echo password_hash('password-anda', PASSWORD_DEFAULT);"
     ```

4. **Sesuaikan konfigurasi lewat environment variable**
   - `APP_URL` → URL publik aplikasi (default `http://localhost:8080`).
   - `DB_HOST`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`, `DB_PORT` → koneksi database.
   - Contoh (PowerShell):
     ```powershell
     $env:APP_URL="http://localhost:8080"
     $env:DB_HOST="127.0.0.1"
     $env:DB_DATABASE="db_capsule_letter"
     $env:DB_USERNAME="root"
     $env:DB_PASSWORD=""
     $env:DB_PORT="3306"
     ```
   - Contoh (Docker Compose):
     ```yaml
     environment:
       APP_URL: "http://localhost:8080"
       DB_HOST: "db"
       DB_DATABASE: "db_capsule_letter"
       DB_USERNAME: "root"
       DB_PASSWORD: "secret"
       DB_PORT: "3306"
     ```
   - Sesuaikan `APP_URL` dengan lokasi aplikasi:
     - Laragon subfolder: `http://localhost/capsule-letter`
     - Docker / document root: `http://localhost:8080`

5. **Pastikan folder upload dapat ditulis**
   - `resources/uploads/` harus ada dan writable.

6. **Jalankan**
   - Akses sesuai nilai `APP_URL`, misalnya `http://localhost:8080/`.
   - `index.php` akan mengarahkan ke halaman login/home.

---

## Konfigurasi

### `config/app.php`
```php
$appUrl = getenv('APP_URL');

$baseUrl = is_string($appUrl) && trim($appUrl) !== ''
    ? rtrim(trim($appUrl), '/')
    : 'http://localhost:8080';
```
`$baseUrl` digunakan sebagai basis semua link, redirect, form action, dan asset. Ubah lewat environment variable `APP_URL` jika port/folder berbeda.

### `config/database.php`
```php
$servername = $readEnv('DB_HOST', '127.0.0.1');
$database   = $readEnv('DB_DATABASE', 'db_capsule_letter');
$username   = $readEnv('DB_USERNAME', 'root');
$port       = (int) $readEnv('DB_PORT', '3306');
$password   = getenv('DB_PASSWORD') ?: '';

$conn = mysqli_connect($servername, $username, $password, $database, $port);
```
Mengembalikan objek koneksi `mysqli` yang dipakai oleh controller dan view.

| Variable | Default | Keterangan |
|---|---|---|
| `APP_URL` | `http://localhost:8080` | URL publik aplikasi |
| `DB_HOST` | `127.0.0.1` | Host database |
| `DB_DATABASE` | `db_capsule_letter` | Nama database |
| `DB_USERNAME` | `root` | User database |
| `DB_PASSWORD` | `""` | Password database |
| `DB_PORT` | `3306` | Port database |

> **Docker:** gunakan `DB_HOST=db` (nama service MySQL di Compose), bukan `localhost`. Di dalam container PHP, `localhost` menunjuk ke container PHP itu sendiri.
>
> **Produksi:** jangan commit kredensial. Set environment variable melalui server/Compose/secret manager.

---

## Aturan Bisnis

| Aturan | Detail |
|---|---|
| Surat terkunci | Isi & lampiran hanya bisa dibuka jika `unlock_date <= now` |
| Akses surat | Pemilik surat **atau** surat `is_public = 1` |
| Batas edit | 3 hari sejak `created_at`; edit tidak mereset deadline |
| Batas hapus | 7 hari sejak `created_at` |
| Like | Hanya surat publik, satu user satu like (toggle) |
| Komentar | Hanya surat publik yang sudah terbuka |
| Filter kata | Kata terlarang diganti `*` sejumlah panjang kata |
| Anonimitas | Surat publik ditampilkan sebagai "Anonymous" |
| Zona waktu | `Asia/Jakarta` (`+07:00`) |

---

## Keamanan

Beberapa praktik keamanan yang sudah diterapkan:

- **Prepared statements** (`mysqli_prepare` + `bind_param`) untuk mencegah SQL injection.
- **Password hashing** dengan `password_hash()` dan `password_verify()`.
- **CSRF token** pada aksi update, delete, dan like (`hash_equals`).
- **Output escaping** dengan `htmlspecialchars()`.
- **Sanitasi HTML** isi surat/komentar menggunakan `DOMDocument` dengan allowlist tag dan atribut.
- **Validasi upload**: ekstensi, ukuran (2 MB), dan MIME type (`finfo`).
- **Proteksi path traversal** saat mengunduh lampiran (`basename`, `realpath`, cek direktori).
- **Header keamanan** untuk unduhan: `X-Content-Type-Options: nosniff`, `Content-Disposition`, `Cache-Control: private, no-store`.

---

## CI / Workflow

File `.github/workflows/ci.yml` menjalankan pengecekan pada setiap push ke `main`, `master`, `develop`, dan setiap pull request:

1. **Cek sintaks PHP** — menjalankan `php -l` pada seluruh file `.php`.
2. **Verifikasi Docker** — cek `docker --version` dan `docker compose version`, serta validasi `docker compose config` jika file compose tersedia.

Workflow menggunakan **self-hosted runner**.

---

## Catatan & Keterbatasan

Beberapa hal yang perlu diketahui untuk pengembangan selanjutnya:

- **Belum ada file migrasi/seed SQL** di repo. Skema database perlu dibuat manual.
- **`controller/edit-letter/process-edit.php` masih kosong**. Proses edit sebenarnya ditangani oleh `update-letter/process-update.php`.
- **CSRF belum diterapkan** pada form create dan comment.
- **Validasi komentar** (surat publik & sudah terbuka) belum dicek ulang di server pada `process-comment.php`.
- **Hapus data admin** menggunakan `GET` (`?action=delete&id=...`), sebaiknya diubah ke `POST` + CSRF.
- **Like toggle** menggunakan pola hapus-lalu-insert; constraint unik `(letter_id, user_id)` tetap disarankan.
- **Folder `resources/uploads/`** berisi file contoh; pastikan tidak menyimpan file sensitif dan tambahkan aturan agar file tidak dieksekusi sebagai script.
- **Kredensial database** masih hardcoded; sebaiknya dipindahkan ke environment variable untuk produksi.

---

## Kontributor

Dibuat untuk project **CCIT FTUI Semester 3**.

Repository: [github.com/afaizulh/capsule-letter](https://github.com/afaizulh/capsule-letter)
