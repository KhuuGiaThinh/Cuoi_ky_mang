<?php
session_start();
require 'db.php';

// Nếu chưa đăng nhập
if(!isset($_SESSION['user_id'])){
    if(isset($_GET['action']) && $_GET['action']=='history'){
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([]);
        exit;
    }
    header("Location: index.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];

// Trả lịch sử chat AJAX
if(isset($_GET['action']) && $_GET['action']=='history'){
    $to = intval($_GET['to_id']);
    if($to <= 0){
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([]);
        exit;
    }

    $stmt = $conn->prepare("
        SELECT * FROM tin_nhan 
        WHERE (id_nguoi_gui=? AND id_nguoi_nhan=?) 
           OR (id_nguoi_gui=? AND id_nguoi_nhan=?) 
        ORDER BY thoi_gian_gui ASC
    ");
    $stmt->bind_param("iiii",$user_id,$to,$to,$user_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $messages = [];
    while($row=$res->fetch_assoc()) $messages[]=$row;
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($messages,JSON_UNESCAPED_UNICODE);
    exit;
}

// Load danh sách user
$res = $conn->query("SELECT id_khach_hang, ten_khach_hang FROM khach_hang WHERE id_khach_hang != $user_id ORDER BY ten_khach_hang ASC");
$users = $res->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Chat Realtime</title>
<style>
body{margin:0;font-family:Arial,sans-serif;background:#e5ddd5;}
h3{padding:15px;margin:0;background:#075e54;color:white;}
.chat-container{display:flex;height:calc(100vh - 50px);}
#users{width:25%;background:#f0f0f0;overflow-y:auto;border-right:1px solid #ccc;position:relative;}
.user-item{padding:12px;cursor:pointer;border-bottom:1px solid #ddd;}
.user-item.active{background:#d9fdd3;font-weight:bold;}
#logoutBtn{position:absolute;bottom:10px;left:10px;background:#d9534f;color:white;border:none;padding:8px 12px;border-radius:8px;cursor:pointer;}
#chat{flex:1;display:flex;flex-direction:column;}
#messages{flex:1;padding:15px;overflow-y:auto;background:#ece5dd;}
.message{padding:10px 15px;margin:5px 0;max-width:60%;border-radius:20px;line-height:1.4;word-wrap:break-word;box-shadow:0 1px 3px rgba(0,0,0,0.1);}
.message.me{background:#dcf8c6;margin-left:auto;text-align:right;}
.message.them{background:white;margin-right:auto;text-align:left;}
#input{display:flex;border-top:1px solid #ccc;padding:10px;background:#f0f0f0;}
#input input{flex:1;padding:12px;border-radius:20px;border:1px solid #ccc;}
#input button{padding:12px 20px;margin-left:10px;border:none;background:#25d366;color:white;border-radius:20px;cursor:pointer;font-weight:bold;}
#input button:hover{background:#128c7e;}
</style>
</head>
<body>
<h3>Xin chào <?php echo htmlspecialchars($user_name); ?></h3>
<div class="chat-container">
    <div id="users">
        <?php foreach($users as $u): ?>
        <div class="user-item" data-id="<?php echo $u['id_khach_hang']; ?>">
            <?php echo htmlspecialchars($u['ten_khach_hang']); ?>
        </div>
        <?php endforeach; ?>
        <button id="logoutBtn">Quay lại</button>
    </div>

    <div id="chat">
        <div id="messages"></div>
        <div id="input">
            <input type="text" id="msgInput" placeholder="Nhập tin nhắn...">
            <button id="sendBtn">Gửi</button>
        </div>
    </div>
</div>

<script src="chat.js"></script>
<script>
initChat(<?php echo $user_id; ?>);
document.getElementById("logoutBtn").addEventListener("click",()=>{window.location="friends.php";});
</script>
</body>
</html>
