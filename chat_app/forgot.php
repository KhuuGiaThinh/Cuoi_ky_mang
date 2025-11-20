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
        // Kiểm tra tên khách hàng + tài khoản có tồn tại
        $stmt = $conn->prepare("SELECT id_khach_hang FROM khach_hang WHERE ten_khach_hang=? AND tai_khoan=?");
        $stmt->bind_param("ss",$ten,$tai_khoan);
        $stmt->execute();
        $res = $stmt->get_result();

        if($res->num_rows === 0){
            $error = "Tên khách hàng hoặc tài khoản không tồn tại!";
        } else {
            $row = $res->fetch_assoc();
            $id = $row['id_khach_hang'];

            // Cập nhật mật khẩu mới (plain text)
            $stmt = $conn->prepare("UPDATE khach_hang SET mat_khau=? WHERE id_khach_hang=?");
            $stmt->bind_param("si",$mat_khau,$id);
            if($stmt->execute()){
                $success = "Đổi mật khẩu thành công! Chuyển về trang đăng nhập...";
                header("refresh:2;url=index.php"); // tự chuyển về login sau 2 giây
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
<title>Quên mật khẩu</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <h2>Quên mật khẩu</h2>
    <?php 
    if($error) echo "<p class='error'>$error</p>"; 
    if($success) echo "<p class='success'>$success</p>";
    ?>
    <form method="post">
        <input type="text" name="ten_khach_hang" placeholder="Tên khách hàng" required>
        <input type="text" name="tai_khoan" placeholder="Tên tài khoản" required>
        <input type="password" name="mat_khau" placeholder="Mật khẩu mới" required>
        <input type="password" name="mat_khau2" placeholder="Nhập lại mật khẩu" required>
        <button type="submit">Đổi mật khẩu</button>
    </form>
    <p class="message">Đã nhớ mật khẩu? <a href="index.php">Đăng nhập</a></p>
</div>
</body>
</html>
