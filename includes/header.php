<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$cart_count = 0;
if(isset($_SESSION['cart'])) {
    foreach($_SESSION['cart'] as $item) {
        $cart_count += $item['quantity'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>THUÊ MÁY ẢNH ĐÀ Nẵng</title>
    <!-- Default CSS -->
    <link rel="stylesheet" href="../CSS/tuan2.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/normalize/8.0.1/normalize.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <?php 
    if(isset($page_css)) { 
        if(is_array($page_css)) {
            foreach($page_css as $css) {
                echo '<link rel="stylesheet" href="'.$css.'">';
            }
        } else {
             echo '<link rel="stylesheet" href="'.$page_css.'">';
        }
    } 
    ?>
</head>
<body>
  <div class="header">
    <div class="logo">
      <a href="trang_chu.php" style="text-decoration: none; color: inherit;">
        <span>BINBIN</span><br>
        <small>CAMERA </small>
      </a>
    </div>
    <div class="nav">
      <ul class="navbar">
        <li>
          <a href="gioi_thieu.php">Giới thiệu</a>
        </li>
        <li>
          <a href="#">Dịch vụ cho thuê ▼</a>
          <ul class="dropdown">
            <li><a class="dropdown-item" href="thue_camera.php">Thuê Camera (Máy ảnh)</a></li>
            <li><a class="dropdown-item" href="thue_lens.php">Thuê Lens (Ống kính)</a></li>
            <li><a class="dropdown-item" href="thue_phu_kien.php">Thuê Phụ kiện</a></li>
            <li><a class="dropdown-item" href="thue_den.php">Thuê Đèn chụp</a></li>
            <li><a class="dropdown-item" href="thue_gimbal.php">Thuê Gimbal</a></li>
          </ul>
        </li>
        <li>
          <a href="chinh_sac.php">Chính sách</a>
        </li>
        <li>
          <a href="lien_he.php">Liên hệ</a>
        </li>
      </ul>
    </div>
    
    <div class="nav">
      <div class="cart">
        <a href="gio_hang.php">
          <i class="fa-solid fa-cart-shopping"></i>
          <span class="count" id="cart-count"><?php echo $cart_count; ?></span>
        </a>
      </div>
      <?php if(isset($_SESSION['user_id'])): ?>
         <div class="user-section">
             <a href="dat_hang_thanh_cong.php" class="user-info" style="text-decoration: none; color: inherit;">
                 <i class="fas fa-user"></i> <b><?php echo htmlspecialchars($_SESSION['username']); ?></b>
             </a>
         </div>
      <?php else: ?>
         <a href="dang_nhap.php" class="login-link"><i class="fas fa-user"></i> <b>Đăng nhập / Đăng ký</b></a>
      <?php endif; ?>
    </div>
  </div>

  <?php if(isset($_SESSION['success_msg'])): ?>
    <script>
        Swal.fire({
            title: 'Thành công!',
            text: '<?php echo $_SESSION['success_msg']; ?>',
            icon: 'success',
            confirmButtonText: 'OK'
        });
    </script>
    <?php unset($_SESSION['success_msg']); ?>
  <?php endif; ?>
