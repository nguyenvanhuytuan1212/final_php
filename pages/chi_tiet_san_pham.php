<?php
include '../includes/db_connect.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$product = null;

if ($id > 0) {
    $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();
}

if (!$product) {
    echo "<script>alert('Sản phẩm không tồn tại'); window.location='thue_camera.php';</script>";
    exit;
}

$page_css = ['../assets/css/style.css', '../assets/css/san_pham.css'];
include '../includes/header.php';
?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
<!-- tìm kiếm -->


<h2><?php echo htmlspecialchars($product['name']); ?></h2>
<div class="product-container">
    <div class="left">
        <div class="right">
        <?php
             $brand = strtolower($product['brand'] ?? '');
             $brand_img = "../assets/images/logo/logo_nikon.jpeg"; // Default
             if($brand == 'sony') $brand_img =  "../assets/images/logo/sony.jpeg";
             elseif($brand == 'canon') $brand_img = "../assets/images/logo/canon.jpeg";
             elseif($brand == 'sigma') $brand_img = "../assets/images/logo/sigma.jpeg";
             elseif($brand == 'tamron') $brand_img = "../assets/images/logo/tamron.jpeg";
        ?>
        <img src="<?php echo $brand_img; ?>" class="brand-logo" alt="<?php echo htmlspecialchars($product['brand'] ?? ''); ?> Logo">
        <div class="stars">★★★★★</div>
        </div>

        <img id="mainImage" class="main-image" src="<?php echo htmlspecialchars($product['image'] ?? ''); ?>" alt="<?php echo htmlspecialchars($product['name'] ?? ''); ?>">
        <div class="thumbnails">
            <?php if(!empty($product['image'])): ?>
                <img src="<?php echo htmlspecialchars($product['image']); ?>" alt="thumb1" onclick="document.getElementById('mainImage').src=this.src">
            <?php endif; ?>
            <?php if(!empty($product['image_back'])): ?>
                <img src="<?php echo htmlspecialchars($product['image_back']); ?>" alt="thumb2" onclick="document.getElementById('mainImage').src=this.src">
            <?php endif; ?>
            <?php for($i=1; $i<=4; $i++): $img_key = "detail_image_$i"; ?>
                <?php if(!empty($product[$img_key])): ?>
                    <img src="<?php echo htmlspecialchars($product[$img_key]); ?>" alt="thumb_detail_<?php echo $i; ?>" onclick="document.getElementById('mainImage').src=this.src">
                <?php endif; ?>
            <?php endfor; ?>
        </div>
    </div>
    <div class="right">
        <a></a>
        <a></a>
        <a></a>
        <a></a>
        <a></a>
        
        <h3>Thông tin nổi bật</h3>
        <ul>
            <?php 
            if (!empty($product['description'])) {
                $lines = explode("\n", $product['description']);
                foreach ($lines as $line) {
                    if (trim($line) != '') {
                        echo "<li>" . htmlspecialchars($line) . "</li>";
                    }
                }
            } else {
                echo "<li>Thông tin đang được cập nhật</li>";
            }
            ?>
        </ul>
         <div class="availability-table">
            <h3>📅 Lịch thuê sản phẩm</h3>
            <table class="rental-schedule">
                <thead>
                    <tr>
                        <th>Thời gian</th>
                        <th>Trạng thái</th>
                        <th>Ghi chú</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                        $rental_status = $product['rental_status'] ?? 'available';
                        $is_rented = ($rental_status === 'rented');
                    ?>
                    <tr class="<?php echo $is_rented ? 'unavailable' : 'available'; ?>">
                        <td>Hôm nay (<?php echo date('d/m/Y'); ?>)</td>
                        <td>
                            <span class="status" style="<?php echo $is_rented ? 'color: red; font-weight: bold;' : 'color: green; font-weight: bold;'; ?>">
                                <?php echo $is_rented ? 'Đã thuê' : 'Có sẵn'; ?>
                            </span>
                        </td>
                      
                        <td><?php echo $is_rented ? 'Sản phẩm hiện đang được thuê' : 'Có thể thuê ngay'; ?></td>
                    </tr>

                </tbody>
            </table>

        </div>
        <div class="rental-price">
            <span class="price-title">Giá thuê</span>
            <div class="price-box">
                <span class="price red"><?php echo number_format($product['price_session'], 0, ',', '.'); ?> đ/buổi</span>
                <span class="price red"><?php echo number_format($product['price_day'], 0, ',', '.'); ?> đ/ngày</span>
                <span class="price-label">Bảng giá</span>
            </div>
        </div>

        <button class="rent-button" onclick="<?php echo $is_rented ? '' : 'addToCart()'; ?>" 
                style="<?php echo $is_rented ? 'background-color: grey; cursor: not-allowed;' : ''; ?>" 
                <?php echo $is_rented ? 'disabled' : ''; ?>>
            <?php echo $is_rented ? 'Đang được thuê' : 'Thuê sản phẩm này'; ?>
        </button>
