<?php
// Define page-specific CSS if needed
$page_css = '../assets/css/style.css'; // Include page specific CSS but header already has it?
// The header.php has logic to include $page_css.
// But tuan2.css is already in header.php hardcoded? 
// Let's check header.php content again.
// Yes, I put <link rel="stylesheet" href="/CSS/tuan2.css"> in header.php.
// So I don't need to pass it unless I want extra.
include '../includes/header.php';
?>

  <h1></h1>
  <h1></h1>

<div id="slideshow">
    <img src="../assets/images/banners/anh_bia.jpeg" class="active" alt="Ảnh 1">
    <img src="../assets/images/banners/banner_slide2.png" alt="Ảnh 2">
    <img src="../assets/images/banners/banner_slide3.png" alt="Ảnh 3">
</div>
<script src="../assets/js/slideshow.js"></script>
  <div class="section-header">
    <div class="section-title">DỊCH VỤ CHO THUÊ / BÁN MÀY ẢNH</div>
    <a href="#" class="see-more">Xem thêm</a>
  </div>

    <div class="grid">
      <a href="thue_camera.php" class="item">
        <img src="../assets/images/banners/camera_banner.jpeg" alt="Máy ảnh"><span>Máy Ảnh</span>
      </a>

      <a href="thue_lens.php" class="item">
        <img src="../assets/images/banners/lens_banner.jpeg" alt="Ống kính"><span>Ống Kính</span>
      </a>

      <a href="thue_phu_kien.php" class="item">
        <img src="../assets/images/banners/phu_kien.jpeg" alt="Phụ kiện"><span>Phụ Kiện</span>
      </a>

      <a href="thue_den.php" class="item">
        <img src="../assets/images/banners/den_chup.jpeg" alt="Đèn chụp"><span>Đèn chụp</span>
      </a>

      <a href="thue_gimbal.php" class="item">
        <img src="../assets/images/banners/gimbal.jpeg" alt="Gimbal"><span>Gimbal</span>
      </a>

      <a href="thue_micro.php" class="item">
        <img src="../assets/images/banners/micro.jpeg" alt="Micro"><span>Micro</span>
      </a>

      <a href="thue_den.php" class="item">
        <img src="../assets/images/banners/den_quay.jpeg" alt="Đèn quay"><span>Đèn quay</span>
      </a>

      <a href="thue_flycam.php" class="item">
        <img src="../assets/images/banners/flycam.jpeg" alt="Flycam"><span>Flycam|Gopro|Pocket</span>
      </a>

      <a href="thue_phu_kien.php" class="item">
        <img src="../assets/images/banners/phu_kien_quay.jpeg" alt="Phụ kiện quay"><span>Phụ kiện quay</span>
      </a>
      <a href="" class="iteam"></a>
      <a href="thue_digital.php" class="item">
        <img src="../assets/images/banners/digital_camera.jpeg" alt="Digital Camera"><span>Digital Camera</span>
      </a>
    </div>
  </div>
  <!-- Bài viết -->
<div class="section-header">
  <h2 class="section-title">Bài viết</h2>
  <a href="#" class="see-more">Xem thêm</a> 
</div>

<div class="post-grid">
  <div class="post-item">
    <img src="../assets/images/banners/huong_dan.jpeg" alt="Hướng dẫn">
    <span>Hướng dẫn</span>
  </div>
  <div class="post-item">
    <img src="../assets/images/banners/ban_tin.jpeg" alt="Bản tin">
    <span>Bản tin</span>
  </div>
  <div class="post-item">
    <img src="../assets/images/banners/danh_gia.jpeg" alt="Đánh giá">
    <span>Đánh giá</span>
  </div>
  <div class="post-item">
    <img src="../assets/images/banners/huong_dan.jpeg" alt="Hướng dẫn">
    <span>Hướng dẫn</span>
  </div>
  <div class="post-item">
    <img src="../assets/images/banners/ban_tin.jpeg" alt="Bản tin">
    <span>Bản tin</span>
  </div>
</div>


<?php include '../includes/footer.php'; ?>
