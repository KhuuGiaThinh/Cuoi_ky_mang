<?php
session_start();
require 'db.php';

if($_SERVER['REQUEST_METHOD']==='POST'){
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id_khach_hang, mat_khau, ten_khach_hang FROM khach_hang WHERE tai_khoan=?");
    $stmt->bind_param("s",$username);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();

    if($res && $password === $res['mat_khau']){
        $_SESSION['user_id']=$res['id_khach_hang'];
        $_SESSION['user_name']=$res['ten_khach_hang'];
        header("Location: friends.php"); 
        exit;
    } else {
        $error="Sai tài khoản hoặc mật khẩu";
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Đăng nhập Chat</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <h2>Đăng nhập</h2>
    <?php if(isset($error)) echo "<p class='error'>$error</p>"; ?>
    <form method="post">
        <input type="text" name="username" placeholder="Tài khoản" required>
        <input type="password" name="password" placeholder="Mật khẩu" required>
        <button type="submit">Đăng nhập</button>
    </form>
    <p class="message" style="display:flex; justify-content:space-between; margin-top:10px;">
    <a href="register.php">Đăng ký</a>
    <a href="forgot.php">Quên mật khẩu</a>
</p>

</div>
</body>
</html>
sasdasdas