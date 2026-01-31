<?php
include '../includes/db_connect.php';

// !!! CẢNH BÁO BẢO MẬT !!!
// Thêm một lớp bảo vệ bằng cách yêu cầu một khóa bí mật trong URL.
// Thay đổi 'your-very-secret-key-12345' thành một chuỗi ngẫu nhiên và khó đoán.
// $secret_key = 'your-very-secret-key-12345';
// if (!isset($_GET['setup_key']) || $_GET['setup_key'] !== $secret_key) {
//     die('Truy cập bị từ chối. Yêu cầu khóa thiết lập hợp lệ.');
// }
// (Đã tạm ẩn bảo mật để bạn dễ dàng reset tài khoản, hãy mở lại sau khi xong)

// 1. Tạo bảng users nếu chưa có (đảm bảo cấu trúc đúng)
$sql_create_table = "CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    phone VARCHAR(20),
    full_name VARCHAR(100),
    role VARCHAR(20) DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($sql_create_table) === TRUE) {
    echo "Kiểm tra bảng users: OK<br>";
} else {
    die("Lỗi tạo bảng users: " . $conn->error);
}

// 2. Kiểm tra cột role (đề phòng bảng cũ chưa có cột này)
$check_col = $conn->query("SHOW COLUMNS FROM users LIKE 'role'");
if ($check_col->num_rows == 0) {
    $conn->query("ALTER TABLE users ADD COLUMN role VARCHAR(20) DEFAULT 'user'");
    echo "Đã cập nhật cấu trúc bảng (thêm cột role).<br>";
}

// 3. Tạo bảng orders nếu chưa có
$sql_create_orders = "CREATE TABLE IF NOT EXISTS `orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `customer_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `address` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `note` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `images` TEXT COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `total_money` decimal(10,2) NOT NULL,
  `status` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

if ($conn->query($sql_create_orders) === TRUE) {
    echo "Kiểm tra bảng orders: OK<br>";
} else {
    die("Lỗi tạo bảng orders: " . $conn->error);
}

// 4. Tạo bảng order_details nếu chưa có
$sql_create_order_details = "CREATE TABLE IF NOT EXISTS `order_details` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `product_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

if ($conn->query($sql_create_order_details) === TRUE) {
    echo "Kiểm tra bảng order_details: OK<br>";
} else {
    die("Lỗi tạo bảng order_details: " . $conn->error);
}

// 5. Tạo tài khoản admin
$username = 'admin';
$password = 'admin123'; // Mật khẩu mặc định, CẦN THAY ĐỔI NGAY
$email = 'admin@binbincamera.com';
$role = 'admin';

// Kiểm tra xem đã có admin chưa bằng prepared statement
$stmt_check = $conn->prepare("SELECT id FROM users WHERE username = ?");
$stmt_check->bind_param("s", $username);
$stmt_check->execute();
$result_check = $stmt_check->get_result();

if ($result_check->num_rows > 0) {
    echo "✅ Tài khoản admin ($username) đã tồn tại.<br>";
    // Cập nhật quyền admin VÀ đặt lại mật khẩu về mặc định để đảm bảo đăng nhập được
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $stmt_update = $conn->prepare("UPDATE users SET role='admin', password=? WHERE username=?");
    $stmt_update->bind_param("ss", $hashed_password, $username);
    $stmt_update->execute();
    echo "Đã reset mật khẩu về: <b>$password</b> (Đã được mã hóa trong Database)<br>";
} else {
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $stmt_insert = $conn->prepare("INSERT INTO users (username, password, email, role) VALUES (?, ?, ?, ?)");
    $stmt_insert->bind_param("ssss", $username, $hashed_password, $email, $role);
    
    if ($stmt_insert->execute()) {
        echo "🎉 Đã tạo tài khoản Admin thành công!<br>";
        echo "User: <b>$username</b><br>";
        echo "Pass: <b>$password</b> (Vui lòng đổi mật khẩu này ngay lập tức!)<br>";
    } else {
        echo "❌ Lỗi: " . $stmt_insert->error;
    }
}
?>