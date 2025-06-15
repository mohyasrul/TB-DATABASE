<!DOCTYPE html>
<html>
<head>
    <title>Food Best - Login</title>
</head>
<body>
    <h1>Food Best - Login</h1>
      <?php
    session_start();
    require_once 'includes/auth.php';
    
    $auth = new Auth();
    $error = '';
    
    // Redirect if already logged in
    if ($auth->isLoggedIn()) {
        if ($_SESSION['role'] === 'admin') {
            header('Location: admin.php');
        } else {
            header('Location: customer.php');
        }
        exit();
    }
    
    if ($_POST) {
        $username = trim($_POST['username']);
        $password = $_POST['password'];
        
        if (empty($username) || empty($password)) {
            $error = 'Username dan password harus diisi!';
        } else {
            if ($auth->login($username, $password)) {
                if ($_SESSION['role'] === 'admin') {
                    header('Location: admin.php');
                } else {
                    header('Location: customer.php');
                }
                exit();
            } else {
                $error = 'Username atau password salah!';
            }
        }
    }
    ?>
    
    <?php if ($error): ?>
        <p style="color: red;"><?php echo $error; ?></p>
    <?php endif; ?>
   
    <div style="background-color: #f8f9fa; padding: 15px; margin: 15px 0; border: 1px solid #dee2e6;">
    <h2>Login</h2>
    <p>Silakan masukkan username dan password Anda untuk masuk ke akun.</p>
    <form method="POST">
        <table>
            <tr>
                <td>Username:</td>
                <td><input type="text" name="username" required></td>
            </tr>
            <tr>
                <td>Password:</td>
                <td><input type="password" name="password" required></td>
            </tr>
            <tr>
                <td></td>
                <td><input type="submit" value="Login"></td>
            </tr>
        </table>
    </form>
    </div>
    <p>
        <a href="register.php">Belum punya akun? Daftar di sini</a>
    </p>

</body>
</html>
