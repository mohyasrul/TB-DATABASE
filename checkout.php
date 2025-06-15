<?php
require_once 'includes/auth.php';
require_once 'config/database.php';

$auth = new Auth();
$auth->requireLogin();

$database = new Database();
$conn = $database->getConnection();

if ($_POST) {
    $user_id = $_SESSION['user_id'];
    $delivery_address = trim($_POST['delivery_address']);
    $phone = trim($_POST['phone']);
    $notes = trim($_POST['notes']);
    
    // Validate input
    if (empty($delivery_address) || empty($phone)) {
        echo "<!DOCTYPE html>";
        echo "<html><head><title>Food Best - Error</title></head><body>";
        echo "<h1>Error!</h1>";
        echo "<p>Alamat pengiriman dan nomor telepon harus diisi!</p>";
        echo "<p><a href='cart.php'>Kembali ke Keranjang</a></p>";
        echo "</body></html>";
        exit();
    }
    
    try {
        $conn->beginTransaction();
        
        // Calculate total amount from cart
        $total_query = "SELECT SUM(c.quantity * mi.price) as total_amount
                       FROM cart c
                       JOIN menu_items mi ON c.item_id = mi.item_id
                       WHERE c.user_id = ?";
        $total_stmt = $conn->prepare($total_query);
        $total_stmt->execute([$user_id]);
        $total_result = $total_stmt->fetch();
        $total_amount = $total_result['total_amount'];
        
        if (!$total_amount || $total_amount <= 0) {
            throw new Exception("Keranjang kosong atau tidak valid!");
        }
        
        // Create new order
        $order_query = "INSERT INTO orders (user_id, total_amount, delivery_address, phone, notes) VALUES (?, ?, ?, ?, ?)";
        $order_stmt = $conn->prepare($order_query);
        $order_stmt->execute([$user_id, $total_amount, $delivery_address, $phone, $notes]);
        
        $order_id = $conn->lastInsertId();
        
        // Move items from cart to order_items
        $items_query = "INSERT INTO order_items (order_id, item_id, quantity, price, subtotal)
                       SELECT ?, c.item_id, c.quantity, mi.price, (c.quantity * mi.price)
                       FROM cart c
                       JOIN menu_items mi ON c.item_id = mi.item_id
                       WHERE c.user_id = ?";
        $items_stmt = $conn->prepare($items_query);
        $items_stmt->execute([$order_id, $user_id]);
        
        // Clear cart
        $clear_query = "DELETE FROM cart WHERE user_id = ?";
        $clear_stmt = $conn->prepare($clear_query);
        $clear_stmt->execute([$user_id]);
        
        $conn->commit();
        
        echo "<!DOCTYPE html>";
        echo "<html><head><title>Food Best - Checkout Berhasil</title></head><body>";
        echo "<h1>Checkout Berhasil!</h1>";
        echo "<p>Order ID: #" . $order_id . "</p>";
        echo "<p>Total: Rp " . number_format($total_amount, 0, ',', '.') . "</p>";
        echo "<p>Status: Pending</p>";
        echo "<p>Pesanan Anda sedang diproses. Silakan tunggu konfirmasi dari admin.</p>";
        echo "<p><a href='customer.php'>Kembali ke Menu</a> | <a href='order_history.php'>Lihat Riwayat Pesanan</a></p>";
        echo "</body></html>";
        
    } catch (Exception $e) {
        $conn->rollback();
        echo "<!DOCTYPE html>";
        echo "<html><head><title>Food Best - Checkout Gagal</title></head><body>";
        echo "<h1>Checkout Gagal!</h1>";
        echo "<p>Error: " . $e->getMessage() . "</p>";
        echo "<p><a href='cart.php'>Kembali ke Keranjang</a></p>";
        echo "</body></html>";
    }
} else {
    header('Location: cart.php');
    exit();
}
?>
