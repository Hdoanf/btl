<?php
require_once '../includes/db.php';
require_once 'check_login.php';

$thongbao = '';
$loi = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $ma_sv = $_POST['ma_sv'];
    $ho_ten = $_POST['ho_ten'];
    $email = $_POST['email'];
    $sdt = $_POST['sdt'];
    
    try {
        $kiemtra = $db->prepare("SELECT id FROM hoc_vien WHERE ma_sv = ?");
        $kiemtra->execute([$ma_sv]);
        
        if ($kiemtra->fetch()) {
            $loi = "Mã sinh viên đã tồn tại!";
        } else {
            $sql = "INSERT INTO hoc_vien (ma_sv, ho_ten, email, sdt) VALUES (?, ?, ?, ?)";
            $stmt = $db->prepare($sql);
            $stmt->execute([$ma_sv, $ho_ten, $email, $sdt]);
            $thongbao = "Thêm sinh viên thành công!";
        }
    } catch (PDOException $e) {
        $loi = "Lỗi: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Thêm sinh viên mới</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
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
        .required-field::after {
            content: " *";
            color: red;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container">
        <div class="form-container">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0"><i class="fas fa-user-plus me-2"></i>Thêm sinh viên mới</h2>
                <a href="index.php" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Quay lại
                </a>
            </div>
            
            <?php if ($thongbao): ?>
                <div class="alert alert-success"><?= $thongbao ?></div>
            <?php endif; ?>
            
            <?php if ($loi): ?>
                <div class="alert alert-danger"><?= $loi ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="mb-3">
                    <label for="ma_sv" class="form-label required-field">Mã sinh viên</label>
                    <input type="text" class="form-control" id="ma_sv" name="ma_sv" required>
                </div>
                
                <div class="mb-3">
                    <label for="ho_ten" class="form-label required-field">Họ và tên</label>
                    <input type="text" class="form-control" id="ho_ten" name="ho_ten" required>
                </div>
                
                <div class="mb-3">
                    <label for="email" class="form-label required-field">Email</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>
                
                <div class="mb-3">
                    <label for="sdt" class="form-label">Số điện thoại</label>
                    <input type="text" class="form-control" id="sdt" name="sdt">
                </div>
                
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Lưu thông tin
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
