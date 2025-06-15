<!DOCTYPE html>
<html>
<head>
    <title>Food Best - Laporan</title>
</head>
<body>
    <?php
    require_once 'includes/auth.php';
    require_once 'config/database.php';
    
    $auth = new Auth();
    $auth->requireAdmin();
    
    $database = new Database();
    $conn = $database->getConnection();
    
    $report_type = isset($_GET['type']) ? $_GET['type'] : 'daily';
    $filter_date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
    $filter_month = isset($_GET['month']) ? $_GET['month'] : date('Y-m');
    $filter_year = isset($_GET['year']) ? $_GET['year'] : date('Y');
    ?>
    
    <h1>Laporan Penjualan</h1>
      <p>
        <a href="admin.php">📊Kembali ke Dashboard</a> | 
        <a href="admin_menu.php">🍽️Kelola Menu</a> | 
        <a href="admin_users.php">👯Kelola User</a> | 
        <a href="admin_all_orders.php">📃Semua Pesanan</a> | 
        <a href="logout.php">🚪Logout</a>
    </p>
    </div>

    <div style="background-color: #f8f9fa; padding: 15px; margin: 15px 0; border: 1px solid #dee2e6;">
    <h2>Filter Laporan</h2>
    <form method="GET">
        <table>
            <tr>
                <td>Jenis Laporan:</td>
                <td>
                    <select name="type" onchange="this.form.submit()">
                        <option value="daily" <?php echo ($report_type == 'daily') ? 'selected' : ''; ?>>Harian</option>
                        <option value="monthly" <?php echo ($report_type == 'monthly') ? 'selected' : ''; ?>>Bulanan</option>
                        <option value="yearly" <?php echo ($report_type == 'yearly') ? 'selected' : ''; ?>>Tahunan</option>
                    </select>
                </td>
            </tr>
            
            <?php if ($report_type == 'daily'): ?>
            <tr>
                <td>Tanggal:</td>
                <td><input type="date" name="date" value="<?php echo $filter_date; ?>" onchange="this.form.submit()"></td>
            </tr>
            <?php elseif ($report_type == 'monthly'): ?>
            <tr>
                <td>Bulan:</td>
                <td><input type="month" name="month" value="<?php echo $filter_month; ?>" onchange="this.form.submit()"></td>
            </tr>
            <?php elseif ($report_type == 'yearly'): ?>
            <tr>
                <td>Tahun:</td>
                <td><input type="number" name="year" value="<?php echo $filter_year; ?>" min="2020" max="2030" onchange="this.form.submit()"></td>
            </tr>
            <?php endif; ?>
        </table>
    </form>
    </div>

    <div style="background-color: #f8f9fa; padding: 15px; margin: 15px 0; border: 1px solid #dee2e6;">
      <?php if ($report_type == 'daily'): ?>
        <h2>Laporan Harian - <?php echo date('d/m/Y', strtotime($filter_date)); ?></h2>
        <?php
        $query = "SELECT 
                    DATE(order_date) as sale_date,
                    COUNT(*) as total_orders,
                    SUM(total_amount) as total_revenue,
                    AVG(total_amount) as avg_order_value
                  FROM orders 
                  WHERE status NOT IN ('cancelled') AND DATE(order_date) = ?
                  GROUP BY DATE(order_date)";
        $stmt = $conn->prepare($query);
        $stmt->execute([$filter_date]);
        $report = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($report) {
            echo "<table border='1'>";
            echo "<tr><td>Total Pesanan:</td><td>" . $report['total_orders'] . "</td></tr>";
            echo "<tr><td>Total Revenue:</td><td>Rp " . number_format($report['total_revenue'], 0, ',', '.') . "</td></tr>";
            echo "<tr><td>Rata-rata per Pesanan:</td><td>Rp " . number_format($report['avg_order_value'], 0, ',', '.') . "</td></tr>";
            echo "</table>";
        } else {
            echo "<p>Tidak ada data untuk tanggal tersebut.</p>";
        }
        ?>
        
    <?php elseif ($report_type == 'monthly'): ?>
        <h2>Laporan Bulanan - <?php echo date('F Y', strtotime($filter_month . '-01')); ?></h2>
        <?php
        $year = date('Y', strtotime($filter_month . '-01'));
        $month = date('m', strtotime($filter_month . '-01'));
        
        $query = "SELECT 
                    YEAR(order_date) as year,
                    MONTH(order_date) as month,
                    MONTHNAME(order_date) as month_name,
                    COUNT(*) as total_orders,
                    SUM(total_amount) as total_revenue,
                    AVG(total_amount) as avg_order_value
                  FROM orders 
                  WHERE status NOT IN ('cancelled') AND YEAR(order_date) = ? AND MONTH(order_date) = ?
                  GROUP BY YEAR(order_date), MONTH(order_date)";
        $stmt = $conn->prepare($query);
        $stmt->execute([$year, $month]);
        $report = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($report) {
            echo "<table border='1'>";
            echo "<tr><td>Total Pesanan:</td><td>" . $report['total_orders'] . "</td></tr>";
            echo "<tr><td>Total Revenue:</td><td>Rp " . number_format($report['total_revenue'], 0, ',', '.') . "</td></tr>";
            echo "<tr><td>Rata-rata per Pesanan:</td><td>Rp " . number_format($report['avg_order_value'], 0, ',', '.') . "</td></tr>";
            echo "</table>";
        } else {
            echo "<p>Tidak ada data untuk bulan tersebut.</p>";
        }
        ?>
        
    <?php elseif ($report_type == 'yearly'): ?>
        <h2>Laporan Tahunan - <?php echo $filter_year; ?></h2>
        <?php
        $query = "SELECT 
                    YEAR(order_date) as year,
                    COUNT(*) as total_orders,
                    SUM(total_amount) as total_revenue,
                    AVG(total_amount) as avg_order_value
                  FROM orders 
                  WHERE status NOT IN ('cancelled') AND YEAR(order_date) = ?
                  GROUP BY YEAR(order_date)";
        $stmt = $conn->prepare($query);
        $stmt->execute([$filter_year]);
        $report = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($report) {
            echo "<table border='1'>";
            echo "<tr><td>Total Pesanan:</td><td>" . $report['total_orders'] . "</td></tr>";
            echo "<tr><td>Total Revenue:</td><td>Rp " . number_format($report['total_revenue'], 0, ',', '.') . "</td></tr>";
            echo "<tr><td>Rata-rata per Pesanan:</td><td>Rp " . number_format($report['avg_order_value'], 0, ',', '.') . "</td></tr>";
            echo "</table>";
        } else {
            echo "<p>Tidak ada data untuk tahun tersebut.</p>";
        }
        ?>
    <?php endif; ?>
    </div>

    <div style="background-color: #f8f9fa; padding: 15px; margin: 15px 0; border: 1px solid #dee2e6;">
    <h3>Menu Terpopuler</h3>
    <?php
    $popular_query = "SELECT mi.item_name, SUM(oi.quantity) as total_sold, SUM(oi.subtotal) as total_revenue
                     FROM order_items oi
                     JOIN menu_items mi ON oi.item_id = mi.item_id
                     JOIN orders o ON oi.order_id = o.order_id
                     WHERE o.status NOT IN ('cancelled')";
    
    if ($report_type == 'daily') {
        $popular_query .= " AND DATE(o.order_date) = '$filter_date'";
    } elseif ($report_type == 'monthly') {
        $popular_query .= " AND YEAR(o.order_date) = $year AND MONTH(o.order_date) = $month";
    } elseif ($report_type == 'yearly') {
        $popular_query .= " AND YEAR(o.order_date) = $filter_year";
    }
    
    $popular_query .= " GROUP BY oi.item_id ORDER BY total_sold DESC LIMIT 10";
    
    $popular_stmt = $conn->prepare($popular_query);
    $popular_stmt->execute();
    $popular_items = $popular_stmt->fetchAll(PDO::FETCH_ASSOC);
    ?>
    
    <?php if (count($popular_items) > 0): ?>
        <table border="1">
            <tr>
                <th>Menu</th>
                <th>Terjual</th>
                <th>Revenue</th>
            </tr>
            <?php foreach ($popular_items as $item): ?>
            <tr>
                <td><?php echo htmlspecialchars($item['item_name']); ?></td>
                <td><?php echo $item['total_sold']; ?></td>
                <td>Rp <?php echo number_format($item['total_revenue'], 0, ',', '.'); ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
    <?php else: ?>
        <p>Tidak ada data penjualan untuk periode tersebut.</p>
    <?php endif; ?>
    </div>
</body>
</html>
