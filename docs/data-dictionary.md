# Data Dictionary - WearWoreWorn

| Tabel | Kolom | Tipe Data | Constraint | Keterangan |
| :--- | :--- | :--- | :--- | :--- |
| roles | id_role | INT | PK, AUTO_INCREMENT | ID unik role pengguna |
| roles | nama_role | VARCHAR(50) | NOT NULL | Nama role (Admin, Customer) |
| users | id_user | INT | PK, AUTO_INCREMENT | ID unik pengguna |
| users | id_role | INT | FK, NOT NULL | Referensi ke tabel roles |
| users | nama | VARCHAR(100) | NOT NULL | Nama lengkap pengguna |
| users | email | VARCHAR(100) | UNIQUE, NOT NULL | Email untuk login |
| users | password | VARCHAR(255) | NOT NULL | Password (hashed dengan bcrypt) |
| categories | id_category | INT | PK, AUTO_INCREMENT | ID unik kategori pakaian |
| categories | nama_category | VARCHAR(100) | NOT NULL | Nama label kategori |
| products | id_product | INT | PK, AUTO_INCREMENT | ID unik produk pakaian |
| products | nama_product | VARCHAR(255) | NOT NULL | Nama atau judul produk |
| products | deskripsi | TEXT | | Penjelasan detail produk |
| products | material_pakaian | VARCHAR(150) | | Informasi jenis bahan pakaian |
| products | url_size_chart | VARCHAR(255) | | Lokasi file gambar panduan ukuran |
| products | harga | DECIMAL(10,2) | NOT NULL | Harga jual produk |
| product_category | id_product | INT | PK, FK, NOT NULL | Referensi ke tabel products |
| product_category | id_category | INT | PK, FK, NOT NULL | Referensi ke tabel categories |
| product_images | id_image | INT | PK, AUTO_INCREMENT | ID unik gambar |
| product_images | id_product | INT | FK, NOT NULL | Referensi produk pemilik gambar |
| product_images | url_gambar | VARCHAR(255) | NOT NULL | Lokasi URL/Path file gambar |
| product_images | is_primary | TINYINT(1) | NOT NULL, DEFAULT 0 | Cover gambar utama |
| sizes | id_size | INT | PK, AUTO_INCREMENT | ID unik ukuran baku |
| sizes | nama_size | VARCHAR(10) | NOT NULL | Nama ukuran (S, M, L, XL, dll) |
| product_variants | id_variant | INT | PK, AUTO_INCREMENT | ID unik kombinasi varian |
| product_variants | id_product | INT | FK, NOT NULL | Referensi ke model produk |
| product_variants | id_size | INT | FK, NOT NULL | Referensi ke jenis ukuran |
| product_variants | stok | INT | NOT NULL, DEFAULT 0 | Jumlah stok fisik yang tersedia |
| carts | id_cart | INT | PK, AUTO_INCREMENT | ID unik keranjang belanja |
| carts | id_user | INT | FK, NOT NULL | Referensi ke pemilik keranjang |
| cart_items | id_cart_item | INT | PK, AUTO_INCREMENT | ID unik item dalam keranjang |
| cart_items | id_cart | INT | FK, NOT NULL | Referensi ke keranjang induk |
| cart_items | id_variant | INT | FK, NOT NULL | Referensi varian yang dipilih |
| cart_items | qty | INT | NOT NULL, DEFAULT 1 | Jumlah barang yang dimasukkan |
| orders | id_order | INT | PK, AUTO_INCREMENT | ID unik pesanan (checkout) |
| orders | id_user | INT | FK, NOT NULL | Referensi pembuat pesanan |
| orders | tanggal_order | DATETIME | NOT NULL | Waktu pesanan dibuat |
| orders | alamat_pengiriman | TEXT | NOT NULL | Alamat tujuan pengiriman lengkap |
| orders | kurir | VARCHAR(50) | NOT NULL | Nama layanan jasa pengiriman |
| orders | total_harga | DECIMAL(10,2) | NOT NULL | Total biaya keseluruhan |
| orders | status_pesanan | VARCHAR(50) | NOT NULL | Status pesanan (Pending, Proses, dll) |
| order_items | id_order_item | INT | PK, AUTO_INCREMENT | ID unik detail barang pesanan |
| order_items | id_order | INT | FK, NOT NULL | Referensi pesanan induk |
| order_items | id_variant | INT | FK, NOT NULL | Varian yang sah dibeli |
| order_items | qty | INT | NOT NULL | Jumlah barang yang dibeli |
| order_items | harga_satuan | DECIMAL(10,2) | NOT NULL | Rekaman harga saat transaksi |
| payments | id_payment | INT | PK, AUTO_INCREMENT | ID unik transaksi pembayaran |
| payments | id_order | INT | FK, NOT NULL | Referensi ke nomor pesanan |
| payments | metode_pembayaran | VARCHAR(50) | NOT NULL | Cara bayar (misal: Transfer Bank) |
| payments | status_pembayaran | VARCHAR(50) | NOT NULL | Status (Pending, Lunas, Ditolak) |
| payments | jumlah_bayar | DECIMAL(10,2) | NOT NULL | Nominal yang harus dibayar |
| payments | waktu_transaksi | DATETIME | | Waktu saat transfer berhasil dilakukan |
| payments | bukti_pembayaran | VARCHAR(255) | | Lokasi file setruk bukti transfer |