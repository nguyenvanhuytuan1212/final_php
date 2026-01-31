<?php
include '../includes/db_connect.php';
$page_css = ['../CSS/tuan2.css', '../CSS/thue_lens.css', '../CSS/san_pham.css'];
include '../includes/header.php';
?>

<!-- tìm kiếm -->
     <div class="filter-container">
  <div class="filter-row">
    <select>
      <option>Thuê Lens (Ống kính)</option>
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
    <a href="thue_lens.php" class="brand-btn active" data-brand="all">Tất cả các hãng</a>
    <a href="lens_canon.php" class="brand-btn" data-brand="canon"><img src="../logo/canon.jpeg" alt="Canon" /></a>
    <a href="lens_sony.php" class="brand-btn" data-brand="sony"><img src="../logo/sony.jpeg" alt="Sony" /></a>
    <a href="lens_nikon.php" class="brand-btn" data-brand="nikon"><img src="../logo/logo_nikon.jpeg" alt="Nikon" /></a>
    <a href="lens_tamron.php" class="brand-btn" data-brand="tamron"><img src="../logo/TAMRON.jpeg" alt="Tamron" /></a>
    <a href="lens_sigma.php" class="brand-btn" data-brand="Sigma"><img src="../logo/SIGMA.jpeg" alt="Sigma" /></a>
  </div>

</div>


    <div class="camera-grid">
<?php
// Query products
$sql = "SELECT * FROM products WHERE category = 'Lens'";
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
        
        $brand_img = "../logo/logo_nikon.jpeg"; // Default
        if($brand == 'sony') $brand_img = "../logo/sony.jpeg";
        if($brand == 'canon') $brand_img = "../logo/canon.jpeg";
        if($brand == 'tamron') $brand_img = "../logo/TAMRON.jpeg";
        if($brand == 'sigma') $brand_img = "../logo/SIGMA.jpeg";
        
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

<div class="suggest">
    <h3>Gợi ý thuê cùng</h3>
    <div class="suggest-items">
        <?php
        // Suggest accessories for lenses
        $suggest_sql = "SELECT * FROM products WHERE category = 'Accessory' LIMIT 3";
        $suggest_result = $conn->query($suggest_sql);
        
        if ($suggest_result && $suggest_result->num_rows > 0) {
            while($suggest = $suggest_result->fetch_assoc()) {
        ?>
        <div class="suggest-item">
            <a href="chi_tiet_san_pham.php?id=<?php echo $suggest['id']; ?>" style="text-decoration: none; color: inherit;">
                <img src="<?php echo htmlspecialchars($suggest['image']); ?>" alt="<?php echo htmlspecialchars($suggest['name']); ?>">
                <p><?php echo htmlspecialchars($suggest['name']); ?></p>
                <div class="price">
                    <span class="red"><?php echo number_format($suggest['price_session'], 0, ',', '.'); ?> đ/buổi</span><br>
                    <span><?php echo number_format($suggest['price_day'], 0, ',', '.'); ?> đ/ngày</span>
                </div>
            </a>
        </div>
        <?php 
            }
        } else {
            echo '<p>Không có gợi ý nào.</p>';
        }
        ?>
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
                    color: '#333'
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
