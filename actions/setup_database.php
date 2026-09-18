<?php
include '../includes/db_connect.php';

// 1. Bảng Users
$sql_users = "CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    phone VARCHAR(20),
    full_name VARCHAR(100),
    role VARCHAR(20) DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
$conn->query($sql_users);

// 2. Bảng Products
$sql_products = "CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    category VARCHAR(50),
    brand VARCHAR(50),
    price_session DECIMAL(15,2),
    price_day DECIMAL(15,2),
    image VARCHAR(255),
    image_back VARCHAR(255),
    description TEXT,
    detailed_description TEXT,
    detail_image_1 VARCHAR(255),
    detail_image_2 VARCHAR(255),
    detail_image_3 VARCHAR(255),
    detail_image_4 VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ";
$conn->query($sql_products);

// --- Cập nhật nếu bảng đã tồn tại nhưng thiếu cột ---
$cols_to_add = [
    'detailed_description' => "TEXT",
    'detail_image_1' => "VARCHAR(255)",
    'detail_image_2' => "VARCHAR(255)",
    'detail_image_3' => "VARCHAR(255)",
    'detail_image_4' => "VARCHAR(255)"
];

foreach ($cols_to_add as $col => $type) {
    $check = $conn->query("SHOW COLUMNS FROM products LIKE '$col'");
    if ($check->num_rows == 0) {
        $conn->query("ALTER TABLE products ADD COLUMN $col $type");
        echo "<div>Đã thêm cột <b>$col</b> vào bảng products.</div>";
    }
}

// 3. Bảng Orders (Đơn hàng)
$sql_orders = "CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    customer_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    email VARCHAR(100),
    address VARCHAR(255),
    note TEXT,
    total_money DECIMAL(15,2) DEFAULT 0,
    status VARCHAR(50) DEFAULT 'Mới đặt',
    images TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
$conn->query($sql_orders);

// 4. Bảng Order Details (Chi tiết đơn hàng)
$sql_order_details = "CREATE TABLE IF NOT EXISTS order_details (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT,
    product_id INT,
    product_name VARCHAR(255),
    quantity INT,
    price DECIMAL(15,2),
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
)";
$conn->query($sql_order_details);

// --- FIX LỖI: Cập nhật cấu trúc bảng cũ nếu thiếu cột product_name ---
$check_col = $conn->query("SHOW COLUMNS FROM order_details LIKE 'product_name'");
if ($check_col && $check_col->num_rows == 0) {
    // Thêm cột nếu chưa có
    $conn->query("ALTER TABLE order_details ADD COLUMN product_name VARCHAR(255) AFTER product_id");
    echo "<div>Đã thêm cột <b>product_name</b> vào bảng order_details.</div>";
    
    // Cập nhật dữ liệu tên sản phẩm từ bảng products cho các dòng cũ
    $update_sql = "UPDATE order_details od 
                   JOIN products p ON od.product_id = p.id 
                   SET od.product_name = p.name 
                   WHERE od.product_name IS NULL OR od.product_name = ''";
    if($conn->query($update_sql)) {
        echo "<div>Đã cập nhật dữ liệu tên sản phẩm cho các đơn hàng cũ.</div>";
    }
}

// --- FIX LỖI: Đồng bộ hóa cột cho bảng ORDERS (do xung đột với file setup cũ) ---
$missing_cols = [];

// 1. Xử lý cột PHONE
$check_phone = $conn->query("SHOW COLUMNS FROM orders LIKE 'phone'");
if ($check_phone->num_rows == 0) {
    // Kiểm tra xem có customer_phone không
    $check_old = $conn->query("SHOW COLUMNS FROM orders LIKE 'customer_phone'");
    if ($check_old->num_rows > 0) {
        $conn->query("ALTER TABLE orders CHANGE COLUMN customer_phone phone VARCHAR(20)");
        echo "<div>Đã đổi tên cột <b>customer_phone</b> thành <b>phone</b>.</div>";
    } else {
        $conn->query("ALTER TABLE orders ADD COLUMN phone VARCHAR(20) AFTER customer_name");
        echo "<div>Đã thêm cột <b>phone</b>.</div>";
    }
}

// 2. Xử lý cột ADDRESS
$check_addr = $conn->query("SHOW COLUMNS FROM orders LIKE 'address'");
if ($check_addr->num_rows == 0) {
     $check_old = $conn->query("SHOW COLUMNS FROM orders LIKE 'customer_address'");
    if ($check_old->num_rows > 0) {
        $conn->query("ALTER TABLE orders CHANGE COLUMN customer_address address VARCHAR(255)");
        echo "<div>Đã đổi tên cột <b>customer_address</b> thành <b>address</b>.</div>";
    } else {
        $conn->query("ALTER TABLE orders ADD COLUMN address VARCHAR(255)");
        echo "<div>Đã thêm cột <b>address</b>.</div>";
    }
}

// 3. Các cột bắt buộc khác
$required_cols = [
    'email' => "VARCHAR(100)",
    'note' => "TEXT",
    'facebook' => "VARCHAR(255)",
    'images' => "TEXT",
    'total_money' => "DECIMAL(15,2) DEFAULT 0"
];

foreach ($required_cols as $col => $def) {
    $check = $conn->query("SHOW COLUMNS FROM orders LIKE '$col'");
    if ($check->num_rows == 0) {
        $conn->query("ALTER TABLE orders ADD COLUMN $col $def");
        echo "<div>Đã thêm cột <b>$col</b>.</div>";
    } else if ($col == 'total_money') {
        // Đảm bảo kiểu dữ liệu đúng
        $conn->query("ALTER TABLE orders MODIFY COLUMN total_money $def");
    }
}
// ---------------------------------------------------------------------
// ---------------------------------------------------------------------

// Tạo tài khoản admin mặc định nếu chưa có
$check_admin = $conn->query("SELECT * FROM users WHERE username = 'admin'");
if ($check_admin->num_rows == 0) {
    $pass = password_hash('admin123', PASSWORD_DEFAULT);
    $conn->query("INSERT INTO users (username, password, email, role) VALUES ('admin', '$pass', 'admin@binbincamera.com', 'admin')");
    echo "Đã tạo tài khoản Admin (admin/admin123)<br>";
}

echo "<h3>Cài đặt cơ sở dữ liệu hoàn tất!</h3>";
echo "<a href='../pages/trang_chu.php'>Về trang chủ</a>";
?>