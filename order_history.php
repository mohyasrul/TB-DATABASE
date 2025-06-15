<!DOCTYPE html>
<html>
<head>
    <title>Food Best - Riwayat Pesanan</title>
</head>
<body>
    <?php
    require_once 'includes/auth.php';
    require_once 'config/database.php';
    
    $auth = new Auth();
    $auth->requireLogin();
    
    $database = new Database();
    $conn = $database->getConnection();
    $user_id = $_SESSION['user_id'];
    
    // Get order history
    $orders_query = "SELECT * FROM orders WHERE user_id = ? ORDER BY order_date DESC";
    $orders_stmt = $conn->prepare($orders_query);
    $orders_stmt->execute([$user_id]);
    $orders = $orders_stmt->fetchAll(PDO::FETCH_ASSOC);
    ?>
    
    <h1>🕛Riwayat Pesanan</h1>
    
    <p>
        <a href="customer.php">🏠Kembali ke Menu</a> | 
        <a href="cart.php">🛒Lihat Keranjang</a> | 
        <a href="logout.php">🚪Logout</a>
    </p>
    
    <?php if (count($orders) > 0): ?>
        <?php foreach ($orders as $order): ?>
        <div style="border: 1px solid #dee2e6; margin: 10px 0; padding: 10px;">
            <h3>Order #<?php echo $order['order_id']; ?></h3>
            <p><strong>Tanggal:</strong> <?php echo date('d/m/Y H:i', strtotime($order['order_date'])); ?></p>
            <p><strong>Status:</strong> <?php echo ucfirst($order['status']); ?></p>
            <p><strong>Total:</strong> Rp <?php echo number_format($order['total_amount'], 0, ',', '.'); ?></p>
            <p><strong>Alamat:</strong> <?php echo htmlspecialchars($order['delivery_address']); ?></p>
            
            <?php
            // Get order items
            $items_query = "SELECT oi.*, mi.item_name FROM order_items oi 
                           JOIN menu_items mi ON oi.item_id = mi.item_id 
                           WHERE oi.order_id = ?";
            $items_stmt = $conn->prepare($items_query);
            $items_stmt->execute([$order['order_id']]);
            $items = $items_stmt->fetchAll(PDO::FETCH_ASSOC);
            ?>
            
            <h4>Detail Pesanan:</h4>
            <table border="1">
                <tr>
                    <th>Menu</th>
                    <th>Jumlah</th>
                    <th>Harga</th>
                    <th>Subtotal</th>
                </tr>
                <?php foreach ($items as $item): ?>
                <tr>
                    <td><?php echo htmlspecialchars($item['item_name']); ?></td>
                    <td><?php echo $item['quantity']; ?></td>
                    <td>Rp <?php echo number_format($item['price'], 0, ',', '.'); ?></td>
                    <td>Rp <?php echo number_format($item['subtotal'], 0, ',', '.'); ?></td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>Belum ada riwayat pesanan. <a href="customer.php">Mulai pesan sekarang</a></p>
    <?php endif; ?>
</body>
</html>
