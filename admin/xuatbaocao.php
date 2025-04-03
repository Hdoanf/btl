<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once __DIR__ . '/../vendor/autoload.php';


use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Lấy tất cả sinh viên có nợ (bao gồm chưa nộp và nộp thiếu)
$sql = "SELECT 
            hv.ma_sv, 
            hv.ho_ten, 
            hv.email,
            SUM(hp.tong_tien) as tong_phai_nop,
            SUM(hp.da_dong) as da_nop,
            (SUM(hp.tong_tien) - SUM(hp.da_dong)) as con_no,
            CASE 
                WHEN SUM(hp.da_dong) = 0 THEN 'Chưa nộp'
                WHEN (SUM(hp.tong_tien) - SUM(hp.da_dong)) > 0 THEN 'Nộp thiếu'
                ELSE 'Đã nộp đủ'
            END as trang_thai
        FROM hoc_vien hv
        LEFT JOIN hoc_phi hp ON hv.ma_sv = hp.ma_sv
        GROUP BY hv.ma_sv
        HAVING con_no > 0 OR tong_phai_nop IS NULL
        ORDER BY con_no DESC";

$stmt = $db->prepare($sql);
$stmt->execute();
$ds_sinhvien = $stmt->fetchAll();

// Tạo file Excel
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Tiêu đề báo cáo
$sheet->setCellValue('A1', 'BÁO CÁO TỔNG HỢP TÌNH TRẠNG NỘP HỌC PHÍ');
$sheet->mergeCells('A1:G1');
$sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);

// Header bảng
$headers = ['STT', 'Mã SV', 'Họ tên', 'Email', 'Tổng phải nộp', 'Đã nộp', 'Còn nợ', 'Trạng thái'];
$sheet->fromArray($headers, NULL, 'A2');
$sheet->getStyle('A2:G2')->getFont()->setBold(true);

// Đổ dữ liệu
$row = 3;
foreach ($ds_sinhvien as $index => $sv) {
    $sheet->setCellValue('A'.$row, $index + 1);
    $sheet->setCellValue('B'.$row, $sv['ma_sv']);
    $sheet->setCellValue('C'.$row, $sv['ho_ten']);
    $sheet->setCellValue('D'.$row, $sv['email']);
    $sheet->setCellValue('E'.$row, number_format($sv['tong_phai_nop'] ?? 0));
    $sheet->setCellValue('F'.$row, number_format($sv['da_nop'] ?? 0));
    $sheet->setCellValue('G'.$row, number_format($sv['con_no'] ?? 0));
    
    // Trạng thái với màu sắc
    $sheet->setCellValue('H'.$row, $sv['trang_thai']);
    
    // Tô màu ô trạng thái
    $color = ($sv['trang_thai'] == 'Chưa nộp') ? 'FF0000' : 'FFA500'; // Đỏ hoặc cam
    $sheet->getStyle('H'.$row)
          ->getFill()
          ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
          ->getStartColor()
          ->setARGB($color);
    
    $row++;
}

// Định dạng cột số tiền
$sheet->getStyle('E3:G'.$row)
      ->getNumberFormat()
      ->setFormatCode('#,##0');

// Tự động điều chỉnh độ rộng cột
foreach(range('A','H') as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

// Xuất file
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="bao_cao_hoc_phi_'.date('d-m-Y').'.xlsx"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;