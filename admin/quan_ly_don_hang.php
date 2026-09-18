<?php
session_start();
include '../includes/db_connect.php';

// Bảo vệ trang: chỉ admin mới có quyền truy cập
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    die('Truy cập bị từ chối. Bạn không phải là quản trị viên.');
}

// Kiểm tra bảng orders có tồn tại không để tránh lỗi
$check_table = $conn->query("SHOW TABLES LIKE 'orders'");
if($check_table->num_rows == 0) {
    die('<div style="padding:20px;">Chưa có bảng dữ liệu đơn hàng. Vui lòng chạy câu lệnh SQL tạo bảng (xem hướng dẫn).</div>');
}

// Lấy danh sách đơn hàng kèm tổng quan sản phẩm
$sql = "SELECT o.*, 
        (SELECT GROUP_CONCAT(CONCAT(od.product_name, ' (x', od.quantity, ')') SEPARATOR ', ') 
         FROM order_details od WHERE od.order_id = o.id) as items_summary
        FROM orders o 
        ORDER BY o.created_at DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Đơn Hàng - Admin</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

<!-- Sidebar -->
<?php include '../includes/admin_sidebar.php'; ?>

<!-- Main Content -->
<div class="main-content">
    <div class="page-header">
        <h2>Quản Lý Đơn Hàng</h2>
    </div>

    <div class="admin-container">
    <?php if(isset($_SESSION['msg'])): ?>
        <div class="alert alert-success"><?php echo $_SESSION['msg']; unset($_SESSION['msg']); ?></div>
    <?php endif; ?>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Khách hàng</th>
                <th>Liên hệ</th>
                <th>Sản phẩm thuê</th>
                <th>Tổng tiền</th>
                <th>Ngày đặt</th>
                <th>Trạng thái</th>
                <th>Hành động</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result && $result->num_rows > 0): ?>
                <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td>#<?php echo $row['id']; ?></td>
                    <td>
                        <strong><?php echo htmlspecialchars($row['customer_name']); ?></strong><br>
                        <small style="color:#666;"><?php echo htmlspecialchars($row['address']); ?></small>
                    </td>
                    <td>
                        <?php echo htmlspecialchars($row['phone']); ?><br>
                        <small><?php echo htmlspecialchars($row['email']); ?></small>
                    </td>
                    <td style="max-width: 250px;">
                        <small><?php echo $row['items_summary'] ? htmlspecialchars($row['items_summary']) : 'Không có chi tiết'; ?></small>
                    </td>
                    <td style="color:#d33; font-weight:bold;"><?php echo number_format($row['total_money']); ?> đ</td>
                    <td><?php echo date('d/m/Y H:i', strtotime($row['created_at'])); ?></td>
                    <td>
                        <?php 
                            $status_class = 'bg-secondary';
                            if($row['status'] == 'Mới đặt') $status_class = 'bg-info';
                            elseif($row['status'] == 'Đã xác nhận') $status_class = 'bg-warning';
                            elseif($row['status'] == 'Hoàn thành') $status_class = 'bg-success';
                            elseif($row['status'] == 'Đã hủy') $status_class = 'bg-danger';
                        ?>
                        <span class="badge <?php echo $status_class; ?>"><?php echo $row['status']; ?></span>
                    </td>
                    <td>
                        <form action="../actions/xu_ly_don_hang.php" method="POST" style="display:inline;">
                            <input type="hidden" name="order_id" value="<?php echo $row['id']; ?>">
                            <select name="status" onchange="this.form.submit()" style="padding:4px; border-radius:4px; border:1px solid #ddd;">
                                <option value="">-- Xử lý --</option>
                                <option value="Đã xác nhận">Xác nhận</option>
                                <option value="Hoàn thành">Hoàn thành</option>
                                <option value="Đã hủy">Hủy đơn</option>
                            </select>
                            <input type="hidden" name="action" value="update_status">
                        </form>
                        <button class="btn btn-danger btn-sm" onclick="confirmDelete(<?php echo $row['id']; ?>)" style="margin-top:5px;">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="8" style="text-align:center;">Chưa có đơn hàng nào.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
    </div>
</div>

<form id="deleteForm" action="../actions/xu_ly_don_hang.php" method="POST" style="display:none;">
    <input type="hidden" name="action" value="delete">
    <input type="hidden" name="order_id" id="deleteId">
</form>

<script>
    function confirmDelete(id) {
        Swal.fire({
            title: 'Xóa đơn hàng?',
            text: "Hành động này không thể hoàn tác!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Xóa ngay'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('deleteId').value = id;
                document.getElementById('deleteForm').submit();
            }
        })
    }
</script>
</body>
</html>