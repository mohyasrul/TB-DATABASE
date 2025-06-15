# Food Best - Sistem Pemesanan Makanan Online

Sistem pemesanan makanan online berbasis PHP dengan fitur lengkap untuk customer dan admin.

## 📋 Fitur Utama

### Customer
- 🍽️ Melihat menu makanan dengan kategori
- 🛒 Menambah/hapus item ke/dari keranjang
- 💳 Checkout pesanan
- 📋 Melihat riwayat pesanan
- 📊 Dashboard dengan statistik pesanan

### Admin
- 🏠 Dashboard dengan statistik penjualan
- 📦 Kelola status pesanan
- 🍽️ Kelola menu makanan
- 👥 Kelola user dan role
- 📊 Laporan penjualan (harian, bulanan, tahunan)
- 📋 Lihat semua pesanan
- 🔍 Filter dan pencarian pesanan

## 🛠️ Teknologi

- **Backend**: PHP 7.4+
- **Database**: MySQL 5.7+
- **Frontend**: HTML, Basic CSS (inline styling)
- **Server**: XAMPP/Apache

## 📁 Struktur Project

```
food_best/
├── config/
│   └── database.php           # Konfigurasi database
├── includes/
│   └── auth.php              # Sistem autentikasi
├── admin.php                 # Dashboard admin
├── admin_all_orders.php      # Semua pesanan admin
├── admin_menu.php            # Kelola menu
├── admin_users.php           # Kelola user
├── admin_reports.php         # Laporan penjualan
├── admin_order_detail.php    # Detail pesanan
├── customer.php              # Dashboard customer
├── cart.php                  # Keranjang belanja
├── checkout.php              # Proses checkout
├── order_history.php         # Riwayat pesanan
├── login.php                 # Halaman login
├── register.php              # Halaman registrasi
├── logout.php                # Proses logout
├── index.php                 # Halaman utama
├── setup.php                 # Setup database otomatis
├── test_db.php               # Test koneksi database
├── error.php                 # Halaman error
└── database_food_best.sql    # Database schema
```

## 🚀 Instalasi

1. **Clone repository**
   ```bash
   git clone https://github.com/mohyasrul/TB-DATABASE.git
   cd TB-DATABASE
   ```

2. **Setup XAMPP**
   - Install XAMPP
   - Copy folder project ke `C:\xampp\htdocs\`
   - Start Apache dan MySQL

3. **Konfigurasi Database**
   - Buka `config/database.php`
   - Sesuaikan pengaturan database:
     ```php
     define('DB_HOST', 'localhost');
     define('DB_NAME', 'food_best');
     define('DB_PORT', '3306'); // atau 3307
     define('DB_USER', 'root');
     define('DB_PASS', '');
     ```

4. **Setup Database**
   - Akses `http://localhost/food_best/setup.php`
   - Klik "Mulai Setup Database"
   - Atau import manual `database_food_best.sql` ke phpMyAdmin

5. **Test Koneksi**
   - Akses `http://localhost/food_best/test_db.php`
   - Pastikan koneksi database berhasil

## 👤 Akun Default

### Admin
- **Username**: admin
- **Password**: admin123

### Customer
- Daftar akun baru melalui halaman registrasi

## 📊 Database Schema

### Tabel Utama
- `users` - Data user (customer & admin)
- `categories` - Kategori makanan
- `menu_items` - Data menu makanan
- `cart` - Keranjang belanja
- `orders` - Data pesanan
- `order_items` - Detail item pesanan
- `order_status_history` - Riwayat perubahan status

### Fitur Database
- ✅ Trigger otomatis untuk tracking status
- ✅ Views untuk laporan penjualan
- ✅ Stored procedures untuk checkout
- ✅ Proper foreign key relationships

## 🔧 Fitur Khusus

### Customer Features
- Dashboard dengan ringkasan pesanan
- Keranjang belanja dengan update quantity
- Checkout dengan alamat pengiriman
- Riwayat pesanan lengkap dengan detail

### Admin Features
- Dashboard dengan statistik real-time
- Kelola menu (tambah, edit, aktif/nonaktif)
- Update status pesanan
- Filter dan pencarian pesanan
- Laporan penjualan dengan berbagai periode
- Detail pesanan dengan riwayat status

### Security Features
- Session management
- SQL injection prevention (prepared statements)
- Input validation
- Role-based access control

## 🌐 URL Akses

- **Halaman Utama**: `http://localhost/food_best/`
- **Login**: `http://localhost/food_best/login.php`
- **Register**: `http://localhost/food_best/register.php`
- **Setup Database**: `http://localhost/food_best/setup.php`
- **Test Database**: `http://localhost/food_best/test_db.php`

## 📝 Cara Penggunaan

### Untuk Customer
1. Daftar akun baru atau login
2. Pilih menu makanan dari daftar
3. Tambahkan ke keranjang
4. Checkout dengan mengisi alamat
5. Lihat status pesanan di riwayat

### Untuk Admin
1. Login dengan akun admin
2. Kelola menu di "Kelola Menu"
3. Pantau pesanan di "Semua Pesanan"
4. Update status pesanan sesuai proses
5. Lihat laporan di "Laporan"

## 🐛 Troubleshooting

### Database Connection Error
- Pastikan MySQL sudah running
- Cek port di `config/database.php`
- Pastikan database `food_best` sudah dibuat

### Permission Error
- Pastikan folder project memiliki permission yang tepat
- Jalankan XAMPP sebagai administrator

### Page Not Found
- Pastikan path URL sesuai dengan struktur folder
- Cek .htaccess jika menggunakan URL rewrite

## 🤝 Kontribusi

1. Fork repository
2. Buat branch fitur (`git checkout -b feature/amazing-feature`)
3. Commit perubahan (`git commit -m 'Add amazing feature'`)
4. Push ke branch (`git push origin feature/amazing-feature`)
5. Buat Pull Request

## 📄 Lisensi

Project ini dibuat untuk keperluan tugas besar database.

## 👨‍💻 Developer

Dikembangkan sebagai sistem pemesanan makanan online dengan fitur lengkap untuk manajemen restaurant.

---

**© 2025 Food Best - Sistem Pemesanan Makanan Online**
