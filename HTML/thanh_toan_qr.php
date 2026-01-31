<?php
session_start();
include '../includes/db_connect.php';

if (!isset($_GET['order_id'])) {
    die("Không tìm thấy mã đơn hàng.");
}

$order_id = intval($_GET['order_id']);

// Lấy thông tin đơn hàng
$stmt = $conn->prepare("SELECT * FROM orders WHERE id = ?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$result = $stmt->get_result();
$order = $result->fetch_assoc();

if (!$order) {
    die("Đơn hàng không tồn tại.");
}

$amount = $order['total_money'];
$content = "THANHTOAN DON " . $order_id; // Nội dung chuyển khoản: THANHTOAN DON <Mã đơn>

// Thông tin tài khoản ngân hàng (Bạn có thể thay đổi ở đây)
$bank_id = "BIDV"; // Ngân hàng MB Bank (ví dụ)
$account_no = "5601938559"; // Số tài khoản ví dụ
$account_name = "NGUYEN VAN HUY TUAN"; // Tên chủ tài khoản

// Link tạo QR Code VietQR
// Format: https://img.vietqr.io/image/<BANK_ID>-<ACCOUNT_NO>-<TEMPLATE>.png?amount=<AMOUNT>&addInfo=<CONTENT>&accountName=<NAME>
$qr_url = "https://img.vietqr.io/image/{$bank_id}-{$account_no}-compact2.png?amount={$amount}&addInfo={$content}&accountName=" . urlencode($account_name);

$page_css = ['../CSS/tuan2.css']; // Dùng chung CSS header
include '../includes/header.php';
?>

<div style="max-width: 800px; margin: 50px auto; padding: 20px; text-align: center; border: 1px solid #ddd; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1);">
    <h2 style="color: #28a745;">ĐẶT HÀNG THÀNH CÔNG!</h2>
    <p>Mã đơn hàng của bạn: <strong>#<?php echo $order_id; ?></strong></p>
    <p>Tổng tiền cần thanh toán: <strong style="color: #d33; font-size: 1.2em;"><?php echo number_format($amount, 0, ',', '.'); ?> đ</strong></p>
    
    <hr style="margin: 20px 0;">
    
    <h3>Vui lòng quét mã QR bên dưới để thanh toán</h3>
    <div style="margin: 20px 0;">
        <img src="<?php echo $qr_url; ?>" alt="QR Code Thanh Toán" style="max-width: 300px; border: 1px solid #ddd; padding: 5px;">
    </div>
    
    <div style="text-align: left; display: inline-block; background: #f9f9f9; padding: 15px; border-radius: 5px;">
        <p><strong>Ngân hàng:</strong> MB Bank</p>
        <p><strong>Số tài khoản:</strong> <?php echo $account_no; ?></p>
        <p><strong>Chủ tài khoản:</strong> <?php echo $account_name; ?></p>
        <p><strong>Nội dung CK:</strong> <?php echo $content; ?></p>
    </div>

    <hr style="margin: 20px 0;">
    
    <p><i>Sau khi chuyển khoản, hệ thống sẽ kiểm tra và xác nhận đơn hàng của bạn.</i></p>
    
    <div style="margin-top: 20px;">
        <a href="trang_chu.php" class="btn btn-primary" style="padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px;">Về Trang Chủ</a>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
