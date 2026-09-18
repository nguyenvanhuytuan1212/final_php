<?php
include '../includes/db_connect.php';
$page_css = ['../assets/css/style.css', '../assets/css/thue_camera.css'];
include '../includes/header.php';
?>

<!-- tìm kiếm -->
 <div class="filter-container">
  <div class="filter-row">
    <select>
      <option>Thuê Camera (Máy ảnh)</option>
      <!-- Thêm các lựa chọn khác nếu cần -->
    </select>

    <select>
      <option>Tất cả các hãng</option>
      <option>SONY</option>
      <option>Canon</option>
      <option>Nikon</option>
    </select>

    <select>
      <option>Giá tăng dần</option>
      <option>Giá giảm dần</option>
    </select>

    <form action="search.php" method="GET" class="search-wrapper">
      <input type="text" name="q" placeholder="Tìm..." />
      <button type="submit" class="search-btn">🔍</button>
    </form>
  </div>

  <div class="brand-filter">
    <a href="#" class="brand-btn active" data-brand="all">Tất cả các hãng</a>
    <a href="canon.php" class="brand-btn" data-brand="canon"><img src="../assets/images/logo/canon.jpeg" alt="Canon" /></a>
    <a href="sony.php" class="brand-btn" data-brand="sony"><img src="../assets/images/logo/sony.jpeg" alt="Sony" /></a>
    <a href="nikon.php" class="brand-btn" data-brand="nikon"><img src="../assets/images/logo/logo_nikon.jpeg" alt="Nikon" /></a>
  </div>
</div>

<div class="camera-grid">
<?php
// Query products
$sql = "SELECT * FROM products WHERE category = 'Camera'";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $id = $row['id'];
        $name = htmlspecialchars($row['name']);
        $image = htmlspecialchars($row['image']);
        $image_back = htmlspecialchars($row['image_back']);
        $price_session = number_format($row['price_session'], 0, ',', '.');
        $price_day = number_format($row['price_day'], 0, ',', '.');
        $brand = strtolower($row['brand']); 
        
        $brand_img = "../assets/images/logo/logo_nikon.jpeg"; // Default
        if($brand == 'sony') $brand_img = "../assets/images/logo/sony.jpeg";
        if($brand == 'canon') $brand_img = "../assets/images/logo/canon.jpeg";
        
        echo '
        <a href="chi_tiet_san_pham.php?id='.$id.'" class="camera-card-link">
          <div class="camera-card flip-card">
            <div class="brand-rating">
              <img src="'.$brand_img.'" alt="'.$row['brand'].'" class="brand-logo" />
              <span class="stars">★★★★★</span>
            </div>

            <div class="flip-inner">
              <div class="flip-front">
                <img src="'.$image.'" alt="'.$name.'" class="camera-img" style="object-fit: cover;" />
              </div>
              <div class="flip-back">
                <img src="'.$image_back.'" alt="'.$name.'" class="camera-img" style="object-fit: cover;" />
              </div>
            </div>

            <h3 class="camera-name" style="min-height: 40px;">'.$name.'</h3>

            <div>
              <p class="price">
                <span class="price-red">'.$price_session.'</span> đ/buổi<br />
                <span class="price-gray">'.$price_day.'</span> đ/ngày
              </p>
              <button class="rent-btn" onclick="addToCart(event, '.$id.')">Thuê</button>
            </div>
          </div>
        </a>';
    }
} else {
    echo '<p style="padding: 20px;">Hiện chưa có sản phẩm nào trong danh mục này.</p>';
}
?>
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

<script>
function addToCart(event, productId) {
    event.preventDefault(); // Prevent responding to the <a> tag
    event.stopPropagation();
    
    $.ajax({
        url: '../actions/add_to_cart.php',
        type: 'POST',
        data: {id: productId},
        dataType: 'json',
        success: function(response) {
            if(response.status === 'success') {
                $('#cart-count').text(response.count);
                Swal.fire({
                    title: '🎉 Đã thêm vào giỏ hàng!',
                    text: 'Sản phẩm của bạn đã được thêm vào giỏ hàng.',
                    icon: 'success',
                    showConfirmButton: false,
                    timer: 2000,
                    toast: true,
                    position: 'top-end',
                    background: '#fefefe',
                    color: '#333',
                     didOpen: (toast) => {
                        toast.addEventListener('mouseenter', Swal.stopTimer)
                        toast.addEventListener('mouseleave', Swal.resumeTimer)
                    }
                });
            } else {
                 Swal.fire('Lỗi', response.message || 'Không thể thêm sản phẩm', 'error');
            }
        },
        error: function(xhr, status, error) {
            console.error(error);
            Swal.fire('Lỗi', 'Lỗi kết nối server', 'error');
        }
    });
}
</script>

<?php include '../includes/footer.php'; ?>
