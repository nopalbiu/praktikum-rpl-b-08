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

## 3. Kebutuhan Fungsional
- FR-01: Sistem menyediakan kolom pencarian produk agar pengguna dapat menemukan item yang lebih sesuai dengan yang dicari. Prioritas: High. Ref: US-01.
- FR-02: Sistem menyediakan fitur filter produk berdasarkan kategori tertentu pada katalog utama untuk memudahkan navigasi. Prioritas: Medium. Ref: US-02.
- FR-03: Sistem menampilkan informasi detail produk yang mencakup deskripsi bahan pakaian dan tabel ukuran. Prioritas: Medium. Ref: US-03.
- FR-04: Sistem memungkinkan pelanggan untuk memilih ukuran dan menambahkan produk tersebut ke dalam keranjang belanja. Prioritas: High. Ref: US-04.
- FR-05: Sistem memproses transaksi checkout dengan menghitung total biaya secara otomatis setelah pelanggan melengkapi data pengiriman. Prioritas: High. Ref: US-05.
- FR-06: Sistem memberikan akses kepada Admin untuk menambahkan artikel pakaian baru ke dalam katalog produk. Prioritas: High. Ref: US-06.
- FR-07: Sistem menyediakan daftar pesanan masuk bagi Admin yang lengkap dengan detail barang dan alamat tujuan pelanggan. Prioritas: High. Ref: US-07.
- FR-08: Sistem memungkinkan Admin untuk memperbarui jumlah stok pada setiap varian ukuran produk. Prioritas: Medium. Ref: US-08.
- FR-09: Sistem memungkinkan Admin untuk mengubah detail informasi produk yang sudah ada di katalog. Prioritas: Low. Ref: US-09.
- FR-10: Sistem memungkinkan Admin untuk menghapus produk tertentu dari tampilan katalog. Prioritas: medium. Ref: US-10.