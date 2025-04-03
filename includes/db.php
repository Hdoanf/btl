<?php
require_once 'config.php';

try {
    $dsn = "mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=utf8";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ];
    $db = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (PDOException $e) {
    die("Kết nối database thất bại: " . $e->getMessage());
}
?>
