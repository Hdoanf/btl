<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once 'check_login.php';

if (isset($_GET['ma_sv'])) {
    $ma_sv = $_GET['ma_sv'];
    
    try {
        // Bắt đầu transaction
        $db->beginTransaction();
        
        // 1. Kiểm tra tổng nợ
        $sql_kiemtra = "SELECT SUM(tong_tien - da_dong) as tong_no 
                       FROM hoc_phi 
                       WHERE ma_sv = ?";
        $stmt = $db->prepare($sql_kiemtra);
        $stmt->execute([$ma_sv]);
        $tong_no = $stmt->fetchColumn();

        if ($tong_no > 0) {
            $_SESSION['loi'] = "Không thể xóa sinh viên $ma_sv vì còn nợ học phí!";
            $db->rollBack();
            header('Location: index.php');
            exit();
        }

        // 2. Xóa các bản ghi liên quan trong bảng hoc_phi trước (nếu có)
        $sql_xoa_hocphi = "DELETE FROM hoc_phi WHERE ma_sv = ?";
        $db->prepare($sql_xoa_hocphi)->execute([$ma_sv]);
        
        // 3. Xóa sinh viên
        $sql_xoa_sinhvien = "DELETE FROM hoc_vien WHERE ma_sv = ?";
        $db->prepare($sql_xoa_sinhvien)->execute([$ma_sv]);
        
        $db->commit();
        
        $_SESSION['thongbao'] = "Đã xóa sinh viên $ma_sv thành công!";
        
    } catch (PDOException $e) {
        $db->rollBack();
        $_SESSION['loi'] = "Lỗi hệ thống: " . $e->getMessage();
    }
}

header('Location: index.php');
exit();
?>