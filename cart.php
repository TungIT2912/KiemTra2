<?php
session_start();
include 'includes/header.php';
include 'includes/db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

// Handle Remove from Cart
if (isset($_POST['remove_course'])) {
    $mahp = $_POST['mahp'];
    $key = array_search($mahp, $_SESSION['cart']);
    if ($key !== false) {
        unset($_SESSION['cart'][$key]);
        $_SESSION['cart'] = array_values($_SESSION['cart']); // Reindex array
    }
}

// Handle Clear Cart
if (isset($_POST['clear_cart'])) {
    $_SESSION['cart'] = array();
}

// Fetch courses in cart
$courses = array();
$total_credits = 0;
if (!empty($_SESSION['cart'])) {
    $placeholders = str_repeat('?,', count($_SESSION['cart']) - 1) . '?';
    $sql = "SELECT * FROM HocPhan WHERE MaHP IN ($placeholders)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param(str_repeat('s', count($_SESSION['cart'])), ...$_SESSION['cart']);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $courses[] = $row;
        $total_credits += $row['SoTinChi'];
    }
}

// Fetch student information
$masv = $_SESSION['user']['username'];
$sql = "SELECT sv.*, nh.TenNganh 
        FROM SinhVien sv 
        LEFT JOIN NganhHoc nh ON sv.MaNganh = nh.MaNganh 
        WHERE sv.MaSV = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $masv);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();

// Handle Final Registration
if (isset($_POST['confirm_registration'])) {
    $conn->begin_transaction();
    
    try {
        // Create new registration
        $masv = $_SESSION['user']['username'];
        $today = date('Y-m-d');
        
        $sql = "INSERT INTO DangKy (NgayDK, MaSV) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $today, $masv);
        $stmt->execute();
        
        $madk = $conn->insert_id;
        
        // Insert course details and update student count
        $sql_detail = "INSERT INTO ChiTietDangKy (MaDK, MaHP) VALUES (?, ?)";
        $sql_update = "UPDATE HocPhan SET DaDangKy = DaDangKy + 1 WHERE MaHP = ?";
        
        $stmt_detail = $conn->prepare($sql_detail);
        $stmt_update = $conn->prepare($sql_update);
        
        foreach ($_SESSION['cart'] as $mahp) {
            $stmt_detail->bind_param("is", $madk, $mahp);
            $stmt_detail->execute();
            
            $stmt_update->bind_param("s", $mahp);
            $stmt_update->execute();
        }
        
        $conn->commit();
        $_SESSION['cart'] = array();
        $_SESSION['success_message'] = "Đăng ký học phần thành công!";
        header("Location: course_list.php");
        exit();
        
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error_message'] = "Có lỗi xảy ra khi đăng ký học phần: " . $e->getMessage();
    }
}
?>

