<?php
session_start();
include '../includes/db_connect.php';

// Bảo vệ trang: chỉ admin mới có quyền truy cập
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    die('Truy cập bị từ chối. Bạn không phải là quản trị viên.');
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    $order_id = intval($_POST['order_id']);

    if ($action == 'update_status') {
        $status = $_POST['status'];
        if (!empty($status)) {
            $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
            $stmt->bind_param("si", $status, $order_id);
            if ($stmt->execute()) {
                $_SESSION['msg'] = "Đã cập nhật trạng thái đơn hàng #$order_id thành: $status";
            }
        }
    } 
    elseif ($action == 'delete') {
        // Xóa chi tiết đơn hàng trước (an toàn hơn khi không chắc có ON DELETE CASCADE)
        $stmt_details = $conn->prepare("DELETE FROM order_details WHERE order_id = ?");
        $stmt_details->bind_param("i", $order_id);
        $stmt_details->execute();
        $stmt_details->close();

        // Xóa đơn hàng chính
        $stmt = $conn->prepare("DELETE FROM orders WHERE id = ?");
        $stmt->bind_param("i", $order_id);
        if ($stmt->execute()) {
            $_SESSION['msg'] = "Đã xóa đơn hàng #$order_id";
        }
    }

    header("Location: ../admin/quan_ly_don_hang.php");
    exit();
}
?>