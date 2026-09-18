<?php
session_start();
include '../includes/db_connect.php';

// Bảo vệ trang: chỉ admin mới có quyền truy cập
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    die('Truy cập bị từ chối. Bạn không phải là quản trị viên.');
}

$current_user_id = $_SESSION['user_id'] ?? 0;

// Xử lý Thêm / Sửa / Xóa tài khoản
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action == 'add' || $action == 'edit') {
        $full_name = trim($_POST['full_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $role = (isset($_POST['role']) && $_POST['role'] === 'admin') ? 'admin' : 'user';

        if ($action == 'add') {
            $username = trim($_POST['username'] ?? '');
            $password_input = trim($_POST['password'] ?? '');
            $password_raw = !empty($password_input) ? $password_input : '123456';

            if (empty($username)) {
                $_SESSION['error'] = "Vui lòng nhập tên đăng nhập!";
            } else {
                // Kiểm tra trùng tên đăng nhập
                $check_stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
                $check_stmt->bind_param("s", $username);
                $check_stmt->execute();
                $check_stmt->store_result();

                if ($check_stmt->num_rows > 0) {
                    $_SESSION['error'] = "Tên đăng nhập '{$username}' đã tồn tại. Vui lòng chọn tên khác!";
                } else {
                    $password_hashed = password_hash($password_raw, PASSWORD_DEFAULT);
                    $stmt = $conn->prepare("INSERT INTO users (username, password, full_name, email, phone, address, role) VALUES (?, ?, ?, ?, ?, ?, ?)");
                    $stmt->bind_param("sssssss", $username, $password_hashed, $full_name, $email, $phone, $address, $role);

                    if ($stmt->execute()) {
                        $role_label = ($role === 'admin') ? 'quản trị viên (Admin)' : 'khách hàng';
                        $_SESSION['msg'] = "Thêm tài khoản {$role_label} thành công!";
                    } else {
                        $_SESSION['error'] = "Lỗi khi tạo tài khoản: " . $conn->error;
                    }
                    $stmt->close();
                }
                $check_stmt->close();
            }
        } else { // Edit
            $user_id = intval($_POST['user_id'] ?? 0);
            $new_password = trim($_POST['password'] ?? '');

            // An toàn: Không cho phép tự hạ quyền của tài khoản đang đăng nhập
            if ($current_user_id > 0 && $current_user_id == $user_id && $role !== 'admin') {
                $_SESSION['error'] = "Bạn không thể tự hạ quyền quản trị viên của tài khoản đang đăng nhập!";
            } else {
                if (!empty($new_password)) {
                    $password_hashed = password_hash($new_password, PASSWORD_DEFAULT);
                    $stmt = $conn->prepare("UPDATE users SET full_name=?, email=?, phone=?, address=?, role=?, password=? WHERE id=?");
                    $stmt->bind_param("ssssssi", $full_name, $email, $phone, $address, $role, $password_hashed, $user_id);
                } else {
                    $stmt = $conn->prepare("UPDATE users SET full_name=?, email=?, phone=?, address=?, role=? WHERE id=?");
                    $stmt->bind_param("sssssi", $full_name, $email, $phone, $address, $role, $user_id);
                }

                if ($stmt->execute()) {
                    $_SESSION['msg'] = "Cập nhật thông tin tài khoản thành công!";
                } else {
                    $_SESSION['error'] = "Lỗi cập nhật: " . $conn->error;
                }
                $stmt->close();
            }
        }
    } elseif ($action == 'delete' && isset($_POST['user_id'])) {
        $user_id = intval($_POST['user_id']);

        if ($current_user_id > 0 && $current_user_id == $user_id) {
            $_SESSION['error'] = "Bạn không thể tự xóa tài khoản quản trị viên đang đăng nhập!";
        } else {
            // Kiểm tra ràng buộc đơn hàng trước khi xóa
            $check_orders = $conn->prepare("SELECT COUNT(*) FROM orders WHERE user_id = ?");
            $check_orders->bind_param("i", $user_id);
            $check_orders->execute();
            $check_orders->bind_result($order_count);
            $check_orders->fetch();
            $check_orders->close();

            if ($order_count > 0) {
                $_SESSION['error'] = "Không thể xóa tài khoản này vì đã có {$order_count} đơn hàng liên kết trong hệ thống.";
            } else {
                try {
                    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
                    $stmt->bind_param("i", $user_id);
                    if ($stmt->execute()) {
                        $_SESSION['msg'] = "Xóa tài khoản thành công!";
                    } else {
                        $_SESSION['error'] = "Không thể xóa tài khoản: " . $conn->error;
                    }
                    $stmt->close();
                } catch (mysqli_sql_exception $e) {
                    $_SESSION['error'] = "Không thể xóa tài khoản này do ràng buộc dữ liệu liên quan.";
                }
            }
        }
    }
    header("Location: quan_ly_khach_hang.php");
    exit();
}

// Bộ lọc & Tìm kiếm
$role_filter = isset($_GET['role']) ? trim($_GET['role']) : 'all';
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';

$where_clauses = ["1=1"];
$params = [];
$param_types = "";

if ($role_filter === 'admin' || $role_filter === 'user') {
    $where_clauses[] = "role = ?";
    $params[] = $role_filter;
    $param_types .= "s";
}

if ($search_query !== '') {
    $where_clauses[] = "(username LIKE ? OR full_name LIKE ? OR email LIKE ? OR phone LIKE ?)";
    $search_term = "%$search_query%";
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
    $param_types .= "ssss";
}

$sql = "SELECT * FROM users WHERE " . implode(" AND ", $where_clauses) . " ORDER BY (role = 'admin') DESC, created_at DESC";

if (!empty($params)) {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($param_types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query($sql);
}

// Thống kê nhanh
$count_all = $conn->query("SELECT COUNT(*) as c FROM users")->fetch_assoc()['c'] ?? 0;
$count_admin = $conn->query("SELECT COUNT(*) as c FROM users WHERE role = 'admin'")->fetch_assoc()['c'] ?? 0;
$count_user = $conn->query("SELECT COUNT(*) as c FROM users WHERE role = 'user'")->fetch_assoc()['c'] ?? 0;
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Tài Khoản - BinBin Admin</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

<!-- Sidebar -->
<?php include '../includes/admin_sidebar.php'; ?>

<div class="main-content">
    <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
        <div>
            <h2>Quản Lý Tài Khoản</h2>
            <p style="color: var(--secondary); margin-top: 0.5rem;">Quản lý toàn bộ tài khoản Khách hàng và Quản trị viên (Admin) trong hệ thống.</p>
        </div>
        <div>
            <button type="button" class="btn btn-primary" onclick="openModal('add')">
                <i class="fas fa-user-plus"></i> Thêm tài khoản mới
            </button>
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
            <form action="" method="GET" style="display:flex; gap:12px; width: 100%; flex-wrap: wrap;">
                <div style="position: relative; flex: 1; min-width: 250px;">
                    <i class="fas fa-search" style="position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: var(--secondary);"></i>
                    <input type="text" name="search" class="form-control" placeholder="Tìm theo tên đăng nhập, họ tên, email, SĐT..." value="<?php echo htmlspecialchars($search_query); ?>" style="padding-left: 45px; width: 100%;">
                </div>
                <div style="min-width: 200px;">
                    <select name="role" class="form-control" onchange="this.form.submit()">
                        <option value="all" <?php if($role_filter=='all') echo 'selected'; ?>>Tất cả vai trò (<?php echo $count_all; ?>)</option>
                        <option value="user" <?php if($role_filter=='user') echo 'selected'; ?>>Khách hàng (<?php echo $count_user; ?>)</option>
                        <option value="admin" <?php if($role_filter=='admin') echo 'selected'; ?>>Quản trị viên (<?php echo $count_admin; ?>)</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-secondary">
                    <i class="fas fa-filter"></i> Lọc
                </button>
                <?php if($search_query !== '' || $role_filter !== 'all'): ?>
                    <a href="quan_ly_khach_hang.php" class="btn btn-secondary">Xóa lọc</a>
                <?php endif; ?>
            </form>
        </div>

        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Mã TK</th>
                        <th>Thông tin tài khoản</th>
                        <th>Vai trò</th>
                        <th>Liên hệ</th>
                        <th>Địa chỉ</th>
                        <th>Ngày tạo</th>
                        <th style="text-align: right;">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while($row = $result->fetch_assoc()): ?>
                        <?php 
                            $is_current_admin = ($current_user_id > 0 && $current_user_id == $row['id']);
                            $is_admin = ($row['role'] === 'admin');
                        ?>
                        <tr>
                            <td><span style="font-weight: 700; color: var(--secondary);">#<?php echo $row['id']; ?></span></td>
                            <td>
                                <div style="display: flex; align-items: center; gap: 1rem;">
                                    <div class="user-avatar" style="width: 40px; height: 40px; font-size: 1rem; <?php echo $is_admin ? 'background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%); color: white;' : ''; ?>">
                                        <?php echo mb_strtoupper(mb_substr($row['full_name'] ?: $row['username'], 0, 1, 'UTF-8'), 'UTF-8'); ?>
                                    </div>
                                    <div>
                                        <div style="font-weight: 700; color: var(--dark); display: flex; align-items: center; gap: 6px;">
                                            <?php echo htmlspecialchars($row['full_name'] ?: 'Chưa đặt tên'); ?>
                                            <?php if ($is_current_admin): ?>
                                                <span style="font-size: 0.7rem; background: #e0e7ff; color: #4338ca; padding: 2px 6px; border-radius: 6px; font-weight: 600;">(Bạn)</span>
                                            <?php endif; ?>
                                        </div>
                                        <div style="font-size: 0.8rem; color: var(--primary);">@<?php echo htmlspecialchars($row['username']); ?></div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <?php if ($is_admin): ?>
                                    <span class="badge" style="background: #ede9fe; color: #6d28d9; border: 1px solid #ddd6fe; font-weight: 600;">
                                        <i class="fas fa-shield-alt" style="margin-right: 4px;"></i> Quản trị viên
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-info" style="font-weight: 600;">
                                        <i class="fas fa-user" style="margin-right: 4px;"></i> Khách hàng
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div style="font-size: 0.9rem;">
                                    <div><i class="fas fa-envelope" style="width: 18px; color: var(--secondary);"></i> <?php echo htmlspecialchars($row['email'] ?: 'Chưa có'); ?></div>
                                    <div><i class="fas fa-phone" style="width: 18px; color: var(--secondary);"></i> <?php echo htmlspecialchars($row['phone'] ?: 'Chưa có'); ?></div>
                                </div>
                            </td>
                            <td style="max-width: 200px;">
                                <span style="font-size: 0.85rem; color: var(--secondary);">
                                    <?php echo htmlspecialchars($row['address'] ?: '—'); ?>
                                </span>
                            </td>
                            <td>
                                <span style="font-size: 0.85rem; color: var(--secondary);">
                                    <?php echo date('d/m/Y', strtotime($row['created_at'])); ?>
                                </span>
                            </td>
                            <td style="text-align: right;">
                                <div style="display: flex; gap: 8px; justify-content: flex-end;">
                                    <button class="btn btn-sm btn-primary" onclick='openModal("edit", <?php echo json_encode($row); ?>)' title="Chỉnh sửa tài khoản">
                                        <i class="fas fa-user-edit"></i>
                                    </button>
                                    <?php if ($is_current_admin): ?>
                                        <button class="btn btn-sm btn-secondary" style="opacity: 0.5; cursor: not-allowed;" title="Không thể tự xóa tài khoản của chính mình" disabled>
                                            <i class="fas fa-ban"></i>
                                        </button>
                                    <?php else: ?>
                                        <button class="btn btn-sm btn-danger" onclick="confirmDelete(<?php echo $row['id']; ?>, '<?php echo htmlspecialchars($row['username']); ?>', '<?php echo $row['role']; ?>')" title="Xóa tài khoản">
                                            <i class="fas fa-user-times"></i>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="7" style="text-align:center; padding: 4rem;">
                            <i class="fas fa-users-slash" style="font-size: 3rem; opacity: 0.2; margin-bottom: 1rem; display: block;"></i>
                            Không tìm thấy tài khoản phù hợp.
                        </td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Thêm / Cập nhật tài khoản -->
<div id="userModal" class="modal">
    <div class="modal-content" style="max-width: 550px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
            <h3 id="modalTitle" style="margin: 0; font-size: 1.25rem;">Quản lý tài khoản</h3>
            <span class="close" onclick="closeModal()" style="cursor: pointer; font-size: 1.25rem; color: var(--secondary);"><i class="fas fa-times"></i></span>
        </div>

        <form action="" method="POST" id="userForm">
            <input type="hidden" name="action" id="formAction" value="add">
            <input type="hidden" name="user_id" id="userId">

            <div class="form-group">
                <label>Vai trò tài khoản: <span style="color: red;">*</span></label>
                <select name="role" id="uRole" class="form-control" required>
                    <option value="user">👤 Khách hàng (User)</option>
                    <option value="admin">🛡️ Quản trị viên (Admin)</option>
                </select>
                <small style="color: var(--secondary); font-size: 0.8rem; display: block; margin-top: 4px;">
                    Tài khoản Quản trị viên có toàn quyền quản lý sản phẩm, đơn hàng và hệ thống.
                </small>
            </div>

            <div id="usernameSection" class="form-group">
                <label>Tên đăng nhập: <span style="color: red;">*</span></label>
                <input type="text" name="username" id="uUsername" class="form-control" placeholder="Ví dụ: khachhang01, admin_chin...">
            </div>

            <div class="form-group">
                <label id="passwordLabel">Mật khẩu:</label>
                <input type="password" name="password" id="uPassword" class="form-control" placeholder="Mặc định: 123456 nếu để trống">
                <small id="passwordHelp" style="color: var(--secondary); font-size: 0.8rem; display: none; margin-top: 4px;">
                    Để trống nếu không muốn thay đổi mật khẩu hiện tại.
                </small>
            </div>

            <div class="form-group">
                <label>Họ và tên:</label>
                <input type="text" name="full_name" id="uFullName" class="form-control" placeholder="Nguyễn Văn A">
            </div>

            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:16px;">
                <div class="form-group">
                    <label>Email:</label>
                    <input type="email" name="email" id="uEmail" class="form-control" placeholder="example@gmail.com">
                </div>
                <div class="form-group">
                    <label>Số điện thoại:</label>
                    <input type="text" name="phone" id="uPhone" class="form-control" placeholder="090xxxxxxx">
                </div>
            </div>

            <div class="form-group">
                <label>Địa chỉ liên hệ:</label>
                <textarea name="address" id="uAddress" class="form-control" rows="3" placeholder="Địa chỉ thường trú hoặc nơi giao hàng..."></textarea>
            </div>

            <div style="display: flex; gap: 1rem; justify-content: flex-end; margin-top: 1.5rem;">
                <button type="button" class="btn btn-secondary" onclick="closeModal()">Đóng</button>
                <button type="submit" id="submitBtn" class="btn btn-primary" style="min-width: 12rem;">
                    <i class="fas fa-save"></i> Lưu tài khoản
                </button>
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
            document.getElementById('modalTitle').innerHTML = '<i class="fas fa-user-plus" style="color: var(--primary); margin-right: 8px;"></i> Thêm Tài Khoản Mới';
            document.getElementById('formAction').value = "add";
            document.getElementById('usernameSection').style.display = "block";
            document.getElementById('uUsername').required = true;
            document.getElementById('passwordLabel').innerHTML = 'Mật khẩu:';
            document.getElementById('passwordHelp').style.display = "none";
            document.getElementById('uPassword').placeholder = "Mặc định: 123456 (hoặc nhập mật khẩu riêng)";
            document.getElementById('submitBtn').innerHTML = '<i class="fas fa-plus-circle"></i> Tạo tài khoản';
            
            document.getElementById('userId').value = "";
            document.getElementById('uRole').value = "user";
            document.getElementById('uUsername').value = "";
            document.getElementById('uPassword').value = "";
            document.getElementById('uFullName').value = "";
            document.getElementById('uEmail').value = "";
            document.getElementById('uPhone').value = "";
            document.getElementById('uAddress').value = "";
        } else {
            document.getElementById('modalTitle').innerHTML = '<i class="fas fa-user-edit" style="color: var(--primary); margin-right: 8px;"></i> Cập Nhật Tài Khoản (@' + (data.username || '') + ')';
            document.getElementById('formAction').value = "edit";
            document.getElementById('usernameSection').style.display = "none";
            document.getElementById('uUsername').required = false;
            document.getElementById('passwordLabel').innerHTML = 'Đổi mật khẩu mới:';
            document.getElementById('passwordHelp').style.display = "block";
            document.getElementById('uPassword').placeholder = "Để trống nếu không muốn đổi mật khẩu";
            document.getElementById('submitBtn').innerHTML = '<i class="fas fa-save"></i> Cập nhật hồ sơ';

            document.getElementById('userId').value = data.id;
            document.getElementById('uRole').value = data.role || 'user';
            document.getElementById('uPassword').value = "";
            document.getElementById('uFullName').value = data.full_name || '';
            document.getElementById('uEmail').value = data.email || '';
            document.getElementById('uPhone').value = data.phone || '';
            document.getElementById('uAddress').value = data.address || '';
        }
    }

    function closeModal() {
        modal.style.display = "none";
    }

    function confirmDelete(id, username, role) {
        let roleText = (role === 'admin') ? 'Quản trị viên' : 'Khách hàng';
        Swal.fire({
            title: `Xóa tài khoản ${roleText}?`,
            html: `Bạn có chắc muốn xóa tài khoản <b>@${username}</b> không?<br><span style="color:#ef4444; font-size: 0.85rem;">Hành động này không thể hoàn tác!</span>`,
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
