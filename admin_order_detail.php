<!DOCTYPE html>
<html>
<head>
    <title>Food Best - Detail Pesanan</title>
</head>
<body>
    <?php
    require_once 'includes/auth.php';
    require_once 'config/database.php';
    
    $auth = new Auth();
    $auth->requireAdmin();
    
    $database = new Database();
    $conn = $database->getConnection();
    
    $order_id = isset($_GET['id']) ? $_GET['id'] : 0;
    
    // Get order details
    $order_query = "SELECT o.*, u.full_name, u.email, u.phone as user_phone 
                    FROM orders o 
                    JOIN users u ON o.user_id = u.user_id 
                    WHERE o.order_id = ?";
    $order_stmt = $conn->prepare($order_query);
    $order_stmt->execute([$order_id]);
    $order = $order_stmt->fetch(PDO::FETCH_ASSOC);
      if (!$order) {
        echo "<h1>Pesanan tidak ditemukan</h1>";
        echo "<p><a href='admin.php'>Kembali ke Dashboard</a> | <a href='admin_all_orders.php'>Semua Pesanan</a></p>";
        exit();
    }
    
    // Get order items
    $items_query = "SELECT oi.*, mi.item_name FROM order_items oi 
                   JOIN menu_items mi ON oi.item_id = mi.item_id 
                   WHERE oi.order_id = ?";
    $items_stmt = $conn->prepare($items_query);
    $items_stmt->execute([$order_id]);
    $items = $items_stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get status history
    $history_query = "SELECT osh.*, u.full_name as changed_by_name 
                     FROM order_status_history osh 
                     LEFT JOIN users u ON osh.changed_by = u.user_id 
                     WHERE osh.order_id = ? 
                     ORDER BY osh.changed_at ASC";
    $history_stmt = $conn->prepare($history_query);
    $history_stmt->execute([$order_id]);
    $history = $history_stmt->fetchAll(PDO::FETCH_ASSOC);
    ?>
      <h1>Food Best</h1>
    <h2>Detail Pesanan #<?php echo $order['order_id']; ?></h2>
    
    <p>
        <a href="admin.php">🏠 Dashboard</a> | 
        <a href="admin_all_orders.php">📋 Semua Pesanan</a> | 
        <a href="admin_all_orders.php?status=<?php echo $order['status']; ?>">🔍 Pesanan <?php echo ucfirst($order['status']); ?></a>
    </p>
    
    <div style="background-color: #f8f9fa; padding: 15px; margin: 15px 0; border: 1px solid #dee2e6;">
    <h2>Informasi Pesanan</h2>
    <table border="1">
        <tr>
            <td>Order ID:</td>
            <td><?php echo $order['order_id']; ?></td>
        </tr>
        <tr>
            <td>Customer:</td>
            <td><?php echo htmlspecialchars($order['full_name']); ?></td>
        </tr>
        <tr>
            <td>Email:</td>
            <td><?php echo htmlspecialchars($order['email']); ?></td>
        </tr>
        <tr>
            <td>Telepon:</td>
            <td><?php echo htmlspecialchars($order['phone']); ?></td>
        </tr>
        <tr>
            <td>Tanggal Pesanan:</td>
            <td><?php echo date('d/m/Y H:i:s', strtotime($order['order_date'])); ?></td>
        </tr>
        <tr>
            <td>Status:</td>
            <td><?php echo ucfirst($order['status']); ?></td>
        </tr>
        <tr>
            <td>Total:</td>
            <td>Rp <?php echo number_format($order['total_amount'], 0, ',', '.'); ?></td>
        </tr>
        <tr>
            <td>Alamat Pengiriman:</td>
            <td><?php echo htmlspecialchars($order['delivery_address']); ?></td>
        </tr>
        <?php if ($order['notes']): ?>
        <tr>
            <td>Catatan:</td>
            <td><?php echo htmlspecialchars($order['notes']); ?></td>
        </tr>
        <?php endif; ?>
    </table>
    </div>

    <div style="background-color: #f8f9fa; padding: 15px; margin: 15px 0; border: 1px solid #dee2e6;">
    <h2>Detail Item</h2>
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
        <tr>
            <td colspan="3"><strong>Total:</strong></td>
            <td><strong>Rp <?php echo number_format($order['total_amount'], 0, ',', '.'); ?></strong></td>
        </tr>
    </table>
    </div>

    <div style="background-color: #f8f9fa; padding: 15px; margin: 15px 0; border: 1px solid #dee2e6;">
    <h2>Riwayat Status</h2>
    <table border="1">
        <tr>
            <th>Waktu</th>
            <th>Status Lama</th>
            <th>Status Baru</th>
            <th>Diubah Oleh</th>
            <th>Catatan</th>
        </tr>
        <?php foreach ($history as $hist): ?>
        <tr>
            <td><?php echo date('d/m/Y H:i:s', strtotime($hist['changed_at'])); ?></td>
            <td><?php echo $hist['old_status'] ? ucfirst($hist['old_status']) : '-'; ?></td>
            <td><?php echo ucfirst($hist['new_status']); ?></td>
            <td><?php echo $hist['changed_by_name'] ? htmlspecialchars($hist['changed_by_name']) : 'System'; ?></td>
            <td><?php echo htmlspecialchars($hist['notes']); ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
    </div>
    
</body>
</html>
