<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

$mail = new PHPMailer(true);

try {
    // Cấu hình SMTP
    $mail->SMTPDebug = 0; // Tắt debug khi chạy thật
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'bestbubuom@gmail.com';
    $mail->Password   = 'zdsm szsu pqcw nvux';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;
    $mail->CharSet    = 'UTF-8';

    // Thông tin sinh viên nợ học phí (thay bằng dữ liệu thực từ database)
    $student_name = "Nguyễn Văn A";
    $student_code = "SV2024001";
    $student_email = "doanoidoioi@gmail.com";
    
    // Thông tin học phí (ví dụ)
    $ky_hoc = "Học kỳ 1 - Năm học 2024";
    $tong_tien = 5000000;
    $da_dong = 3000000;
    $con_thieu = $tong_tien - $da_dong;
    $han_dong = "15/04/2024";
    
    // Người gửi/nhận
    $mail->setFrom('bestbubuom@gmail.com', 'Phòng Đào Tạo - Trường ABC');
    $mail->addAddress($student_email, $student_name);
    $mail->addReplyTo('daotao@truong.edu.vn', 'Phòng Đào Tạo');
    
    // Nội dung email
    $mail->Subject = 'THÔNG BÁO NỢ HỌC PHÍ - ' . $student_code;
    
    $mail->Body = '
    <!DOCTYPE html>
    <html lang="vi">
    <head>
        <meta charset="UTF-8">
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 5px; }
            .header { color: #d9534f; border-bottom: 1px solid #eee; padding-bottom: 10px; }
            .content { margin: 20px 0; }
            table { width: 100%; border-collapse: collapse; margin: 15px 0; }
            th, td { padding: 10px; border: 1px solid #ddd; text-align: left; }
            th { background-color: #f5f5f5; }
            .warning { color: #d9534f; font-weight: bold; }
            .footer { margin-top: 20px; font-size: 12px; color: #777; }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <h2>THÔNG BÁO NỢ HỌC PHÍ</h2>
            </div>
            
            <div class="content">
                <p>Kính gửi: <strong>' . $student_name . '</strong> (Mã SV: ' . $student_code . ')</p>
                
                <p>Hệ thống ghi nhận bạn chưa hoàn thành thanh toán học phí sau:</p>
                
                <table>
                    <tr>
                        <th>Kỳ học</th>
                        <td>' . $ky_hoc . '</td>
                    </tr>
                    <tr>
                        <th>Tổng học phí</th>
                        <td>' . number_format($tong_tien) . ' VNĐ</td>
                    </tr>
                    <tr>
                        <th>Đã thanh toán</th>
                        <td>' . number_format($da_dong) . ' VNĐ</td>
                    </tr>
                    <tr class="warning">
                        <th>Số tiền còn thiếu</th>
                        <td>' . number_format($con_thieu) . ' VNĐ</td>
                    </tr>
                    <tr>
                        <th>Hạn thanh toán</th>
                        <td>' . $han_dong . '</td>
                    </tr>
                </table>
                
                <p class="warning">LƯU Ý QUAN TRỌNG: Nếu không thanh toán trước ngày ' . $han_dong . ', bạn sẽ bị <strong>cấm thi</strong> và <strong>không được xét điểm</strong> trong kỳ học này.</p>
                
                <p>Vui lòng thanh toán sớm qua một trong các cách sau:</p>
                <ol>
                    <li>Chuyển khoản qua ngân hàng (xem chi tiết bên dưới)</li>
                    <li>Nộp trực tiếp tại Phòng Tài chính Kế toán</li>
                </ol>
            </div>
            
            <div class="footer">
                <p>Đây là email tự động, vui lòng không trả lời.</p>
                <p>Mọi thắc mắc xin liên hệ Phòng Đào Tạo - ĐT: 0243.xxx.xxx</p>
            </div>
        </div>
    </body>
    </html>';

    // Phiên bản text thuần
    $mail->AltBody = "THONG BAO NO HOC PHI\n" .
                     "Kinh gui: $student_name (Ma SV: $student_code)\n\n" .
                     "Ky hoc: $ky_hoc\n" .
                     "Tong hoc phi: " . number_format($tong_tien) . " VND\n" .
                     "Da thanh toan: " . number_format($da_dong) . " VND\n" .
                     "Con thieu: " . number_format($con_thieu) . " VND\n" .
                     "Han thanh toan: $han_dong\n\n" .
                     "CANH BAO: Neu khong thanh toan truoc ngay $han_dong, ban se bi CAM THI va KHONG duoc xet diem.\n\n" .
                     "Lien he Phong Dao Tao neu co thac mac.";

    $mail->send();
    echo 'Email thông báo nợ học phí đã được gửi thành công!';
} catch (Exception $e) {
    echo "Gửi email thất bại. Lỗi: {$mail->ErrorInfo}";
}
