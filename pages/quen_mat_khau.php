<?php
session_start();
$page_css = '../assets/css/style.css'; // Sử dụng lại CSS của trang đăng nhập
include '../includes/header.php';
?>

<center>
  <div class="login-container" style="margin-top: 50px; margin-bottom: 50px;">
    <h3>Quên Mật Khẩu</h3>
    <p style="font-size: 14px; color: #666; margin-bottom: 20px;">Nhập Tên đăng nhập và Email đã đăng ký để đặt lại mật khẩu.</p>

    <?php
    if(isset($_SESSION['error'])) {
        echo '<p style="color: red; margin-bottom: 10px;">'.$_SESSION['error'].'</p>';
        unset($_SESSION['error']);
    }
    ?>

    <form action="../actions/xu_ly_quen_mat_khau.php" method="POST">
        <div class="input-group">
            <input type="text" name="username" placeholder="Tên đăng nhập" required>
            <span class="icon">👤</span>
        </div>
        <div class="input-group">
            <input type="email" name="email" placeholder="Email đăng ký" required>
            <span class="icon">📧</span>
        </div>
        <hr style="margin: 15px 0; border: 0; border-top: 1px solid #eee;">
        <div class="input-group">
            <input type="password" name="new_password" placeholder="Mật khẩu mới" required>
            <span class="icon">🔒</span>
        </div>
        <div class="input-group">
            <input type="password" name="confirm_password" placeholder="Xác nhận mật khẩu mới" required>
            <span class="icon">🔒</span>
        </div>
        
        <button type="submit" class="login-btn">Đổi Mật Khẩu</button>
    </form>

    <div class="login-footer">
      <a href="dang_nhap.php">Quay lại Đăng nhập</a>
    </div>
  </div>
</center>

<?php include '../includes/footer.php'; ?>
