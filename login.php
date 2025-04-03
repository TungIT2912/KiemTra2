<?php
session_start();
include 'includes/db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = md5($_POST['password']);

    $sql = "SELECT * FROM users WHERE username = ? AND password = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $username, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        $_SESSION['user'] = [
            'id' => $user['id'],
            'username' => $user['username'],
            'role' => $user['role']
        ];
        if ($user['role'] === 'admin') {
            header("Location: students.php");
        } else {
            header("Location: course_list.php");
        }
        exit();
    } else {
        $error = "Sai tài khoản hoặc mật khẩu!";
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập - Hệ thống Quản lý Sinh viên</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #7F00FF;
            --secondary-color: #E100FF;
            --error-color: #FF4B4B;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            min-height: 100vh;
            background: linear-gradient(135deg, #7F00FF 0%, #E100FF 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .animated-bg {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 0;
            background: linear-gradient(45deg, 
                rgba(127, 0, 255, 0.8) 0%, 
                rgba(225, 0, 255, 0.8) 50%, 
                rgba(127, 0, 255, 0.8) 100%);
            background-size: 400% 400%;
            animation: gradientBG 15s ease infinite;
        }

        @keyframes gradientBG {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        .login-container {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 40px;
            width: 100%;
            max-width: 450px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            position: relative;
            z-index: 1;
            transform: translateY(0);
            transition: all 0.3s ease;
        }

        .login-container:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
        }

        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .login-header .logo {
            font-size: 3rem;
            margin-bottom: 15px;
            color: var(--primary-color);
            animation: float 3s ease-in-out infinite;
        }

        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
            100% { transform: translateY(0px); }
        }

        .login-header h2 {
            color: #333;
            font-size: 1.8rem;
            font-weight: 600;
            margin-bottom: 5px;
        }

        .login-header p {
            color: #666;
            font-size: 0.9rem;
        }

        .input-group {
            margin-bottom: 20px;
            position: relative;
        }

        .input-group i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #666;
            transition: all 0.3s ease;
        }

        .input-group input {
            width: 100%;
            padding: 12px 40px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.9);
        }

        .input-group input:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(127, 0, 255, 0.1);
            outline: none;
        }

        .input-group input:focus + i {
            color: var(--primary-color);
        }

        .login-btn {
            width: 100%;
            padding: 12px;
            background: linear-gradient(45deg, var(--primary-color), var(--secondary-color));
            border: none;
            border-radius: 10px;
            color: white;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(127, 0, 255, 0.4);
        }

        .login-btn::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            transform: translate(-50%, -50%);
            transition: width 0.6s ease, height 0.6s ease;
        }

        .login-btn:hover::after {
            width: 300%;
            height: 300%;
        }

        .error {
            background: rgba(255, 75, 75, 0.1);
            color: var(--error-color);
            padding: 12px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            animation: shake 0.5s ease-in-out;
        }

        .error i {
            margin-right: 8px;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }

        /* Responsive Design */
        @media (max-width: 480px) {
            .login-container {
                padding: 30px 20px;
            }

            .login-header h2 {
                font-size: 1.5rem;
            }

            .login-header .logo {
                font-size: 2.5rem;
            }
        }

        /* Floating Labels Animation */
        .input-group label {
            position: absolute;
            left: 40px;
            top: 50%;
            transform: translateY(-50%);
            color: #666;
            pointer-events: none;
            transition: all 0.3s ease;
        }

        .input-group input:focus ~ label,
        .input-group input:not(:placeholder-shown) ~ label {
            top: 0;
            left: 15px;
            font-size: 0.8rem;
            padding: 0 5px;
            background: white;
            color: var(--primary-color);
        }
    </style>
</head>
<body>
    <div class="animated-bg"></div>
    
    <div class="login-container">
        <div class="login-header">
            <div class="logo">
                <i class="fas fa-graduation-cap"></i>
            </div>
            <h2>Đăng nhập</h2>
            <p>Hệ thống Quản lý Sinh viên</p>
        </div>

        <?php if (isset($error)): ?>
            <div class="error">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form class="login-form" method="POST" action="">
            <div class="input-group">
                <input type="text" 
                       name="username" 
                       id="username" 
                       required 
                       placeholder=" ">
                <i class="fas fa-user"></i>
                <label for="username">Tên đăng nhập</label>
            </div>

            <div class="input-group">
                <input type="password" 
                       name="password" 
                       id="password" 
                       required 
                       placeholder=" ">
                <i class="fas fa-lock"></i>
                <label for="password">Mật khẩu</label>
            </div>

            <button type="submit" class="login-btn">
                Đăng nhập
            </button>
        </form>
    </div>

    <script>
        // Add loading state to button when form is submitted
        document.querySelector('.login-form').addEventListener('submit', function() {
            const button = this.querySelector('.login-btn');
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang xử lý...';
            button.disabled = true;
        });
    </script>
</body>
</html>
