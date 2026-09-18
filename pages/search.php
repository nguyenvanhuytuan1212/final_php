<?php
include '../includes/db_connect.php';
$page_css = ['../assets/css/style.css', '../assets/css/thue_camera.css'];
include '../includes/header.php';

$search_query = isset($_GET['q']) ? trim($_GET['q']) : '';
?>

<div class="filter-container">
    <div class="filter-row" style="justify-content: flex-end;">
        <!-- Thanh tìm kiếm vẫn giữ ở đây để tìm tiếp -->
        <form action="search.php" method="GET" class="search-wrapper" style="max-width: 400px;">
            <input type="text" name="q" placeholder="Tìm kiếm sản phẩm..." value="<?php echo htmlspecialchars($search_query); ?>" />
            <button type="submit" class="search-btn">🔍</button>
        </form>
    </div>
</div>

<div class="camera-grid">
    <?php
    if ($search_query) {
        $search_term = "%" . $search_query . "%";
        // Tìm kiếm theo tên hoặc mô tả sản phẩm
        $stmt = $conn->prepare("SELECT * FROM products WHERE name LIKE ? OR description LIKE ?");
        $stmt->bind_param("ss", $search_term, $search_term);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            echo '<h3 style="width:100%; grid-column: 1/-1; margin-bottom: 20px; padding-left: 10px;">Kết quả tìm kiếm cho: "'.htmlspecialchars($search_query).'"</h3>';
            
            while ($row = $result->fetch_assoc()) {
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
                if($brand == 'tamron') $brand_img = "../assets/images/logo/tamron.jpeg";
                if($brand == 'sigma') $brand_img = "../assets/images/logo/sigma.jpeg";
                
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
            echo '<p style="padding: 20px; grid-column: 1/-1; text-align:center;">Không tìm thấy sản phẩm nào phù hợp với từ khóa "'.htmlspecialchars($search_query).'".</p>';
        }
    } else {
        echo '<p style="padding: 20px; grid-column: 1/-1; text-align:center;">Vui lòng nhập từ khóa để tìm kiếm.</p>';
    }
    ?>
</div>

<?php include '../includes/footer.php'; ?>
