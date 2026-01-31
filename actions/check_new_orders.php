<?php
session_start();
include '../includes/db_connect.php';

header('Content-Type: application/json');

// Chỉ cho phép admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$last_id = isset($_GET['last_id']) ? intval($_GET['last_id']) : 0;

// Đếm tổng số đơn hàng đang chờ (status = 'Mới đặt')
$res_total = $conn->query("SELECT COUNT(id) as total FROM orders WHERE status = 'Mới đặt'");
$total_pending = intval($res_total->fetch_assoc()['total'] ?? 0);

// Tìm ID lớn nhất hiện tại
$res_max = $conn->query("SELECT MAX(id) as max_id FROM orders");
$current_max_id = 0;
if ($res_max && $row = $res_max->fetch_assoc()) {
    $current_max_id = intval($row['max_id']);
}

// Trả về kết quả
$response = [
    'new_orders' => 0,
    'total_pending' => $total_pending,
    'latest_id' => $current_max_id,
    'orders' => [],
    'pending_list' => []
];

// Lấy danh sách 5 đơn hàng mới nhất đang chờ (để hiển thị toast nếu cần)
if ($total_pending > 0) {
    $res_pending = $conn->query("SELECT id, customer_name, total_money, created_at FROM orders WHERE status = 'Mới đặt' ORDER BY id DESC LIMIT 5");
    while ($row = $res_pending->fetch_assoc()) {
        $response['pending_list'][] = [
            'id' => $row['id'],
            'customer' => $row['customer_name'],
            'total' => number_format($row['total_money']) . ' đ',
            'time' => date('H:i', strtotime($row['created_at']))
        ];
    }
}

// Nếu là lần đầu tiên check (last_id = 0), trả về thông tin để UI biết có bao nhiêu đơn đang chờ
if ($last_id == 0) {
    echo json_encode($response);
    exit();
}

// Nếu có ID mới lớn hơn last_id
if ($last_id > 0 && $current_max_id > $last_id) {
    $stmt = $conn->prepare("SELECT id, customer_name, total_money FROM orders WHERE id > ? AND status = 'Mới đặt' ORDER BY id ASC");
    $stmt->bind_param("i", $last_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $response['orders'][] = [
            'id' => $row['id'],
            'customer' => $row['customer_name'],
            'total' => number_format($row['total_money']) . ' đ'
        ];
    }
    
    $response['new_orders'] = count($response['orders']);
}

echo json_encode($response);
?>
