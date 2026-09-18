<?php
include '../includes/db_connect.php';
$page_css = ['../assets/css/style.css', '../assets/css/thue_camera.css'];
include '../includes/header.php';
?>

<!-- Filter section -->
<div class="filter-container">
  <div class="filter-row">
    <select>
      <option>Thuê Digital Camera</option>
    </select>
    <select>
      <option>Tất cả các hãng</option>
      <option>Canon</option>
      <option>Sony</option>
      <option>Fujifilm</option>
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
</div>

<div class="camera-grid">
<?php
// Query products
$sql = "SELECT * FROM products WHERE category = 'Digital'";
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
        
        $brand_img = ""; 
        if($brand == 'canon') $brand_img = "../assets/images/logo/canon.jpeg";
        elseif($brand == 'sony') $brand_img = "../assets/images/logo/sony.jpeg";
        
        echo '
        <a href="chi_tiet_san_pham.php?id='.$id.'" class="camera-card-link">
          <div class="camera-card flip-card">
            <div class="brand-rating">
              '.($brand_img && file_exists($brand_img) ? '<img src="'.$brand_img.'" alt="'.$row['brand'].'" class="brand-logo" />' : '<span class="brand-text">'.strtoupper($row['brand']).'</span>').'
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
    echo '<p style="padding: 20px; text-align: center; width: 100%;">Hiện chưa có sản phẩm Digital Camera nào.</p>';
}
?>
</div>

<script>
function addToCart(event, productId) {
    event.preventDefault();
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
                    text: 'Sản phẩm đã được thêm vào giỏ hàng.',
                    icon: 'success',
                    showConfirmButton: false,
                    timer: 2000,
                    toast: true,
                    position: 'top-end'
                });
            } else {
                 Swal.fire('Lỗi', response.message || 'Không thể thêm sản phẩm', 'error');
            }
        },
        error: function() {
            Swal.fire('Lỗi', 'Lỗi kết nối server', 'error');
        }
    });
}
</script>

<?php include '../includes/footer.php'; ?>
