<?php
require_once 'db.php';

/**
 * Gửi email thông báo nợ học phí
 */
function gui_email_thongbao($email, $ten, $noi_dung) {
    require_once 'PHPMailer/PHPMailerAutoload.php';
    
    $mail = new PHPMailer;
    $mail->isSMTP();
    $mail->Host = SMTP_HOST;
    $mail->Port = SMTP_PORT;
    $mail->SMTPAuth = true;
    $mail->Username = SMTP_USER;
    $mail->Password = SMTP_PASS;
    $mail->SMTPSecure = 'tls';
    
    $mail->setFrom(EMAIL_FROM, EMAIL_NAME);
    $mail->addAddress($email, $ten);
    $mail->isHTML(true);
    
    $mail->Subject = 'Thông báo nợ học phí';
    $mail->Body = $noi_dung;
    
    return $mail->send();
}

/**
 * Lấy danh sách sinh viên nợ học phí
 */
function lay_ds_sinhvien_no() {
    global $db;
    
    $sql = "SELECT hv.*, 
            SUM(hp.tong_tien) as tong_no,
            SUM(hp.da_dong) as da_dong,
            (SUM(hp.tong_tien) - SUM(hp.da_dong)) as con_no
            FROM hoc_vien hv
            JOIN hoc_phi hp ON hv.ma_sv = hp.ma_sv
            WHERE hp.han_dong < CURDATE()
            GROUP BY hv.ma_sv
            HAVING con_no > 0";
    
    $stmt = $db->prepare($sql);
    $stmt->execute();
    
    return $stmt->fetchAll();
}

/**
 * Gửi thông báo cho 1 sinh viên
 */
function gui_thongbao_cho_sv($ma_sv) {
    global $db;
    
    // Lấy thông tin sinh viên
    $sql = "SELECT hv.*, 
            SUM(hp.tong_tien) as tong_no,
            SUM(hp.da_dong) as da_dong,
            (SUM(hp.tong_tien) - SUM(hp.da_dong)) as con_no
            FROM hoc_vien hv
            JOIN hoc_phi hp ON hv.ma_sv = hp.ma_sv
            WHERE hv.ma_sv = ? AND hp.han_dong < CURDATE()
            GROUP BY hv.ma_sv";
    
    $stmt = $db->prepare($sql);
    $stmt->execute([$ma_sv]);
    $sv = $stmt->fetch();
    
    if ($sv && $sv['con_no'] > 0) {
        $noi_dung = "<h3>THÔNG BÁO NỢ HỌC PHÍ</h3>
                    <p>Kính gửi: {$sv['ho_ten']} - {$sv['ma_sv']}</p>
                    <p>Bạn đang có khoản nợ học phí như sau:</p>
                    <table border='1' cellpadding='5'>
                        <tr>
                            <th>Tổng nợ</th>
                            <th>Đã thanh toán</th>
                            <th>Còn nợ</th>
                        </tr>
                        <tr>
                            <td>".number_format($sv['tong_no'])." VNĐ</td>
                            <td>".number_format($sv['da_dong'])." VNĐ</td>
                            <td style='color:red'>".number_format($sv['con_no'])." VNĐ</td>
                        </tr>
                    </table>
                    <p>Vui lòng thanh toán trước ngày ".date('d/m/Y', strtotime('+7 days'))."</p>";
        
        return gui_email_thongbao($sv['email'], $sv['ho_ten'], $noi_dung);
    }
    
    return false;
}
?>
