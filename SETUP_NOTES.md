# Setup dan File yang Tidak Termasuk

Beberapa file yang diperlukan untuk development tetapi tidak termasuk dalam repository:

## 📁 File yang Tidak Disertakan

### setup.php dan test_db.php
File-file utilitas untuk setup database dan testing koneksi tidak disertakan untuk keamanan.

Jika Anda memerlukan file-file ini, silakan buat manual:

**setup.php**
```php
<?php
// File untuk setup database otomatis
// Buat database dan import schema dari database_food_best.sql
?>
```

**test_db.php**
```php
<?php
require_once 'config/database.php';
// Test koneksi database
$database = new Database();
$conn = $database->getConnection();
if ($conn) {
    echo "Database connection successful!";
} else {
    echo "Database connection failed!";
}
?>
```

## 🔧 Konfigurasi Manual

### Database Setup
1. Buat database dengan nama `food_best`
2. Import file `database_food_best.sql`
3. Sesuaikan konfigurasi di `config/database.php`

### Admin Account
Default admin account akan dibuat otomatis:
- Username: admin
- Password: admin123

## 📝 Catatan Development
- File konfigurasi database sudah dikonfigurasi untuk localhost
- Port database default 3306, ubah ke 3307 jika diperlukan
- Pastikan MySQL service sudah running sebelum mengakses aplikasi
