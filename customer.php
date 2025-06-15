<!DOCTYPE html>
<html>
<head>
    <title>Food Best - Customer Dashboard</title>
</head>
<body>
    <?php
    require_once 'includes/auth.php';
    require_once 'config/database.php';
    
    $auth = new Auth();
    $auth->requireLogin();
    
    $database = new Database();
    $conn = $database->getConnection();
      // Handle add to cart
    if (isset($_POST['add_to_cart'])) {
        $item_id = (int)$_POST['item_id'];
        $quantity = (int)$_POST['quantity'];
        $user_id = $_SESSION['user_id'];
        
        if ($quantity > 0) {
            // Check if item already in cart
            $check_query = "SELECT cart_id, quantity FROM cart WHERE user_id = ? AND item_id = ?";
            $check_stmt = $conn->prepare($check_query);
            $check_stmt->execute([$user_id, $item_id]);
            
            if ($check_stmt->rowCount() > 0) {
                // Update quantity
                $cart_item = $check_stmt->fetch();
                $new_quantity = $cart_item['quantity'] + $quantity;
                $update_query = "UPDATE cart SET quantity = ? WHERE cart_id = ?";
                $update_stmt = $conn->prepare($update_query);
                if ($update_stmt->execute([$new_quantity, $cart_item['cart_id']])) {
                    echo "<p style='color: green;'>Item berhasil ditambahkan ke keranjang!</p>";
                }
            } else {
                // Add new item to cart
                $insert_query = "INSERT INTO cart (user_id, item_id, quantity) VALUES (?, ?, ?)";
                $insert_stmt = $conn->prepare($insert_query);
                if ($insert_stmt->execute([$user_id, $item_id, $quantity])) {
                    echo "<p style='color: green;'>Item berhasil ditambahkan ke keranjang!</p>";
                }
            }
        } else {
            echo "<p style='color: red;'>Quantity harus lebih dari 0!</p>";
        }
    }
      // Get menu items
    $menu_query = "SELECT mi.*, c.category_name FROM menu_items mi LEFT JOIN categories c ON mi.category_id = c.category_id WHERE mi.is_available = 1";
    $menu_stmt = $conn->prepare($menu_query);
    $menu_stmt->execute();
    $menu_items = $menu_stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get customer order statistics
    $user_id = $_SESSION['user_id'];
    $customer_stats_query = "SELECT 
                            COUNT(*) as total_orders,
                            SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_orders,
                            SUM(CASE WHEN status IN ('delivered', 'ready') THEN 1 ELSE 0 END) as completed_orders,
                            SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_orders
                            FROM orders WHERE user_id = ?";
    $customer_stats_stmt = $conn->prepare($customer_stats_query);
    $customer_stats_stmt->execute([$user_id]);
    $customer_stats = $customer_stats_stmt->fetch(PDO::FETCH_ASSOC);
    
    // Get cart item count
    $cart_count_query = "SELECT COUNT(*) as cart_items FROM cart WHERE user_id = ?";
    $cart_count_stmt = $conn->prepare($cart_count_query);
    $cart_count_stmt->execute([$user_id]);
    $cart_count = $cart_count_stmt->fetch()['cart_items'];
    ?>
    
    <h1>Food Best</h1>
    <h2>Selamat datang, <?php echo $_SESSION['full_name']; ?>!👋</h2>
    
    <p>
        <a href="cart.php">🛒 Lihat Keranjang <?php echo ($cart_count > 0) ? "($cart_count)" : ""; ?></a> | 
        <a href="order_history.php">📋 Riwayat Pesanan</a> | 
        <a href="logout.php">🚪 Logout</a>
    </p>
    <!-- Customer Statistics -->
    <div style="background-color: #f8f9fa; padding: 15px; margin: 15px 0; border: 1px solid #dee2e6;">
        <h3>Ringkasan Pesanan Anda</h3>
        <table border="1">
            <tr>
                <td>Total Pesanan:</td>
                <td><?php echo $customer_stats['total_orders']; ?></td>
                <td>Item di Keranjang:</td>
                <td><?php echo $cart_count; ?></td>
            </tr>
            <tr>
                <td>Pesanan Pending:</td>
                <td><?php echo $customer_stats['pending_orders']; ?></td>
                <td>Pesanan Selesai:</td>
                <td><?php echo $customer_stats['completed_orders']; ?></td>
            </tr>
        </table>
        <?php if ($customer_stats['cancelled_orders'] > 0): ?>
        <p style="color: #e74c3c;"><small>Pesanan Dibatalkan: <?php echo $customer_stats['cancelled_orders']; ?></small></p>
        <?php endif; ?>
    </div>
    
    <div style="background-color: #f8f9fa; padding: 15px; margin: 15px 0; border: 1px solid #dee2e6;">
    <h2>Menu Makanan</h2>

    <table border="1">
        <tr>
            <th>Nama Menu</th>
            <th>Kategori</th>
            <th>Deskripsi</th>
            <th>Harga</th>
            <th>Jumlah</th>
            <th>Aksi</th>
        </tr>
        <?php foreach ($menu_items as $item): ?>
        <tr>
            <td><?php echo htmlspecialchars($item['item_name']); ?></td>
            <td><?php echo htmlspecialchars($item['category_name']); ?></td>
            <td><?php echo htmlspecialchars($item['description']); ?></td>
            <td>Rp <?php echo number_format($item['price'], 0, ',', '.'); ?></td>
            <td>
                <form method="POST" style="display: inline;">
                    <input type="hidden" name="item_id" value="<?php echo $item['item_id']; ?>">
                    <input type="number" name="quantity" value="1" min="1" style="width: 50px;">
            </td>
            <td>
                    <input type="submit" name="add_to_cart" value="Tambah ke Keranjang">
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
    </div>
</body>
</html>
