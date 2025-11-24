# <div style="text-align: center;"> TSU Project Template <br> (Modular Monolith Edition) </div>

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

Ikuti langkah berikut untuk mengatur lingkungan pengembangan lokal:
1. Clone & Install Dependencies Pastikan menjalankan dump-autoload agar namespace kustom pada folder sources/ terbaca.
```bash
git clone <repository_url>
composer install
composer dump-autoload
```
2. Konfigurasi Environment Salin file konfigurasi dan atur kredensial database (MySQL/Oracle).
```bash
cp .env.example .env
php artisan key:generate
```
3. Setup Database & Modules Pastikan modul diaktifkan dan migrasi dijalankan.
```bash
php artisan module:enable Admin Users System
php artisan migrate --seed
```
4. Menjalankan Aplikasi
```bash
 php artisan serve
```

## 📝 Catatan Pengembang

- Namespace: Semua logika inti berada di bawah namespace App\ (untuk sources/app) dan Modules\ (untuk sources/Modules).
- Assets: Aset publik dikelola secara manual di public/assets dan public/assetsku. Pastikan path aset di file Blade mengarah ke direktori yang benar.

---

<div style="text-align: center; font-weight: bold"> Pusat Informasi, Komunikasi dan Digital (PIKDI) <br> Tiga Serangkai University </div>