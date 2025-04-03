<?php
session_start();
require_once '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    $sql = "SELECT * FROM nguoi_dung WHERE username = ?";
    $stmt = $db->prepare($sql);
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    
    // Sửa đoạn kiểm tra mật khẩu thành:
    if ($user) {
        // Nếu password trong DB là plain text
        if ($user['password'] === $password) {
            // Nâng cấp lưu password mã hóa
            $hashed_pw = password_hash($password, PASSWORD_BCRYPT);
            $update_sql = "UPDATE nguoi_dung SET password = ? WHERE id = ?";
            $db->prepare($update_sql)->execute([$hashed_pw, $user['id']]);
            
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            header('Location: index.php');
            exit();
        }
        // Nếu password đã mã hóa
        elseif (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            header('Location: index.php');
            exit();
        }
    }
    
    $error = "Sai tên đăng nhập hoặc mật khẩu";
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Đăng nhập hệ thống</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background-color: #f5f5f5;
        }
        .login-container {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            width: 300px;
        }
        h2 {
            text-align: center;
            margin-bottom: 20px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
        }
        input {
            width: 100%;
            padding: 8px;
            box-sizing: border-box;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        button {
            width: 100%;
            padding: 10px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .error {
            color: red;
            margin-bottom: 15px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>Đăng nhập hệ thống</h2>
        <?php if(isset($error)): ?>
            <div class="error"><?= $error ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label>Tên đăng nhập:</label>
                <input type="text" name="username" required>
            </div>
            <div class="form-group">
                <label>Mật khẩu:</label>
                <input type="password" name="password" required>
            </div>
            <button type="submit">Đăng nhập</button>
        </form>
    </div>
</body>
</html>
