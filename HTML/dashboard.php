<?php
session_start();
include '../includes/db_connect.php';

// Bảo vệ trang: chỉ admin mới có quyền truy cập
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    die('Truy cập bị từ chối. Bạn không phải là quản trị viên.');
}

// --- Lấy dữ liệu thống kê ---

// 1. Tổng số sản phẩm
$result_products = $conn->query("SELECT COUNT(id) as total FROM products");
$total_products = $result_products->fetch_assoc()['total'];

// 2. Tổng số đơn hàng
$result_orders = $conn->query("SELECT COUNT(id) as total FROM orders");
$total_orders = $result_orders->fetch_assoc()['total'];

// 3. Tổng doanh thu (chỉ tính đơn đã hoàn thành)
$result_revenue = $conn->query("SELECT SUM(total_money) as total FROM orders WHERE status = 'Hoàn thành'");
$total_revenue = $result_revenue->fetch_assoc()['total'] ?? 0;

// 4. Đơn hàng mới
$result_new_orders = $conn->query("SELECT COUNT(id) as total FROM orders WHERE status = 'Mới đặt'");
$new_orders_count = $result_new_orders->fetch_assoc()['total'];

// 5. Tổng số khách hàng (người dùng)
$result_customers = $conn->query("SELECT COUNT(id) as total FROM users WHERE role = 'user'");
$total_customers = $result_customers->fetch_assoc()['total'];

// 6. Đơn hàng gần đây
$recent_orders = $conn->query("SELECT * FROM orders ORDER BY created_at DESC LIMIT 5");

?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Admin</title>
    <link rel="stylesheet" href="../CSS/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

<!-- Sidebar -->
<?php include '../includes/admin_sidebar.php'; ?>

<!-- Main Content -->
<div class="main-content">
    <div class="page-header">
        <h2>Bảng điều khiển</h2>
    </div>

    <!-- Các thẻ thống kê -->
    <div class="dashboard-cards">
        <div class="card">
            <div class="card-body">
                <div class="card-text">
                    <h4>Tổng Sản Phẩm</h4>
                    <span><?php echo $total_products; ?></span>
                </div>
                <div class="card-icon"><i class="fas fa-box"></i></div>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <div class="card-text">
                    <h4>Tổng Doanh Thu</h4>
                    <span><?php echo number_format($total_revenue); ?> đ</span>
                </div>
                <div class="card-icon"><i class="fas fa-dollar-sign"></i></div>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <div class="card-text">
                    <h4>Tổng Đơn Hàng</h4>
                    <span><?php echo $total_orders; ?></span>
                </div>
                <div class="card-icon"><i class="fas fa-clipboard-list"></i></div>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <div class="card-text">
                    <h4>Đơn Hàng Mới</h4>
                    <span><?php echo $new_orders_count; ?></span>
                </div>
                <div class="card-icon"><i class="fas fa-inbox"></i></div>
            </div>
        </div>
        <div class="card" style="border-left-color: #6610f2;">
            <div class="card-body">
                <div class="card-text">
                    <h4>Tổng Khách Hàng</h4>
                    <span><?php echo $total_customers; ?></span>
                </div>
                <div class="card-icon"><i class="fas fa-users"></i></div>
            </div>
        </div>
    </div>

    <!-- Bảng đơn hàng gần đây -->
    <div class="admin-container">
        <h3><i class="fas fa-history"></i> Đơn hàng gần đây</h3>
        <table>
            <thead>
                <tr><th>ID</th><th>Khách hàng</th><th>Tổng tiền</th><th>Ngày đặt</th><th>Trạng thái</th></tr>
            </thead>
            <tbody>
                <?php if ($recent_orders && $recent_orders->num_rows > 0): ?>
                    <?php while($row = $recent_orders->fetch_assoc()): ?>
                    <tr>
                        <td>#<?php echo $row['id']; ?></td>
                        <td><?php echo htmlspecialchars($row['customer_name']); ?></td>
                        <td style="color:red;"><?php echo number_format($row['total_money']); ?> đ</td>
                        <td><?php echo date('d/m/Y', strtotime($row['created_at'])); ?></td>
                        <td>
                            <?php 
                                $status_class = 'bg-secondary';
                                if($row['status'] == 'Mới đặt') $status_class = 'bg-info';
                                elseif($row['status'] == 'Hoàn thành') $status_class = 'bg-success';
                                elseif($row['status'] == 'Đã hủy') $status_class = 'bg-danger';
                            ?>
                            <span class="badge <?php echo $status_class; ?>"><?php echo $row['status']; ?></span>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="5" style="text-align:center;">Chưa có đơn hàng nào.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>
