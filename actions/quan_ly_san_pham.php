<?php
session_start();
include '../includes/db_connect.php';

// Bảo vệ trang: chỉ admin mới có quyền truy cập
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    die('Truy cập bị từ chối. Bạn không phải là quản trị viên.');
}

// Lấy danh sách sản phẩm
$category_filter = isset($_GET['category']) ? $_GET['category'] : '';
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';

$sql = "SELECT * FROM products WHERE 1=1";
$params = [];
$types = "";

if ($category_filter && $category_filter != 'all') {
    $sql .= " AND category = ?";
    $params[] = $category_filter;
    $types .= "s";
}

if ($search_query) {
    $sql .= " AND (name LIKE ? OR brand LIKE ?)";
    $search_term = "%" . $search_query . "%";
    $params[] = $search_term;
    $params[] = $search_term;
    $types .= "ss";
}

$sql .= " ORDER BY id DESC";

if (!empty($params)) {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
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
    <title>Quản Lý Sản Phẩm - Admin</title>
    <link rel="stylesheet" href="../CSS/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

<!-- Sidebar -->
<?php include '../includes/admin_sidebar.php'; ?>

<!-- Main Content -->
<div class="main-content">
    <div class="page-header">
        <h2>Quản Lý Sản Phẩm</h2>

    </div>

    <div class="admin-container">
    <?php if(isset($_SESSION['msg'])): ?>
        <div class="alert alert-success"><?php echo $_SESSION['msg']; unset($_SESSION['msg']); ?></div>
    <?php endif; ?>
    <?php if(isset($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <div class="filter-bar">
        <form action="" method="GET" style="display:flex; gap:10px; align-items:center; flex-grow: 1;">
            <input type="text" name="search" class="form-control" placeholder="Tìm tên sản phẩm..." value="<?php echo htmlspecialchars($search_query); ?>" style="width: 300px;">
            
            <select name="category" class="form-control" onchange="this.form.submit()" style="width: 200px;">
                <option value="all">Tất cả danh mục</option>
                <option value="Camera" <?php if($category_filter=='Camera') echo 'selected'; ?>>Camera</option>
                <option value="Lens" <?php if($category_filter=='Lens') echo 'selected'; ?>>Lens (Ống kính)</option>
                <option value="Accessory" <?php if($category_filter=='Accessory') echo 'selected'; ?>>Phụ kiện</option>
                <option value="Gimbal" <?php if($category_filter=='Gimbal') echo 'selected'; ?>>Gimbal</option>
                <option value="Lighting" <?php if($category_filter=='Lighting') echo 'selected'; ?>>Đèn (Flash/Led)</option>
                <option value="Micro" <?php if($category_filter=='Micro') echo 'selected'; ?>>Micro</option>
                <option value="Flycam" <?php if($category_filter=='Flycam') echo 'selected'; ?>>Flycam / Gopro</option>
                <option value="Digital" <?php if($category_filter=='Digital') echo 'selected'; ?>>Digital Camera</option>
            </select>
            
            <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Tìm</button>
            <?php if($search_query || ($category_filter && $category_filter != 'all')): ?>
                <a href="quan_ly_san_pham.php" class="btn btn-secondary">Đặt lại</a>
            <?php endif; ?>
        </form>
        <button onclick="openModal('add')" class="btn btn-success"><i class="fas fa-plus"></i> Thêm Sản Phẩm Mới</button>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Hình ảnh</th>
                <th>Tên sản phẩm</th>
                <th>Danh mục</th>
                <th>Hãng</th>
                <th>Trạng thái</th>
                <th>Giá/Buổi</th>
                <th>Giá/Ngày</th>
                <th>Hành động</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result && $result->num_rows > 0): ?>
                <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td>#<?php echo $row['id']; ?></td>
                    <td>
                        <img src="<?php echo htmlspecialchars($row['image'] ?? ''); ?>" class="product-img-thumb" alt="Img">
                    </td>
                    <td><strong><?php echo htmlspecialchars($row['name'] ?? ''); ?></strong></td>
                    <td><span class="badge"><?php echo htmlspecialchars($row['category'] ?? ''); ?></span></td>
                    <td><?php echo htmlspecialchars(strtoupper($row['brand'] ?? '')); ?></td>
                    <td>
                        <?php 
                            $status = $row['rental_status'] ?? 'available';
                        ?>
                        <select onchange="updateStatus(<?php echo $row['id']; ?>, this)" 
                                class="form-control status-select" 
                                style="width: 130px; font-weight:bold; <?php echo ($status == 'rented') ? 'color: red; border-color: red;' : 'color: green; border-color: green;'; ?>">
                            <option value="available" <?php echo ($status == 'available') ? 'selected' : ''; ?> style="color: green; font-weight:bold;">Có sẵn</option>
                            <option value="rented" <?php echo ($status == 'rented') ? 'selected' : ''; ?> style="color: red; font-weight:bold;">Đã thuê</option>
                        </select>
                    </td>
                    <td style="color:red;"><?php echo number_format((float)($row['price_session'] ?? 0)); ?> đ</td>
                    <td style="color:red;"><?php echo number_format((float)($row['price_day'] ?? 0)); ?> đ</td>
                    <td>
                        <a href="../HTML/chi_tiet_san_pham.php?id=<?php echo $row['id']; ?>" class="btn btn-info btn-sm" target="_blank" title="Xem chi tiết">
                            <i class="fas fa-eye"></i>
                        </a>
                        <button class="btn btn-warning btn-sm" onclick='openModal("edit", <?php echo json_encode($row); ?>)' title="Chỉnh sửa">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-danger btn-sm" onclick="confirmDelete(<?php echo $row['id']; ?>)" title="Xóa">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="8" style="text-align:center;">
                    <?php if($search_query): ?>
                        Không tìm thấy sản phẩm nào khớp với "<?php echo htmlspecialchars($search_query); ?>".
                    <?php else: ?>
                        Chưa có sản phẩm nào.
                    <?php endif; ?>
                </td></tr>
            <?php endif; ?>
        </tbody>
    </table>
    </div>
</div>

<!-- Modal Form -->
<div id="productModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>
        <h3 id="modalTitle">Thêm Sản Phẩm Mới</h3>
        <form action="xu_ly_san_pham.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" id="formAction" value="add">
            <input type="hidden" name="id" id="productId">

            <div class="form-group">
                <label>Tên sản phẩm:</label>
                <input type="text" name="name" id="pName" class="form-control" required>
            </div>

            <div style="display:flex; gap:20px;">
                <div class="form-group" style="flex:1;">
                    <label>Danh mục:</label>
                    <select name="category" id="pCategory" class="form-control">
                        <option value="Camera">Camera</option>
                        <option value="Lens">Lens</option>
                        <option value="Accessory">Phụ kiện</option>
                        <option value="Gimbal">Gimbal</option>
                        <option value="Lighting">Đèn (Flash/Led)</option>
                        <option value="Micro">Micro</option>
                        <option value="Flycam">Flycam / Gopro</option>
                        <option value="Digital">Digital Camera</option>
                    </select>
                </div>
                <div class="form-group" style="flex:1;">
                    <label>Hãng sản xuất:</label>
                    <select name="brand" id="pBrand" class="form-control">
                        <option value="Canon">Canon</option>
                        <option value="Sony">Sony</option>
                        <option value="Nikon">Nikon</option>
                        <option value="Fujifilm">Fujifilm</option>
                        <option value="Tamron">Tamron</option>
                        <option value="Sigma">Sigma</option>
                        <option value="GoPro">GoPro</option>
                        <option value="DJI">DJI</option>
                        <option value="Khac">Khác</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label>Trạng thái thuê:</label>
                <select name="rental_status" id="pRentalStatus" class="form-control">
                    <option value="available">Có sẵn (Available)</option>
                    <option value="rented">Đã thuê (Rented)</option>
                </select>
            </div>

            <div style="display:flex; gap:20px;">
                <div class="form-group" style="flex:1;">
                    <label>Giá thuê theo buổi (VNĐ):</label>
                    <input type="number" name="price_session" id="pPriceSession" class="form-control" required>
                </div>
                <div class="form-group" style="flex:1;">
                    <label>Giá thuê theo ngày (VNĐ):</label>
                    <input type="number" name="price_day" id="pPriceDay" class="form-control" required>
                </div>
            </div>

            <div class="form-group">
                <label>Ảnh đại diện (Mặt trước):</label>
                <input type="file" name="image" class="form-control" accept="image/*">
                <small style="color:gray;">Để trống nếu không muốn thay đổi ảnh khi sửa.</small>
            </div>

            <div class="form-group">
                <label>Ảnh chi tiết (Mặt sau/Flip):</label>
                <input type="file" name="image_back" class="form-control" accept="image/*">
            </div>

            <div class="form-group">
                <label>Mô tả ngắn:</label>
                <textarea name="description" id="pDescription" class="form-control" rows="3" placeholder="Mô tả ngắn về sản phẩm..."></textarea>
            </div>

            <div class="form-group">
                <label>Mô tả chi tiết sản phẩm:</label>
                <textarea name="detailed_description" id="pDetailedDescription" class="form-control" rows="8" placeholder="Nhập mô tả chi tiết về sản phẩm, tính năng, thông số kỹ thuật..."></textarea>
                <small style="color:gray;">Mô tả này sẽ hiển thị trong trang chi tiết sản phẩm.</small>
            </div>

            <div class="form-group">
                <label>Ảnh chi tiết 1 (Cho phần mô tả):</label>
                <input type="file" name="detail_image_1" class="form-control" accept="image/*">
            </div>

            <div class="form-group">
                <label>Ảnh chi tiết 2 (Cho phần mô tả):</label>
                <input type="file" name="detail_image_2" class="form-control" accept="image/*">
            </div>

            <div class="form-group">
                <label>Ảnh chi tiết 3 (Cho phần mô tả):</label>
                <input type="file" name="detail_image_3" class="form-control" accept="image/*">
            </div>

            <div class="form-group">
                <label>Ảnh chi tiết 4 (Cho phần mô tả):</label>
                <input type="file" name="detail_image_4" class="form-control" accept="image/*">
            </div>

            <div style="text-align:right; margin-top:20px;">
                <button type="button" class="btn btn-secondary" onclick="closeModal()">Hủy</button>
                <button type="submit" class="btn btn-success">Lưu Lại</button>
            </div>
        </form>
    </div>
</div>

<!-- Hidden Form for Delete -->
<form id="deleteForm" action="xu_ly_san_pham.php" method="POST" style="display:none;">
    <input type="hidden" name="action" value="delete">
    <input type="hidden" name="id" id="deleteId">
</form>

<script>
    const modal = document.getElementById("productModal");

    function openModal(mode, data = null) {
        modal.style.display = "flex";
        if (mode === 'add') {
            document.getElementById('modalTitle').innerText = "Thêm Sản Phẩm Mới";
            document.getElementById('formAction').value = "add";
            document.getElementById('productId').value = "";
            document.getElementById('pName').value = "";
            document.getElementById('pPriceSession').value = "";
            document.getElementById('pPriceDay').value = "";
            document.getElementById('pRentalStatus').value = "available";
            document.getElementById('pDescription').value = "";
            document.getElementById('pDetailedDescription').value = "";
        } else {
            document.getElementById('modalTitle').innerText = "Cập Nhật Sản Phẩm";
            document.getElementById('formAction').value = "edit";
            document.getElementById('productId').value = data.id;
            document.getElementById('pName').value = data.name;
            document.getElementById('pCategory').value = data.category;
            document.getElementById('pBrand').value = data.brand; // Lưu ý: brand trong DB cần khớp value option
            document.getElementById('pPriceSession').value = data.price_session;
            document.getElementById('pPriceDay').value = data.price_day;
            document.getElementById('pRentalStatus').value = data.rental_status || "available";
            document.getElementById('pDescription').value = data.description || "";
            document.getElementById('pDetailedDescription').value = data.detailed_description || "";
        }
    }

    function closeModal() {
        modal.style.display = "none";
    }

    function confirmDelete(id) {
        Swal.fire({
            title: 'Bạn có chắc chắn?',
            text: "Sản phẩm sẽ bị xóa vĩnh viễn!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Xóa ngay!',
            cancelButtonText: 'Hủy'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('deleteId').value = id;
                document.getElementById('deleteForm').submit();
            }
        })
    }

    window.onclick = function(event) {
        if (event.target == modal) {
            closeModal();
        }
    }

    // Tự động mở modal nếu có tham số action=add trong URL
    window.onload = function() {
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('action') === 'add') {
            openModal('add');
        }
    }

    function updateStatus(id, selectElement) {
        const newStatus = selectElement.value;
        
        // Cập nhật giao diện ngay lập tức
        if(newStatus === 'rented') {
            selectElement.style.color = 'red';
            selectElement.style.borderColor = 'red';
        } else {
            selectElement.style.color = 'green';
            selectElement.style.borderColor = 'green';
        }

        // Gửi AJAX request
        const formData = new FormData();
        formData.append('action', 'update_status');
        formData.append('id', id);
        formData.append('rental_status', newStatus);

        fetch('../actions/xu_ly_san_pham.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                const Toast = Swal.mixin({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 1500,
                    timerProgressBar: true,
                });
                Toast.fire({
                    icon: 'success',
                    title: 'Cập nhật trạng thái thành công'
                });
            } else {
                Swal.fire('Lỗi', data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire('Lỗi', 'Không thể kết nối đến server', 'error');
        });
    }
</script>

</body>
</html>