<?php
include '../includes/db_connect.php';
$page_css = ['../CSS/tuan2.css', '../CSS/GII_HANG.CSS'];
include '../includes/header.php';

$cart_items = [];
if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
    $ids = array_keys($_SESSION['cart']);
    if (!empty($ids)) {
        $ids_str = implode(',', $ids);
        $sql = "SELECT * FROM products WHERE id IN ($ids_str)";
        $result = $conn->query($sql);
        while ($row = $result->fetch_assoc()) {
            // Merge DB data with Session data (quantity)
            $row['quantity'] = $_SESSION['cart'][$row['id']]['quantity'];
            $cart_items[] = $row;
        }
    }
}
?>

<!-- GIỎ hàng -->
<div class="container">
        <header>
            <h1>Giỏ hàng</h1>
            <div class="header-links">
                </div>
        </header>

        <main class="cart-content">
            <div class="cart-header-row">
                <div class="col-stt">STT</div>
                <div class="col-product">Sản phẩm</div>
                <div class="col-rent-time">Thời điểm thuê</div>
                <div class="col-days">Số ngày thuê</div>
                <div class="col-price">Giá</div>
                <div class="col-action">chức năng</div>
            </div>

            <?php if (empty($cart_items)): ?>
                <div style="padding: 20px; text-align: center;">Giỏ hàng trống</div>
            <?php else: ?>
                <?php $stt = 1; foreach ($cart_items as $item): 
                    $price_buoi = $item['price_session'];
                    $price_day = $item['price_day'];
                ?>
                <div class="cart-item" data-id="<?php echo $item['id']; ?>" data-price-per-buoi="<?php echo $price_buoi; ?>" data-price-per-day="<?php echo $price_day; ?>">
                    <div class="col-stt"><?php echo $stt++; ?></div>
                    <div class="col-product">
                        <img src="<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                        <div class="product-info">
                            <p><?php echo htmlspecialchars($item['name']); ?></p>
                            <p data-price-type="buoi"><?php echo number_format($price_buoi, 0, ',', '.'); ?> đ/buổi</p>
                            <p data-price-type="ngay"><?php echo number_format($price_day, 0, ',', '.'); ?> đ/ngày</p>
                        </div>
                    </div>
                    <div class="col-rent-time"><?php echo date('H:i d/m/Y'); ?></div>
                    <div class="col-days">
                        <select class="rental-period">
                            <option value="buoi_1">1 buổi</option>
                            <option value="ngay_1">1 ngày</option>
                            <option value="ngay_2">2 ngày</option>
                             <option value="ngay_3">3 ngày</option>
                        </select>
                    </div>
                    <div class="col-price" data-current-price="<?php echo $price_day; ?>"><?php echo number_format($price_day, 0, ',', '.'); ?> đ</div>
                    <div class="col-action"><button class="btn-delete" onclick="removeFromCart(<?php echo $item['id']; ?>)">xóa</button></div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>

            <div class="cart-summary">
                <div class="total-price">
                    <span>Tổng tiền</span>
                    <span id="calculatedTotalPrice">0 đ</span>
                </div>
            </div>

            <div class="coupon-section">
                <div class="coupon-input">
                    <label for="discount-code">Mã giảm giá</label>
                    <input type="text" id="discount-code" placeholder="">
                    <button class="btn-check">Kiểm tra</button>
                </div>
                 <!-- Note: In a real app, clicking this should create an order in DB -->
                <button class="btn-checkout" onclick="placeOrder()">Thuê ngay</button>
            </div>
        </main>
    </div>

    <script>
        function formatCurrency(amount) {
            return amount.toLocaleString('vi-VN') + ' đ';
        }

        function calculateTotalPrice() {
            let total = 0;
            const cartItems = document.querySelectorAll('.cart-item');

            cartItems.forEach(item => {
                const selectElement = item.querySelector('.rental-period');
                const priceDisplayElement = item.querySelector('.col-price');
                const selectedValue = selectElement.value;

                let itemPrice = 0;
                let numUnits = 0; 
                const [type, count] = selectedValue.split('_');
                numUnits = parseInt(count);

                if (type === 'buoi') {
                    itemPrice = parseFloat(item.dataset.pricePerBuoi) * numUnits;
                } else if (type === 'ngay') {
                    itemPrice = parseFloat(item.dataset.pricePerDay) * numUnits;
                }

                priceDisplayElement.textContent = formatCurrency(itemPrice);
                priceDisplayElement.dataset.currentPrice = itemPrice;

                total += itemPrice;
            });

            document.getElementById('calculatedTotalPrice').textContent = formatCurrency(total);
        }

        document.addEventListener('DOMContentLoaded', () => {
             // Initial select based on session quantity? 
             // Currently quantity is just items count, but rental period is complex logic.
             // We default to 1 day as per original logic somewhat.
             // Actually original defaulted to selecting the first option (1 buoi or similar)?
             // The loop has buoi_1 first. So it will default to buoi_1/price_buoi.
            
            const rentalPeriodSelects = document.querySelectorAll('.rental-period');
            rentalPeriodSelects.forEach(select => {
                select.addEventListener('change', calculateTotalPrice);
            });
            
            calculateTotalPrice();
        });

        function removeFromCart(id) {
            if(!confirm('Bạn có chắc muốn xóa sản phẩm này?')) return;
             $.ajax({
                url: '../actions/remove_from_cart.php',
                type: 'POST',
                data: {id: id},
                success: function(response) {
                    location.reload();
                }
            });
        }
        
        function placeOrder() {
             // Chuyển sang trang điền thông tin thanh toán
             window.location.href = "dat_hang_thanh_cong.php";
        }
    </script>

<?php 
// Include footer... but check if modifying footer works.
// Original HTML had article section then footer.
// Using standard footer include.
?>

<?php include '../includes/footer.php'; ?>
