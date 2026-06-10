# WearWoreWorn
Aplikasi katalog fashion untuk menampilkan koleksi outfit terbaru secara interaktif. Untuk kalangan remaja dan dewasa yang sedang mencari outfit terbaru.

## Anggota Kelompok
| Nama | NIM | Role |
| :-- | :-- | :-- |
| Naufal Abiyyu Ghazy | L0124028 | Project Manager |
| Muhammad Yahya Saputra | L0124025 | UI/UX Designer |
| Andrew Yusuf Valentino Padang | L0124039 | Front End Developer |
| Hesa Khansa Arka | L0124158 | Back End Developer |

## Fitur Utama
1. **Katalog & Manajemen Produk:** Menampilkan daftar koleksi outfit secara visual dengan fitur *pagination*. Admin dapat menambah, mengedit, menghapus, serta mengatur stok produk (termasuk *multi-size*).
2. **Sistem Checkout & Pesanan:** Customer dapat menambahkan produk ke keranjang belanja, melihat detail (bahan, *size chart*, harga) dan melakukan *checkout*. Admin dapat mengelola dan memperbarui status pesanan.
3. **Autentikasi & Otorisasi Hak Akses:** Fitur Register, Login, Logout, dan perlindungan *Custom Middleware* untuk memisahkan hak akses secara aman antara halaman panel Admin dan halaman Customer biasa.

## Tech Stack
- **Framework Backend:** Laravel 11 (PHP)
- **Database:** MySQL
- **Frontend / Styling:** Tailwind CSS, Blade Templating
- **Asset Compiler:** Vite (Node.js)
- **Version Control:** Git & GitHub

## Cara Instalasi dan Menjalankan

Terdapat dua cara untuk mencoba dan mengakses aplikasi kami:

### Opsi 1: Akses Langsung (Live Hosting)
[https://wearworeworn.onrender.com/](https://wearworeworn.onrender.com/)

Link login admin 
[https://wearworeworn.onrender.com/admin/login](https://wearworeworn.onrender.com/admin/login)

### Opsi 2: Instalasi Lokal (Dari 0)

1. Unduh dan instal **Git** melalui tautan: [https://git-scm.com/downloads](https://git-scm.com/downloads)
2. Unduh dan instal **Laragon Full** (termasuk PHP dan MySQL): [https://laragon.org/download/](https://laragon.org/download/)
3. Unduh dan instal **Composer** (Dependency Manager PHP): [https://getcomposer.org/download/](https://getcomposer.org/download/)
4. Unduh dan instal **Node.js** (dibutuhkan untuk *compile* CSS/JS): [https://nodejs.org/](https://nodejs.org/)
5. Buka aplikasi **Laragon** di komputer Anda, lalu klik tombol **Start All** untuk menyalakan server Apache dan database MySQL.
6. Buka *Terminal*, *Command Prompt*, atau *Git Bash*, lalu arahkan ke folder root server Laragon (biasanya di direktori `C:\laragon\www`).
7. Jalankan perintah berikut di untuk mengunduh kode aplikasi dari GitHub:
   `git clone https://github.com/nopalbiu/praktikum-rpl-b-08`
8. Masuk ke dalam folder proyek dan sub-folder **src** yang sudah di clone
   `cd praktikum-rpl-b-08/src`
9. Install semua dependensi PHP (Laravel)
   `composer install`
10. Install dependensi *frontend*
    `npm install`
11. Buat file *environment* baru dengan menyalin *template* bawaan
    `cp .env.example .env`
12. Buka file .env di dalam folder proyek menggunakan misal VS Code, dan isikan dibawah ini
    ```bash
    DB_CONNECTION=mysql
    DB_HOST=wearworeworn-wearworeworn.g.aivencloud.com
    DB_PORT=16066
    DB_DATABASE=defaultdb
    DB_USERNAME=avnadmin
    DB_PASSWORD=AVNS_hNSGeAkIRDMfil0_otb
13. Tetap di file .env, isikan dibawah ini di paling bawah file .env
    `CLOUDINARY_URL=cloudinary://444414231413839:eM79SjKxaDTuYxLczxeJNlhz43g@dsejnow6k`
15. Buat *application key* Laravel yang baru
    `php artisan key:generate`
16. Nyalakan server lokal Laravel
    `php artisan serve`
17. Buka tab terminal baru di folder proyek yang sama, lalu jalankan compiler aset Vite
    `npm run dev`

## Akun Testing
- Akun Admin: adminwww@gmail.com | admin123