<div class="container mt-4">
    <div class="card shadow mb-4 fade-in-up">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-white">Giỏ đăng ký học phần</h6>
            <a href="course_list.php" class="btn btn-light btn-icon">
                <i class="fas fa-arrow-left me-2"></i> Quay lại danh sách
            </a>
        </div>
        <div class="card-body">
            <?php if (empty($courses)): ?>
                <div class="alert alert-info">
                    Giỏ đăng ký trống. <a href="course_list.php">Quay lại danh sách học phần</a>
                </div>
            <?php else: ?>
                <?php if (!isset($_POST['show_confirmation'])): ?>
                    <!-- Cart View -->
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Mã HP</th>
                                    <th>Tên học phần</th>
                                    <th>Số tín chỉ</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($courses as $course): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($course['MaHP']); ?></td>
                                    <td><?php echo htmlspecialchars($course['TenHP']); ?></td>
                                    <td><?php echo htmlspecialchars($course['SoTinChi']); ?></td>
                                    <td>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="mahp" value="<?php echo $course['MaHP']; ?>">
                                            <button type="submit" 
                                                    name="remove_course" 
                                                    class="btn btn-danger btn-sm">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <tr class="table-info">
                                    <td colspan="2" class="text-end"><strong>Tổng số tín chỉ:</strong></td>
                                    <td colspan="2"><strong><?php echo $total_credits; ?></strong></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">
                        <form method="POST" class="d-inline">
                            <button type="submit" 
                                    name="show_confirmation" 
                                    class="btn btn-primary">
                                <i class="fas fa-check me-2"></i>Đăng ký học phần
                            </button>
                        </form>
                        
                        <form method="POST" class="d-inline">
                            <button type="submit" 
                                    name="clear_cart" 
                                    class="btn btn-warning"
                                    onclick="return confirm('Bạn có chắc muốn xóa tất cả học phần khỏi giỏ?');">
                                <i class="fas fa-trash me-2"></i>Xóa tất cả
                            </button>
                        </form>
                    </div>

                <?php else: ?>
                    <!-- Registration Confirmation View -->
                    <div class="confirmation-details">
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header bg-primary text-white">
                                        <h6 class="mb-0">Thông tin sinh viên</h6>
                                    </div>
                                    <div class="card-body">
                                        <table class="table table-borderless">
                                            <tr>
                                                <th>Mã sinh viên:</th>
                                                <td><?php echo htmlspecialchars($student['MaSV']); ?></td>
                                            </tr>
                                            <tr>
                                                <th>Họ tên:</th>
                                                <td><?php echo htmlspecialchars($student['HoTen']); ?></td>
                                            </tr>
                                            <tr>
                                                <th>Ngành học:</th>
                                                <td><?php echo htmlspecialchars($student['TenNganh']); ?></td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header bg-info text-white">
                                        <h6 class="mb-0">Thông tin đăng ký</h6>
                                    </div>
                                    <div class="card-body">
                                        <table class="table table-borderless">
                                            <tr>
                                                <th>Ngày đăng ký:</th>
                                                <td><?php echo date('d/m/Y'); ?></td>
                                            </tr>
                                            <tr>
                                                <th>Số học phần:</th>
                                                <td><?php echo count($courses); ?></td>
                                            </tr>
                                            <tr>
                                                <th>Tổng số tín chỉ:</th>
                                                <td><?php echo $total_credits; ?></td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card mb-4">
                            <div class="card-header bg-success text-white">
                                <h6 class="mb-0">Chi tiết học phần đăng ký</h6>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>STT</th>
                                                <th>Mã HP</th>
                                                <th>Tên học phần</th>
                                                <th>Số tín chỉ</th>
                                                <th>Sĩ số hiện tại</th>
                                                <th>Còn trống</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php 
                                            $stt = 1;
                                            foreach ($courses as $course): 
                                                $available = $course['SoLuongSV'] - $course['DaDangKy'];
                                            ?>
                                            <tr>
                                                <td><?php echo $stt++; ?></td>
                                                <td><?php echo htmlspecialchars($course['MaHP']); ?></td>
                                                <td><?php echo htmlspecialchars($course['TenHP']); ?></td>
                                                <td><?php echo htmlspecialchars($course['SoTinChi']); ?></td>
                                                <td><?php echo $course['DaDangKy']; ?>/<?php echo $course['SoLuongSV']; ?></td>
                                                <td>
                                                    <span class="badge <?php echo $available > 0 ? 'bg-success' : 'bg-danger'; ?>">
                                                        <?php echo $available; ?> chỗ
                                                    </span>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="text-center">
                            <form method="POST" class="d-inline">
                                <button type="submit" 
                                        name="confirm_registration" 
                                        class="btn btn-primary btn-lg">
                                    <i class="fas fa-check-circle me-2"></i>Xác nhận đăng ký
                                </button>
                            </form>
                            
                            <a href="cart.php" class="btn btn-secondary btn-lg">
                                <i class="fas fa-times-circle me-2"></i>Hủy
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.confirmation-details .card {
    border: none;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s ease;
}

.confirmation-details .card:hover {
    transform: translateY(-5px);
}

.confirmation-details .table {
    margin-bottom: 0;
}

.confirmation-details .badge {
    padding: 8px 12px;
    font-size: 0.9rem;
}

.btn-lg {
    padding: 12px 24px;
    font-size: 1.1rem;
}

.table-hover tbody tr:hover {
    background-color: rgba(127, 0, 255, 0.05);
    transform: scale(1.01);
    transition: all 0.3s ease;
}
</style>

<?php include 'includes/footer.php'; ?> 