<?php
session_start();
include '../includes/db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $login_input = trim($_POST['login_input']); // can be username, email or phone
    $password = $_POST['password'];

    if (empty($login_input) || empty($password)) {
        $_SESSION['login_error'] = "Vui lòng nhập thông tin đăng nhập và mật khẩu.";
        header("Location: ../HTML/dang_nhap.php");
        exit();
    }

    // Check by username, email, or phone
    $stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE username = ? OR email = ? OR phone = ?");
    $stmt->bind_param("sss", $login_input, $login_input, $login_input);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        if (password_verify($password, $row['password'])) {
            // Success
            // Chống tấn công Session Fixation bằng cách tạo mới session id
            session_regenerate_id(true);

            $_SESSION['user_id'] = $row['id'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['role'] = $row['role']; // Lưu quyền hạn vào session

            if ($row['role'] == 'admin') {
                header("Location: ../HTML/dashboard.php"); // Chuyển hướng Admin đến Dashboard
            } else {
                header("Location: ../HTML/trang_chu.php"); // Chuyển hướng User thường
            }
            exit();
        }
    }

    $_SESSION['login_error'] = "Thông tin đăng nhập hoặc mật khẩu không đúng.";
    header("Location: ../HTML/dang_nhap.php");
    exit();
}
?>
