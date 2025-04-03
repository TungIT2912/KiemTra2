<?php
session_start();
include 'includes/header.php';
include 'includes/db_connect.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Fetch all departments for the dropdown
$sql = "SELECT * FROM NganhHoc ORDER BY TenNganh";
$nganh_result = $conn->query($sql);

$success_message = '';
$error_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $masv = $_POST['masv'];
    $hoten = $_POST['hoten'];
    $gioitinh = $_POST['gioitinh'];
    $ngaysinh = $_POST['ngaysinh'];
    $manganh = $_POST['manganh'];
    $email = $_POST['email'];
    
    // Use MaSV as both username and password
    $username = $masv;
    $password = $masv;

    // Start transaction
    $conn->begin_transaction();

    try {
        // Handle image upload
        $hinh = '';
        if (isset($_FILES['hinh']) && $_FILES['hinh']['error'] == 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'gif'];
            $filename = $_FILES['hinh']['name'];
            $filetype = pathinfo($filename, PATHINFO_EXTENSION);
            
            if (in_array(strtolower($filetype), $allowed)) {
                $newname = $masv . '_' . uniqid() . '.' . $filetype;
                if (move_uploaded_file($_FILES['hinh']['tmp_name'], 'images/' . $newname)) {
                    $hinh = $newname;
                }
            }
        }

        // Insert into SinhVien table
        $sql = "INSERT INTO SinhVien (MaSV, HoTen, GioiTinh, NgaySinh, Hinh, MaNganh) 
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssss", $masv, $hoten, $gioitinh, $ngaysinh, $hinh, $manganh);
        $stmt->execute();

        // Create user account with MaSV as both username and password
        $sql = "INSERT INTO users (username, password, role, fullname, email) 
                VALUES (?, MD5(?), 'user', ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssss", $username, $password, $hoten, $email);
        $stmt->execute();

        // Commit transaction
        $conn->commit();
        $success_message = "Thêm sinh viên thành công! Tài khoản được tạo với:<br>
                           Tên đăng nhập: " . htmlspecialchars($masv) . "<br>
                           Mật khẩu: " . htmlspecialchars($masv);
                           header("Location: students.php");

    } catch (Exception $e) {
        // Rollback on error
        $conn->rollback();
        $error_message = "Lỗi: " . $e->getMessage();
        
        // Delete uploaded image if exists
        if (!empty($hinh) && file_exists('images/' . $hinh)) {
            unlink('images/' . $hinh);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thêm Sinh viên mới</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .preview-image {
            max-width: 200px;
            max-height: 200px;
            margin-top: 10px;
        }
        .success-message {
            background-color: #d4edda;
            border-color: #c3e6cb;
            padding: 15px;
            margin-bottom: 20px;
            border: 1px solid transparent;
            border-radius: 4px;
            color: #155724;
        }
        .credentials-box {
            background-color: #f8f9fa;
            padding: 10px;
            border-radius: 4px;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <div class="row">
            <div class="col-md-8 offset-md-2">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">Thêm Sinh viên mới</h4>
                    </div>
                    <div class="card-body">
                        <?php if ($success_message): ?>
                            <div class="success-message">
                                <h5>✅ Thêm sinh viên thành công!</h5>
                                <div class="credentials-box">
                                    <strong>Thông tin đăng nhập:</strong><br>
                                    Tên đăng nhập: <code><?php echo htmlspecialchars($masv); ?></code><br>
                                    Mật khẩu: <code><?php echo htmlspecialchars($masv); ?></code>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if ($error_message): ?>
                            <div class="alert alert-danger">
                                <?php echo $error_message; ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST" enctype="multipart/form-data" onsubmit="return validateForm()">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="masv" class="form-label">Mã sinh viên:</label>
                                        <input type="text" class="form-control" id="masv" name="masv" required
                                               pattern="[A-Za-z0-9]+" 
                                               title="Chỉ cho phép chữ và số">
                                        <small class="text-muted">Mã sinh viên sẽ được sử dụng làm tên đăng nhập và mật khẩu</small>
                                    </div>

                                    <div class="mb-3">
                                        <label for="hoten" class="form-label">Họ tên:</label>
                                        <input type="text" class="form-control" id="hoten" name="hoten" required>
                                    </div>

                                    <div class="mb-3">
                                        <label for="gioitinh" class="form-label">Giới tính:</label>
                                        <select class="form-select" id="gioitinh" name="gioitinh" required>
                                            <option value="Nam">Nam</option>
                                            <option value="Nữ">Nữ</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="ngaysinh" class="form-label">Ngày sinh:</label>
                                        <input type="date" class="form-control" id="ngaysinh" name="ngaysinh" required>
                                    </div>

                                    <div class="mb-3">
                                        <label for="email" class="form-label">Email:</label>
                                        <input type="email" class="form-control" id="email" name="email" required>
                                    </div>

                                    <div class="mb-3">
                                        <label for="manganh" class="form-label">Ngành học:</label>
                                        <select class="form-select" id="manganh" name="manganh" required>
                                            <?php while ($nganh = $nganh_result->fetch_assoc()): ?>
                                                <option value="<?php echo htmlspecialchars($nganh['MaNganh']); ?>">
                                                    <?php echo htmlspecialchars($nganh['TenNganh']); ?>
                                                </option>
                                            <?php endwhile; ?>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <div class="mb-3">
                                        <label for="hinh" class="form-label">Hình ảnh:</label>
                                        <input type="file" class="form-control" id="hinh" name="hinh" 
                                               accept="image/*" onchange="previewImage(this)">
                                        <img id="preview" class="preview-image" style="display: none;">
                                    </div>
                                </div>
                            </div>

                            <div class="mt-4">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> Thêm sinh viên
                                </button>
                                <a href="students.php" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left"></i> Quay lại
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function previewImage(input) {
            var preview = document.getElementById('preview');
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                }
                reader.readAsDataURL(input.files[0]);
            } else {
                preview.style.display = 'none';
            }
        }

        function validateForm() {
            var masv = document.getElementById('masv').value;
            var email = document.getElementById('email').value;
            var ngaysinh = new Date(document.getElementById('ngaysinh').value);
            var today = new Date();

            // Validate student ID
            if (!/^[A-Za-z0-9]+$/.test(masv)) {
                alert('Mã sinh viên chỉ được chứa chữ và số!');
                return false;
            }

            // Validate email
            if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                alert('Email không hợp lệ!');
                return false;
            }

            // Validate birth date
            if (ngaysinh >= today) {
                alert('Ngày sinh không hợp lệ!');
                return false;
            }

            return true;
        }
    </script>
</body>
</html> 