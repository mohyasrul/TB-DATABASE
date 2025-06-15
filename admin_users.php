<!DOCTYPE html>
<html>
<head>
    <title>Food Best - Kelola User</title>
</head>
<body>
    <?php
    require_once 'includes/auth.php';
    require_once 'config/database.php';
    
    $auth = new Auth();
    $auth->requireAdmin();
    
    $database = new Database();
    $conn = $database->getConnection();
    
    // Handle user role update
    if (isset($_POST['update_role'])) {
        $user_id = $_POST['user_id'];
        $new_role = $_POST['new_role'];
        
        $update_query = "UPDATE users SET role = ? WHERE user_id = ?";
        $update_stmt = $conn->prepare($update_query);
        $update_stmt->execute([$new_role, $user_id]);
        echo "<p>Role user berhasil diupdate!</p>";
    }
    
    // Get all users
    $users_query = "SELECT user_id, username, email, full_name, phone, role, created_at FROM users ORDER BY created_at DESC";
    $users_stmt = $conn->prepare($users_query);
    $users_stmt->execute();
    $users = $users_stmt->fetchAll(PDO::FETCH_ASSOC);
    ?>
    
    <h1>Kelola User</h1>
      <p>
        <a href="admin.php">📊Kembali ke Dashboard</a> | 
        <a href="admin_menu.php">🍽️Kelola Menu</a> | 
        <a href="admin_all_orders.php">📃Semua Pesanan</a> | 
        <a href="admin_reports.php">📜Laporan</a> | 
        <a href="logout.php">🚪Logout</a>
    </p>
    
    <div style="background-color: #f8f9fa; padding: 15px; margin: 15px 0; border: 1px solid #dee2e6;">
    <h2>Daftar User</h2>
    <table border="1">
        <tr>
            <th>ID</th>
            <th>Username</th>
            <th>Email</th>
            <th>Nama Lengkap</th>
            <th>Telepon</th>
            <th>Role</th>
            <th>Terdaftar</th>
            <th>Aksi</th>
        </tr>
        <?php foreach ($users as $user): ?>
        <tr>
            <td><?php echo $user['user_id']; ?></td>
            <td><?php echo htmlspecialchars($user['username']); ?></td>
            <td><?php echo htmlspecialchars($user['email']); ?></td>
            <td><?php echo htmlspecialchars($user['full_name']); ?></td>
            <td><?php echo htmlspecialchars($user['phone']); ?></td>
            <td><?php echo ucfirst($user['role']); ?></td>
            <td><?php echo date('d/m/Y', strtotime($user['created_at'])); ?></td>
            <td>
                <?php if ($user['user_id'] != $_SESSION['user_id']): ?>
                <form method="POST" style="display: inline;">
                    <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">
                    <select name="new_role">
                        <option value="customer" <?php echo ($user['role'] == 'customer') ? 'selected' : ''; ?>>Customer</option>
                        <option value="admin" <?php echo ($user['role'] == 'admin') ? 'selected' : ''; ?>>Admin</option>
                    </select>
                    <input type="submit" name="update_role" value="Update Role">
                </form>
                <?php else: ?>
                <em>Current User</em>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
    </div>

    <div style="background-color: #f8f9fa; padding: 15px; margin: 15px 0; border: 1px solid #dee2e6;">
    <h3>Statistik User</h3>
    <?php
    $stats_query = "SELECT 
                    COUNT(*) as total_users,
                    SUM(CASE WHEN role = 'customer' THEN 1 ELSE 0 END) as total_customers,
                    SUM(CASE WHEN role = 'admin' THEN 1 ELSE 0 END) as total_admins,
                    COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 END) as new_users_30_days
                    FROM users";
    $stats_stmt = $conn->prepare($stats_query);
    $stats_stmt->execute();
    $stats = $stats_stmt->fetch(PDO::FETCH_ASSOC);
    ?>
    
    <table border="1">
        <tr>
            <td>Total User:</td>
            <td><?php echo $stats['total_users']; ?></td>
        </tr>
        <tr>
            <td>Total Customer:</td>
            <td><?php echo $stats['total_customers']; ?></td>
        </tr>
        <tr>
            <td>Total Admin:</td>
            <td><?php echo $stats['total_admins']; ?></td>
        </tr>
        <tr>
            <td>User Baru (30 hari):</td>
            <td><?php echo $stats['new_users_30_days']; ?></td>
        </tr>
    </table>
    </div>
</body>
</html>
