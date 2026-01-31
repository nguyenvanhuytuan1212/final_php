<?php
session_start();
include '../includes/db_connect.php';

// Bảo vệ: chỉ admin mới có quyền truy cập
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    die('Truy cập bị từ chối. Bạn không phải là quản trị viên.');
}

// Đặt tên file và header để trình duyệt hiểu và cho phép tải về
$filename = "danh-sach-don-hang_" . date('Y-m-d') . ".csv";
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

// Mở output stream của PHP để ghi dữ liệu
$output = fopen('php://output', 'w');

// Thêm BOM (Byte Order Mark) để Excel có thể đọc file UTF-8 có dấu tiếng Việt
fputs($output, chr(239) . chr(187) . chr(191));

// Ghi dòng tiêu đề của file CSV
fputcsv($output, [
    'ID Đơn hàng', 
    'Tên Khách Hàng', 
    'Số Điện Thoại', 
    'Email', 
    'Địa Chỉ', 
    'Ghi Chú', 
    'Tổng Tiền', 
    'Trạng Thái', 
    'Ngày Đặt'
]);

// Lấy toàn bộ dữ liệu đơn hàng từ database
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';
$sql = "SELECT id, customer_name, phone, email, address, note, total_money, status, created_at FROM orders";

if (!empty($search_query)) {
    $search_term = "%" . $search_query . "%";
    $sql .= " WHERE id = ? OR customer_name LIKE ? OR phone LIKE ? OR email LIKE ?";
    $stmt = $conn->prepare($sql . " ORDER BY created_at DESC");
    $stmt->bind_param("ssss", $search_query, $search_term, $search_term, $search_term);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $sql .= " ORDER BY created_at DESC";
    $result = $conn->query($sql);
}

if ($result && $result->num_rows > 0) {
    // Lặp qua từng dòng kết quả và ghi vào file CSV
    while ($row = $result->fetch_assoc()) {
        // Sanitize data to prevent CSV Injection (Formula Injection)
        foreach ($row as $key => $value) {
            if (is_string($value) && !empty($value) && in_array($value[0], ['=', '+', '-', '@'])) {
                // Thêm một ký tự tab vào trước để Excel hiểu đây là text
                $row[$key] = "\t" . $value;
            }
        }
        $csv_row = [
            $row['id'],
            $row['customer_name'],
            $row['phone'],
            $row['email'],
            $row['address'],
            $row['note'],
            $row['total_money'],
            $row['status'],
            date('d/m/Y H:i', strtotime($row['created_at']))
        ];
        fputcsv($output, $csv_row);
    }
}

fclose($output);
exit();
?>