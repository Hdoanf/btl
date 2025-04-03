<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once 'check_login.php';

$thongbao = '';
$loi = '';

// Lấy thông tin sinh viên hiện tại
if (isset($_GET['ma_sv'])) {
    $ma_sv = $_GET['ma_sv'];
    
    $sql = "SELECT * FROM hoc_vien WHERE ma_sv = ?";
    $stmt = $db->prepare($sql);
    $stmt->execute([$ma_sv]);
    $hocvien = $stmt->fetch();
    
    if (!$hocvien) {
        $_SESSION['loi'] = "Không tìm thấy sinh viên";
        header('Location: index.php');
        exit();
    }
}

// Xử lý khi submit form
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $ma_sv = $_POST['ma_sv'];
    $ho_ten = $_POST['ho_ten'];
    $email = $_POST['email'];
    $sdt = $_POST['sdt'];
    
    // Validate dữ liệu
    if (empty($ho_ten)) {
        $loi = "Tên sinh viên không được để trống";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $loi = "Email không hợp lệ";
    } else {
        try {
            $sql = "UPDATE hoc_vien 
                    SET ho_ten = ?, email = ?, sdt = ?
                    WHERE ma_sv = ?";
            $stmt = $db->prepare($sql);
            $stmt->execute([$ho_ten, $email, $sdt, $ma_sv]);
            
            $_SESSION['thongbao'] = "Cập nhật thông tin sinh viên thành công!";
            header('Location: index.php');
            exit();
        } catch (PDOException $e) {
            $loi = "Lỗi cập nhật: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sửa thông tin sinh viên</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .form-container {
            max-width: 600px;
            margin: 30px auto;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            background: white;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container">
        <div class="form-container">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0"><i class="fas fa-user-edit me-2"></i>Sửa thông tin sinh viên</h2>
                <a href="index.php" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Quay lại
                </a>
            </div>
            
            <?php if ($loi): ?>
                <div class="alert alert-danger"><?= $loi ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <input type="hidden" name="ma_sv" value="<?= $hocvien['ma_sv'] ?? '' ?>">
                
                <div class="mb-3">
                    <label class="form-label">Mã sinh viên</label>
                    <input type="text" class="form-control" value="<?= $hocvien['ma_sv'] ?? '' ?>" readonly>
                </div>
                
                <div class="mb-3">
                    <label for="ho_ten" class="form-label">Họ và tên</label>
                    <input type="text" class="form-control" id="ho_ten" name="ho_ten" 
                           value="<?= $hocvien['ho_ten'] ?? '' ?>" required>
                </div>
                
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" 
                           value="<?= $hocvien['email'] ?? '' ?>" required>
                </div>
                
                <div class="mb-3">
                    <label for="sdt" class="form-label">Số điện thoại</label>
                    <input type="tel" class="form-control" id="sdt" name="sdt" 
                           value="<?= $hocvien['sdt'] ?? '' ?>">
                </div>
                
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Lưu thay đổi
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
