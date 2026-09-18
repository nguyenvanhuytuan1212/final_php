<?php
// Sử dụng CSS của trang liên hệ hoặc tạo mới, ở đây dùng tạm CSS có sẵn để hiển thị form đẹp
$page_css = ['../assets/css/lien_he.css', '../assets/css/tai_khoan.css']; 
include '../includes/header.php';

// Tự động lấy thông tin nếu đã đăng nhập
$user_email = '';
$user_full_name = '';
$user_phone = '';
$user_address = '';

if (isset($_SESSION['user_id'])) {
    include_once '../includes/db_connect.php';
    $u_id = $_SESSION['user_id'];
    $u_stmt = $conn->prepare("SELECT email, full_name, phone, address FROM users WHERE id = ?");
    $u_stmt->bind_param("i", $u_id);
    $u_stmt->execute();
    $u_res = $u_stmt->get_result();
    if ($u_row = $u_res->fetch_assoc()) {
        $user_email = $u_row['email'];
        $user_full_name = $u_row['full_name'];
        $user_phone = $u_row['phone'];
        $user_address = $u_row['address'];
    }
}
?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
<!-- tìm kiếm -->

    <div class="account-container">
        <div class="sidebar">
            <p>Xin chào <?php echo isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'Quý khách'; ?></p>
            <ul>
                <li><a href="dat_hang_thanh_cong.php" class="active" style="color: #ffffff !important;">Thông tin giao hàng</a></li>
                <li><a href="change_password.php" style="color: #ffffff !important;">Đổi mật khẩu</a></li>
                <li><a href="lich_su_thue.php" style="color: #ffffff !important;">Lịch sử thuê</a></li>
                <li><a href="../actions/logout.php" style="color: #ffffff !important;">Đăng xuất</a></li>
            </ul>
        </div>

        <div class="main-content">
            <h2>Thông tin giao hàng</h2>
            <form id="accountForm" action="../actions/place_order.php" method="POST" enctype="multipart/form-data">
                <label>Email/Gmail:</label>
                <input type="email" name="email" placeholder="Email/Gmail" value="<?php echo htmlspecialchars($user_email); ?>" required>

                <label>Họ Tên:</label>
                <input type="text" name="name" placeholder="Họ và Tên" value="<?php echo htmlspecialchars($user_full_name); ?>" required>

                <label>Số điện thoại:</label>
                <input type="tel" name="phone" placeholder="Số điện thoại" value="<?php echo htmlspecialchars($user_phone); ?>" required>

                <label>Địa chỉ:</label>
                <input type="text" name="address" placeholder="Địa chỉ nhận hàng" value="<?php echo htmlspecialchars($user_address); ?>" required>

                <label>Ghi chú (CMND/CCCD):</label>
                <input type="text" name="note" placeholder="Số CMND/CCCD để làm thủ tục thuê">

                <label>Link Facebook:</label>
                <input type="url" name="facebook" placeholder="Facebook URL">

                <label>Ảnh CMND/CCCD:</label>
                <input type="file" name="image_cmnd" accept="image/*" onchange="previewImage(event, 'preview1')">
                <img id="preview1" class="image-preview" alt="Xem trước ảnh">

                <label>Giấy tờ cá nhân khác 1:</label>
                <input type="file" name="image_doc1" accept="image/*" onchange="previewImage(event, 'preview2')">
                <img id="preview2" class="image-preview" alt="Xem trước ảnh">

                <label>Giấy tờ cá nhân khác 2:</label>
                <input type="file" name="image_doc2" accept="image/*" onchange="previewImage(event, 'preview3')">
                <img id="preview3" class="image-preview" alt="Xem trước ảnh">

                <button type="submit">XÁC NHẬN ĐẶT HÀNG</button>
                
            </form>
        </div>
    </div>
  
<!-- Bài viết -->

</div>
<!-- Nhúng SweetAlert2 và Script xử lý -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="../assets/js/account.js"></script>
<?php include '../includes/footer.php'; ?>
