<?php
session_start();
include '../includes/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../HTML/dang_nhap.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $order_id = intval($_POST['order_id']);
    $user_id = $_SESSION['user_id'];

    // Kiểm tra đơn hàng có thuộc về user này và đang ở trạng thái 'Mới đặt' không
    $stmt = $conn->prepare("SELECT id, status FROM orders WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $order_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        if ($row['status'] == 'Mới đặt') {
            // Thực hiện hủy
            $update_stmt = $conn->prepare("UPDATE orders SET status = 'Đã hủy' WHERE id = ?");
            $update_stmt->bind_param("i", $order_id);
            if ($update_stmt->execute()) {
                $_SESSION['msg'] = "Đã hủy đơn hàng #$order_id thành công.";
            } else {
                $_SESSION['error'] = "Lỗi hệ thống, không thể hủy đơn.";
            }
        } else {
            $_SESSION['error'] = "Không thể hủy đơn hàng này (đã được xử lý hoặc đã hủy).";
        }
    } else {
        $_SESSION['error'] = "Đơn hàng không tồn tại.";
    }
}

header("Location: ../HTML/lich_su_thue.php");
exit();
?>