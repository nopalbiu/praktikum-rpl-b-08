User Story: As a Guest, I want to search for clothes by name or category, so I can find specific items quickly.

Acceptance Criteria:
AC-1: Given customer berada pada halaman utama, When customer menggetikan nama baju atau kategori pada kolom pencarian, Then Sistem hanya akan menampilkan produk yang sesuai dengan nama baju atau kategori yang dipillih oleh customer.
AC-2: Given customer berada pada halaman utama, When customer mengetikkan nama produk yang tidak terdaftar, Then Sistem menampilkan pesan "Produk tidak ditemukan".


User Story: As a Guest, I want to filter the item by specific categories so that I can browse items that match my needs more efficiently.

Acceptance Criteria:
AC-1: Given customer berada di halaman utama, When customer mengklik salah satu kategori pada menu filter item, Then sistem memperbarui tampilan katalog dan hanya menampilkan produk yang termasuk dalam kategori yang dipilih.


User Story: As a Customer, I want to view the size chart and material information, so that I can ensure the outfit fits me perfectly and meets my expectations.

Acceptance Criteria:
AC-1: Given customer berada di halaman detail produk, When customer mengklik bagian "Details & Size Chart", Then sistem menampilkan informasi jenis bahan pakaian dan tabel ukuran yang lengkap.


User Story: As a Customer, I want to add a product with specific sizes to the shopping cart, so I can pick all the items that I want to buy before checking out. 

Acceptance Criteria: 
AC-1: Given customer berada di halaman detail produk dan sudah memilih ukuran produk yang diinginkan, When customer menekan tombol "Tambahkan ke keranjang", Then Sistem menyimpan item tersebut ke dalam daftar keranjang belanja customer.
AC-2: Given customer berada di halaman detail produk yang stoknya habis, When customer menekan tombol "Tambahkan ke keranjang", Then Sistem menonaktifkan tombol dan menampilkan label "Stok Habis".


User Story: As a Customer, I want to input my address and see the total price, so that I know how much I need to pay and finish my order. 

Acceptance Criteria: 
AC-1: Given customer berada di halaman keranjang belanja yang berisi produk-produk yang ingin dibeli, When customer menekan tombol "Checkout" dan mengisi data alamat,kurir pengiriman, serta metode pembayaran, Then Sistem menampilkan ringkasan pesanan, total biaya, dan nomor rekening pembayaran untuk diselesaikan oleh customer.


User Story: As an Admin, I want to update the stock quantity for an article, so that the information on the website is up to date.

Acceptance Criteria:
AC-1: Given Admin berada di panel manajemen stok, When Admin mengubah angka stok pada salah satu artikel pakaian di varian ukuran tertentu dan menekan tombol "perbarui", Then Sistem memperbarui jumlah stok di database dan menampilkan jumlah stok terbaru di website jika dilihat customer.


User Story: As an Admin, I want to remove certain product that are limited or not selling well from the catalog, so that the catalog doesn't have to much product and more focused on newer products.

Acceptance Criteria:
AC-1: Given Admin berada di daftar manajemen produk, When Admin memilih satu item dan menekan opsi "Hapus", Then Sistem menghapus produk tersebut dari katalog yang dilihat customer.


User Story: As an Admin, I want to edit existing product details, so that I can correct any mistake and update the product information without needing to delete it.

Acceptance Criteria:
AC-1: Given Admin berada di daftar manajemen produk dan memilih tombol "Edit" pada salah satu produk, When Admin mengubah deskripsi produk dan menekan tombol "Simpan Perubahan", Then Sistem memperbarui informasi tersebut dan langsung menampilkannya di katalog.


User Story: As an Admin, I want to view the list of orders, so that I can manage shipping and update the status for each orders.

Acceptance Criteria:
AC-1: Given Admin berada di halaman manajemen pesanan, When Ada pesanan yang masuk dari Customer, Then Sistem menampilkan daftar transaksi lengkap dengan detail produk yang dibeli dan alamat pengiriman Customer.


User Story: As an Admin, I want to add new clothing items to the catalog, so that the product always up to date.

Acceptance Criteria:
AC-1: Given Admin berada di dalam halaman dasbord manajemen produk, When Admin mengisi formulir produk seperti  nama, harga, stok, foto produk dan lain lain lalu menekan tombol save, Then Sistem akan menyimpan ke database lalu produk baru akan muncul di halaman katalog.
AC-2: Given Admin berada di dalam halaman dasbord manajemen produk, When Admin menekan tombol save tanpa mengisi data wajib, Then Sistem menampilkan pesan error "Semua kolom wajib diisi".