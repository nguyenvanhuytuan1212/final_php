<?php
session_start();
$page_css = '../assets/css/style.css';
include '../includes/header.php';
?>

<style>
    .error-msg { color: red; text-align: center; }
    .success-msg { color: green; text-align: center; }
</style>

 <center>
  <div class="login-container">
    <h3>Đăng ký tài khoản BIN BIN Camera</h3>
    
    <?php
    if(isset($_SESSION['register_errors'])) {
        foreach($_SESSION['register_errors'] as $err) {
            echo '<p class="error-msg">'.$err.'</p>';
        }
        unset($_SESSION['register_errors']);
    }
    ?>
    <br>
    <form action="../actions/register.php" method="POST">
        <div class="input-group">
          <input type="text" name="username" placeholder="Tên đăng nhập" required>
          <span class="icon"><i class="fa-solid fa-user"></i></span>
        </div>
        <div class="input-group">
            <input type="text" name="fullname" placeholder="Họ và tên đầy đủ" required>
            <span class="icon"><i class="fa-solid fa-signature"></i></span>
        </div>
        <div class="input-group">
            <input type="password" name="password" placeholder="Mật khẩu" required>
            <span class="icon"><i class="fa-solid fa-lock"></i></span>
        </div>
          <div class="input-group">
            <input type="password" name="re_password" placeholder="Nhập lại mật khẩu" required>
            <span class="icon"><i class="fa-solid fa-lock"></i></span>
        </div>
        <div class="input-group">
            <input type="text" name="phone" placeholder="Số điện thoại">
            <span class="icon"><i class="fa-solid fa-phone"></i></span>
          </div>    
        <div class="input-group">
            <input type="email" name="email" placeholder="Gmail của bạn" required>
            <span class="icon"><i class="fa-solid fa-envelope"></i></span>
          </div>    
        
        <button type="submit" class="login-btn">Đăng Ký</button>
    </form>

    <h2></h2>
    <div class="social-login">
        <button class="facebook-btn">
          <i class="fab fa-facebook-f"></i> Facebook
        </button>
        <button class="google-btn">
          <i class="fab fa-google"></i> Google
        </button>
      </div>
    
      <div class="login-footer">
        <a href="dang_nhap.php" style="font-weight: normal;">Đã có tài khoản? Đăng nhập ngay</a>
      </div>
      
    </div>
</center> 
 
<br><br><br>

<?php include '../includes/footer.php'; ?>
