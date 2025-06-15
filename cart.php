<!DOCTYPE html>
<html>
<head>
    <title>Food Best - Keranjang Belanja</title>
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
      // Handle remove from cart
    if (isset($_POST['remove_item'])) {
        $cart_id = $_POST['cart_id'];
        $delete_query = "DELETE FROM cart WHERE cart_id = ? AND user_id = ?";
        $delete_stmt = $conn->prepare($delete_query);
        if ($delete_stmt->execute([$cart_id, $user_id])) {
            echo "<p style='color: green;'>Item berhasil dihapus dari keranjang!</p>";
        }
    }
    
    // Handle update quantity
    if (isset($_POST['update_quantity'])) {
        $cart_id = $_POST['cart_id'];
        $quantity = (int)$_POST['quantity'];
        if ($quantity > 0) {
            $update_query = "UPDATE cart SET quantity = ? WHERE cart_id = ? AND user_id = ?";
            $update_stmt = $conn->prepare($update_query);
            if ($update_stmt->execute([$quantity, $cart_id, $user_id])) {
                echo "<p style='color: green;'>Quantity berhasil diupdate!</p>";
            }
        } else {
            // If quantity is 0 or negative, remove the item
            $delete_query = "DELETE FROM cart WHERE cart_id = ? AND user_id = ?";
            $delete_stmt = $conn->prepare($delete_query);
            $delete_stmt->execute([$cart_id, $user_id]);
            echo "<p style='color: green;'>Item berhasil dihapus dari keranjang!</p>";
        }
    }
    
    // Get cart items
    $cart_query = "SELECT c.cart_id, c.quantity, mi.item_name, mi.price, (c.quantity * mi.price) as subtotal 
                   FROM cart c 
                   JOIN menu_items mi ON c.item_id = mi.item_id 
                   WHERE c.user_id = ?";
    $cart_stmt = $conn->prepare($cart_query);
    $cart_stmt->execute([$user_id]);
    $cart_items = $cart_stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $total = 0;
    foreach ($cart_items as $item) {
        $total += $item['subtotal'];
    }
    ?>
    
    <h1>🛒Keranjang Belanja</h1>
    
    <p>
        <a href="customer.php">🏠Kembali ke Menu</a> | 
        <a href="order_history.php">📋Riwayat Pesanan</a> | 
        <a href="logout.php">🚪Logout</a>
    </p>
    
    <?php if (count($cart_items) > 0): ?>

        <div style="background-color: #f8f9fa; padding: 15px; margin: 15px 0; border: 1px solid #dee2e6;">
        <h2>📃Daftar Item di Keranjang</h2>
        <table border="1">
            <tr>
                <th>Nama Menu</th>
                <th>Harga</th>
                <th>Jumlah</th>
                <th>Subtotal</th>
                <th>Aksi</th>
            </tr>
            <?php foreach ($cart_items as $item): ?>
            <tr>
                <td><?php echo htmlspecialchars($item['item_name']); ?></td>
                <td>Rp <?php echo number_format($item['price'], 0, ',', '.'); ?></td>
                <td>
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="cart_id" value="<?php echo $item['cart_id']; ?>">
                        <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>" min="1" style="width: 50px;">
                        <input type="submit" name="update_quantity" value="Update">
                    </form>
                </td>
                <td>Rp <?php echo number_format($item['subtotal'], 0, ',', '.'); ?></td>
                <td>
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="cart_id" value="<?php echo $item['cart_id']; ?>">
                        <input type="submit" name="remove_item" value="Hapus" onclick="return confirm('Yakin ingin menghapus item ini?')">
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
            <tr>
                <td colspan="3"><strong>Total:</strong></td>
                <td><strong>Rp <?php echo number_format($total, 0, ',', '.'); ?></strong></td>
                <td></td>
            </tr>
        </table>
        </div>

        <div style="background-color: #f8f9fa; padding: 15px; margin: 15px 0; border: 1px solid #dee2e6;">
        <h2>📝Isi Detail Pesanan</h2>
        <form method="POST" action="checkout.php">
            <table>
                <tr>
                    <td>Alamat Pengiriman:</td>
                    <td><textarea name="delivery_address" rows="3" required></textarea></td>
                </tr>
                <tr>
                    <td>Nomor Telepon:</td>
                    <td><input type="text" name="phone" required></td>
                </tr>
                <tr>
                    <td>Catatan:</td>
                    <td><textarea name="notes" rows="2"></textarea></td>
                </tr>
                <tr>
                    <td></td>
                    <td><input type="submit" value="Checkout" onclick="return confirm('Lanjutkan checkout?')"></td>
                </tr>
            </table>
        </form>
        </div>
    <?php else: ?>
        <p>Keranjang kosong. <a href="customer.php">Pilih menu untuk ditambahkan</a></p>
    <?php endif; ?>
</body>
</html>
