<?php
session_start();
include '../includes/db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $re_password = $_POST['re_password'];
    $phone = trim($_POST['phone']);
    $email = trim($_POST['email']);
    $full_name = trim($_POST['fullname']); // Add fullname if form has it, or just use username

    // Basic Validation
    $errors = [];
    if (empty($username) || empty($password) || empty($email)) {
        $errors[] = "Vui lòng điền đầy đủ thông tin bắt buộc.";
    }

    if ($password !== $re_password) {
        $errors[] = "Mật khẩu nhập lại không khớp.";
    }

    // Check if username or email exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    $stmt->bind_param("ss", $username, $email);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $errors[] = "Tên đăng nhập hoặc Email đã tồn tại.";
    }
    $stmt->close();

    if (empty($errors)) {
        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Insert User
        $stmt = $conn->prepare("INSERT INTO users (username, password, email, phone, full_name, role) VALUES (?, ?, ?, ?, ?, 'user')");
        $stmt->bind_param("sssss", $username, $hashed_password, $email, $phone, $full_name); // Assuming fullname field exists in form

        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Đăng ký thành công! Vui lòng đăng nhập.";
            header("Location: ../HTML/dang_nhap.php");
            exit();
        } else {
            $errors[] = "Lỗi hệ thống: " . $conn->error;
        }
    }

    if (!empty($errors)) {
        $_SESSION['register_errors'] = $errors;
        header("Location: ../HTML/dang_ky.php");
        exit();
    }
}
?>
