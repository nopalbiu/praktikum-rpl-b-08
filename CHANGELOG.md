# Changelog

Semua perubahan penting pada proyek "Wearworeworn E-Commerce" akan didokumentasikan di file ini.

## [1.0.0] - 2026-07-08

### Added
- Sistem autentikasi pengguna (Register, Login, Logout).
- Katalog produk dinamis dengan tampilan *grid* dan informasi ketersediaan stok.
- Fitur manajemen keranjang belanja (tambah produk, ubah kuantitas, hapus item).
- Kalkulasi total harga keranjang secara otomatis.
- Fitur checkout pesanan pelanggan beserta rekapitulasi keranjang.
- Halaman riwayat pesanan pelanggan dengan pelacakan status (*tracking*).
- Dashboard admin untuk manajemen produk (Create, Read, Update, Delete) dan stok.
- Dashboard admin untuk pembaruan status pesanan pelanggan secara *real-time*.
- Ekstraksi data (parsing) alamat pengiriman otomatis pada form *checkout*.
- Algoritma pemformatan otomatis untuk status pesanan dari database (misal: `MENUNGGU_PEMBAYARAN` menjadi `menunggu pembayaran`).
- Implementasi 6 Unit Test menggunakan pola AAA (Arrange-Act-Assert) untuk menguji logika inti store (status pesanan, parsing alamat, dan kalkulasi keranjang).

### Fixed
- Perbaikan bug keranjang di mana user bisa memasukkan kuantitas negatif via *inspect element* (sekarang otomatis di-set menjadi 0).
- Penanganan format alamat yang tidak standar pada saat *checkout* menggunakan *regex fallback*.
- Memperbaiki responsivitas UI katalog produk yang sebelumnya pecah saat dibuka di perangkat *mobile*.
- *Handling error* saat gambar produk gagal dimuat (menampilkan gambar *default placeholder*).