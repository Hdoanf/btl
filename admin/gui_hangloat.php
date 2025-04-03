<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "quanly_hocphi";
$conn = new mysqli($servername, $username, $password, $dbname);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../includes/PHPMailer/src/Exception.php';
require '../includes/PHPMailer/src/PHPMailer.php';
require '../includes/PHPMailer/src/SMTP.php';

// Lấy danh sách tất cả sinh viên nợ học phí
$sql = "SELECT hv.ma_sv, hv.ho_ten, hv.email, hp.ky_hoc, hp.tong_tien, hp.da_dong, hp.han_dong 
        FROM hoc_vien hv 
        JOIN hoc_phi hp ON hv.ma_sv = hp.ma_sv 
        WHERE hp.da_dong < hp.tong_tien";

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $mail = new PHPMailer(true);
        try {
            // Cấu hình SMTP
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'bestbubuom@gmail.com';
            $mail->Password   = 'zdsm szsu pqcw nvux';  // Thay bằng mật khẩu ứng dụng của Gmail
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;
            $mail->CharSet    = 'UTF-8';

            // Thông tin sinh viên
            $student_name = $row["ho_ten"];
            $student_code = $row["ma_sv"];
            $student_email = $row["email"];
            $ky_hoc = $row["ky_hoc"];
            $tong_tien = $row["tong_tien"];
            $da_dong = $row["da_dong"];
            $con_thieu = $tong_tien - $da_dong;
            $han_dong = $row["han_dong"];

            // Thiết lập email
            $mail->setFrom('bestbubuom@gmail.com', 'Phòng Đào Tạo');
            $mail->addAddress('doanoidoioi@gmail.com', $student_name);
            $mail->addReplyTo('daotao@truong.edu.vn', 'Phòng Đào Tạo');
            $mail->Subject = 'THÔNG BÁO NỢ HỌC PHÍ - ' . $student_code;

            // Nội dung email (HTML)
            $mail->isHTML(true);
$mail->Body = '
    <!DOCTYPE html>
    <html lang="vi">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Thông báo nợ học phí</title>
        <style>
            body { font-family: Arial, sans-serif; background-color: #f4f4f4; margin: 0; padding: 0; }
            .container { max-width: 600px; margin: 20px auto; background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1); }
            .header { text-align: center; padding-bottom: 15px; border-bottom: 2px solid #eee; }
            .header img { max-width: 100px; }
            .header h2 { color: #d9534f; }
            .content { margin: 20px 0; line-height: 1.6; }
            table { width: 100%; border-collapse: collapse; margin-top: 15px; }
            th, td { padding: 10px; border: 1px solid #ddd; text-align: left; }
            th { background-color: #f8f8f8; font-weight: bold; }
            .warning { background-color: #fff3cd; color: #856404; font-weight: bold; }
            .footer { text-align: center; margin-top: 20px; font-size: 12px; color: #777; padding-top: 10px; border-top: 1px solid #eee; }
            .btn { display: inline-block; background: #d9534f; color: #fff; padding: 10px 15px; text-decoration: none; border-radius: 5px; margin-top: 15px; }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <img src="https://i.pinimg.com/736x/5f/10/b4/5f10b4869be899fdb8793c09497343a3.jpg" alt="Logo">
                <h2>THÔNG BÁO NỢ HỌC PHÍ</h2>
            </div>
            
            <div class="content">
                <p><strong>Kính gửi:</strong> <span style="color:#d9534f;">' . $student_name . '</span> (Mã SV: <strong>' . $student_code . '</strong>)</p>
                
                <p>Hệ thống ghi nhận bạn chưa hoàn thành thanh toán học phí cho kỳ học sau:</p>
                
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
                        <td style="color:#d9534f; font-weight:bold;">' . $han_dong . '</td>
                    </tr>
                </table>
                
                <p class="warning" style="color: #d9534f; font-weight: bold;">
                    ⚠️ LƯU Ý QUAN TRỌNG: Nếu không thanh toán trước ngày <strong>' . $han_dong . '</strong>, bạn sẽ bị <strong>CẤM THI</strong> và <strong>KHÔNG ĐƯỢC XÉT ĐIỂM</strong>.
                </p>
                
                <p> 🏛️Vui lòng thanh toán sớm tại phòng hành chính</p>
            </div>
            
            <div class="footer">
                <p>📩 Đây là email tự động, vui lòng không trả lời.</p>
                <p>☎️ Mọi thắc mắc xin liên hệ Phòng Đào Tạo - ĐT: 0243.xxx.xxx</p>
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

            // Gửi email
            $mail->send();
            echo "✅ Đã gửi email cho $student_name ($student_email) thành công!<br>";
        } catch (Exception $e) {
            echo "❌ Gửi email cho $student_name thất bại. Lỗi: {$mail->ErrorInfo}<br>";
        }
    }
} else {
    echo "Không có sinh viên nào đang nợ học phí.";
}

$conn->close();
?>

