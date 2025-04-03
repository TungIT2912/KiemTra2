<?php
session_start();
include 'includes/header.php';
include 'includes/db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

// Initialize shopping cart if it doesn't exist
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = array();
}

// Handle Add to Cart action
if (isset($_POST['add_to_cart'])) {
    $mahp = $_POST['mahp'];
    
    // Check if course is full
    $sql = "SELECT * FROM HocPhan WHERE MaHP = ? AND DaDangKy < SoLuongSV";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $mahp);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 0) {
        $_SESSION['error_message'] = "Học phần đã đủ số lượng sinh viên!";
    } else if (!in_array($mahp, $_SESSION['cart'])) {
        $_SESSION['cart'][] = $mahp;
        $_SESSION['success_message'] = "Đã thêm học phần vào giỏ đăng ký!";
    } else {
        $_SESSION['error_message'] = "Học phần này đã có trong giỏ đăng ký!";
    }
}

// Fetch all courses with available slots
$sql = "SELECT *, (SoLuongSV - DaDangKy) as SoChoConLai FROM HocPhan ORDER BY TenHP";
$result = $conn->query($sql);
?>

<div class="container mt-4">
    <div class="card shadow mb-4 fade-in-up">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-white">Danh sách Học phần</h6>
            <a href="cart.php" class="btn btn-light btn-icon">
                <i class="fas fa-shopping-cart me-2"></i> 
                Giỏ đăng ký (<?php echo count($_SESSION['cart']); ?>)
            </a>
        </div>
        <div class="card-body">
            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php 
                    echo $_SESSION['success_message'];
                    unset($_SESSION['success_message']);
                    ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php 
                    echo $_SESSION['error_message'];
                    unset($_SESSION['error_message']);
                    ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="table-responsive">
                <table class="table table-hover course-table" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Mã HP</th>
                            <th>Tên học phần</th>
                            <th>Số tín chỉ</th>
                            <th>Sĩ số tối đa</th>
                            <th>Đã đăng ký</th>
                            <th>Còn trống</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['MaHP']); ?></td>
                            <td><?php echo htmlspecialchars($row['TenHP']); ?></td>
                            <td class="text-center"><?php echo htmlspecialchars($row['SoTinChi']); ?></td>
                            <td class="text-center"><?php echo htmlspecialchars($row['SoLuongSV']); ?></td>
                            <td class="text-center">
                                <div class="progress" style="height: 20px;">
                                    <div class="progress-bar <?php echo ($row['DaDangKy'] >= $row['SoLuongSV']) ? 'bg-danger' : 'bg-success'; ?>" 
                                         role="progressbar" 
                                         style="width: <?php echo ($row['DaDangKy']/$row['SoLuongSV']*100); ?>%">
                                        <?php echo $row['DaDangKy']; ?>
                                    </div>
                                </div>
                            </td>
                            <td class="text-center">
                                <span class="badge <?php echo ($row['SoChoConLai'] > 0) ? 'bg-success' : 'bg-danger'; ?>">
                                    <?php echo $row['SoChoConLai']; ?> chỗ
                                </span>
                            </td>
                            <td>
                                <form method="POST" class="d-inline">
                                    <input type="hidden" name="mahp" value="<?php echo $row['MaHP']; ?>">
                                    <button type="submit" 
                                            name="add_to_cart" 
                                            class="btn btn-primary btn-sm"
                                            <?php echo (in_array($row['MaHP'], $_SESSION['cart']) || $row['SoChoConLai'] <= 0) ? 'disabled' : ''; ?>>
                                        <i class="fas fa-plus me-1"></i>
                                        <?php 
                                        if (in_array($row['MaHP'], $_SESSION['cart'])) {
                                            echo 'Đã thêm';
                                        } elseif ($row['SoChoConLai'] <= 0) {
                                            echo 'Hết chỗ';
                                        } else {
                                            echo 'Đăng ký';
                                        }
                                        ?>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('.course-table').DataTable({
        pageLength: 4,
        lengthChange: false,
        searching: false,
        info: false,
        language: {
            paginate: {
                first: '<i class="fas fa-angle-double-left"></i>',
                previous: '<i class="fas fa-angle-left"></i>',
                next: '<i class="fas fa-angle-right"></i>',
                last: '<i class="fas fa-angle-double-right"></i>'
            }
        },
        dom: '<"row"<"col-sm-12"tr>><"row"<"col-sm-12"p>>'
    });
});
</script>

<?php include 'includes/footer.php'; ?> 