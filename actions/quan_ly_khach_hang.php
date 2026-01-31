<?php
session_start();
include '../includes/db_connect.php';

// Bảo vệ trang: chỉ admin mới có quyền truy cập
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    die('Truy cập bị từ chối. Bạn không phải là quản trị viên.');
}

// Xử lý Thêm/Sửa/Xóa
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action == 'add' || $action == 'edit') {
        $username = trim($_POST['username']);
        $full_name = trim($_POST['full_name']);
        $email = trim($_POST['email']);
        $phone = trim($_POST['phone']);
        $address = trim($_POST['address']);
        
        if ($action == 'add') {
            $password = password_hash($_POST['password'] ?: '123456', PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (username, password, full_name, email, phone, address, role) VALUES (?, ?, ?, ?, ?, ?, 'user')");
            $stmt->bind_param("ssssss", $username, $password, $full_name, $email, $phone, $address);
            if ($stmt->execute()) $_SESSION['msg'] = "Thêm khách hàng thành công!";
            else $_SESSION['error'] = "Lỗi: " . $conn->error;
        } else {
            $user_id = $_POST['user_id'];
            $stmt = $conn->prepare("UPDATE users SET full_name=?, email=?, phone=?, address=? WHERE id=? AND role='user'");
            $stmt->bind_param("ssssi", $full_name, $email, $phone, $address, $user_id);
            if ($stmt->execute()) $_SESSION['msg'] = "Cập nhật hồ sơ thành công!";
            else $_SESSION['error'] = "Lỗi: " . $conn->error;
        }
    } elseif ($action == 'delete' && isset($_POST['user_id'])) {
        $user_id = $_POST['user_id'];
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ? AND role = 'user'");
        $stmt->bind_param("i", $user_id);
        if ($stmt->execute()) $_SESSION['msg'] = "Xóa khách hàng thành công!";
        else $_SESSION['error'] = "Không thể xóa khách hàng này. Họ có thể đã có đơn hàng.";
    }
    header("Location: quan_ly_khach_hang.php");
    exit();
}

// Lấy danh sách khách hàng
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';
$sql = "SELECT * FROM users WHERE role = 'user'";
if ($search_query) {
    $search_term = "%$search_query%";
    $sql .= " AND (username LIKE ? OR full_name LIKE ? OR email LIKE ? OR phone LIKE ?)";
}
$sql .= " ORDER BY created_at DESC";

if ($search_query) {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $search_term, $search_term, $search_term, $search_term);
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
    <title>Quản Lý Khách Hàng - BinBin Admin</title>
    <link rel="stylesheet" href="../CSS/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

<!-- Sidebar -->
<?php include '../includes/admin_sidebar.php'; ?>