<script>
function addToCart() {
    const productId = <?php echo $id; ?>;
    
    fetch('../actions/add_to_cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'id=' + productId
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            // Update cart count immediately visually
            const cartCountEl = document.getElementById('cart-count');
            if (cartCountEl) {
                cartCountEl.innerText = data.count;
            }

            Swal.fire({
                title: '🎉 Đã thêm vào giỏ hàng!',
                text: 'Sản phẩm của bạn đã được thêm vào giỏ hàng thành công.',
                icon: 'success',
                showConfirmButton: false,
                timer: 1500,
                toast: true,
                position: 'top-end',
                timerProgressBar: true
            }).then(() => {
                // Optional: reload if you want to ensure session state is fully synced across everything, 
                // but updating the badge is often enough for "Add to Cart"
                 location.reload(); 
            });
        } else {
            Swal.fire('Lỗi', data.message || 'Không thể thêm vào giỏ hàng', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire('Lỗi', 'Có lỗi xảy ra khi kết nối máy chủ', 'error');
    });
}
</script>

    </div>
</div>









<div class="details">
    <h3>Thông tin chi tiết</h3>
    <p><?php echo !empty($product['detailed_description']) ? nl2br(htmlspecialchars($product['detailed_description'])) : 'Mô tả chi tiết đang được cập nhật cho sản phẩm này.'; ?></p>
</div>

<?php if(!empty($product['detail_image_1']) || !empty($product['detail_image_2']) || !empty($product['detail_image_3']) || !empty($product['detail_image_4'])): ?>
    <!-- Hiển thị ảnh chi tiết từ database -->
    <?php if(!empty($product['detail_image_1'])): ?>
    <div class="product-details">
        <img src="<?php echo htmlspecialchars($product['detail_image_1']); ?>" alt="Chi tiết 1" class="detail-image">
        <!-- Có thể thêm tiêu đề ảnh chi tiết ở đây nếu sau này có cột trong DB -->
    </div>
    <?php endif; ?>

    <?php if(!empty($product['detail_image_2'])): ?>
    <div class="product-details">
        <img src="<?php echo htmlspecialchars($product['detail_image_2']); ?>" alt="Chi tiết 2" class="detail-image">
    </div>
    <?php endif; ?>

    <?php if(!empty($product['detail_image_3'])): ?>
    <div class="product-details">
        <img src="<?php echo htmlspecialchars($product['detail_image_3']); ?>" alt="Chi tiết 3" class="detail-image">
    </div>
    <?php endif; ?>

    <?php if(!empty($product['detail_image_4'])): ?>
    <div class="product-details">
        <img src="<?php echo htmlspecialchars($product['detail_image_4']); ?>" alt="Chi tiết 4" class="detail-image">
    </div>
    <?php endif; ?>
<?php else: ?>
    <div class="product-details">
        <p style="text-align:center; color:gray;">Không có thêm hình ảnh chi tiết.</p>
    </div>
<?php endif; ?>

<script src=""></script>
  
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
