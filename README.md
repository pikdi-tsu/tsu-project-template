# <p align="center"> TSU Project Template <br> (Modular Monolith Edition) </p>

## 📢 Description

Template aplikasi berbasis Laravel yang dikustomisasi dengan pendekatan arsitektur *Modular Monolith*. Proyek ini dirancang untuk mengakomodasi kompleksitas sistem informasi akademik dengan memisahkan logika bisnis berdasarkan domain (Modul), bukan hanya berdasarkan lapisan teknis.

## 📋 Project Overview

Proyek ini adalah *boilerplate* yang memindahkan struktur standar Laravel (`app/`) ke dalam direktori kustom `sources/` untuk mendukung skalabilitas jangka panjang.
Saat ini, aplikasi berjalan menggunakan **koneksi database langsung (Direct DB)** dengan autentikasi lokal berbasis sesi. Namun, arsitektur kode (Service Layer) telah disiapkan untuk transisi menuju implementasi berbasis API (*API-Driven*) di masa mendatang tanpa perlu merombak struktur utama.

## 🏗️ Struktur Direktori & Arsitektur

Perbedaan mendasar pada template ini adalah lokasi *core logic*. Direktori `sources/` berfungsi sebagai root namespace utama untuk menggantikan `app/` standar, dengan pembagian sebagai berikut:

```text
root/
├── public/assets/      # Aset statis (AdminLTE, Plugins, Custom UI)
├── sources/            # Direktori Utama Logika Aplikasi
│   ├── app/            # Logika Global (Shared Controllers, Models, Helpers)
│   └── Modules/        # Domain-Driven Modules
│       ├── Admin/      # Modul khusus manajemen Administrator & Konfigurasi
│       ├── System/     # Modul pengaturan sistem inti
│       └── Users/      # Modul manajemen pengguna (Dosen, Tendik, Mahasiswa)
```

Implementasi ini menggunakan pola nwidart/laravel-modules untuk memastikan setiap domain bisnis terisolasi dengan baik.

## 🛠️ Spesifikasi Teknis (Tech Stack)

- Framework Core: Laravel
- Architecture Pattern: Modular Monolith
- Database Interface: Eloquent ORM & yajra/laravel-datatables-oracle (Support MySQL & Oracle)
- Authentication: Custom Local Authentication (Session-based)
    - Pemisahan logika login untuk user internal (Dosen/Tendik) dan user eksternal (Mahasiswa).
- Frontend Stack:
    - Blade Templating Engine
    - Bootstrap 4 Ecosystem
    - AdminLTE Assets & Custom Components
    - Libraries: Select2, Summernote, SweetAlert2, Chart.js

## 🛣️ Roadmap Pengembangan

Proyek ini dikembangkan dengan peta jalan (roadmap) teknis sebagai berikut:

1. Phase 1 (Current): Struktur Modular Monolith, Autentikasi Lokal, Koneksi Database Langsung.
2. Phase 2: Refactoring Service Layer untuk persiapan abstraksi data.
3. Phase 3: Transisi ke Arsitektur berbasis API (Headless Readiness).

## ⚙️ Panduan Instalasi

Ikuti langkah berikut untuk mengatur lingkungan pengembangan lokal Anda:

1. **Clone & Install Dependencies**

    Karena struktur *core* Laravel berada di dalam folder `sources/`, semua perintah instalasi dan `artisan` **wajib** dijalankan di dalam folder tersebut.
   ```bash
   git clone <repository_url_proyek> tsu-project-template
   cd <nama_folder_proyek>/sources
   composer install atau composer update
   ```

2. **Penyesuaian Environment (Wajib Diperhatikan!)**
   
    Pastikan Anda masih berada di dalam direktori `sources/`. Salin `.env.example` menjadi `.env` dengan menjalankan perintah berikut:
    ```bash
    cp .env.example .env
    ```
    Setelah itu, buka file `.env` dan **wajib** sesuaikan kelompok variabel krusial berikut agar aplikasi dan fitur SSO berjalan lancar di *local* Anda:

   **🔹 Core & Database**
   - `APP_NAME`: Nama proyek baru (Contoh: "TSU Template").
   - `APP_URL`: URL lokal proyek baru (Contoh: "http://tsu-template.test").
   - `SESSION_COOKIE`: Ubah spesifik per proyek (Contoh: "template_session"). *Penting agar sesi login tidak bentrok dengan aplikasi TSU lain di browser.*
   - `DB_*`: Masukkan kredensial koneksi dan nama database lokal. 

   **🔹 Arsitektur Modular (Otomatisasi Prefix)**
   - `MODULE_FULL_NAME`: Nama lengkap proyek, huruf kecil & underscore (Contoh: "tsu_template").
   - `MODULE_NAME`: Prefix untuk tabel Spatie, huruf kecil (Contoh: "template").
   - `TABLE_NAME`: Kosongkan jadi `""` jika ingin menggunakan nama tabel bawaan, atau isi eksplisit.

   **🔹 Integrasi SSO & API (Homebase TSU)**
   - `TSU_SSO_CLIENT_ID` & `SECRET`: Kredensial SSO dari TSU Homebase.
   - `TSU_SSO_REDIRECT_URI`: Sesuaikan URL callback dengan URL lokal Anda (Contoh: "http://tsu-template.test/login/sso/callback").
   - `HOMEBASE_CLIENT_ID` & `SECRET`: Kredensial untuk jalur komunikasi API antar layanan.

   **🔹 Keamanan & Hak Akses Khusus**
   - `PIKDI_EMERGENCY_SECRET` & `RESCUE_SECRET`: Kunci rahasia untuk otorisasi *bypass/login* darurat tim PIKDI.
   - `APP_ALLOWED_ROLES`: Batasi role yang boleh mengakses aplikasi (Contoh: "dosen,tendik"). Kosongkan jika semua sivitas akademika diizinkan masuk.
   - `GMAPS_KEY`: Isi jika modul Anda menggunakan fitur pemetaan/lokasi.


3. **Generate Key & Clear Cache**
    *(Pastikan masih di dalam direktori `sources/`)*
   ```bash
   php artisan key:generate
   php artisan config:clear
   ```

4. **Setup Database & Modules**
   Pastikan Anda sudah membuat database kosong. Aktifkan modul dan jalankan migrasi beserta seeder-nya. *(Pastikan masih di dalam direktori `sources/`)*
   ```bash
   php artisan module:enable Admin Users System
   php artisan migrate --seed
   ```

5. **Menjalankan Aplikasi**
   
   Karena *entry point* (`index.php`) berada di luar direktori `sources/`, disarankan untuk **mengakses aplikasi langsung melalui Laragon** (misal: `http://[nama_proyek].test`).
   
   > [!WARNING]
   > Menjalankan `php artisan serve` dari dalam direktori `sources/` mungkin tidak akan berjalan dengan baik tanpa penyesuaian khusus pada konfigurasi *server* lokal Anda.

## 📝 Catatan Pengembang

- Namespace: Semua logika inti berada di bawah namespace App\ (untuk sources/app) dan Modules\ (untuk sources/Modules).
- Assets: Aset publik dikelola secara manual di public/assets dan public/assetsku. Pastikan path aset di file Blade mengarah ke direktori yang benar.

---

<div style="text-align: center; font-weight: bold"> Pusat Informasi, Komunikasi dan Digital (PIKDI) <br> Tiga Serangkai University </div>
