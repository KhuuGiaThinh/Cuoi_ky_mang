<?php
session_start();
require 'db.php';
$success = $error = "";

if($_SERVER['REQUEST_METHOD']==='POST'){
    $ten = trim($_POST['ten_khach_hang']);
    $tai_khoan = trim($_POST['tai_khoan']);
    $mat_khau = trim($_POST['mat_khau']);
    $mat_khau2 = trim($_POST['mat_khau2']);

    if($mat_khau !== $mat_khau2){
        $error = "Mật khẩu nhập lại không khớp!";
    } else {
        // Kiểm tra tài khoản đã tồn tại
        $stmt = $conn->prepare("SELECT id_khach_hang FROM khach_hang WHERE tai_khoan=?");
        $stmt->bind_param("s",$tai_khoan);
        $stmt->execute();
        $res = $stmt->get_result();
        if($res->num_rows > 0){
            $error = "Tài khoản đã tồn tại!";
        } else {
            $stmt = $conn->prepare("INSERT INTO khach_hang (ten_khach_hang,tai_khoan,mat_khau) VALUES (?,?,?)");
            $stmt->bind_param("sss",$ten,$tai_khoan,$mat_khau); // dùng plain text password
            if($stmt->execute()){
                $success = "Đăng ký thành công! Chuyển về trang đăng nhập...";
                header("refresh:2;url=index.php"); // tự chuyển về sau 2 giây
            } else {
                $error = "Có lỗi xảy ra, vui lòng thử lại.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Đăng ký Chat</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <h2>Đăng ký</h2>
    <?php 
    if($error) echo "<p class='error'>$error</p>"; 
    if($success) echo "<p class='success'>$success</p>";
    ?>
    <form method="post">
        <input type="text" name="ten_khach_hang" placeholder="Tên khách hàng" required>
        <input type="text" name="tai_khoan" placeholder="Tên tài khoản" required>
        <input type="password" name="mat_khau" placeholder="Mật khẩu" required>
        <input type="password" name="mat_khau2" placeholder="Nhập lại mật khẩu" required>
        <button type="submit">Đăng ký</button>
    </form>
    <p class="message">Đã có tài khoản? <a href="index.php">Đăng nhập</a></p>
</div>
</body>
</html>