<div class="main-content">
    <div class="page-header">
        <div>
            <h2>Hồ Sơ Khách Hàng</h2>
            <p style="color: var(--secondary); margin-top: 0.5rem;">Quản lý thông tin tài khoản và lịch sử khách hàng.</p>
        </div>

    </div>

    <div class="admin-container">
        <?php if(isset($_SESSION['msg'])): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <?php echo $_SESSION['msg']; unset($_SESSION['msg']); ?>
            </div>
        <?php endif; ?>
        <?php if(isset($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <div class="filter-bar">
            <form action="" method="GET" style="display:flex; gap:15px; width: 100%;">
                <div style="position: relative; flex: 1;">
                    <i class="fas fa-search" style="position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: var(--secondary);"></i>
                    <input type="text" name="search" class="form-control" placeholder="Tìm theo tên, email, số điện thoại..." value="<?php echo htmlspecialchars($search_query); ?>" style="padding-left: 45px; width: 100%;">
                </div>
                <button type="submit" class="btn btn-secondary">
                    <i class="fas fa-search"></i> Tìm kiếm
                </button>
                <?php if($search_query): ?>
                    <a href="quan_ly_khach_hang.php" class="btn btn-secondary">Xóa tìm</a>
                <?php endif; ?>
            </form>
        </div>

        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Mã KH</th>
                        <th>Thông tin khách</th>
                        <th>Liên hệ</th>
                        <th>Địa chỉ</th>
                        <th>Ngày đăng ký</th>
                        <th style="text-align: right;">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><span style="font-weight: 700; color: var(--secondary);">#<?php echo $row['id']; ?></span></td>
                            <td>
                                <div style="display: flex; align-items: center; gap: 1rem;">
                                    <div class="user-avatar" style="width: 40px; height: 40px; font-size: 1rem;">
                                        <?php echo strtoupper(substr($row['full_name'], 0, 1)); ?>
                                    </div>
                                    <div>
                                        <div style="font-weight: 700; color: var(--dark);"><?php echo htmlspecialchars($row['full_name']); ?></div>
                                        <div style="font-size: 0.8rem; color: var(--primary);">@<?php echo htmlspecialchars($row['username']); ?></div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div style="font-size: 0.9rem;">
                                    <div><i class="fas fa-envelope" style="width: 18px; color: var(--secondary);"></i> <?php echo htmlspecialchars($row['email']); ?></div>
                                    <div><i class="fas fa-phone" style="width: 18px; color: var(--secondary);"></i> <?php echo htmlspecialchars($row['phone']); ?></div>
                                </div>
                            </td>
                            <td style="max-width: 200px;">
                                <span style="font-size: 0.85rem; color: var(--secondary);">
                                    <?php echo htmlspecialchars($row['address']); ?>
                                </span>
                            </td>
                            <td>
                                <span style="font-size: 0.85rem; color: var(--secondary);">
                                    <?php echo date('d/m/Y', strtotime($row['created_at'])); ?>
                                </span>
                            </td>
                            <td style="text-align: right;">
                                <div style="display: flex; gap: 8px; justify-content: flex-end;">
                                    <button class="btn btn-sm btn-primary" onclick='openModal("edit", <?php echo json_encode($row); ?>)' title="Chỉnh sửa">
                                        <i class="fas fa-user-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger" onclick="confirmDelete(<?php echo $row['id']; ?>)" title="Xóa tài khoản">
                                        <i class="fas fa-user-times"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="6" style="text-align:center; padding: 4rem;">
                            <i class="fas fa-users-slash" style="font-size: 3rem; opacity: 0.2; margin-bottom: 1rem; display: block;"></i>
                            Không có dữ liệu khách hàng nào.
                        </td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Form -->
<div id="userModal" class="modal">
    <div class="modal-content">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <h3 id="modalTitle" style="margin: 0;">Quản lý khách hàng</h3>
            <span class="close" onclick="closeModal()" style="cursor: pointer;"><i class="fas fa-times"></i></span>
        </div>

        <form action="" method="POST">
            <input type="hidden" name="action" id="formAction" value="add">
            <input type="hidden" name="user_id" id="userId">

            <div id="usernameSection">
                <div class="form-group">
                    <label>Tên đăng nhập:</label>
                    <input type="text" name="username" id="uUsername" class="form-control" placeholder="Dùng để đăng nhập">
                </div>

                <div class="form-group">
                    <label>Mật khẩu:</label>
                    <input type="password" name="password" id="uPassword" class="form-control" placeholder="Mặc định: 123456">
                </div>
            </div>

            <div class="form-group">
                <label>Họ và tên khách hàng:</label>
                <input type="text" name="full_name" id="uFullName" class="form-control" required placeholder="Nguyễn Văn A">
            </div>

            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:20px;">
                <div class="form-group">
                    <label>Email:</label>
                    <input type="email" name="email" id="uEmail" class="form-control" required placeholder="example@gmail.com">
                </div>
                <div class="form-group">
                    <label>Số điện thoại:</label>
                    <input type="text" name="phone" id="uPhone" class="form-control" required placeholder="090xxxxxxx">
                </div>
            </div>

            <div class="form-group">
                <label>Địa chỉ liên hệ:</label>
                <textarea name="address" id="uAddress" class="form-control" rows="3" placeholder="Địa chỉ thường trú..."></textarea>
            </div>

            <div style="display: flex; gap: 1rem; justify-content: flex-end; margin-top: 2rem;">
                <button type="button" class="btn btn-secondary" onclick="closeModal()">Đóng</button>
                <button type="submit" class="btn btn-primary" style="min-width: 15rem;">Cập nhật hồ sơ</button>
            </div>
        </form>
    </div>
</div>

<form id="deleteForm" action="" method="POST" style="display:none;">
    <input type="hidden" name="action" value="delete">
    <input type="hidden" name="user_id" id="deleteId">
</form>

<script>
    const modal = document.getElementById("userModal");

    function openModal(mode, data = null) {
        modal.style.display = "flex";
        modal.style.alignItems = "center";
        modal.style.justifyContent = "center";
        
        if (mode === 'add') {
            document.getElementById('modalTitle').innerText = "Đăng Ký Khách Hàng";
            document.getElementById('formAction').value = "add";
            document.getElementById('usernameSection').style.display = "block";
            document.getElementById('uUsername').required = true;
            
            document.getElementById('userId').value = "";
            document.getElementById('uUsername').value = "";
            document.getElementById('uFullName').value = "";
            document.getElementById('uEmail').value = "";
            document.getElementById('uPhone').value = "";
            document.getElementById('uAddress').value = "";
        } else {
            document.getElementById('modalTitle').innerText = "Cập Nhật Hồ Sơ Khách";
            document.getElementById('formAction').value = "edit";
            document.getElementById('usernameSection').style.display = "none";
            document.getElementById('uUsername').required = false;

            document.getElementById('userId').value = data.id;
            document.getElementById('uFullName').value = data.full_name;
            document.getElementById('uEmail').value = data.email;
            document.getElementById('uPhone').value = data.phone;
            document.getElementById('uAddress').value = data.address;
        }
    }

    function closeModal() {
        modal.style.display = "none";
    }

    function confirmDelete(id) {
        Swal.fire({
            title: 'Xóa vĩnh viễn khách hàng?',
            text: "Tất cả hồ sơ liên quan đến khách hàng này sẽ bị xóa!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Xóa ngay',
            cancelButtonText: 'Hủy'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('deleteId').value = id;
                document.getElementById('deleteForm').submit();
            }
        })
    }

    window.onclick = function(event) {
        if (event.target == modal) closeModal();
    }
</script>
</body>
</html>
