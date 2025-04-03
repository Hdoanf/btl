<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once 'check_login.php';

// Xử lý xóa nợ học phí nếu có yêu cầu
if (isset($_GET['xoa_no']) && isset($_GET['ma_sv']) && isset($_GET['ky_hoc'])) {
    $ma_sv = $_GET['ma_sv'];
    $ky_hoc = $_GET['ky_hoc'];

    try {
        $sql = "DELETE FROM hoc_phi WHERE ma_sv = ? AND ky_hoc = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$ma_sv, $ky_hoc]);

        $_SESSION['thongbao'] = "Đã xóa nợ học phí học kỳ $ky_hoc của sinh viên $ma_sv";
        header('Location: index.php');
        exit();
    } catch (PDOException $e) {
        $_SESSION['loi'] = "Lỗi khi xóa: " . $e->getMessage();
        header('Location: index.php');
        exit();
    }
}


// Thay đổi câu SQL trong phần lấy dữ liệu
// Lấy tham số tìm kiếm từ URL (sử dụng null coalescing để tránh lỗi)
// Lấy tham số tìm kiếm từ URL
$search_ma_sv = $_GET['search_ma_sv'] ?? '';
$search_ho_ten = $_GET['search_ho_ten'] ?? '';
$search_ky_hoc = $_GET['search_ky_hoc'] ?? '';

// SQL gốc có điều kiện tìm kiếm
$sql = "SELECT 
    hv.ma_sv, 
    hv.ho_ten,
    hv.email,
    hp.ky_hoc,
    SUM(hp.tong_tien) as tong_tien,
    SUM(hp.da_dong) as da_dong,
    SUM(hp.tong_tien - hp.da_dong) as con_no,
    MAX(hp.han_dong) as han_dong
FROM hoc_vien hv
LEFT JOIN hoc_phi hp ON hv.ma_sv = hp.ma_sv";

// Thêm điều kiện WHERE nếu có tham số tìm kiếm
$whereConditions = [];
$params = [];

if (!empty($search_ma_sv)) {
    $whereConditions[] = "hv.ma_sv LIKE ?";
    $params[] = "%$search_ma_sv%";
}

if (!empty($search_ho_ten)) {
    $whereConditions[] = "hv.ho_ten LIKE ?";
    $params[] = "%$search_ho_ten%";
}

if (!empty($search_ky_hoc)) {
    $whereConditions[] = "hp.ky_hoc LIKE ?";
    $params[] = "%$search_ky_hoc%";
}

// Kết hợp điều kiện WHERE vào SQL gốc
if (!empty($whereConditions)) {
    $sql .= " WHERE " . implode(" AND ", $whereConditions);
}

// Phần GROUP BY và ORDER BY giữ nguyên
$sql .= " GROUP BY hv.ma_sv, hv.ho_ten, hv.email, hp.ky_hoc
          ORDER BY hv.ho_ten, hp.ky_hoc";

// Thực thi câu lệnh
$stmt = $db->prepare($sql);
$stmt->execute($params);
$ds_hoc_phi = $stmt->fetchAll();
;

