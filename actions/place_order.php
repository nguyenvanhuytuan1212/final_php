<?php
session_start();
include '../includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Kiểm tra giỏ hàng
    if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
        $_SESSION['error'] = "Giỏ hàng trống!";
        header("Location: ../HTML/gio_hang.php");
        exit();
    }

    // Lấy thông tin từ form
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    $note = $_POST['note'] ?? '';
    $facebook = $_POST['facebook'] ?? '';
    $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : NULL;

    // --- AUTO MIGRATION: Đảm bảo đầy đủ các cột trong bảng orders ---
    $required_columns = [
        'facebook' => "VARCHAR(255) AFTER note",
        'note' => "TEXT AFTER address",
        'images' => "TEXT AFTER total_money"
    ];
    foreach ($required_columns as $col => $definition) {
        $check = $conn->query("SHOW COLUMNS FROM `orders` LIKE '$col'");
        if ($check && $check->num_rows == 0) {
            $conn->query("ALTER TABLE `orders` ADD COLUMN `$col` $definition");
        }
    }

    // --- XỬ LÝ UPLOAD ẢNH (CMND/Giấy tờ) ---
    $upload_dir = "../uploads/orders/";
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0755, true);
        // Bảo mật thư mục upload
        file_put_contents($upload_dir . '.htaccess', "Options -Indexes\n<FilesMatch \"\.(php|phtml|php3|php4|php5|pl|py|jsp|asp|html|htm|shtml|sh|cgi)$\">\nOrder allow,deny\nDeny from all\n</FilesMatch>");
    }

    $uploaded_file_paths = [];
    
    function processOrderFile($file_input_name, $target_dir) {
        if (isset($_FILES[$file_input_name]) && $_FILES[$file_input_name]['error'] == 0) {
            $file_ext = strtolower(pathinfo($_FILES[$file_input_name]['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            if (in_array($file_ext, $allowed)) {
                $new_name = time() . "_" . uniqid() . "." . $file_ext;
                $physical_path = $target_dir . $new_name; // Đường dẫn vật lý để lưu file
                $db_path = str_replace('../', '', $target_dir) . $new_name; // Đường dẫn web để lưu vào DB

                if (move_uploaded_file($_FILES[$file_input_name]['tmp_name'], $physical_path)) {
                    return $db_path; // Trả về đường dẫn web
                }
            }
        }
        return null;
    }

    // Xử lý từng file và thêm vào mảng nếu thành công
    if ($path = processOrderFile('image_cmnd', $upload_dir)) {
        $uploaded_file_paths['cmnd'] = $path;
    }
    if ($path = processOrderFile('image_doc1', $upload_dir)) {
        $uploaded_file_paths['doc1'] = $path;
    }
    if ($path = processOrderFile('image_doc2', $upload_dir)) {
        $uploaded_file_paths['doc2'] = $path;
    }

    // Chuyển mảng đường dẫn thành chuỗi JSON để lưu vào DB
    $images_json = !empty($uploaded_file_paths) ? json_encode($uploaded_file_paths) : NULL;

    // Tính tổng tiền
    $total_money = 0;
    foreach ($_SESSION['cart'] as $item) {
        $total_money += $item['price'] * $item['quantity'];
    }

    // 1. Lưu vào bảng orders
    $sql_order = "INSERT INTO `orders` (`user_id`, `customer_name`, `phone`, `email`, `address`, `note`, `facebook`, `total_money`, `images`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql_order);
    
    if (!$stmt) {
        // --- AUTO-FIX REMOVED (đã chạy fix_db_schema.php rồi nên không cần logic phức tạp ở đây gây chậm/rối) ---
        // Thay vào đó, hiển thị thông tin debug chi tiết
        $db_name_current = $conn->query("SELECT DATABASE()")->fetch_row()[0];
        
        die("<div style='background:#edd; color:#a00; padding:20px; border:1px solid #d00;'>
            <h3>Lỗi Hệ Thống (Database Error)</h3>
            <p><b>Lỗi:</b> " . $conn->error . "</p>
            <p><b>Câu lệnh SQL:</b> " . htmlspecialchars($sql_order) . "</p>
            <p><b>Database hiện tại:</b> " . $db_name_current . "</p>
            <hr>
            <p>Vui lòng thử chạy lại công cụ sửa lỗi: <a href='fix_db_schema.php'>Click vào đây để sửa Database</a></p>
        </div>");
    }

    $stmt->bind_param("issssssds", $user_id, $name, $phone, $email, $address, $note, $facebook, $total_money, $images_json);
    
    if ($stmt->execute()) {
        $order_id = $stmt->insert_id;

        // 2. Lưu vào bảng order_details
        $sql_detail = "INSERT INTO `order_details` (`order_id`, `product_id`, `product_name`, `quantity`, `price`) VALUES (?, ?, ?, ?, ?)";
        $stmt_detail = $conn->prepare($sql_detail);
        
        if (!$stmt_detail) {
             die("Lỗi hệ thống (Prepare failed - Order Details): " . $conn->error . "<br>SQL: " . $sql_detail);
        }
        
        foreach ($_SESSION['cart'] as $item) {
            $p_id = $item['id'];
            $p_name = $item['name'];
            $qty = $item['quantity'];
            $price = $item['price'];
            $stmt_detail->bind_param("iisid", $order_id, $p_id, $p_name, $qty, $price);
            $stmt_detail->execute();
        }

        // 3. Xóa giỏ hàng và thông báo thành công
        // 3. Xóa giỏ hàng và chuyển hướng đến trang thanh toán
        unset($_SESSION['cart']);
        // $_SESSION['success_msg'] = "Đặt hàng thành công! Mã đơn: #$order_id"; // Không cần set session msg ở đây nữa, trang kia sẽ lo
        header("Location: ../HTML/thanh_toan_qr.php?order_id=" . $order_id);
        exit();
    } else {
        $_SESSION['error'] = "Lỗi hệ thống: " . $conn->error;
        // Quay lại trang form nhập liệu nếu lỗi (hiện tại là dat_hang_thanh_cong.php)
        header("Location: ../HTML/dat_hang_thanh_cong.php"); 
        exit();
    }
}
?>