<?php
session_start();
include '../includes/db_connect.php';

// 1. Kiểm tra xem người dùng đã đăng nhập chưa
if (!isset($_SESSION['user_id'])) {
    header("Location: ../pages/dang_nhap.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['user_id'];
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // 2. Validate input
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $_SESSION['password_error'] = "Vui lòng điền đầy đủ các trường.";
        if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') { header("Location: ../admin/admin_change_password.php"); } else { header("Location: ../pages/change_password.php"); }
        exit();
    }

    if ($new_password !== $confirm_password) {
        $_SESSION['password_error'] = "Mật khẩu mới không khớp.";
        if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') { header("Location: ../admin/admin_change_password.php"); } else { header("Location: ../pages/change_password.php"); }
        exit();
    }

    // 3. Lấy mật khẩu hiện tại từ DB
    $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($user = $result->fetch_assoc()) {
        // 4. Xác thực mật khẩu hiện tại
        if (password_verify($current_password, $user['password'])) {
            // 5. Mật khẩu hiện tại đúng, hash và cập nhật mật khẩu mới
            $new_hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            
            $update_stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $update_stmt->bind_param("si", $new_hashed_password, $user_id);
            
            if ($update_stmt->execute()) {
                $_SESSION['password_success'] = "Đổi mật khẩu thành công!";
            } else {
                $_SESSION['password_error'] = "Lỗi hệ thống, không thể cập nhật mật khẩu.";
            }
        } else {
            $_SESSION['password_error'] = "Mật khẩu hiện tại không đúng.";
        }
    }

    if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') { header("Location: ../admin/admin_change_password.php"); } else { header("Location: ../pages/change_password.php"); }
    exit();
}
?>