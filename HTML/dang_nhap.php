<?php
session_start();
// Kiểm tra nếu đã đăng nhập thì chuyển hướng ngay lập tức
if (isset($_SESSION['user_id'])) {
    if (isset($_SESSION['role']) && $_SESSION['role'] == 'admin') {
        header("Location: dashboard.php"); // Admin về Dashboard
    } else {
        header("Location: trang_chu.php"); // User thường về Trang chủ
    }
    exit();
}
$page_css = '../CSS/TUAN2.CSS';
include '../includes/header.php';
?>
<br><br><br>  

 <center><div class="login-container">
    <h3>Đăng nhập BIN BIN Camera Rental</h3>

    <?php
    if(isset($_SESSION['login_error'])) {
        echo '<p style="color: red; margin-bottom: 10px;">'.$_SESSION['login_error'].'</p>';
        unset($_SESSION['login_error']);
    }
    if(isset($_SESSION['success_message'])) {
        echo '<p style="color: green; margin-bottom: 10px;">'.$_SESSION['success_message'].'</p>';
        unset($_SESSION['success_message']);
    }
    ?>

    <form action="../actions/login.php" method="POST">
        <div class="input-group">
        <input type="text" name="login_input" placeholder="Tên đăng nhập, Email hoặc SDT" required>
        <span class="icon"><i class="fa-solid fa-envelope"></i></span>
        </div>

        <div class="input-group">
        <input type="password" name="password" placeholder="Mật khẩu" required>
        <span class="icon"><i class="fa-solid fa-lock"></i></span>
        </div>
        <button type="submit" class="login-btn">Đăng Nhập</button>
    </form>

    <div class="login-footer">
      <a href="quen_mat_khau.php" style="font-weight: normal;">Quên mật khẩu</a>
      <a href="dang_ky.php" style="font-weight: normal;">Đăng ký tài khoản</a>
    </div>
  </div>
</center> 
 
<br><br><br>

<?php include '../includes/footer.php'; ?>
