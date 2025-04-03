<?php
include 'includes/header.php';
include 'includes/db_connect.php';

if(!isset($_GET['id'])) {
    header("Location: students.php");
    exit();
}

$id = $_GET['id'];

// Fetch student data with department name
$sql = "SELECT s.*, n.TenNganh 
        FROM SinhVien s 
        LEFT JOIN NganhHoc n ON s.MaNganh = n.MaNganh 
        WHERE s.MaSV = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $id);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();

if(!$student) {
    header("Location: students.php");
    exit();
}

// Fetch registered courses
$sql = "SELECT hp.* 
        FROM HocPhan hp 
        INNER JOIN ChiTietDangKy ctdk ON hp.MaHP = ctdk.MaHP 
        INNER JOIN DangKy dk ON ctdk.MaDK = dk.MaDK 
        WHERE dk.MaSV = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $id);
$stmt->execute();
$courses = $stmt->get_result();
?>

<div class="container mt-4">
    <h2>Thông tin Chi tiết Sinh viên</h2>
    
    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <?php if($student['Hinh']): ?>
                        <img src="images/<?php echo htmlspecialchars($student['Hinh']); ?>" 
                             alt="Student Photo" class="img-fluid">
                    <?php endif; ?>
                </div>
                <div class="col-md-8">
                    <h4><?php echo htmlspecialchars($student['HoTen']); ?></h4>
                    <p><strong>Mã sinh viên:</strong> <?php echo htmlspecialchars($student['MaSV']); ?></p>
                    <p><strong>Giới tính:</strong> <?php echo htmlspecialchars($student['GioiTinh']); ?></p>
                    <p><strong>Ngày sinh:</strong> <?php echo date('d/m/Y', strtotime($student['NgaySinh'])); ?></p>
                    <p><strong>Ngành học:</strong> <?php echo htmlspecialchars($student['TenNganh']); ?></p>
                </div>
            </div>
            
            <h5 class="mt-4">Các môn học đã đăng ký:</h5>
            <?php if($courses->num_rows > 0): ?>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Mã HP</th>
                            <th>Tên học phần</th>
                            <th>Số tín chỉ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($course = $courses->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($course['MaHP']); ?></td>
                                <td><?php echo htmlspecialchars($course['TenHP']); ?></td>
                                <td><?php echo htmlspecialchars($course['SoTinChi']); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>Chưa đăng ký môn học nào.</p>
            <?php endif; ?>
            
            <a href="students.php" class="btn btn-secondary">Quay lại</a>
            <a href="edit_student.php?id=<?php echo $student['MaSV']; ?>" class="btn btn-warning">Sửa</a>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?> 