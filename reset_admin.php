<?php
include 'includes/db_connect.php';

$username = 'admin';
$password = 'admin123';
$email = 'admin@binbincamera.com';
$role = 'admin';

// Check if admin exists
$stmt_check = $conn->prepare("SELECT id FROM users WHERE username = ?");
$stmt_check->bind_param("s", $username);
$stmt_check->execute();
$result_check = $stmt_check->get_result();

if ($result_check->num_rows > 0) {
    // Update existing admin
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $stmt_update = $conn->prepare("UPDATE users SET role='admin', password=? WHERE username=?");
    $stmt_update->bind_param("ss", $hashed_password, $username);
    
    if ($stmt_update->execute()) {
        echo "<h1>Đã reset tài khoản Admin thành công!</h1>";
        echo "<p>User: <b>$username</b></p>";
        echo "<p>Pass: <b>$password</b></p>";
        echo "<p><a href='pages/dang_nhap.php'>Bấm vào đây để đăng nhập</a></p>";
    } else {
        echo "Lỗi: " . $stmt_update->error;
    }
} else {
    // Create new admin
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $stmt_insert = $conn->prepare("INSERT INTO users (username, password, email, role) VALUES (?, ?, ?, ?)");
    $stmt_insert->bind_param("ssss", $username, $hashed_password, $email, $role);
    
    if ($stmt_insert->execute()) {
        echo "<h1>Đã tạo tài khoản Admin thành công!</h1>";
        echo "<p>User: <b>$username</b></p>";
        echo "<p>Pass: <b>$password</b></p>";
        echo "<p><a href='pages/dang_nhap.php'>Bấm vào đây để đăng nhập</a></p>";
    } else {
        echo "Lỗi: " . $stmt_insert->error;
    }
}
?>
