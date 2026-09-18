<?php
/**
 * BINBINCAMERA - Database Reset & Migration Tool
 * File này sẽ xử lý lại toàn bộ database một cách an toàn
 * 
 * CẢNH BÁO: File này sẽ cập nhật cấu trúc database
 * Dữ liệu hiện có sẽ KHÔNG bị xóa, chỉ cập nhật cấu trúc
 */

// Include database connection
include '../includes/db_connect.php';

// Thiết lập hiển thị lỗi
error_reporting(E_ALL);
ini_set('display_errors', 1);

?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Migration - BinBinCamera</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 1000px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .header h1 {
            font-size: 32px;
            margin-bottom: 10px;
        }
        
        .header p {
            font-size: 16px;
            opacity: 0.9;
        }
        
        .content {
            padding: 40px;
        }
        
        .step {
            background: #f8f9fa;
            border-left: 4px solid #667eea;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        
        .step h3 {
            color: #667eea;
            margin-bottom: 15px;
            font-size: 20px;
        }
        
        .success {
            background: #d4edda;
            border-left-color: #28a745;
            color: #155724;
        }
        
        .success h3 {
            color: #28a745;
        }
        
        .warning {
            background: #fff3cd;
            border-left-color: #ffc107;
            color: #856404;
        }
        
        .warning h3 {
            color: #ffc107;
        }
        
        .error {
            background: #f8d7da;
            border-left-color: #dc3545;
            color: #721c24;
        }
        
        .error h3 {
            color: #dc3545;
        }
        
        .info {
            background: #d1ecf1;
            border-left-color: #17a2b8;
            color: #0c5460;
        }
        
        .info h3 {
            color: #17a2b8;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            background: white;
            border-radius: 5px;
            overflow: hidden;
        }
        
        table th {
            background: #667eea;
            color: white;
            padding: 12px;
            text-align: left;
            font-weight: 600;
        }
        
        table td {
            padding: 10px 12px;
            border-bottom: 1px solid #dee2e6;
        }
        
        table tr:last-child td {
            border-bottom: none;
        }
        
        table tr:hover {
            background: #f8f9fa;
        }
        
        .btn {
            display: inline-block;
            padding: 12px 30px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            border-radius: 25px;
            font-weight: 600;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            margin-top: 20px;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }
        
        .icon {
            font-size: 24px;
            margin-right: 10px;
        }
        
        code {
            background: #e9ecef;
            padding: 2px 8px;
            border-radius: 3px;
            font-family: 'Courier New', monospace;
            color: #e83e8c;
        }
        
        ul {
            margin-left: 20px;
            margin-top: 10px;
        }
        
        ul li {
            margin-bottom: 8px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔧 Database Migration Tool</h1>
            <p>Xử lý lại cấu trúc database - BinBinCamera</p>
        </div>
        
        <div class="content">
            <?php
            $errors = [];
            $success = [];
            $warnings = [];
            
            // =====================================================
            // BƯỚC 1: Kiểm tra kết nối database
            // =====================================================
            echo '<div class="step info">';
            echo '<h3><span class="icon">🔌</span>Bước 1: Kiểm tra kết nối Database</h3>';
            
            if ($conn->connect_error) {
                echo '<p>❌ Lỗi kết nối: ' . $conn->connect_error . '</p>';
                $errors[] = 'Không thể kết nối database';
            } else {
                echo '<p>✅ Kết nối database thành công!</p>';
                echo '<p>📊 Database: <code>binbincamera</code></p>';
                $success[] = 'Kết nối database';
            }
            echo '</div>';
            
            if (empty($errors)) {
                // =====================================================
                // BƯỚC 2: Tạo/Cập nhật bảng USERS
                // =====================================================
                echo '<div class="step">';
                echo '<h3><span class="icon">👥</span>Bước 2: Xử lý bảng USERS</h3>';
                
                $sql_users = "CREATE TABLE IF NOT EXISTS `users` (
                  `id` INT(11) NOT NULL AUTO_INCREMENT,
                  `username` VARCHAR(50) NOT NULL UNIQUE,
                  `password` VARCHAR(255) NOT NULL,
                  `email` VARCHAR(100) NOT NULL UNIQUE,
                  `phone` VARCHAR(20) DEFAULT NULL,
                  `full_name` VARCHAR(100) DEFAULT NULL,
                  `role` VARCHAR(20) DEFAULT 'user',
                  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                  PRIMARY KEY (`id`),
                  INDEX `idx_username` (`username`),
                  INDEX `idx_email` (`email`),
                  INDEX `idx_role` (`role`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
                
                if ($conn->query($sql_users) === TRUE) {
                    echo '<p>✅ Bảng <code>users</code> đã sẵn sàng</p>';
                    $success[] = 'Bảng users';
                } else {
                    echo '<p>❌ Lỗi: ' . $conn->error . '</p>';
                    $errors[] = 'Bảng users';
                }
                echo '</div>';
                
                // =====================================================
                // BƯỚC 3: Tạo/Cập nhật bảng PRODUCTS
                // =====================================================
                echo '<div class="step">';
                echo '<h3><span class="icon">📦</span>Bước 3: Xử lý bảng PRODUCTS</h3>';
                
                $sql_products = "CREATE TABLE IF NOT EXISTS `products` (
                  `id` INT(11) NOT NULL AUTO_INCREMENT,
                  `name` VARCHAR(255) NOT NULL,
                  `category` VARCHAR(50) DEFAULT NULL,
                  `brand` VARCHAR(50) DEFAULT NULL,
                  `price_session` DECIMAL(15,2) DEFAULT NULL,
                  `price_day` DECIMAL(15,2) DEFAULT NULL,
                  `image` VARCHAR(255) DEFAULT NULL,
                  `image_back` VARCHAR(255) DEFAULT NULL,
                  `description` TEXT DEFAULT NULL,
                  `detailed_description` TEXT DEFAULT NULL,
                  `detail_image_1` VARCHAR(255) DEFAULT NULL,
                  `detail_image_2` VARCHAR(255) DEFAULT NULL,
                  `detail_image_3` VARCHAR(255) DEFAULT NULL,
                  `detail_image_4` VARCHAR(255) DEFAULT NULL,
                  `stock` INT(11) DEFAULT 0,
                  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                  PRIMARY KEY (`id`),
                  INDEX `idx_category` (`category`),
                  INDEX `idx_brand` (`brand`),
                  INDEX `idx_name` (`name`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
                
                if ($conn->query($sql_products) === TRUE) {
                    echo '<p>✅ Bảng <code>products</code> đã sẵn sàng</p>';
                    
                    // Kiểm tra và thêm các cột mới nếu chưa có
                    $columns_to_add = [
                        'description' => 'TEXT DEFAULT NULL',
                        'detailed_description' => 'TEXT DEFAULT NULL',
                        'detail_image_1' => 'VARCHAR(255) DEFAULT NULL',
                        'detail_image_2' => 'VARCHAR(255) DEFAULT NULL',
                        'detail_image_3' => 'VARCHAR(255) DEFAULT NULL',
                        'detail_image_4' => 'VARCHAR(255) DEFAULT NULL',
                        'stock' => 'INT(11) DEFAULT 0',
                        'updated_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'
                    ];
                    
                    foreach ($columns_to_add as $col => $def) {
                        $check = $conn->query("SHOW COLUMNS FROM products LIKE '$col'");
                        if ($check->num_rows == 0) {
                            if ($conn->query("ALTER TABLE products ADD COLUMN `$col` $def")) {
                                echo '<p>➕ Đã thêm cột <code>' . $col . '</code></p>';
                            }
                        }
                    }
                    
                    $success[] = 'Bảng products';
                } else {
                    echo '<p>❌ Lỗi: ' . $conn->error . '</p>';
                    $errors[] = 'Bảng products';
                }
                echo '</div>';
                
                // =====================================================
                // BƯỚC 4: Tạo/Cập nhật bảng ORDERS
                // =====================================================
                echo '<div class="step">';
                echo '<h3><span class="icon">🛒</span>Bước 4: Xử lý bảng ORDERS</h3>';
                
                $sql_orders = "CREATE TABLE IF NOT EXISTS `orders` (
                  `id` INT(11) NOT NULL AUTO_INCREMENT,
                  `user_id` INT(11) DEFAULT NULL,
                  `customer_name` VARCHAR(100) NOT NULL,
                  `phone` VARCHAR(20) NOT NULL,
                  `email` VARCHAR(100) DEFAULT NULL,
                  `address` VARCHAR(255) DEFAULT NULL,
                  `note` TEXT DEFAULT NULL,
                  `facebook` VARCHAR(255) DEFAULT NULL,
                  `images` TEXT DEFAULT NULL,
                  `total_money` DECIMAL(15,2) DEFAULT 0.00,
                  `status` VARCHAR(50) DEFAULT 'Mới đặt',
                  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                  PRIMARY KEY (`id`),
                  INDEX `idx_user_id` (`user_id`),
                  INDEX `idx_status` (`status`),
                  INDEX `idx_created_at` (`created_at`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
                
                if ($conn->query($sql_orders) === TRUE) {
                    echo '<p>✅ Bảng <code>orders</code> đã sẵn sàng</p>';
                    
                    // Thêm các cột nếu thiếu
                    $order_columns = [
                        'facebook' => 'VARCHAR(255) DEFAULT NULL',
                        'images' => 'TEXT DEFAULT NULL',
                        'updated_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'
                    ];
                    
                    foreach ($order_columns as $col => $def) {
                        $check = $conn->query("SHOW COLUMNS FROM orders LIKE '$col'");
                        if ($check->num_rows == 0) {
                            if ($conn->query("ALTER TABLE orders ADD COLUMN `$col` $def")) {
                                echo '<p>➕ Đã thêm cột <code>' . $col . '</code></p>';
                            }
                        }
                    }
                    
                    $success[] = 'Bảng orders';
                } else {
                    echo '<p>❌ Lỗi: ' . $conn->error . '</p>';
                    $errors[] = 'Bảng orders';
                }
                echo '</div>';
                
                // =====================================================
                // BƯỚC 5: Tạo/Cập nhật bảng ORDER_DETAILS
                // =====================================================
                echo '<div class="step">';
                echo '<h3><span class="icon">📋</span>Bước 5: Xử lý bảng ORDER_DETAILS</h3>';
                
                $sql_order_details = "CREATE TABLE IF NOT EXISTS `order_details` (
                  `id` INT(11) NOT NULL AUTO_INCREMENT,
                  `order_id` INT(11) NOT NULL,
                  `product_id` INT(11) NOT NULL,
                  `product_name` VARCHAR(255) NOT NULL,
                  `quantity` INT(11) NOT NULL DEFAULT 1,
                  `price` DECIMAL(15,2) NOT NULL,
                  PRIMARY KEY (`id`),
                  INDEX `idx_order_id` (`order_id`),
                  INDEX `idx_product_id` (`product_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
                
                if ($conn->query($sql_order_details) === TRUE) {
                    echo '<p>✅ Bảng <code>order_details</code> đã sẵn sàng</p>';
                    
                    // Kiểm tra cột product_name
                    $check = $conn->query("SHOW COLUMNS FROM order_details LIKE 'product_name'");
                    if ($check->num_rows == 0) {
                        if ($conn->query("ALTER TABLE order_details ADD COLUMN `product_name` VARCHAR(255) NOT NULL AFTER `product_id`")) {
                            echo '<p>➕ Đã thêm cột <code>product_name</code></p>';
                        }
                    }
                    
                    $success[] = 'Bảng order_details';
                } else {
                    echo '<p>❌ Lỗi: ' . $conn->error . '</p>';
                    $errors[] = 'Bảng order_details';
                }
                echo '</div>';
                
                // =====================================================
                // BƯỚC 6: Tạo/Cập nhật bảng CONTACTS
                // =====================================================
                echo '<div class="step">';
                echo '<h3><span class="icon">📧</span>Bước 6: Xử lý bảng CONTACTS</h3>';
                
                $sql_contacts = "CREATE TABLE IF NOT EXISTS `contacts` (
                  `id` INT(11) NOT NULL AUTO_INCREMENT,
                  `name` VARCHAR(100) NOT NULL,
                  `email` VARCHAR(100) NOT NULL,
                  `phone` VARCHAR(20) DEFAULT NULL,
                  `message` TEXT NOT NULL,
                  `status` VARCHAR(20) DEFAULT 'new',
                  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                  PRIMARY KEY (`id`),
                  INDEX `idx_status` (`status`),
                  INDEX `idx_created_at` (`created_at`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
                
                if ($conn->query($sql_contacts) === TRUE) {
                    echo '<p>✅ Bảng <code>contacts</code> đã sẵn sàng</p>';
                    $success[] = 'Bảng contacts';
                } else {
                    echo '<p>❌ Lỗi: ' . $conn->error . '</p>';
                    $errors[] = 'Bảng contacts';
                }
                echo '</div>';
                
                // =====================================================
                // BƯỚC 7: Tạo tài khoản admin
                // =====================================================
                echo '<div class="step">';
                echo '<h3><span class="icon">🔑</span>Bước 7: Tạo tài khoản Admin</h3>';
                
                $check_admin = $conn->query("SELECT id FROM users WHERE username = 'admin'");
                
                if ($check_admin->num_rows > 0) {
                    echo '<p>⚠️ Tài khoản admin đã tồn tại</p>';
                    echo '<p>🔄 Đang reset mật khẩu về mặc định...</p>';
                    
                    $password = 'admin123';
                    $hashed = password_hash($password, PASSWORD_DEFAULT);
                    
                    $stmt = $conn->prepare("UPDATE users SET password = ?, role = 'admin' WHERE username = 'admin'");
                    $stmt->bind_param("s", $hashed);
                    
                    if ($stmt->execute()) {
                        echo '<p>✅ Đã reset mật khẩu admin thành công!</p>';
                        echo '<p>👤 Username: <code>admin</code></p>';
                        echo '<p>🔐 Password: <code>admin123</code></p>';
                        $warnings[] = 'Reset mật khẩu admin';
                    }
                } else {
                    $password = 'admin123';
                    $hashed = password_hash($password, PASSWORD_DEFAULT);
                    
                    $stmt = $conn->prepare("INSERT INTO users (username, password, email, role, full_name) VALUES (?, ?, ?, 'admin', 'Administrator')");
                    $username = 'admin';
                    $email = 'admin@binbincamera.com';
                    $stmt->bind_param("sss", $username, $hashed, $email);
                    
                    if ($stmt->execute()) {
                        echo '<p>✅ Đã tạo tài khoản admin thành công!</p>';
                        echo '<p>👤 Username: <code>admin</code></p>';
                        echo '<p>🔐 Password: <code>admin123</code></p>';
                        $success[] = 'Tạo tài khoản admin';
                    } else {
                        echo '<p>❌ Lỗi tạo admin: ' . $stmt->error . '</p>';
                        $errors[] = 'Tạo admin';
                    }
                }
                echo '</div>';
                
                // =====================================================
                // BƯỚC 8: Hiển thị cấu trúc database
                // =====================================================
                echo '<div class="step info">';
                echo '<h3><span class="icon">📊</span>Bước 8: Cấu trúc Database hiện tại</h3>';
                
                $tables = ['users', 'products', 'orders', 'order_details', 'contacts'];
                
                foreach ($tables as $table) {
                    $result = $conn->query("SELECT COUNT(*) as count FROM $table");
                    $row = $result->fetch_assoc();
                    echo '<p>📁 Bảng <code>' . $table . '</code>: <strong>' . $row['count'] . '</strong> bản ghi</p>';
                }
                
                echo '</div>';
            }
            
            // =====================================================
            // TỔNG KẾT
            // =====================================================
            if (empty($errors)) {
                echo '<div class="step success">';
                echo '<h3><span class="icon">🎉</span>Migration hoàn tất!</h3>';
                echo '<p><strong>Tất cả các bước đã được thực hiện thành công!</strong></p>';
                echo '<ul>';
                foreach ($success as $item) {
                    echo '<li>✅ ' . $item . '</li>';
                }
                echo '</ul>';
                
                if (!empty($warnings)) {
                    echo '<p style="margin-top: 15px;"><strong>Cảnh báo:</strong></p>';
                    echo '<ul>';
                    foreach ($warnings as $item) {
                        echo '<li>⚠️ ' . $item . '</li>';
                    }
                    echo '</ul>';
                }
                
                echo '<p style="margin-top: 20px;">🚀 Database đã sẵn sàng sử dụng!</p>';
                echo '<a href="../pages/trang_chu.php" class="btn">Về trang chủ</a>';
                echo '<a href="../admin/quan_ly_san_pham.php" class="btn" style="margin-left: 10px;">Quản lý sản phẩm</a>';
                echo '</div>';
            } else {
                echo '<div class="step error">';
                echo '<h3><span class="icon">❌</span>Có lỗi xảy ra!</h3>';
                echo '<p>Các bước sau gặp lỗi:</p>';
                echo '<ul>';
                foreach ($errors as $item) {
                    echo '<li>❌ ' . $item . '</li>';
                }
                echo '</ul>';
                echo '<p style="margin-top: 15px;">Vui lòng kiểm tra lại cấu hình database và thử lại.</p>';
                echo '</div>';
            }
            
            $conn->close();
            ?>
        </div>
    </div>
</body>
</html>
