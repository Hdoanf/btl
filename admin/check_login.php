<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Thiết lập username mặc định nếu chưa có
if (!isset($_SESSION['username'])) {
    $_SESSION['username'] = 'Quản trị viên';
}
?>
