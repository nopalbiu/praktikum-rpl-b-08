# Software Requirements Specification (SRS) - WearWoreWorn

## 1. Pendahuluan

### 1.1 Tujuan Dokumen
Dokumen ini mendeskripsikan kebutuhan software dan web untuk aplikasi WearWoreWorn. Tujuannya adalah memberikan panduan teknis bagi tim pengembang dan menjadi kontrak spesifikasi fitur dengan pemangku kepentingan.

### 1.2 Ruang Lingkup
WearWoreWorn adalah platform katalog outfit digital yang berfokus pada penyediaan referensi outfit bagi pengguna remaja dan dewasa. Sistem mencakup fitur manajemen katalog oleh admin dan eksplorasi katalog serta filter produk oleh pengguna.

### 1.3 Definisi dan Akronim
- SRS: Software Requirements Specification.
- FR: Functional Requirement (Kebutuhan Fungsional).
- NFR: Non-Functional Requirement (Kebutuhan Non-Fungsional).
- Admin: Pengelola data katalog pakaian.
- User: Pengguna akhir yang mencari referensi outfit.

## 2. Deskripsi Umum

### 2.1 Perspektif Produk
WearWoreWorn adalah aplikasi katalog outfit yang dirancang untuk menyederhanakan pencarian referensi outfit bagi pengguna. Sistem ini mengintegrasikan data produk dari berbagai kategori ke dalam satu UI yang interaktif.

### 2.2 Fungsi Produk
Fungsi utama sistem meliputi:
- Penjelajahan katalog pakaian secara visual.
- Pencarian dan penyaringan (filter) produk berdasarkan kategori.
- Manajemen data katalog (tambah, ubah, hapus) oleh admin.

### 2.3 Karakteristik Pengguna
- User: Remaja dan dewasa yang mencari tren outfit. Membutuhkan UI yang mudah digunakan dan nyaman dilihat.
- Admin: Pengelola konten yang memiliki hak akses untuk memperbarui stok dan informasi produk di katalog.

### 2.4 Batasan
- Aplikasi ini berfokus pada fitur visual katalog dan belum mendukung fitur transaksi pembayaran.
- Memerlukan koneksi internet yang stabil untuk memuat gambar produk secara real-time.