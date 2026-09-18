<?php
session_start();
include '../includes/db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // 1. Validate input
    if (empty($username) || empty($email) || empty($new_password)) {
        $_SESSION['error'] = "Vui lòng điền đầy đủ thông tin.";
        header("Location: ../pages/quen_mat_khau.php");
        exit();
    }

    if ($new_password !== $confirm_password) {
        $_SESSION['error'] = "Mật khẩu xác nhận không khớp.";
        header("Location: ../pages/quen_mat_khau.php");
        exit();
    }

    // 2. Kiểm tra xem Username và Email có khớp với nhau trong DB không
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? AND email = ?");
    $stmt->bind_param("ss", $username, $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // 3. Thông tin chính xác -> Tiến hành đổi mật khẩu
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        
        $update_stmt = $conn->prepare("UPDATE users SET password = ? WHERE username = ?");
        $update_stmt->bind_param("ss", $hashed_password, $username);
        
        if ($update_stmt->execute()) {
            $_SESSION['success_message'] = "Đổi mật khẩu thành công! Vui lòng đăng nhập bằng mật khẩu mới.";
            header("Location: ../pages/dang_nhap.php");
        } else {
            $_SESSION['error'] = "Lỗi hệ thống, vui lòng thử lại sau.";
            header("Location: ../pages/quen_mat_khau.php");
        }
    } else {
        $_SESSION['error'] = "Tên đăng nhập hoặc Email không chính xác.";
        header("Location: ../pages/quen_mat_khau.php");
    }
    exit();
}
?>