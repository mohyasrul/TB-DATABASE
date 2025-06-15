<!DOCTYPE html>
<html>
<head>
    <title>Food Best - Admin Dashboard</title>
</head>
<body>
    <?php
    require_once 'includes/auth.php';
    require_once 'config/database.php';
    
    $auth = new Auth();
    $auth->requireAdmin();
    
    $database = new Database();
    $conn = $database->getConnection();
      // Handle status update
    if (isset($_POST['update_status'])) {
        $order_id = $_POST['order_id'];
        $new_status = $_POST['new_status'];
        $admin_id = $_SESSION['user_id'];
        
        try {
            // Update order status directly
            $update_query = "UPDATE orders SET status = ? WHERE order_id = ?";
            $update_stmt = $conn->prepare($update_query);
            $update_stmt->execute([$new_status, $order_id]);
            
            // Insert into status history
            $history_query = "INSERT INTO order_status_history (order_id, new_status, changed_by, notes) VALUES (?, ?, ?, ?)";
            $history_stmt = $conn->prepare($history_query);
            $history_stmt->execute([$order_id, $new_status, $admin_id, 'Status diupdate oleh admin']);
            
            echo "<p style='color: green;'>Status pesanan berhasil diupdate!</p>";
        } catch (Exception $e) {
            echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
        }
    }
    
    // Get pending orders
    $pending_query = "SELECT o.*, u.full_name, u.phone FROM orders o 
                     JOIN users u ON o.user_id = u.user_id 
                     WHERE o.status IN ('pending', 'confirmed', 'preparing') 
                     ORDER BY o.order_date ASC";
    $pending_stmt = $conn->prepare($pending_query);
    $pending_stmt->execute();
    $pending_orders = $pending_stmt->fetchAll(PDO::FETCH_ASSOC);
      // Get statistics
    $stats_query = "SELECT 
                    COUNT(*) as total_orders,
                    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_orders,
                    SUM(CASE WHEN status = 'delivered' THEN 1 ELSE 0 END) as delivered_orders,
                    SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_orders,
                    SUM(CASE WHEN status IN ('delivered', 'ready') THEN 1 ELSE 0 END) as completed_orders,
                    SUM(CASE WHEN status NOT IN ('cancelled') THEN total_amount ELSE 0 END) as total_revenue
                    FROM orders";
    $stats_stmt = $conn->prepare($stats_query);
    $stats_stmt->execute();
    $stats = $stats_stmt->fetch(PDO::FETCH_ASSOC);
    ?>
    
    <h1>Food Best</h1>
    <h2>Admin Dashboard - <?php echo $_SESSION['full_name']; ?></h2>
      <p>
        <a href="admin_menu.php">🍽️Kelola Menu</a> | 
        <a href="admin_users.php">👯Kelola User</a> | 
        <a href="admin_all_orders.php">📃Semua Pesanan</a> | 
        <a href="admin_reports.php">📜Laporan</a> | 
        <a href="logout.php">🚪Logout</a>
    </p>
    <div style="background-color: #f8f9fa; padding: 15px; margin: 15px 0; border: 1px solid #dee2e6;">
      <h2>Statistik</h2>
    <table border="1">
        <tr>
            <td>Total Pesanan:</td>
            <td><?php echo $stats['total_orders']; ?></td>
        </tr>
        <tr>
            <td>Pesanan Pending:</td>
            <td><?php echo $stats['pending_orders']; ?></td>
        </tr>
        <tr>
            <td>Pesanan Selesai:</td>
            <td><?php echo $stats['completed_orders']; ?></td>
        </tr>
        <tr>
            <td>Pesanan Dibatalkan:</td>
            <td><?php echo $stats['cancelled_orders']; ?></td>
        </tr>
        <tr>
            <td>Total Revenue:</td>
            <td>Rp <?php echo number_format($stats['total_revenue'], 0, ',', '.'); ?></td>
        </tr>
    </table>
    </div>

    <div style="margin: 15px 0;">
        <p>
            <a href="admin_all_orders.php?status=completed" style="color: green; font-weight: bold;">
                📋 Lihat Semua Pesanan Selesai (<?php echo $stats['completed_orders']; ?>)
            </a> | 
            <a href="admin_all_orders.php?status=cancelled" style="color: red; font-weight: bold;">
                ❌ Lihat Pesanan Dibatalkan (<?php echo $stats['cancelled_orders']; ?>)
            </a> | 
            <a href="admin_all_orders.php" style="color: blue; font-weight: bold;">
                📊 Lihat Semua Pesanan
            </a>
        </p>
    </div>
    
    <div style="background-color: #f8f9fa; padding: 15px; margin: 15px 0; border: 1px solid #dee2e6;">
    <h2>Pesanan yang Perlu Diproses</h2>
    
    <?php if (count($pending_orders) > 0): ?>
        <table border="1">
            <tr>
                <th>Order ID</th>
                <th>Customer</th>
                <th>Tanggal</th>
                <th>Total</th>
                <th>Status</th>
                <th>Alamat</th>
                <th>Aksi</th>
            </tr>
            <?php foreach ($pending_orders as $order): ?>
            <tr>
                <td><?php echo $order['order_id']; ?></td>
                <td><?php echo htmlspecialchars($order['full_name']); ?></td>
                <td><?php echo date('d/m/Y H:i', strtotime($order['order_date'])); ?></td>
                <td>Rp <?php echo number_format($order['total_amount'], 0, ',', '.'); ?></td>
                <td><?php echo ucfirst($order['status']); ?></td>
                <td><?php echo htmlspecialchars($order['delivery_address']); ?></td>
                <td>
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">
                        <select name="new_status">
                            <option value="confirmed" <?php echo ($order['status'] == 'confirmed') ? 'selected' : ''; ?>>Confirmed</option>
                            <option value="preparing" <?php echo ($order['status'] == 'preparing') ? 'selected' : ''; ?>>Preparing</option>
                            <option value="ready" <?php echo ($order['status'] == 'ready') ? 'selected' : ''; ?>>Ready</option>
                            <option value="delivered" <?php echo ($order['status'] == 'delivered') ? 'selected' : ''; ?>>Delivered</option>
                            <option value="cancelled" <?php echo ($order['status'] == 'cancelled') ? 'selected' : ''; ?>>Cancelled</option>
                        </select>
                        <input type="submit" name="update_status" value="Update">
                    </form>
                    <br>
                    <a href="admin_order_detail.php?id=<?php echo $order['order_id']; ?>">Detail</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>

    <?php else: ?>
        <p>Tidak ada pesanan yang perlu diproses.</p>
    <?php endif; ?>
</body>
</html>
