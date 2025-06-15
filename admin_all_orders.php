<!DOCTYPE html>
<html>
<head>
    <title>Food Best - Semua Pesanan</title>
</head>
<body>
    <?php
    require_once 'includes/auth.php';
    require_once 'config/database.php';
    
    $auth = new Auth();
    $auth->requireAdmin();
    
    $database = new Database();
    $conn = $database->getConnection();
    
    // Get filter parameters
    $status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';
    $date_filter = isset($_GET['date']) ? $_GET['date'] : '';
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    
    // Build query based on filters
    $where_conditions = [];
    $params = [];
    
    if ($status_filter != 'all') {
        if ($status_filter == 'completed') {
            $where_conditions[] = "o.status IN ('delivered', 'ready')";
        } elseif ($status_filter == 'cancelled') {
            $where_conditions[] = "o.status = 'cancelled'";
        } else {
            $where_conditions[] = "o.status = ?";
            $params[] = $status_filter;
        }
    }
    
    if (!empty($date_filter)) {
        $where_conditions[] = "DATE(o.order_date) = ?";
        $params[] = $date_filter;
    }
    
    if (!empty($search)) {
        $where_conditions[] = "(u.full_name LIKE ? OR o.order_id LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }
    
    $where_clause = '';
    if (count($where_conditions) > 0) {
        $where_clause = "WHERE " . implode(" AND ", $where_conditions);
    }
    
    // Get orders with pagination
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $per_page = 20;
    $offset = ($page - 1) * $per_page;
    
    // Count total orders
    $count_query = "SELECT COUNT(*) as total 
                    FROM orders o 
                    JOIN users u ON o.user_id = u.user_id 
                    $where_clause";
    $count_stmt = $conn->prepare($count_query);
    $count_stmt->execute($params);
    $total_orders = $count_stmt->fetch()['total'];
    $total_pages = ceil($total_orders / $per_page);
    
    // Get orders
    $orders_query = "SELECT o.*, u.full_name, u.email, u.phone as customer_phone 
                     FROM orders o 
                     JOIN users u ON o.user_id = u.user_id 
                     $where_clause 
                     ORDER BY o.order_date DESC 
                     LIMIT $per_page OFFSET $offset";
    $orders_stmt = $conn->prepare($orders_query);
    $orders_stmt->execute($params);
    $orders = $orders_stmt->fetchAll(PDO::FETCH_ASSOC);
    ?>
    
    <h1>Food Best</h1>
    <h2>Semua Pesanan</h2>
    
    <p>
        <a href="admin.php">📊Dashboard</a> | 
        <a href="admin_menu.php">🍽️Kelola Menu</a> | 
        <a href="admin_users.php">👯Kelola User</a> | 
        <a href="admin_reports.php">📜Laporan</a> | 
        <a href="logout.php">🚪Logout</a>
    </p>
    
    <!-- Filter Form -->
    <div style="background-color: #f5f5f5; padding: 10px; margin: 10px 0; border: 1px solid #ddd;">
        <h3>Filter Pesanan</h3>
        <form method="GET">
            <table>
                <tr>
                    <td>Status:</td>
                    <td>
                        <select name="status" onchange="this.form.submit()">
                            <option value="all" <?php echo ($status_filter == 'all') ? 'selected' : ''; ?>>Semua Status</option>
                            <option value="pending" <?php echo ($status_filter == 'pending') ? 'selected' : ''; ?>>Pending</option>
                            <option value="confirmed" <?php echo ($status_filter == 'confirmed') ? 'selected' : ''; ?>>Confirmed</option>
                            <option value="preparing" <?php echo ($status_filter == 'preparing') ? 'selected' : ''; ?>>Preparing</option>
                            <option value="ready" <?php echo ($status_filter == 'ready') ? 'selected' : ''; ?>>Ready</option>
                            <option value="completed" <?php echo ($status_filter == 'completed') ? 'selected' : ''; ?>>Completed (Delivered/Ready)</option>
                            <option value="delivered" <?php echo ($status_filter == 'delivered') ? 'selected' : ''; ?>>Delivered</option>
                            <option value="cancelled" <?php echo ($status_filter == 'cancelled') ? 'selected' : ''; ?>>Cancelled</option>
                        </select>
                    </td>
                    <td>Tanggal:</td>
                    <td><input type="date" name="date" value="<?php echo $date_filter; ?>" onchange="this.form.submit()"></td>
                    <td>Cari:</td>
                    <td><input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Nama customer atau Order ID"></td>
                    <td><input type="submit" value="Cari"></td>
                    <td><a href="admin_all_orders.php">Reset</a></td>
                </tr>
            </table>
        </form>
    </div>
    
    <!-- Summary Stats -->
    <div style="background-color: #e8f4f8; padding: 10px; margin: 10px 0; border: 1px solid #b8d4e3;">
        <h3>Ringkasan</h3>
        <p>
            <strong>Total Pesanan Ditemukan:</strong> <?php echo $total_orders; ?> | 
            <strong>Halaman:</strong> <?php echo $page; ?> dari <?php echo $total_pages; ?>
            <?php if (!empty($search)): ?>
                | <strong>Pencarian:</strong> "<?php echo htmlspecialchars($search); ?>"
            <?php endif; ?>
        </p>
    </div>
    
    <?php if (count($orders) > 0): ?>
        <!-- Orders Table -->
        <table border="1" style="width: 100%; border-collapse: collapse;">
            <tr style="background-color: #f0f0f0;">
                <th>Order ID</th>
                <th>Customer</th>
                <th>Tanggal</th>
                <th>Total</th>
                <th>Status</th>
                <th>Telepon</th>
                <th>Aksi</th>
            </tr>
            <?php foreach ($orders as $order): ?>
            <tr style="<?php echo ($order['status'] == 'cancelled') ? 'background-color: #ffe6e6;' : (($order['status'] == 'delivered') ? 'background-color: #e6ffe6;' : ''); ?>">
                <td>
                    <strong>#<?php echo $order['order_id']; ?></strong>
                </td>
                <td>
                    <?php echo htmlspecialchars($order['full_name']); ?><br>
                    <small><?php echo htmlspecialchars($order['email']); ?></small>
                </td>
                <td>
                    <?php echo date('d/m/Y', strtotime($order['order_date'])); ?><br>
                    <small><?php echo date('H:i', strtotime($order['order_date'])); ?></small>
                </td>
                <td>
                    <strong>Rp <?php echo number_format($order['total_amount'], 0, ',', '.'); ?></strong>
                </td>
                <td>
                    <span style="
                        padding: 3px 8px; 
                        border-radius: 3px; 
                        font-weight: bold; 
                        color: white;
                        background-color: <?php 
                            switch($order['status']) {
                                case 'pending': echo '#f39c12'; break;
                                case 'confirmed': echo '#3498db'; break;
                                case 'preparing': echo '#e67e22'; break;
                                case 'ready': echo '#2ecc71'; break;
                                case 'delivered': echo '#27ae60'; break;
                                case 'cancelled': echo '#e74c3c'; break;
                                default: echo '#95a5a6';
                            }
                        ?>
                    ">
                        <?php echo ucfirst($order['status']); ?>
                    </span>
                </td>
                <td>
                    <?php echo htmlspecialchars($order['customer_phone']); ?>
                </td>
                <td>
                    <a href="admin_order_detail.php?id=<?php echo $order['order_id']; ?>">Detail</a>
                    <?php if ($order['status'] != 'delivered' && $order['status'] != 'cancelled'): ?>
                    <br>
                    <form method="POST" action="admin.php" style="display: inline; margin-top: 5px;">
                        <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">
                        <select name="new_status" style="font-size: 11px;">
                            <option value="confirmed" <?php echo ($order['status'] == 'confirmed') ? 'selected' : ''; ?>>Confirmed</option>
                            <option value="preparing" <?php echo ($order['status'] == 'preparing') ? 'selected' : ''; ?>>Preparing</option>
                            <option value="ready" <?php echo ($order['status'] == 'ready') ? 'selected' : ''; ?>>Ready</option>
                            <option value="delivered" <?php echo ($order['status'] == 'delivered') ? 'selected' : ''; ?>>Delivered</option>
                            <option value="cancelled" <?php echo ($order['status'] == 'cancelled') ? 'selected' : ''; ?>>Cancelled</option>
                        </select>
                        <input type="submit" name="update_status" value="Update" style="font-size: 11px;">
                    </form>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
        
        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
        <div style="margin: 20px 0; text-align: center;">
            <p><strong>Halaman:</strong></p>
            <?php
            $query_params = $_GET;
            for ($i = 1; $i <= $total_pages; $i++):
                $query_params['page'] = $i;
                $query_string = http_build_query($query_params);
            ?>
                <?php if ($i == $page): ?>
                    <strong><?php echo $i; ?></strong>
                <?php else: ?>
                    <a href="?<?php echo $query_string; ?>"><?php echo $i; ?></a>
                <?php endif; ?>
                <?php if ($i < $total_pages): ?> | <?php endif; ?>
            <?php endfor; ?>
        </div>
        <?php endif; ?>
        
    <?php else: ?>
        <div style="background-color: #fff3cd; padding: 15px; border: 1px solid #ffeaa7; margin: 10px 0;">
            <p><strong>Tidak ada pesanan ditemukan</strong></p>
            <p>Tidak ada pesanan yang sesuai dengan filter yang dipilih.</p>
        </div>
    <?php endif; ?>
    
    <!-- Quick Actions -->
    <div style="background-color: #f8f9fa; padding: 15px; margin: 20px 0; border: 1px solid #dee2e6;">
        <h3>Aksi Cepat</h3>
        <p>
            <a href="admin_all_orders.php?status=pending">Pesanan Pending</a> | 
            <a href="admin_all_orders.php?status=confirmed">Pesanan Confirmed</a> | 
            <a href="admin_all_orders.php?status=preparing">Pesanan Preparing</a> | 
            <a href="admin_all_orders.php?status=completed">Pesanan Selesai</a> | 
            <a href="admin_all_orders.php?status=cancelled">Pesanan Dibatalkan</a>
        </p>
        <p>
            <a href="admin_all_orders.php?date=<?php echo date('Y-m-d'); ?>">Pesanan Hari Ini</a> | 
            <a href="admin_all_orders.php?date=<?php echo date('Y-m-d', strtotime('-1 day')); ?>">Pesanan Kemarin</a>
        </p>
    </div>

</body>
</html>
