<?php
include 'includes/header.php';
include 'includes/db_connect.php';

if(!isset($_GET['id'])) {
    header("Location: students.php");
    exit();
}

$id = $_GET['id'];

// Fetch student data
$sql = "SELECT * FROM SinhVien WHERE MaSV = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $id);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();

if(!$student) {
    header("Location: students.php");
    exit();
}

// Fetch all departments
$sql = "SELECT * FROM NganhHoc ORDER BY TenNganh";
$nganh_result = $conn->query($sql);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $hoten = $_POST['hoten'];
    $gioitinh = $_POST['gioitinh'];
    $ngaysinh = $_POST['ngaysinh'];
    $manganh = $_POST['manganh'];
    
    $hinh = $student['Hinh']; // Keep existing image by default
    
    // Handle new image upload
    if(isset($_FILES['hinh']) && $_FILES['hinh']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['hinh']['name'];
        $filetype = pathinfo($filename, PATHINFO_EXTENSION);
        
        if(in_array(strtolower($filetype), $allowed)) {
            $newname = uniqid() . '.' . $filetype;
            if(move_uploaded_file($_FILES['hinh']['tmp_name'], 'images/' . $newname)) {
                // Delete old image if exists
                if($student['Hinh'] && file_exists('images/' . $student['Hinh'])) {
                    unlink('images/' . $student['Hinh']);
                }
                $hinh = $newname;
            }
        }
    }
    
    $sql = "UPDATE SinhVien SET HoTen=?, GioiTinh=?, NgaySinh=?, Hinh=?, MaNganh=? WHERE MaSV=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssss", $hoten, $gioitinh, $ngaysinh, $hinh, $manganh, $id);
    
    if($stmt->execute()) {
        header("Location: students.php");
        exit();
    }
}
?>

<div class="container mt-4">
    <h2>Sửa thông tin Sinh viên</h2>
    <form method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label>Mã sinh viên:</label>
            <input type="text" class="form-control" value="<?php echo htmlspecialchars($student['MaSV']); ?>" readonly>
        </div>
        
        <div class="form-group">
            <label>Họ tên:</label>
            <input type="text" name="hoten" class="form-control" 
                   value="<?php echo htmlspecialchars($student['HoTen']); ?>" required>
        </div>
        
        <div class="form-group">
            <label>Giới tính:</label>
            <select name="gioitinh" class="form-control">
                <option value="Nam" <?php echo $student['GioiTinh'] == 'Nam' ? 'selected' : ''; ?>>Nam</option>
                <option value="Nữ" <?php echo $student['GioiTinh'] == 'Nữ' ? 'selected' : ''; ?>>Nữ</option>
            </select>
        </div>
        
        <div class="form-group">
            <label>Ngày sinh:</label>
            <input type="date" name="ngaysinh" class="form-control" 
                   value="<?php echo $student['NgaySinh']; ?>" required>
        </div>
        
        <div class="form-group">
            <label>Hình ảnh hiện tại:</label>
            <?php if($student['Hinh']): ?>
                <img src="images/<?php echo htmlspecialchars($student['Hinh']); ?>" 
                     alt="Student Photo" style="max-width: 100px;">
            <?php endif; ?>
            <input type="file" name="hinh" class="form-control mt-2">
        </div>
        
        <div class="form-group">
            <label>Ngành học:</label>
            <select name="manganh" class="form-control" required>
                <?php while($nganh = $nganh_result->fetch_assoc()): ?>
                    <option value="<?php echo $nganh['MaNganh']; ?>" 
                            <?php echo $student['MaNganh'] == $nganh['MaNganh'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($nganh['TenNganh']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
        
        <button type="submit" class="btn btn-primary">Cập nhật</button>
        <a href="students.php" class="btn btn-secondary">Hủy</a>
    </form>
</div>

<?php include 'includes/footer.php'; ?> 