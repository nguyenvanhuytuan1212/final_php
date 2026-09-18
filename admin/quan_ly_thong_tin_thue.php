<?php
session_start();
include '../includes/db_connect.php';

// Bảo vệ trang: chỉ admin mới có quyền truy cập
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    die('Truy cập bị từ chối. Bạn không phải là quản trị viên.');
}

// Logic tìm kiếm
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';
$sql = "SELECT id, customer_name, phone, email, address, note, facebook, images, status, created_at FROM orders WHERE 1=1";

if ($search_query) {
    $search_term = "%$search_query%";
    $sql .= " AND (customer_name LIKE ? OR phone LIKE ? OR email LIKE ? OR note LIKE ? OR facebook LIKE ?)";
}

$sql .= " ORDER BY created_at DESC";

if ($search_query) {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssss", $search_term, $search_term, $search_term, $search_term, $search_term);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query($sql);
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hồ Sơ Thuê Thiết Bị - BinBin Admin</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .doc-images {
            display: flex;
            gap: 12px;
        }
        .doc-images img {
            width: 70px;
            height: 70px;
            object-fit: cover;
            border-radius: var(--radius);
            cursor: pointer;
            border: 2px solid white;
            box-shadow: var(--shadow-sm);
            transition: var(--transition);
        }
        .doc-images img:hover {
            transform: translateY(-5px) scale(1.05);
            box-shadow: var(--shadow);
            border-color: var(--primary);
        }
        
        #imageModal .modal-content {
            background: transparent;
            box-shadow: none;
            padding: 0;
            max-width: 80vw;
            display: flex;
            justify-content: center;
        }
        
        #imgFull {
            border-radius: var(--radius-lg);
            border: 5px solid white;
            box-shadow: 0 0 40px rgba(0,0,0,0.5);
        }
    </style>
</head>
<body>

<!-- Sidebar -->
<?php include '../includes/admin_sidebar.php'; ?>

<div class="main-content">
    <div class="page-header">
        <div>
            <h2>Hồ Sơ Thuê & Giao Nhận</h2>
            <p style="color: var(--secondary); margin-top: 0.5rem;">Quản lý thông tin định danh và hồ sơ tín dụng của khách thuê.</p>
        </div>
    </div>

    <div class="admin-container">
        <div class="filter-bar">
            <form action="" method="GET" style="display:flex; gap:15px; width: 100%;">
                <div style="position: relative; flex: 1;">
                    <i class="fas fa-search" style="position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: var(--secondary);"></i>
                    <input type="text" name="search" class="form-control" placeholder="Tìm kiếm theo tên khách, SĐT, Email..." value="<?php echo htmlspecialchars($search_query); ?>" style="padding-left: 45px; width: 100%;">
                </div>
                <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i> Lọc hồ sơ</button>
                <?php if($search_query): ?>
                    <a href="quan_ly_thong_tin_thue.php" class="btn btn-secondary">Đặt lại</a>
                <?php endif; ?>
            </form>
        </div>

        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Đơn hàng</th>
                        <th>Khách thuê</th>
                        <th>Email/Gmail</th>
                        <th>Địa chỉ giao nhận</th>
                        <th>Facebook</th>
                        <th>Giấy tờ cá nhân</th>
                        <th>Ghi chú / Yêu cầu</th>
                        <th>Ngày gửi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <div style="font-weight: 800; color: var(--primary); margin-bottom: 0.5rem;">#<?php echo $row['id']; ?></div>
                                <?php 
                                    $status_class = 'bg-secondary';
                                    if($row['status'] == 'Mới đặt') $status_class = 'bg-info';
                                    elseif($row['status'] == 'Hoàn thành') $status_class = 'bg-success';
                                ?>
                                <span class="badge <?php echo $status_class; ?>" style="font-size: 0.7rem;"><?php echo $row['status']; ?></span>
                            </td>
                            <td>
                                <div style="font-weight: 700; color: var(--dark);"><?php echo htmlspecialchars($row['customer_name']); ?></div>
                                <div style="font-size: 0.8rem; color: var(--secondary);"><i class="fas fa-phone-alt"></i> <?php echo htmlspecialchars($row['phone']); ?></div>
                            </td>
                            <td>
                                <div style="font-size: 0.85rem; color: var(--primary);"><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($row['email']); ?></div>
                            </td>
                            <td style="max-width: 200px; font-size: 0.85rem; color: var(--secondary);">
                                <i class="fas fa-map-marker-alt" style="color: var(--danger);"></i> <?php echo htmlspecialchars($row['address']); ?>
                            </td>
                            <td>
                                <?php if($row['facebook']): ?>
                                    <a href="<?php echo htmlspecialchars($row['facebook']); ?>" target="_blank" class="btn btn-sm btn-secondary" style="background: #eff6ff; color: #1e40af; border: 1px solid #dbeafe;">
                                        <i class="fab fa-facebook"></i> Link Profile
                                    </a>
                                <?php else: ?>
                                    <span style="color: #cbd5e1; font-size: 0.8rem;">Chưa cung cấp</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="doc-images">
                                    <?php 
                                    $imgs = json_decode($row['images'], true);
                                    if ($imgs) {
                                        foreach ($imgs as $key => $path) {
                                            echo '<img src="../' . htmlspecialchars($path) . '" onclick="viewFullImage(\'../' . htmlspecialchars($path) . '\')" title="' . strtoupper($key) . '">';
                                        }
                                    } else {
                                        echo '<span style="color: #cbd5e1; font-size: 0.8rem;">N/A</span>';
                                    }
                                    ?>
                                </div>
                            </td>
                            <td style="max-width: 200px;">
                                <div style="font-size: 0.85rem; color: var(--secondary); background: #f1f5f9; padding: 0.75rem; border-radius: 8px;">
                                    <?php echo $row['note'] ? htmlspecialchars($row['note']) : '<em>Không có ghi chú</em>'; ?>
                                </div>
                            </td>
                            <td>
                                <div style="font-size: 0.8rem; color: var(--secondary);">
                                    <?php echo date('d/m/Y', strtotime($row['created_at'])); ?><br>
                                    <?php echo date('H:i', strtotime($row['created_at'])); ?>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="8" style="text-align:center; padding:5rem; color: var(--secondary);">
                            <i class="fas fa-id-card" style="font-size: 3.5rem; opacity: 0.15; margin-bottom: 1.5rem; display: block;"></i>
                            Chưa tìm thấy dữ liệu hồ sơ giao nhận nào.
                        </td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal xem ảnh lớn -->
<div id="imageModal" class="modal" onclick="this.style.display='none'">
    <div style="position: absolute; right: 2rem; top: 2rem; color: white; font-size: 2rem; cursor: pointer;">
        <i class="fas fa-times"></i>
    </div>
    <div class="modal-content">
        <img id="imgFull" style="max-width: 100%; max-height: 85vh; object-fit: contain;">
    </div>
</div>

<script>
    function viewFullImage(src) {
        document.getElementById("imageModal").style.display = "flex";
        document.getElementById("imageModal").style.alignItems = "center";
        document.getElementById("imageModal").style.justifyContent = "center";
        document.getElementById("imgFull").src = src;
    }
</script>

</body>
</html>
