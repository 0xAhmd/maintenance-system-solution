<?php
session_start();
include "connection.php";

$error = "";

if ($_SERVER['REQUEST_METHOD'] == "POST") {

    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Use prepared statement instead of string concatenation
    $stmt = $conn->prepare("SELECT * FROM user WHERE email = ? LIMIT 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        // For backward compatibility, check if password is hashed or plain
        $password_valid = false;
        
        // Check if password is hashed
        $password_info = @password_get_info($user['password']);
        if (isset($password_info['algo']) && $password_info['algo'] !== null) {
            // Password is hashed
            $password_valid = password_verify($password, $user['password']);
        } else {
            // Old plain text password (for existing data)
            $password_valid = ($password === $user['password']);
            
            // Optional: Update to hashed password on successful login
            if ($password_valid) {
                $new_hash = password_hash($password, PASSWORD_DEFAULT);
                $update_stmt = $conn->prepare("UPDATE user SET password = ? WHERE user_id = ?");
                $update_stmt->bind_param("si", $new_hash, $user['user_id']);
                $update_stmt->execute();
                $update_stmt->close();
            }
        }

        if ($password_valid) {
            $_SESSION['user'] = $user;

            if ($user['role'] == "admin") {
                header("Location: AdminDashboard/dashboard.php");
            } elseif ($user['role'] == "driver") {
                header("Location: driver/my_vehicle.php");
            } elseif ($user['role'] == "mechanic") {
                header("Location: mechanic/tasks.php");
            }
            exit();
        } else {
            $error = "كلمة المرور غير صحيحة";
        }
    } else {
        $error = "البريد الإلكتروني غير موجود";
    }
    
    if (isset($stmt)) {
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل الدخول - نظام إدارة الصيانة</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            font-family: 'Cairo', sans-serif;
        }
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .login-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            padding: 40px;
            max-width: 450px;
            width: 100%;
        }
        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .login-header i {
            font-size: 60px;
            color: #667eea;
            margin-bottom: 15px;
        }
        .login-header h2 {
            color: #333;
            font-weight: 700;
            margin-bottom: 10px;
        }
        .login-header p {
            color: #666;
            font-size: 14px;
        }
        .form-control {
            border-radius: 10px;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            transition: all 0.3s;
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .input-group-icon {
            position: relative;
        }
        .input-group-icon i {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #667eea;
            z-index: 10;
        }
        .input-group-icon .form-control {
            padding-right: 45px;
        }
        .btn-login {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 10px;
            padding: 12px;
            font-weight: 600;
            font-size: 16px;
            transition: all 0.3s;
        }
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }
        .alert {
            border-radius: 10px;
            border: none;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="login-header">
            <i class="bi bi-shield-lock-fill"></i>
            <h2>تسجيل الدخول</h2>
            <p>مرحباً بك في نظام إدارة الصيانة</p>
        </div>

        <?php if($error): ?>
            <div class="alert alert-danger d-flex align-items-center" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <div><?= $error ?></div>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3 input-group-icon">
                <i class="bi bi-envelope-fill"></i>
                <input type="email" name="email" class="form-control" placeholder="البريد الإلكتروني" required>
            </div>

            <div class="mb-4 input-group-icon">
                <i class="bi bi-lock-fill"></i>
                <input type="password" name="password" class="form-control" placeholder="كلمة المرور" required>
            </div>

            <button type="submit" class="btn btn-primary btn-login w-100">
                <i class="bi bi-box-arrow-in-right me-2"></i>
                تسجيل الدخول
            </button>
        </form>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>