<?php
include '../includes/db_connect.php';
$page_css = ['../CSS/tuan2.css', '../CSS/thue_camera.css'];
include '../includes/header.php';
?>

<!-- Filter section -->
<div class="filter-container">
  <div class="filter-row">
    <select>
      <option>Thuê Phụ Kiện</option>
    </select>
    <select>
      <option>Tất cả các loại</option>
      <option>Thẻ nhớ</option>
      <option>Pin/Sạc</option>
      <option>Túi/Balo</option>
      <option>Tripod</option>
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
// Query products - Chỉ lấy category Accessory (Phụ kiện)
// Gimbal và Đèn đã có trang riêng, nhưng nếu Đèn vẫn nằm trong Accessory thì có thể nó sẽ hiện lại ở đây.
// Để tránh trùng lặp với Gimbal (nếu Gimbal lỡ bị set là Accessory), ta chỉ lấy đúng 'Accessory'.
$sql = "SELECT * FROM products WHERE category = 'Accessory'";
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
        
        // Brand logic cho phụ kiện (Có thể là Sandisk, Sony, Manfrotto...)
        // Tạm thời để logo mặc định hoặc check tên hãng
        $brand_img = "";
        if($brand == 'sony') $brand_img = "../logo/sony.jpeg";
        elseif($brand == 'canon') $brand_img = "../logo/canon.jpeg";
        elseif($brand == 'nikon') $brand_img = "../logo/logo_nikon.jpeg";
        elseif($brand == 'sandisk') $brand_img = "../logo/sandisk.png"; // Ví dụ
        
        echo '
        <a href="chi_tiet_san_pham.php?id='.$id.'" class="camera-card-link">
          <div class="camera-card flip-card">
            <div class="brand-rating">
              '.($brand_img ? '<img src="'.$brand_img.'" alt="'.$row['brand'].'" class="brand-logo" />' : '<span class="brand-text">'.strtoupper($row['brand']).'</span>').'
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
    echo '<p style="padding: 20px; text-align: center; width: 100%;">Hiện chưa có phụ kiện nào.</p>';
}
?>
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
