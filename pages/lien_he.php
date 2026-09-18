<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$page_css = ['../assets/css/lien_he.css'];
include '../includes/header.php';
include '../includes/db_connect.php';

// Tạo bảng contacts nếu chưa có
$createTableSql = "CREATE TABLE IF NOT EXISTS contacts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
$conn->query($createTableSql);

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['send_contact'])) {
    $name = trim($conn->real_escape_string($_POST['name']));
    $email = trim($conn->real_escape_string($_POST['email']));
    $content = trim($conn->real_escape_string($_POST['message']));

    if (!empty($name) && !empty($email) && !empty($content)) {
        $sql = "INSERT INTO contacts (name, email, message) VALUES ('$name', '$email', '$content')";
        
        if ($conn->query($sql) === TRUE) {
            $_SESSION['contact_success'] = "Thông tin liên hệ của bạn đã được gửi. Chúng tôi sẽ sớm phản hồi!";
        } else {
            $_SESSION['contact_error'] = "Có lỗi xảy ra khi lưu: " . $conn->error;
        }
    } else {
        $_SESSION['contact_error'] = "Vui lòng điền đầy đủ thông tin!";
    }
    header("Location: lien_he.php");
    exit();
}
?>

<?php if(isset($_SESSION['contact_success'])): ?>
    <script>
        Swal.fire({
            title: 'Thành công!',
            text: '<?php echo $_SESSION['contact_success']; ?>',
            icon: 'success',
            confirmButtonText: 'Đóng'
        });
    </script>
    <?php unset($_SESSION['contact_success']); ?>
<?php endif; ?>

<?php if(isset($_SESSION['contact_error'])): ?>
    <script>
        Swal.fire({
            title: 'Lỗi!',
            text: '<?php echo $_SESSION['contact_error']; ?>',
            icon: 'error',
            confirmButtonText: 'Thử lại'
        });
    </script>
    <?php unset($_SESSION['contact_error']); ?>
<?php endif; ?>

<!-- liên hệ -->

 <section class="contact-top">
    <div class="contact-item">
      <div class="icon"><i class="fas fa-map-marker-alt"></i></div>
      <h3>Địa Chỉ</h3>
      <p>99 TÔ HIẾN THÀNH - SƠN TRÀ -<br>ĐÀ NẴNG</p>
    </div>
    <div class="contact-item">
      <div class="icon"><i class="fas fa-clock"></i></div>
      <h3>Thời Gian Làm Việc</h3>
      <p>Thứ 2 - Thứ 7 : 7am - 7pm Chủ Nhật : 10am - 5pm</p>
    </div>
    <div class="contact-item">
      <div class="icon"><i class="fas fa-envelope"></i></div>
      <h3>Email</h3>
      <p>nguyenvanhuytuan1212.com<br>shopbalo.com</p>
    </div>
    <div class="contact-item">
      <div class="icon"><i class="fas fa-phone"></i></div>
      <h3>Số Điện Thoại</h3>
      <p>0934731558 09437723123</p>
    </div>
  </section>

  <section class="contact-bottom">
    <div class="map">
      <iframe
        src="https://www.google.com/maps?q=99+Tô+Hiến+Thành,+Sơn+Trà,+Đà+Nẵng,+Vietnam&output=embed"
        width="100%" height="350" style="border:0;" allowfullscreen loading="lazy">
      </iframe>
    </div>

    <div class="form">
      <h2>Chúng tôi rất mong nhận được phản hồi từ bạn!</h2>
      <form id="contact-form" method="POST" action="">
        <label for="name">Họ và tên *</label>
        <input type="text" id="name" name="name" required>

        <label for="email">Email của bạn *</label>
        <input type="email" id="email" name="email" required>

        <label for="message">Nội Dung Chính</label>
        <textarea id="message" name="message" rows="5" required></textarea>
        <button type="submit" name="send_contact">Gửi</button>
        
      </form>
    </div>
  </section>

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
