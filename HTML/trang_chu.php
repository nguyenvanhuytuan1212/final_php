<?php
// Define page-specific CSS if needed
$page_css = '../CSS/tuan2.css'; // Include page specific CSS but header already has it?
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
    <img src="../anh/anh_bia.jpeg" class="active" alt="Ảnh 1">
    <img src="../anh/bbc85f0de4f05e2a0f7feb51b3a27ae6cf32b59d81b7248891.png" alt="Ảnh 2">
    <img src="../anh/ebad50fdb19005ce5c81.png" alt="Ảnh 3">
</div>
<script src="../JS/slideshow.js"></script>
  <div class="section-header">
    <div class="section-title">DỊCH VỤ CHO THUÊ / BÁN MÀY ẢNH</div>
    <a href="#" class="see-more">Xem thêm</a>
  </div>

    <div class="grid">
      <a href="thue_camera.php" class="item">
        <img src="../anh/5c3cbcdd3c05d76fb8f17e41213be0fcd3193c3b268c79b2f1.jpeg" alt="Máy ảnh"><span>Máy Ảnh</span>
      </a>

      <a href="thue_lens.php" class="item">
        <img src="../anh/a1c2d01ca7e13205eaee2af1d4415b6f78e374e883a661761b.jpeg" alt="Ống kính"><span>Ống Kính</span>
      </a>

      <a href="thue_phu_kien.php" class="item">
        <img src="../anh/PHỤ KIỆN.jpeg" alt="Phụ kiện"><span>Phụ Kiện</span>
      </a>

      <a href="thue_den.php" class="item">
        <img src="../anh/ĐÈN CHỤP.jpeg" alt="Đèn chụp"><span>Đèn chụp</span>
      </a>

      <a href="thue_gimbal.php" class="item">
        <img src="../anh/GIMBAL.jpeg" alt="Gimbal"><span>Gimbal</span>
      </a>

      <a href="thue_micro.php" class="item">
        <img src="../anh/MIC.jpeg" alt="Micro"><span>Micro</span>
      </a>

      <a href="thue_den.php" class="item">
        <img src="../anh/ĐÈN QUÂY.jpeg" alt="Đèn quay"><span>Đèn quay</span>
      </a>

      <a href="thue_flycam.php" class="item">
        <img src="../anh/PLYCAM.jpeg" alt="Flycam"><span>Flycam|Gopro|Pocket</span>
      </a>

      <a href="thue_phu_kien.php" class="item">
        <img src="../anh/PHỤ KIỆN QUAY.jpeg" alt="Phụ kiện quay"><span>Phụ kiện quay</span>
      </a>
      <a href="" class="iteam"></a>
      <a href="thue_digital.php" class="item">
        <img src="../anh/MÁY ẢNH KHÁC.jpeg" alt="Digital Camera"><span>Digital Camera</span>
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
    <img src="../anh/hướng dẫn ảnh.jpeg" alt="Hướng dẫn">
    <span>Hướng dẫn</span>
  </div>
  <div class="post-item">
    <img src="../anh/bản tin.jpeg" alt="Bản tin">
    <span>Bản tin</span>
  </div>
  <div class="post-item">
    <img src="../anh/đánh giá.jpeg" alt="Đánh giá">
    <span>Đánh giá</span>
  </div>
  <div class="post-item">
    <img src="../anh/hướng dẫn ảnh.jpeg" alt="Hướng dẫn">
    <span>Hướng dẫn</span>
  </div>
  <div class="post-item">
    <img src="../anh/bản tin.jpeg" alt="Bản tin">
    <span>Bản tin</span>
  </div>
</div>


<?php include '../includes/footer.php'; ?>