// Nhóm dữ liệu theo sinh viên
$ds_sinhvien_no = [];
foreach ($ds_hoc_phi as $hp) {
    $ky_hoc = isset($hp['ky_hoc']) ? $hp['ky_hoc'] : 'Không xác định';

    $ds_sinhvien_no[$hp['ma_sv']]['info'] = [
        'ma_sv' => $hp['ma_sv'],
        'ho_ten' => $hp['ho_ten'],
        'email' => $hp['email']
    ];
    $ds_sinhvien_no[$hp['ma_sv']]['hoc_ky'][$ky_hoc] = [
        'tong_tien' => $hp['tong_tien'],
        'da_dong' => $hp['da_dong'],
        'con_no' => $hp['con_no'],
        'han_dong' => $hp['han_dong']
    ];
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Quản lý nợ học phí</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            padding-top: 20px;
        }

        .card {
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .table-responsive {
            margin-top: 20px;
        }

        .table th {
            background-color: #f1f5fd;
            color: #495057;
            font-weight: 600;
        }

        .text-danger {
            color: #dc3545 !important;
        }

        .text-success {
            color: #28a745 !important;
        }

        .action-btns .btn {
            margin-right: 5px;
            margin-bottom: 5px;
        }

        .header-actions {
            margin-bottom: 20px;
        }

        .badge {
            font-size: 0.85em;
        }

        .btn-danger {
            color: white;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <h3 class="mb-0"><i class="fas fa-graduation-cap me-2"></i>Quản lý nợ học phí</h3>
                    <div>
                        <span class="me-3">Xin chào, <?= $_SESSION['username'] ?></span>
                        <a href="logout.php" class="btn btn-light btn-sm">
                            <i class="fas fa-sign-out-alt me-1"></i> Đăng xuất
                        </a>
                    </div>
                </div>
            </div>

            <div class="card-body">
                <?php if (isset($_SESSION['thongbao'])): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <?= $_SESSION['thongbao'];
                        unset($_SESSION['thongbao']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if (isset($_SESSION['loi'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <?= $_SESSION['loi'];
                        unset($_SESSION['loi']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <div class="header-actions">
                    <div class="d-flex flex-wrap gap-2">
                        <a href="them_hocvien.php" class="btn btn-primary">
                            <i class="fas fa-user-plus me-1"></i> Thêm sinh viên
                        </a>
                        <a href="them_hocphi.php" class="btn btn-primary">
                            <i class="fas fa-money-bill-wave me-1"></i> Thêm học phí
                        </a>
                        <form action="gui_hangloat.php" method="post" class="d-inline">
                            <button type="submit" name="gui_hanh_loat" class="btn btn-warning">
                                <i class="fas fa-envelope me-1"></i> Gửi thông báo
                            </button>
                        </form>
                        <div class="mb-3">
                            <a href="xuatbaocao.php" class="btn btn-danger">
                                <i class="fas fa-file-excel me-1"></i> Xuất báo cáo
                            </a>
                        </div>
                    </div>
                </div>
                <!-- Form tìm kiếm -->
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title"><i class="fas fa-search me-2"></i>Tìm kiếm nợ học phí</h5>
                        <form method="get" action="">
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label for="search_ma_sv" class="form-label">Mã sinh viên</label>
                                    <input type="text" class="form-control" id="search_ma_sv" name="search_ma_sv"
                                        value="<?= htmlspecialchars($search_ma_sv) ?>" placeholder="Nhập mã SV...">
                                </div>
                                <div class="col-md-4">
                                    <label for="search_ho_ten" class="form-label">Tên sinh viên</label>
                                    <input type="text" class="form-control" id="search_ho_ten" name="search_ho_ten"
                                        value="<?= htmlspecialchars($search_ho_ten) ?>" placeholder="Nhập tên...">
                                </div>
                                <div class="col-md-3">
                                    <label for="search_ky_hoc" class="form-label">Học kỳ</label>
                                    <input type="text" class="form-control" id="search_ky_hoc" name="search_ky_hoc"
                                        value="<?= htmlspecialchars($search_ky_hoc) ?>" placeholder="VD: HK1-2023">
                                </div>
                                <div class="col-md-1 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="fas fa-search"></i> Tìm
                                    </button>
                                </div>
                                <div class="col-md-1 d-flex align-items-end">
                                    <a href="index.php" class="btn btn-outline-secondary ms-2" title="Xóa tìm kiếm">
                                        <i class="fas fa-times"></i>
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <h4 class="mb-3"><i class="fas fa-list me-2"></i>Danh sách nợ học phí theo học kỳ</h4>

                <div class="table-responsive">
                    <table class="table table-striped table-hover table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>Mã SV</th>
                                <th>Họ tên</th>
                                <th>Học kỳ</th>
                                <th class="text-end">Tổng tiền</th>
                                <th class="text-end">Đã trả</th>
                                <th class="text-end">Còn nợ</th>
                                <th>Hạn đóng</th>
                                <th>Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($ds_sinhvien_no as $ma_sv => $sinh_vien): ?>
                                <?php $first_row = true; ?>
                                <?php foreach ($sinh_vien['hoc_ky'] as $ky_hoc => $hoc_phi): ?>
                                    <tr>
                                        <?php if ($first_row): ?>
                                            <td rowspan="<?= count($sinh_vien['hoc_ky']) ?>"><?= $sinh_vien['info']['ma_sv'] ?></td>
                                            <td rowspan="<?= count($sinh_vien['hoc_ky']) ?>"><?= $sinh_vien['info']['ho_ten'] ?>
                                            </td>
                                            <?php $first_row = false; ?>
                                        <?php endif; ?>
                                        <td><?= $ky_hoc ?></td>
                                        <td class="text-end"><?= number_format($hoc_phi['tong_tien']) ?> VNĐ</td>
                                        <td class="text-end"><?= number_format($hoc_phi['da_dong']) ?> VNĐ</td>
                                        <td
                                            class="text-end fw-bold <?= ($hoc_phi['con_no'] > 0) ? 'text-danger' : 'text-success' ?>">
                                            <?= number_format($hoc_phi['con_no']) ?> VNĐ
                                        </td>
                                        <td>
                                            <?= ($hoc_phi['con_no'] == 0) ? 'Không có' : date('d/m/Y', strtotime($hoc_phi['han_dong'])) ?>
                                        </td>

                                        <td class="action-btns">
                                            <a href="them_hocphi.php?ma_sv=<?= $sinh_vien['info']['ma_sv'] ?>&ky_hoc=<?= urlencode($ky_hoc) ?>"
                                                class="btn btn-sm btn-primary" title="Thêm thanh toán">
                                                <i class="fas fa-plus-circle"></i>
                                            </a>
                                            <a href="gui_thongbao.php?ma_sv=<?= $sinh_vien['info']['ma_sv'] ?>&ky_hoc=<?= urlencode($ky_hoc) ?>"
                                                class="btn btn-sm btn-warning" title="Gửi thông báo">
                                                <i class="fas fa-bell"></i>
                                            </a>
                                            <a href="index.php?xoa_no=1&ma_sv=<?= $sinh_vien['info']['ma_sv'] ?>&ky_hoc=<?= urlencode($ky_hoc) ?>"
                                                class="btn btn-sm btn-danger" title="Xóa nợ"
                                                onclick="return confirm('Bạn có chắc muốn xóa nợ học kỳ <?= $ky_hoc ?> của sinh viên <?= $sinh_vien['info']['ma_sv'] ?>?')">
                                                <i class="fas fa-trash-alt"></i>
                                            </a>
                                            <a href="sua_hocvien.php?ma_sv=<?= $sinh_vien['info']['ma_sv'] ?>"
                                                class="btn btn-sm btn-info ms-2" title="Sửa thông tin">
                                                <i class="fas fa-user-edit"></i>
                                            </a> <a
                                                href="sua_hocphi.php?ma_sv=<?= $sinh_vien['info']['ma_sv'] ?>&ky_hoc=<?= urlencode($ky_hoc) ?>"
                                                class="btn btn-sm btn-info" title="Sửa học phí">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>