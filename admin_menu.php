<!DOCTYPE html>
<html>
<head>
    <title>Food Best - Kelola Menu</title>
</head>
<body>
    <?php
    require_once 'includes/auth.php';
    require_once 'config/database.php';
    
    $auth = new Auth();
    $auth->requireAdmin();
    
    $database = new Database();
    $conn = $database->getConnection();
      // Handle add menu item
    if (isset($_POST['add_item'])) {
        $item_name = trim($_POST['item_name']);
        $description = trim($_POST['description']);
        $price = (float)$_POST['price'];
        $category_id = (int)$_POST['category_id'];
        
        if (!empty($item_name) && $price > 0 && $category_id > 0) {
            $insert_query = "INSERT INTO menu_items (item_name, description, price, category_id) VALUES (?, ?, ?, ?)";
            $insert_stmt = $conn->prepare($insert_query);
            if ($insert_stmt->execute([$item_name, $description, $price, $category_id])) {
                echo "<p style='color: green;'>Menu berhasil ditambahkan!</p>";
            } else {
                echo "<p style='color: red;'>Gagal menambahkan menu!</p>";
            }
        } else {
            echo "<p style='color: red;'>Semua field wajib diisi dengan benar!</p>";
        }
    }
    
    // Handle toggle availability
    if (isset($_POST['toggle_availability'])) {
        $item_id = (int)$_POST['item_id'];
        $is_available = $_POST['is_available'] == '1' ? 0 : 1;
        
        $update_query = "UPDATE menu_items SET is_available = ? WHERE item_id = ?";
        $update_stmt = $conn->prepare($update_query);
        if ($update_stmt->execute([$is_available, $item_id])) {
            echo "<p style='color: green;'>Status menu berhasil diupdate!</p>";
        }
    }
    
    // Get categories
    $categories_query = "SELECT * FROM categories ORDER BY category_name";
    $categories_stmt = $conn->prepare($categories_query);
    $categories_stmt->execute();
    $categories = $categories_stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get menu items
    $menu_query = "SELECT mi.*, c.category_name FROM menu_items mi 
                   LEFT JOIN categories c ON mi.category_id = c.category_id 
                   ORDER BY mi.item_name";
    $menu_stmt = $conn->prepare($menu_query);
    $menu_stmt->execute();
    $menu_items = $menu_stmt->fetchAll(PDO::FETCH_ASSOC);
    ?>
    
    <h1>Kelola Menu</h1>
      <p>
        <a href="admin.php">📊Kembali ke Dashboard</a> | 
        <a href="admin_users.php">👯Kelola User</a> | 
        <a href="admin_all_orders.php">📃Semua Pesanan</a> | 
        <a href="admin_reports.php">📜Laporan</a> | 
        <a href="logout.php">🚪Logout</a>
    </p>
    
    <div style="background-color: #f8f9fa; padding: 15px; margin: 15px 0; border: 1px solid #dee2e6;">
    <h2>Tambah Menu Baru</h2>
    <form method="POST">
        <table>
            <tr>
                <td>Nama Menu:</td>
                <td><input type="text" name="item_name" required></td>
            </tr>
            <tr>
                <td>Deskripsi:</td>
                <td><textarea name="description" rows="3"></textarea></td>
            </tr>
            <tr>
                <td>Harga:</td>
                <td><input type="number" name="price" step="100" required></td>
            </tr>
            <tr>
                <td>Kategori:</td>
                <td>
                    <select name="category_id" required>
                        <option value="">Pilih Kategori</option>
                        <?php foreach ($categories as $category): ?>
                        <option value="<?php echo $category['category_id']; ?>"><?php echo htmlspecialchars($category['category_name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td></td>
                <td><input type="submit" name="add_item" value="Tambah Menu"></td>
            </tr>
        </table>
    </form>
    </div>

    <div style="background-color: #f8f9fa; padding: 15px; margin: 15px 0; border: 1px solid #dee2e6;">
    <h2>Daftar Menu</h2>
    <table border="1">
        <tr>
            <th>ID</th>
            <th>Nama Menu</th>
            <th>Kategori</th>
            <th>Harga</th>
            <th>Status</th>
            <th>Aksi</th>
        </tr>
        <?php foreach ($menu_items as $item): ?>
        <tr>
            <td><?php echo $item['item_id']; ?></td>
            <td><?php echo htmlspecialchars($item['item_name']); ?></td>
            <td><?php echo htmlspecialchars($item['category_name']); ?></td>
            <td>Rp <?php echo number_format($item['price'], 0, ',', '.'); ?></td>
            <td><?php echo $item['is_available'] ? 'Tersedia' : 'Tidak Tersedia'; ?></td>
            <td>
                <form method="POST" style="display: inline;">
                    <input type="hidden" name="item_id" value="<?php echo $item['item_id']; ?>">
                    <input type="hidden" name="is_available" value="<?php echo $item['is_available']; ?>">
                    <input type="submit" name="toggle_availability" value="<?php echo $item['is_available'] ? 'Nonaktifkan' : 'Aktifkan'; ?>">
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
    </div>
</body>
</html>
