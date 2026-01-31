<?php
session_start();
include '../includes/db_connect.php';

// 1. Bảo vệ trang, yêu cầu người dùng phải đăng nhập
if (!isset($_SESSION['user_id'])) {
    header("Location: dang_nhap.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// 2. Lấy danh sách đơn hàng của người dùng hiện tại từ CSDL
$sql = "SELECT o.*, 
        (SELECT GROUP_CONCAT(CONCAT(od.product_name, ' (x', od.quantity, ')') SEPARATOR '; ') 
         FROM order_details od WHERE od.order_id = o.id) as items_summary 
        FROM orders o 
        WHERE o.user_id = ? 
        ORDER BY o.created_at DESC";
        
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

// Sử dụng CSS đồng bộ với trang dat_hang_thanh_cong
$page_css = ['../CSS/lien_he.css', '../CSS/tai_khoan.css', '../CSS/admin.css']; 
include '../includes/header.php';
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>

<style>
    /* Đảm bảo sidebar và main-content của trang tài khoản không bị ảnh hưởng bởi admin.css */
    .account-container .sidebar {
        position: static !important;
        width: 280px !important;
        background: #0f172a !important;
        padding: 48px 24px !important;
        display: flex !important;
        flex-direction: column !important;
        z-index: auto !important;
    }
    .account-container .main-content {
        margin-left: 0 !important;
        flex: 1 !important;
        padding: 56px 64px !important;
        min-height: auto !important;
        background: rgba(255, 255, 255, 0.4) !important;
    }
    /* Style cho bảng lịch sử */
    .admin-container table {
        background: white;
        border-radius: 12px;
        overflow: hidden;
    }
    .admin-container thead th {
        background: #f8fafc;
        color: #64748b;
        font-weight: 700;
        text-transform: uppercase;
        font-size: 12px;
    }
</style>

<div class="account-container">
    <div class="sidebar">
        <p>Xin chào <?php echo isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'Quý khách'; ?></p>
        <ul>
            <li><a href="dat_hang_thanh_cong.php" style="color: #ffffff !important;">Thông tin giao hàng</a></li>
            <li><a href="change_password.php" style="color: #ffffff !important;">Đổi mật khẩu</a></li>
            <li><a href="lich_su_thue.php" class="active" style="color: #ffffff !important;">Lịch sử thuê</a></li>
            <li><a href="../actions/logout.php" style="color: #ffffff !important;">Đăng xuất</a></li>
        </ul>
    </div>

    <div class="main-content">
        <h2>Lịch sử thuê sản phẩm</h2>
        
        <?php if(isset($_SESSION['msg'])): ?>
            <div style="background-color: #d4edda; color: #155724; padding: 10px; margin-bottom: 15px; border-radius: 4px;"><?php echo $_SESSION['msg']; unset($_SESSION['msg']); ?></div>
        <?php endif; ?>
        <?php if(isset($_SESSION['error'])): ?>
            <div style="background-color: #f8d7da; color: #721c24; padding: 10px; margin-bottom: 15px; border-radius: 4px;"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>

        <div class="admin-container" style="padding: 0; box-shadow: none; border-radius: 8px;">
            <table>
                <thead>
                    <tr>
                        <th>Mã Đơn</th>
                        <th>Ngày Đặt</th>
                        <th style="width: 40%;">Sản phẩm đã thuê</th>
                        <th>Tổng Tiền</th>
                        <th>Trạng Thái</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td>#<?php echo $row['id']; ?></td>
                            <td><?php echo date('d/m/Y H:i', strtotime($row['created_at'])); ?></td>
                            <td style="font-size: 0.9em; max-width: 300px; word-wrap: break-word;"><?php echo htmlspecialchars($row['items_summary'] ?? 'Chưa có chi tiết'); ?></td>
                            <td style="color:#d33; font-weight:bold;"><?php echo number_format($row['total_money']); ?> đ</td>
                            <td>
                                <?php 
                                    $status_map = ['Mới đặt' => 'bg-info', 'Đã xác nhận' => 'bg-warning', 'Hoàn thành' => 'bg-success', 'Đã hủy' => 'bg-danger'];
                                    $status_class = $status_map[$row['status']] ?? 'bg-secondary';
                                ?>
                                <span class="badge <?php echo $status_class; ?>"><?php echo htmlspecialchars($row['status']); ?></span>
                            </td>
                            <td>
                                <?php if($row['status'] == 'Mới đặt'): ?>
                                    <form action="../actions/user_cancel_order.php" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn hủy đơn hàng này?');">
                                        <input type="hidden" name="order_id" value="<?php echo $row['id']; ?>">
                                        <button type="submit" class="btn btn-danger btn-sm" style="padding: 5px 10px; font-size: 12px; cursor: pointer;">Hủy đơn</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="6" style="text-align:center; padding: 20px;">Bạn chưa có đơn hàng nào.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Bài viết -->


<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="../JS/account.js"></script>
<?php include '../includes/footer.php'; ?>
