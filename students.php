<?php
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

include 'includes/header.php';
include 'includes/db_connect.php';

// Fetch statistics
$stats = [
    'total' => $conn->query("SELECT COUNT(*) as count FROM SinhVien")->fetch_assoc()['count'],
    'male' => $conn->query("SELECT COUNT(*) as count FROM SinhVien WHERE GioiTinh = 'Nam'")->fetch_assoc()['count'],
    'female' => $conn->query("SELECT COUNT(*) as count FROM SinhVien WHERE GioiTinh = 'Nữ'")->fetch_assoc()['count'],
    'departments' => $conn->query("SELECT COUNT(DISTINCT MaNganh) as count FROM SinhVien")->fetch_assoc()['count']
];

// Fetch students with their departments
$sql = "SELECT s.*, n.TenNganh 
        FROM SinhVien s 
        LEFT JOIN NganhHoc n ON s.MaNganh = n.MaNganh 
        ORDER BY s.MaSV";
$result = $conn->query($sql);
?>

<div class="container">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Quản lý Sinh viên</h1>
        
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4 fade-in-up">
        <div class="col-xl-3 col-md-6">
            <div class="card stat-card primary h-100">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Tổng sinh viên</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['total']; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card stat-card success h-100">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Nam</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['male']; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-male fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card stat-card info h-100">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Nữ</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['female']; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-female fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card stat-card warning h-100">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Ngành học</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['departments']; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-graduation-cap fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Success Message -->
    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php 
            echo $_SESSION['success_message']; 
            unset($_SESSION['success_message']);
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Students Table Card -->
    <div class="card shadow mb-4 fade-in-up">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-white">Danh sách Sinh viên</h6>
            <a href="add_student.php" class="btn btn-light btn-icon">
                <i class="fas fa-user-plus me-2"></i> Thêm Sinh viên
            </a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover student-table" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Mã SV</th>
                            <th>Họ Tên</th>
                            <th>Giới Tính</th>
                            <th>Ngày Sinh</th>
                            <th>Hình</th>
                            <th>Ngành Học</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['MaSV']); ?></td>
                            <td><?php echo htmlspecialchars($row['HoTen']); ?></td>
                            <td>
                                <?php if($row['GioiTinh'] == 'Nam'): ?>
                                    <span class="badge badge-male">Nam</span>
                                <?php else: ?>
                                    <span class="badge badge-female">Nữ</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo date('d/m/Y', strtotime($row['NgaySinh'])); ?></td>
                            <td class="text-center">
                                <?php if($row['Hinh']): ?>
                                    <img src="images/<?php echo htmlspecialchars($row['Hinh']); ?>" 
                                         alt="Student Photo" 
                                         class="rounded-circle student-avatar"
                                         style="width: 40px; height: 40px; object-fit: cover;">
                                <?php else: ?>
                                    <i class="fas fa-user-circle fa-2x text-gray-300"></i>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($row['TenNganh']); ?></td>
                            <td>
                                <div class="action-buttons">
                                    <a href="view_student.php?id=<?php echo $row['MaSV']; ?>" 
                                       class="btn btn-info btn-sm"
                                       data-bs-toggle="tooltip"
                                       title="Chi tiết">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="edit_student.php?id=<?php echo $row['MaSV']; ?>" 
                                       class="btn btn-warning btn-sm"
                                       data-bs-toggle="tooltip"
                                       title="Sửa">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="delete_student.php?id=<?php echo $row['MaSV']; ?>" 
                                       class="btn btn-danger btn-sm delete-confirm"
                                       data-bs-toggle="tooltip"
                                       title="Xóa">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script>
$(document).ready(function() {
    $('.student-table').DataTable({
        pageLength: 4,            // Fixed number of items per page
        lengthChange: false,      // Remove the length menu
        searching: false,         // Remove the search box
        info: false,             // Remove the information text
        language: {
            paginate: {
                first: '<i class="fas fa-angle-double-left"></i>',
                previous: '<i class="fas fa-angle-left"></i>',
                next: '<i class="fas fa-angle-right"></i>',
                last: '<i class="fas fa-angle-double-right"></i>'
            }
        },
        dom: '<"row"<"col-sm-12"tr>><"row"<"col-sm-12"p>>', // Only show table and pagination
        drawCallback: function() {
            $('.paginate_button').addClass('btn btn-sm');
        }
    });
});
</script>

<style>
/* DataTables Custom Styling */
.dataTables_wrapper .dataTables_paginate {
    margin-top: 20px;
    display: flex;
    justify-content: center;
}

.dataTables_wrapper .dataTables_paginate .paginate_button {
    margin: 0 5px;
    padding: 8px 16px;
    border-radius: 10px;
    border: none !important;
    background: var(--light-color) !important;
    color: var(--dark-color) !important;
    transition: all 0.3s ease;
}

.dataTables_wrapper .dataTables_paginate .paginate_button:hover {
    background: linear-gradient(45deg, var(--gradient-start), var(--gradient-end)) !important;
    color: white !important;
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(127, 0, 255, 0.2);
}

.dataTables_wrapper .dataTables_paginate .paginate_button.current {
    background: linear-gradient(45deg, var(--gradient-start), var(--gradient-end)) !important;
    color: white !important;
    border: none !important;
    box-shadow: 0 4px 15px rgba(127, 0, 255, 0.2);
}

.dataTables_wrapper .dataTables_paginate .paginate_button.disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

/* Table Styling */
.student-table {
    margin-bottom: 0 !important;
}

.student-table thead th {
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    padding: 15px !important;
}

.student-table tbody tr {
    transition: all 0.3s ease;
}

.student-table tbody tr:hover {
    background-color: rgba(127, 0, 255, 0.05);
    transform: scale(1.01);
}

/* Student Avatar */
.student-avatar {
    transition: all 0.3s ease;
}

.student-avatar:hover {
    transform: scale(1.2);
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
}

/* Action Buttons */
.action-buttons {
    display: flex;
    gap: 8px;
    justify-content: center;
}

.action-buttons .btn {
    width: 32px;
    height: 32px;
    padding: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 8px;
    transition: all 0.3s ease;
}

.action-buttons .btn:hover {
    transform: translateY(-2px);
}
</style>