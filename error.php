<!DOCTYPE html>
<html>
<head>
    <title>Food Best - Error</title>
</head>
<body>
    <h1>Terjadi Kesalahan</h1>
    
    <?php
    $error_message = isset($_GET['msg']) ? htmlspecialchars($_GET['msg']) : 'Terjadi kesalahan yang tidak diketahui.';
    $redirect_url = isset($_GET['redirect']) ? $_GET['redirect'] : 'index.php';
    ?>
    
    <p style="color: red;"><?php echo $error_message; ?></p>
    
    <p>
        <a href="<?php echo htmlspecialchars($redirect_url); ?>">Kembali</a> | 
        <a href="index.php">Halaman Utama</a>
    </p>
    
    <p><em>Jika masalah terus berlanjut, silakan hubungi administrator.</em></p>
</body>
</html>
