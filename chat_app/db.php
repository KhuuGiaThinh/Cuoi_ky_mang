<?php
$servername = "sql113.infinityfree.com";
$username = "if0_42161801";
$password = "SdD4KQG4JlBP2";
$dbname = "if0_42161801_ungdungchat"; // thay XXX bằng tên database thật

$conn = new mysqli($servername, $username, $password, $dbname);

$conn->set_charset("utf8mb4");

if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}
?>