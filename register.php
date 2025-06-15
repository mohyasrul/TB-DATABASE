<!DOCTYPE html>
<html>
<head>
    <title>Food Best - Register</title>
</head>
<body>
    <h1>Food Best - Daftar Akun</h1>
      <?php
    require_once 'config/database.php';
    
    $database = new Database();
    $conn = $database->getConnection();
    $message = '';
    
    if ($_POST) {
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        $password = $_POST['password'];
        $full_name = trim($_POST['full_name']);
        $phone = trim($_POST['phone']);
        $address = trim($_POST['address']);
        
        // Basic validation
        if (empty($username) || empty($email) || empty($password) || empty($full_name)) {
            $message = '<span style="color: red;">Semua field wajib diisi!</span>';
        } elseif (strlen($password) < 6) {
            $message = '<span style="color: red;">Password minimal 6 karakter!</span>';
        } else {
            // Check if username or email already exists
            $check_query = "SELECT user_id FROM users WHERE username = ? OR email = ?";
            $check_stmt = $conn->prepare($check_query);
            $check_stmt->execute([$username, $email]);
            
            if ($check_stmt->rowCount() > 0) {
                $message = '<span style="color: red;">Username atau email sudah digunakan!</span>';
            } else {
                $query = "INSERT INTO users (username, email, password, full_name, phone, address, role) VALUES (?, ?, MD5(?), ?, ?, ?, 'customer')";
                $stmt = $conn->prepare($query);
                
                if ($stmt->execute([$username, $email, $password, $full_name, $phone, $address])) {
                    $message = '<span style="color: green;">Registrasi berhasil! <a href="login.php">Login di sini</a></span>';
                } else {
                    $message = '<span style="color: red;">Registrasi gagal! Silakan coba lagi.</span>';
                }
            }
        }
    }
    ?>
    
    <?php if ($message): ?>
        <p><?php echo $message; ?></p>
    <?php endif; ?>
    
    <div style="background-color: #f8f9fa; padding: 15px; margin: 15px 0; border: 1px solid #dee2e6;">
    <form method="POST">
        <table>
            <tr>
                <td>Username:</td>
                <td><input type="text" name="username" required></td>
            </tr>
            <tr>
                <td>Email:</td>
                <td><input type="email" name="email" required></td>
            </tr>
            <tr>
                <td>Password:</td>
                <td><input type="password" name="password" required></td>
            </tr>
            <tr>
                <td>Nama Lengkap:</td>
                <td><input type="text" name="full_name" required></td>
            </tr>
            <tr>
                <td>Telepon:</td>
                <td><input type="text" name="phone"></td>
            </tr>
            <tr>
                <td>Alamat:</td>
                <td><textarea name="address" rows="3"></textarea></td>
            </tr>
            <tr>
                <td></td>
                <td><input type="submit" value="Daftar"></td>
            </tr>
        </table>
    </form>
    </div>
    
    <p>
        <a href="login.php">Sudah punya akun? Login di sini</a>
    </p>
</body>
</html>
