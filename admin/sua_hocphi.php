<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once 'check_login.php';

$thongbao = '';
$loi = '';

// Lấy thông tin học phí hiện tại nếu có ma_sv và ky_hoc
if (isset($_GET['ma_sv']) && isset($_GET['ky_hoc'])) {
    $ma_sv = $_GET['ma_sv'];
    $ky_hoc = urldecode($_GET['ky_hoc']);
    
    $sql = "SELECT * FROM hoc_phi WHERE ma_sv = ? AND ky_hoc = ?";
    $stmt = $db->prepare($sql);
    $stmt->execute([$ma_sv, $ky_hoc]);
    $hocphi = $stmt->fetch();
    
    if (!$hocphi) {
        $_SESSION['loi'] = "Không tìm thấy thông tin học phí";
        header('Location: index.php');
        exit();
    }
}

// Xử lý khi submit form
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $ma_sv = $_POST['ma_sv'];
    $ky_hoc = $_POST['ky_hoc'];
    $tong_tien = str_replace(',', '', $_POST['tong_tien']);
    $da_dong = str_replace(',', '', $_POST['da_dong']);
    $han_dong = $_POST['han_dong'];
    
    // Validate dữ liệu
    if (!is_numeric($tong_tien) || $tong_tien <= 0) {
        $loi = "Tổng tiền phải là số dương";
    } elseif (!is_numeric($da_dong) || $da_dong < 0) {
        $loi = "Số tiền đã đóng không hợp lệ";
    } elseif ($da_dong > $tong_tien) {
        $loi = "Số tiền đã đóng không thể lớn hơn tổng tiền";
    } else {
        try {
            $sql = "UPDATE hoc_phi 
                    SET tong_tien = ?, da_dong = ?, han_dong = ?
                    WHERE ma_sv = ? AND ky_hoc = ?";
            $stmt = $db->prepare($sql);
            $stmt->execute([$tong_tien, $da_dong, $han_dong, $ma_sv, $ky_hoc]);
            
            $_SESSION['thongbao'] = "Cập nhật học phí thành công!";
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
    <title>Sửa thông tin học phí</title>
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
        .input-group-text {
            min-width: 120px;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container">
        <div class="form-container">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0"><i class="fas fa-money-bill-wave me-2"></i>Sửa thông tin học phí</h2>
                <a href="index.php" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Quay lại
                </a>
            </div>
            
            <?php if ($loi): ?>
                <div class="alert alert-danger"><?= $loi ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <input type="hidden" name="ma_sv" value="<?= $hocphi['ma_sv'] ?? '' ?>">
                <input type="hidden" name="ky_hoc" value="<?= $hocphi['ky_hoc'] ?? '' ?>">
                
                <div class="mb-3">
                    <label class="form-label">Mã sinh viên</label>
                    <input type="text" class="form-control" value="<?= $hocphi['ma_sv'] ?? '' ?>" readonly>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Học kỳ</label>
                    <input type="text" class="form-control" value="<?= $hocphi['ky_hoc'] ?? '' ?>" readonly>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Số tiền</label>
                    <div class="input-group">
                        <span class="input-group-text">Tổng tiền</span>
                        <input type="text" class="form-control text-end" name="tong_tien" 
                               value="<?= isset($hocphi['tong_tien']) ? number_format($hocphi['tong_tien']) : '' ?>" required>
                        <span class="input-group-text">VNĐ</span>
                    </div>
                </div>
                
                <div class="mb-3">
                    <div class="input-group">
                        <span class="input-group-text">Đã đóng</span>
                        <input type="text" class="form-control text-end" name="da_dong" 
                               value="<?= isset($hocphi['da_dong']) ? number_format($hocphi['da_dong']) : '' ?>" required>
                        <span class="input-group-text">VNĐ</span>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="han_dong" class="form-label">Hạn đóng</label>
                    <input type="date" class="form-control" id="han_dong" name="han_dong" 
                           value="<?= $hocphi['han_dong'] ?? '' ?>" required>
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
    <script>
        // Định dạng số tiền khi nhập
        document.querySelector('input[name="tong_tien"]').addEventListener('blur', function(e) {
            let value = e.target.value.replace(/,/g, '');
            if (!isNaN(value) && value !== '') {
                e.target.value = Number(value).toLocaleString('vi-VN');
            }
        });
        
        document.querySelector('input[name="da_dong"]').addEventListener('blur', function(e) {
            let value = e.target.value.replace(/,/g, '');
            if (!isNaN(value) && value !== '') {
                e.target.value = Number(value).toLocaleString('vi-VN');
            }
        });
    </script>
</body>
</html>
