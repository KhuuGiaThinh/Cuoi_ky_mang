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
$query = "
SELECT k.id_khach_hang, k.ten_khach_hang 
FROM khach_hang k
JOIN ban_be b 
    ON (
        (b.nguoi_1 = $user_id AND b.nguoi_2 = k.id_khach_hang)
        OR
        (b.nguoi_2 = $user_id AND b.nguoi_1 = k.id_khach_hang)
    )
ORDER BY k.ten_khach_hang ASC
";

$res = $conn->query($query);

if(!$res){
    die("SQL ERROR: " . $conn->error . "<br>Query: " . $query);
}

$users = $res->fetch_all(MYSQLI_ASSOC);


?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Chat Realtime</title>
<link rel="stylesheet" href="chat.css">
</head>
<body>
<h3>Xin chào <?php echo htmlspecialchars($user_name); ?></h3>
<div class="chat-container">
    <div id="users">
    <div id="usersHeader">Danh sách bạn bè</div>
        <?php foreach($users as $u): ?>
        <div class="user-item" data-id="<?php echo $u['id_khach_hang']; ?>">
            <?php echo htmlspecialchars($u['ten_khach_hang']); ?>
        </div>
        <?php endforeach; ?>
        <button id="logoutBtn">Quay lại</button>
    </div>

    <div id="chat">
    <div id="chatHeader">Chọn một người để bắt đầu chat</div>

    <div id="messages"></div>

    <div id="input">
        <input type="text" id="msgInput" placeholder="Nhập tin nhắn...">
        <button id="sendBtn">Gửi</button>
    </div>
</div>

</div>

<script>
    
initChat(<?php echo $user_id; ?>);
document.getElementById("logoutBtn").addEventListener("click",()=>{window.location="friends.php";});

function initChat(userId){
    let selectedUser = null;
    const messagesDiv = document.getElementById("messages");
    const ws = new WebSocket(`ws://localhost:8080/?user_id=${userId}`);

    ws.onopen = () => console.log("WebSocket connected");
    ws.onclose = () => console.log("WebSocket disconnected");
    ws.onerror = e => console.error("WebSocket error:", e);

    ws.onmessage = e => {
        let data;
        try { data = JSON.parse(e.data); } 
        catch(err){ console.error("Invalid JSON:", e.data); return; }

        if(!selectedUser) return;
        if(data.from_id == selectedUser || data.from_id == userId){
            const div = document.createElement("div");
            div.className = (data.from_id==userId) ? "message me" : "message them";
            div.textContent = data.message;
            messagesDiv.appendChild(div);
            messagesDiv.scrollTop = messagesDiv.scrollHeight;
        }
    };

    function addMessage(type, text, time){
    const div = document.createElement("div");
    div.className = "message " + type;
    
    const textDiv = document.createElement("div");
    textDiv.textContent = text;
    div.appendChild(textDiv);

    if(time){
        const t = new Date(time);
        const hours = t.getHours().toString().padStart(2,'0');
        const minutes = t.getMinutes().toString().padStart(2,'0');
        const timeDiv = document.createElement("div");
        timeDiv.className = "message-time";
        timeDiv.textContent = `${hours}:${minutes}`;
        div.appendChild(timeDiv);
    }

    const messagesDiv = document.getElementById("messages");
    messagesDiv.appendChild(div);
    messagesDiv.scrollTop = messagesDiv.scrollHeight;
}


    let chatInterval = null;

function startChatPolling(to_id){
    // Dừng polling cũ nếu có
    if(chatInterval) clearInterval(chatInterval);

    chatInterval = setInterval(()=>{
        fetch(`chat.php?action=history&to_id=${to_id}`)
        .then(res => res.json())
        .then(data=>{
            const messagesDiv = document.getElementById("messages");
            messagesDiv.innerHTML = ""; // xóa hết tin cũ

            data.forEach(msg => {
                addMessage(
                    msg.id_nguoi_gui == userId ? "me" : "them",
                    msg.noi_dung,
                    msg.thoi_gian_gui
                );
            });

            messagesDiv.scrollTop = messagesDiv.scrollHeight;
        });
    }, 1000); 
}
    function loadHistory(to_id){
        fetch(`chat.php?action=history&to_id=${to_id}`)
        .then(res => res.json())
        .then(data=>{
            messagesDiv.innerHTML = ""; // xóa hết tin cũ

            data.forEach(msg => {
                const type = (msg.id_nguoi_gui == userId) ? "me" : "them";
                const div = document.createElement("div");
                div.className = "message " + type;
                div.textContent = msg.noi_dung;
                messagesDiv.appendChild(div);
            });

            messagesDiv.scrollTop = messagesDiv.scrollHeight;
        });
    }

    document.querySelectorAll(".user-item").forEach(el=>{
        el.addEventListener("click",()=>{
    document.querySelectorAll(".user-item").forEach(x=>x.classList.remove("active"));
    el.classList.add("active");
    selectedUser = el.dataset.id;

    document.getElementById("chatHeader").textContent = el.textContent.trim();

    loadHistory(selectedUser);
    startChatPolling(selectedUser);
});

    });

    document.getElementById("sendBtn").addEventListener("click",()=>{
        const msg = document.getElementById("msgInput").value.trim();
        if(msg && selectedUser && ws.readyState===WebSocket.OPEN){
            fetch('send_message.php', {
    method: 'POST',
    headers: {'Content-Type':'application/x-www-form-urlencoded'},
    body: `to_id=${selectedUser}&message=${encodeURIComponent(msg)}`
})
.then(res => res.json())
.then(data=>{
    if(data.success){
        // Thời gian lấy từ CSDL server
        addMessage("me", msg, data.thoi_gian);
        document.getElementById("msgInput").value="";
    }else{
        alert("Gửi thất bại: " + data.error);
    }
});
        }
    });

    document.getElementById("msgInput").addEventListener("keypress", e=>{
        if(e.key==="Enter") document.getElementById("sendBtn").click();
    });
}
// ⭐ Tự động chọn người đã click từ friends.php
let initialFriendId = localStorage.getItem('chat_with');
if (initialFriendId) {
    const el = document.querySelector(`.user-item[data-id='${initialFriendId}']`);
    if (el) {
        el.click(); // kích hoạt sự kiện chọn người
        localStorage.removeItem('chat_with');
    }
}

</script>
</body>
</html>
