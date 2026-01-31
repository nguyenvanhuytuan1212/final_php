<?php
session_start();
include '../includes/db_connect.php';

// Bảo vệ trang: chỉ admin mới có quyền truy cập
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    die('Truy cập bị từ chối. Bạn không phải là quản trị viên.');
}

// Tạo thư mục uploads nếu chưa có
$target_dir = "../uploads/";
if (!file_exists($target_dir)) {
    mkdir($target_dir, 0755, true);
    // Thêm file .htaccess để ngăn chặn thực thi script trong thư mục uploads
    $htaccess_content = "Options -Indexes\n<FilesMatch \"\.(php|phtml|php3|php4|php5|pl|py|jsp|asp|html|htm|shtml|sh|cgi)$\">\nOrder allow,deny\nDeny from all\n</FilesMatch>";
    file_put_contents($target_dir . '.htaccess', $htaccess_content);
}

function uploadImage($file, $target_dir) {
    // Kiểm tra file có được upload và không có lỗi
    if(!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return null;
    }

    // Kiểm tra kích thước file (ví dụ: giới hạn 5MB)
    if ($file["size"] > 5000000) {
        $_SESSION['error'] = "Lỗi: File quá lớn (tối đa 5MB).";
        return null;
    }

    // Sanitize filename
    $filename = time() . "_" . preg_replace("/[^a-zA-Z0-9._-]/", "", basename($file["name"]));
    $target_file = $target_dir . $filename;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // Kiểm tra loại file ảnh hợp lệ (whitelist)
    $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    if (!in_array($imageFileType, $allowed_types)) {
        $_SESSION['error'] = "Lỗi: Chỉ cho phép file ảnh JPG, JPEG, PNG, GIF, WEBP.";
        return null;
    }
    
    // Kiểm tra xem có phải là file ảnh thực sự không bằng getimagesize
    $check = getimagesize($file["tmp_name"]);
    if($check === false) {
        $_SESSION['error'] = "Lỗi: File không phải là ảnh hợp lệ.";
        return null;
    }

    if (move_uploaded_file($file["tmp_name"], $target_file)) {
        // Trả về đường dẫn tương đối để lưu vào DB
        return "../uploads/" . $filename;
    }
    
    return null;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';


    function ensureColumnsExist($conn) {
        $required_columns = [
            'detailed_description' => "TEXT",
            'detail_image_1' => "VARCHAR(255)",
            'detail_image_2' => "VARCHAR(255)",
            'detail_image_3' => "VARCHAR(255)",
            'detail_image_4' => "VARCHAR(255)",
            'rental_status' => "VARCHAR(20) DEFAULT 'available'"
        ];
        foreach ($required_columns as $col => $type) {
            $check = $conn->query("SHOW COLUMNS FROM products LIKE '$col'");
            if ($check && $check->num_rows == 0) {
                // Nếu cột chưa tồn tại, thêm vào
                $conn->query("ALTER TABLE products ADD COLUMN $col $type");
            }
        }
    }

    if ($action == 'add' || $action == 'edit') {
        ensureColumnsExist($conn);

        $name = $_POST['name'];
        $category = $_POST['category'];
        $brand = $_POST['brand'];
        $price_session = $_POST['price_session'];
        $price_day = $_POST['price_day'];
        $rental_status = $_POST['rental_status'] ?? 'available';
        $description = $_POST['description'] ?? '';
        $detailed_description = $_POST['detailed_description'] ?? '';
        
        // Xử lý ảnh
        $image_path = uploadImage($_FILES['image'], $target_dir);
        $image_back_path = uploadImage($_FILES['image_back'], $target_dir);
        
        // Xử lý 4 ảnh chi tiết
        $detail_image_1 = uploadImage($_FILES['detail_image_1'], $target_dir);
        $detail_image_2 = uploadImage($_FILES['detail_image_2'], $target_dir);
        $detail_image_3 = uploadImage($_FILES['detail_image_3'], $target_dir);
        $detail_image_4 = uploadImage($_FILES['detail_image_4'], $target_dir);

        if ($action == 'add') {
            // Mặc định ảnh nếu không upload
            if(!$image_path) $image_path = "../logo/logo_nikon.jpeg"; 
            if(!$image_back_path) $image_back_path = "../logo/logo_nikon.jpeg";

            $sql = "INSERT INTO products (name, category, brand, price_session, price_day, rental_status, image, image_back, description, detailed_description, detail_image_1, detail_image_2, detail_image_3, detail_image_4) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                die("Lỗi SQL (Prepare failed): " . $conn->error . " | SQL: " . $sql);
            }

            $stmt->bind_param("sssddsssssssss", $name, $category, $brand, $price_session, $price_day, $rental_status, $image_path, $image_back_path, $description, $detailed_description, $detail_image_1, $detail_image_2, $detail_image_3, $detail_image_4);
            
            if ($stmt->execute()) {
                $_SESSION['msg'] = "Thêm sản phẩm thành công!";
            } else {
                $_SESSION['error'] = "Lỗi Execute: " . $stmt->error;
            }
        } 
        elseif ($action == 'edit') {
            $id = $_POST['id'];
            
            // Nếu không upload ảnh mới, giữ nguyên ảnh cũ
            $sql_update = "UPDATE products SET name=?, category=?, brand=?, price_session=?, price_day=?, rental_status=?, description=?, detailed_description=?";
            $types = "sssddsss";
            $params = [$name, $category, $brand, $price_session, $price_day, $rental_status, $description, $detailed_description];

            if ($image_path) {
                $sql_update .= ", image=?";
                $types .= "s";
                $params[] = $image_path;
            }
            if ($image_back_path) {
                $sql_update .= ", image_back=?";
                $types .= "s";
                $params[] = $image_back_path;
            }
            
            // Thêm các ảnh chi tiết nếu có upload
            if ($detail_image_1) {
                $sql_update .= ", detail_image_1=?";
                $types .= "s";
                $params[] = $detail_image_1;
            }
            if ($detail_image_2) {
                $sql_update .= ", detail_image_2=?";
                $types .= "s";
                $params[] = $detail_image_2;
            }
            if ($detail_image_3) {
                $sql_update .= ", detail_image_3=?";
                $types .= "s";
                $params[] = $detail_image_3;
            }
            if ($detail_image_4) {
                $sql_update .= ", detail_image_4=?";
                $types .= "s";
                $params[] = $detail_image_4;
            }

            $sql_update .= " WHERE id=?";
            $types .= "i";
            $params[] = $id;

            $stmt = $conn->prepare($sql_update);
            if (!$stmt) {
                die("Lỗi SQL Update (Prepare failed): " . $conn->error . " | SQL: " . $sql_update);
            }

            $stmt->bind_param($types, ...$params);

            if ($stmt->execute()) {
                $_SESSION['msg'] = "Cập nhật sản phẩm thành công!";
            } else {
                $_SESSION['error'] = "Lỗi Update Execute: " . $stmt->error;
            }
        }
    }
    
    if ($action == 'delete') {
        $id = $_POST['id'];
        // TODO: Thêm code xóa file ảnh cũ trên server để giải phóng dung lượng
        // $stmt_get_img = $conn->prepare("SELECT image, image_back FROM products WHERE id=?"); ...
        // unlink($image_path);

        $sql = "DELETE FROM products WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            $_SESSION['msg'] = "Đã xóa sản phẩm!";
        } else {
            $_SESSION['error'] = "Lỗi xóa: " . $conn->error;
        }
    }

    if ($action == 'update_status') {
        ensureColumnsExist($conn);
        $id = $_POST['id'];
        $rental_status = $_POST['rental_status'];
        
        $sql = "UPDATE products SET rental_status=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $rental_status, $id);
        
        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Cập nhật trạng thái thành công!']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Lỗi update: ' . $conn->error]);
        }
        exit; // Dừng script ngay sau khi trả về JSON
    }
    
    // Redirect về trang admin
    header("Location: quan_ly_san_pham.php");
    exit();
} else {
    header("Location: quan_ly_san_pham.php");
    exit();
}
?>