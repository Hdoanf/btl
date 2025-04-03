<?php
require_once '../includes/db.php';
require_once 'check_login.php';

$thongbao = '';
$loi = '';
$ds_sinhvien = [];
if (isset($_GET['ma_sv'])) {
  $ma_sv = $_GET['ma_sv'];
}
$sql_sv = "SELECT ma_sv, ho_ten FROM hoc_vien ORDER BY ho_ten";
$ds_sinhvien = $db->query($sql_sv)->fetchAll();


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $ma_sv = $_POST['ma_sv'];
  $ky_hoc = $_POST['ky_hoc'];

  // Làm sạch dữ liệu số tiền
  $tong_tien = (float)str_replace([',', ' ', '.'], '', $_POST['tong_tien']);
  $da_dong = (float)str_replace([',', ' ', '.'], '', $_POST['da_dong']);

  // Kiểm tra hợp lệ
  if ($tong_tien <= 0) {
    $loi = "Tổng tiền phải lớn hơn 0!";
  } elseif ($da_dong < 0) {
    $loi = "Số tiền đã đóng không được âm!";
  } elseif ($da_dong > $tong_tien) {
    $loi = "Số tiền đã đóng không thể lớn hơn tổng tiền!";
  } else {
    $han_dong = $_POST['han_dong'];

    try {
      $sql = "INSERT INTO hoc_phi (ma_sv, ky_hoc, tong_tien, da_dong, han_dong) 
                    VALUES (?, ?, ?, ?, ?)";
      $stmt = $db->prepare($sql);
      $stmt->execute([$ma_sv, $ky_hoc, $tong_tien, $da_dong, $han_dong]);
      $thongbao = "Thêm học phí thành công!";
    } catch (PDOException $e) {
      $loi = "Lỗi: " . $e->getMessage();
    }
  }
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Thêm học phí</title>
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
      box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
      background: white;
    }

    .required-field::after {
      content: " *";
      color: red;
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
        <h2 class="mb-0"><i class="fas fa-money-bill-wave me-2"></i>Thêm học phí</h2>
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

      <form method="POST" onsubmit="return validateForm()">
        <div class="mb-3">
          <label class="form-label required-field">Sinh viên</label>
          <select class="form-select" name="ma_sv" required>
            <option value="">-- Chọn sinh viên --</option>
            <?php foreach ($ds_sinhvien as $sv) {
              $selected = ($sv["ma_sv"] == $ma_sv) ? "selected" : "";
              echo "<option value='{$sv["ma_sv"]}' $selected>{$sv["ho_ten"]}</option>";
            }
            ?>
          </select>
        </div>

        <div class="mb-3">
          <label for="ky_hoc" class="form-label required-field">Kỳ học</label>
          <input type="text" class="form-control" id="ky_hoc" name="ky_hoc" placeholder="VD: HK1-2023" required>
        </div>

        <div class="mb-3">
          <label class="form-label required-field">Số tiền</label>
          <div class="input-group">
            <span class="input-group-text">Tổng tiền</span>
            <input type="text" class="form-control text-end" name="tong_tien" placeholder="5,000,000" required>
            <span class="input-group-text">VNĐ</span>
          </div>
        </div>

        <div class="mb-3">
          <div class="input-group">
            <span class="input-group-text">Đã đóng</span>
            <input type="text" class="form-control text-end" name="da_dong" placeholder="1,000,000" required>
            <span class="input-group-text">VNĐ</span>
          </div>
        </div>

        <div class="mb-3">
          <label for="han_dong" class="form-label required-field">Hạn đóng</label>
          <input type="date" class="form-control" id="han_dong" name="han_dong" required>
        </div>

        <div class="d-grid gap-2">
          <button type="submit" class="btn btn-primary">
            <i class="fas fa-save me-1"></i> Lưu học phí
          </button>
        </div>
      </form>
    </div>
  </div>

  <!-- Bootstrap 5 JS Bundle with Popper -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

  <script>
    // Hàm định dạng số tiền
    function formatCurrency(input) {
      // Cho phép nhập số bình thường
      input.addEventListener('input', function(e) {
        let value = e.target.value.replace(/[^0-9]/g, '');
        e.target.value = value;
      });

      // Định dạng khi mất focus
      input.addEventListener('blur', function(e) {
        let value = e.target.value.replace(/[^0-9]/g, '');
        if (value !== '') {
          e.target.value = Number(value).toLocaleString('vi-VN');
        }
      });

      // Bỏ định dạng khi focus
      input.addEventListener('focus', function(e) {
        let value = e.target.value.replace(/[^0-9]/g, '');
        e.target.value = value;
      });
    }

    // Áp dụng cho các trường tiền
    formatCurrency(document.querySelector('input[name="tong_tien"]'));
    formatCurrency(document.querySelector('input[name="da_dong"]'));

    // Validate form
    function validateForm() {
      const tongTien = document.querySelector('input[name="tong_tien"]').value.replace(/[^0-9]/g, '');
      const daDong = document.querySelector('input[name="da_dong"]').value.replace(/[^0-9]/g, '');

      if (tongTien === '' || isNaN(tongTien) || parseInt(tongTien) <= 0) {
        alert('Vui lòng nhập tổng tiền hợp lệ (số dương)');
        return false;
      }

      if (daDong === '' || isNaN(daDong) || parseInt(daDong) < 0) {
        alert('Vui lòng nhập số tiền đã đóng hợp lệ (số không âm)');
        return false;
      }

      if (parseInt(daDong) > parseInt(tongTien)) {
        alert('Số tiền đã đóng không được lớn hơn tổng tiền');
        return false;
      }

      // Đảm bảo gửi số nguyên không định dạng
      document.querySelector('input[name="tong_tien"]').value = tongTien;
      document.querySelector('input[name="da_dong"]').value = daDong;

      return true;
    }
  </script>

</html>
