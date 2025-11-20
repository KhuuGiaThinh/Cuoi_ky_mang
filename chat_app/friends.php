<?php
session_start();
require 'db.php';

// Giả lập user đăng nhập (demo)
if(!isset($_SESSION['user_id'])){
    $_SESSION['user_id'] = 1;
    $_SESSION['user_name'] = 'Nguyen Van A';
}
$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];

// XỬ LÝ AJAX
if(isset($_GET['action'])){
    header('Content-Type: application/json; charset=utf-8');

    // 1. Tìm kiếm người dùng
    if($_GET['action']=='search' && isset($_GET['q'])){
        $q = '%'.trim($_GET['q']).'%';
        $stmt = $conn->prepare("
            SELECT id_khach_hang, ten_khach_hang
            FROM khach_hang 
            WHERE ten_khach_hang LIKE ? 
            AND id_khach_hang != ?
        ");
        $stmt->bind_param("si",$q,$user_id);
        $stmt->execute();
        $res=$stmt->get_result();
        $users=[]; while($row=$res->fetch_assoc()) $users[]=$row;
        echo json_encode($users); exit;
    }

    // 2. Gửi yêu cầu kết bạn
    if($_GET['action']=='send_request' && isset($_GET['to'])){
        $to=intval($_GET['to']);
        if($to && $to!=$user_id){
            $stmt = $conn->prepare("
                INSERT IGNORE INTO yeu_cau_ket_ban 
                (nguoi_gui, nguoi_nhan, trang_thai, ngay_gui) 
                VALUES (?, ?, 0, NOW())
            ");
            $stmt->bind_param("ii",$user_id,$to);
            $stmt->execute();
            echo json_encode(['success'=>true]); exit;
        }
        echo json_encode(['success'=>false]); exit;
    }

    // 3. Yêu cầu đến
    if($_GET['action']=='requests_received'){
        $stmt=$conn->prepare("
            SELECT y.id, k.ten_khach_hang 
            FROM yeu_cau_ket_ban y 
            JOIN khach_hang k ON y.nguoi_gui = k.id_khach_hang 
            WHERE y.nguoi_nhan=? AND y.trang_thai=0
            ORDER BY y.ngay_gui DESC
        ");
        $stmt->bind_param("i",$user_id);
        $stmt->execute();
        $res=$stmt->get_result(); 
        $requests=[];
        while($row=$res->fetch_assoc()) $requests[]=$row;
        echo json_encode($requests); exit;
    }

    // 4. Yêu cầu đã gửi
    if($_GET['action']=='requests_sent'){
        $stmt=$conn->prepare("
            SELECT y.id, k.ten_khach_hang, y.nguoi_nhan 
            FROM yeu_cau_ket_ban y 
            JOIN khach_hang k ON y.nguoi_nhan = k.id_khach_hang
            WHERE y.nguoi_gui=? AND y.trang_thai=0
            ORDER BY y.ngay_gui DESC
        ");
        $stmt->bind_param("i",$user_id);
        $stmt->execute();
        $res=$stmt->get_result(); 
        $sent=[];
        while($row=$res->fetch_assoc()) $sent[]=$row;
        echo json_encode($sent); exit;
    }

    // 5. Chấp nhận/Từ chối yêu cầu
    if($_GET['action']=='respond_request'){
        $id=intval($_GET['id']); 
        $resp=intval($_GET['resp']);

        if($resp==1){
            $stmt=$conn->prepare("
                SELECT nguoi_gui, nguoi_nhan 
                FROM yeu_cau_ket_ban 
                WHERE id=? AND nguoi_nhan=?
            ");
            $stmt->bind_param("ii",$id,$user_id);
            $stmt->execute(); 
            $res=$stmt->get_result();

            if($row=$res->fetch_assoc()){
                // cập nhật trạng thái = chấp nhận
                $stmt2=$conn->prepare("UPDATE yeu_cau_ket_ban SET trang_thai=1 WHERE id=?");
                $stmt2->bind_param("i",$id);
                $stmt2->execute();

                // thêm vào danh sách bạn bè
                $stmt3=$conn->prepare("
                    INSERT IGNORE INTO ban_be (nguoi_1, nguoi_2, ngay_tao)
                    VALUES (?, ?, NOW())
                ");
                $stmt3->bind_param("ii",$row['nguoi_gui'],$row['nguoi_nhan']);
                $stmt3->execute();
            }
        } else {
            // từ chối
            $stmt=$conn->prepare("
                UPDATE yeu_cau_ket_ban SET trang_thai=2 WHERE id=?
            ");
            $stmt->bind_param("i",$id);
            $stmt->execute();
        }
        echo json_encode(['success'=>true]); exit;
    }

    // 6. Danh sách bạn bè
    if($_GET['action']=='friends'){
        $stmt = $conn->prepare("
            SELECT 
                CASE 
                    WHEN b.nguoi_1 = ? THEN b.nguoi_2
                    ELSE b.nguoi_1
                END AS friend_id,
                k.ten_khach_hang
            FROM ban_be b
            JOIN khach_hang k 
                ON k.id_khach_hang = CASE 
                    WHEN b.nguoi_1 = ? THEN b.nguoi_2
                    ELSE b.nguoi_1
                END
            WHERE (b.nguoi_1=? OR b.nguoi_2=?)
        ");
        $stmt->bind_param("iiii",$user_id,$user_id,$user_id,$user_id);
        $stmt->execute();
        $res=$stmt->get_result();
        $friends=[];
        while($row=$res->fetch_assoc()) $friends[]=$row;
        echo json_encode($friends);
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Quản lý bạn bè</title>
<style>
body{
    margin:0;font-family:Arial,sans-serif;background:#f0f2f5;display:flex;justify-content:center;padding-top:30px;
}
.container{
    width:550px;background:white;border-radius:10px;box-shadow:0 2px 12px rgba(0,0,0,0.2);padding:20px;
}
h2{margin:10px 0;color:#333;text-align:center;}
.section{
    background:#fafafa;border:1px solid #ddd;border-radius:8px;margin-top:15px;padding:10px;
}
.section h3{margin:5px 0 10px 0;color:#444;}
.search-box input{width:100%;padding:10px;border-radius:5px;border:1px solid #ccc;}
.list{max-height:180px;overflow-y:auto;}
.item{
    display:flex;justify-content:space-between;align-items:center;
    padding:8px;border-bottom:1px solid #eee;
}
button{
    padding:5px 10px;border:none;border-radius:5px;
    background:#25d366;color:white;cursor:pointer;
}
button:hover{background:#128c7e;}
button:disabled{background:#aaa;cursor:not-allowed;}
.header{
    width:100%;
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:15px;
}

.hello{
    font-size:20px;
    font-weight:bold;
    color:#333;
}

.logout-btn{
    padding:8px 14px;
    background:#ff4d4d;
    color:white;
    text-decoration:none;
    border-radius:6px;
    font-weight:bold;
}

.logout-btn:hover{
    background:#cc0000;
}

</style>
</head>
<body>

<div class="container">
<div class="header">
    <div class="hello">Chào <?php echo htmlspecialchars($user_name); ?></div>
    <a href="logout.php" class="logout-btn">Đăng xuất</a>
</div>

<div class="search-box">
    <input type="text" id="searchInput" placeholder="Tìm kiếm người dùng...">
</div>

<div class="section">
    <h3>Kết quả tìm kiếm</h3>
    <div class="list" id="searchResults"></div>
</div>

<div class="section">
    <h3>Yêu cầu đến</h3>
    <div class="list" id="requestsReceived"></div>
</div>

<div class="section">
    <h3>Yêu cầu đã gửi</h3>
    <div class="list" id="requestsSent"></div>
</div>

<div class="section">
    <h3>Bạn bè</h3>
    <div class="list" id="friendsList"></div>
</div>
</div>

<script>
const searchInput=document.getElementById('searchInput');
const searchResults=document.getElementById('searchResults');
const requestsReceived=document.getElementById('requestsReceived');
const requestsSent=document.getElementById('requestsSent');
const friendsList=document.getElementById('friendsList');

let sentList = [];
let friends = [];

// ---- TÌM KIẾM ----
function fetchSearch(q){
    searchResults.innerHTML = '';
    if(!q.trim()) return;

    fetch('?action=search&q='+encodeURIComponent(q))
    .then(r=>r.json())
    .then(data=>{
        searchResults.innerHTML = "";

        data.forEach(u=>{
            let div=document.createElement('div'); 
            div.className='item';

            let btn = document.createElement('button');
            btn.style.minWidth = '100px';

            if(friends.includes(u.id_khach_hang)){
                btn.textContent='Bạn'; 
                btn.disabled=true;
            } else if(sentList.includes(u.id_khach_hang)){
                btn.textContent='Chờ xác nhận'; 
                btn.disabled=true;
            } else {
                btn.textContent='Kết bạn';
                btn.onclick=()=>sendRequest(u.id_khach_hang);
            }

            div.innerHTML=`<span>${u.ten_khach_hang}</span>`;
            div.appendChild(btn);
            searchResults.appendChild(div);
        });
    });
}

searchInput.addEventListener('input',()=>{fetchSearch(searchInput.value)});

// ---- GỬI YÊU CẦU ----
function sendRequest(to){
    fetch(`?action=send_request&to=${to}`)
    .then(r=>r.json())
    .then(r=>{
        if(r.success){
            sentList.push(to);
            fetchSearch(searchInput.value);
            loadRequestsSent();
        }
    });
}

// ---- YÊU CẦU ĐẾN ----
function loadRequestsReceived(){
    fetch('?action=requests_received')
    .then(r=>r.json())
    .then(data=>{
        requestsReceived.innerHTML='';
        data.forEach(r=>{
            let div=document.createElement('div'); 
            div.className='item';

            div.innerHTML=`
                <span>${r.ten_khach_hang}</span>
                <div>
                    <button onclick="respondRequest(${r.id},1)">Chấp nhận</button>
                    <button onclick="respondRequest(${r.id},2)">Từ chối</button>
                </div>
            `;
            requestsReceived.appendChild(div);
        });
    });
}

// ---- YÊU CẦU ĐÃ GỬI ----
function loadRequestsSent(){
    fetch('?action=requests_sent')
    .then(r=>r.json())
    .then(data=>{
        requestsSent.innerHTML='';
        sentList=[];

        data.forEach(r=>{
            sentList.push(r.nguoi_nhan);
            let div=document.createElement('div'); 
            div.className='item';

            div.innerHTML=`<span>${r.ten_khach_hang} (Chờ xác nhận)</span>`;
            requestsSent.appendChild(div);
        });
    });
}

// ---- CHẤP NHẬN / TỪ CHỐI ----
function respondRequest(id,resp){
    fetch(`?action=respond_request&id=${id}&resp=${resp}`)
    .then(r=>r.json())
    .then(r=>{
        loadRequestsReceived();
        loadRequestsSent();
        loadFriends();
    });
}

// ---- DANH SÁCH BẠN BÈ ----
function loadFriends(){
    fetch('?action=friends')
    .then(r => r.json())
    .then(data => {
        friendsList.innerHTML = "";
        friends = [];

        data.forEach(f => {
            friends.push(f.friend_id);

            let div = document.createElement("div");
            div.className = "item";
            div.innerHTML = `
                <span>${f.ten_khach_hang}</span>
                <button onclick="location.href='chat.php?id=${f.friend_id}'">Chat</button>
            `;
            friendsList.appendChild(div);
        });
    });
}

// ---- LOAD MẶC ĐỊNH ----
loadRequestsReceived();
loadRequestsSent();
loadFriends();
</script>

</body>
</html>
