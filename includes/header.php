<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Sinh viên</title>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link href="css/style.css" rel="stylesheet">
    
    <!-- DataTables -->
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    
    <!-- Animate.css -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" rel="stylesheet">
</head>
<body>
    <!-- Animated Background -->
    <div class="animated-bg"></div>

    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark mb-4">
        <div class="container">
            <a class="navbar-brand animate__animated animate__fadeIn" href="#">
                <i class="fas fa-graduation-cap me-2"></i>
                Quản lý Sinh viên
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto animate__animated animate__fadeInLeft">
                    <?php if (isset($_SESSION['user'])): ?>
                        <?php if ($_SESSION['user']['role'] === 'admin'): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="students.php">
                                    <i class="fas fa-users me-1"></i> Sinh viên
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="course_list.php">
                                    <i class="fas fa-book me-1"></i> Học phần
                                </a>
                            </li>
                        <?php else: ?>
                            <li class="nav-item">
                                <a class="nav-link" href="course_list.php">
                                    <i class="fas fa-book me-1"></i> Danh sách học phần
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="cart.php">
                                    <i class="fas fa-shopping-cart me-1"></i> Giỏ đăng ký
                                </a>
                            </li>
                        <?php endif; ?>
                    <?php endif; ?>
                </ul>
                <?php if (isset($_SESSION['user'])): ?>
                    <div class="navbar-nav animate__animated animate__fadeInRight">
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user-circle me-1"></i>
                                <?php echo htmlspecialchars($_SESSION['user']['username']); ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end glass-effect">
                                <li>
                                    <a class="dropdown-item" href="logout.php">
                                        <i class="fas fa-sign-out-alt me-1"></i> Đăng xuất
                                    </a>
                                </li>
                            </ul>
                        </li>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Loading Spinner -->
    <div class="spinner-overlay" style="display: none;">
        <div class="spinner"></div>
    </div>

    <header>
        <h1>Hệ Thống Quản Lý Sinh Viên</h1>
    </header>
