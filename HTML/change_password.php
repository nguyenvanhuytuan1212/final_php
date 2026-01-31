<?php
session_start();
// Nếu chưa đăng nhập, chuyển hướng về trang đăng nhập
if (!isset($_SESSION['user_id'])) {
    header("Location: dang_nhap.php");
    exit();
}

$page_css = '../CSS/TUAN2.CSS'; // Sử dụng chung CSS với trang Đăng nhập
include '../includes/header.php';
?>
<br><br><br>  

<center>
    <div class="login-container">
        <h3>Đổi Mật Khẩu</h3>
        <p style="margin-bottom: 20px;">Xin chào, <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong>.</p>

        <?php if (isset($_SESSION['password_error'])): ?>
            <p style="color: red; margin-bottom: 10px;"><?php echo $_SESSION['password_error']; unset($_SESSION['password_error']); ?></p>
        <?php endif; ?>
        <?php if (isset($_SESSION['password_success'])): ?>
            <p style="color: green; margin-bottom: 10px;"><?php echo $_SESSION['password_success']; unset($_SESSION['password_success']); ?></p>
        <?php endif; ?>

        <form action="../actions/xu_ly_doi_mat_khau.php" method="POST">
            <div class="input-group">
                <input type="password" name="current_password" placeholder="Mật khẩu hiện tại" required>
                <span class="icon">🔒</span>
            </div>

            <div class="input-group">
                <input type="password" name="new_password" placeholder="Mật khẩu mới" required>
                <span class="icon">🔑</span>
            </div>

            <div class="input-group">
                <input type="password" name="confirm_password" placeholder="Xác nhận mật khẩu mới" required>
                <span class="icon">✔️</span>
            </div>

            <button type="submit" class="login-btn">Cập Nhật Mật Khẩu</button>
        </form>

        <div class="login-footer">
             <a href="trang_chu.php" style="font-weight: normal;">Quay lại Tranh chủ</a>
        </div>
    </div>
</center>

<br><br><br>


<?php include '../includes/footer.php'; ?>
