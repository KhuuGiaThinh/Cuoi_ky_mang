<?php
session_start();
require 'db.php';

if(!isset($_SESSION['user_id'])){
    echo json_encode(['success'=>false,'error'=>'Chưa đăng nhập']);
    exit;
}

$user_id = $_SESSION['user_id'];

$to_id = intval($_POST['to_id']);
$message = trim($_POST['message']);

if($to_id <= 0 || $message === ''){
    echo json_encode(['success'=>false,'error'=>'Dữ liệu không hợp lệ']);
    exit;
}

// Chèn tin nhắn vào CSDL và lấy luôn thời gian
$stmt = $conn->prepare("INSERT INTO tin_nhan (id_nguoi_gui, id_nguoi_nhan, noi_dung) VALUES (?, ?, ?)");
$stmt->bind_param("iis", $user_id, $to_id, $message);

if($stmt->execute()){
    $insert_id = $stmt->insert_id;
    // Lấy lại thời gian từ CSDL
    $res = $conn->query("SELECT thoi_gian_gui FROM tin_nhan WHERE id_tin_nhan = $insert_id");
    $row = $res->fetch_assoc();
    echo json_encode(['success'=>true,'thoi_gian'=>$row['thoi_gian_gui']]);
}else{
    echo json_encode(['success'=>false,'error'=>$stmt->error]);
}
