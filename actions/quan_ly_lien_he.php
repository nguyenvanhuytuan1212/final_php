<?php
session_start();
include '../includes/db_connect.php';

// Bảo vệ trang: chỉ admin mới có quyền truy cập
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    die('Truy cập bị từ chối. Bạn không phải là quản trị viên.');
}

// Xử lý Xóa
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] == 'delete' && isset($_POST['contact_id'])) {
        $contact_id = $_POST['contact_id'];
        $stmt = $conn->prepare("DELETE FROM contacts WHERE id = ?");
        $stmt->bind_param("i", $contact_id);
        if ($stmt->execute()) $_SESSION['msg'] = "Xóa thông tin liên hệ thành công!";
        else $_SESSION['error'] = "Lỗi: " . $conn->error;
    }
    header("Location: quan_ly_lien_he.php");
    exit();
}

// Lấy danh sách liên hệ
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';
$sql = "SELECT * FROM contacts";
if ($search_query) {
    $search_term = "%$search_query%";
    $sql .= " WHERE name LIKE ? OR email LIKE ? OR message LIKE ?";
}
$sql .= " ORDER BY created_at DESC";

if ($search_query) {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $search_term, $search_term, $search_term);
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
    <title>Quản Lý Liên Hệ - BinBin Admin</title>
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
            <h2>Tin Nhắn Liên Hệ</h2>
            <p style="color: var(--secondary); margin-top: 0.5rem;">Xem và quản lý các phản hồi từ khách hàng.</p>
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
                    <input type="text" name="search" class="form-control" placeholder="Tìm theo tên, email, nội dung..." value="<?php echo htmlspecialchars($search_query); ?>" style="padding-left: 45px; width: 100%;">
                </div>
                <button type="submit" class="btn btn-secondary">
                    <i class="fas fa-search"></i> Tìm kiếm
                </button>
                <?php if($search_query): ?>
                    <a href="quan_ly_lien_he.php" class="btn btn-secondary">Xóa tìm</a>
                <?php endif; ?>
            </form>
        </div>

        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Khách hàng</th>
                        <th>Nội dung</th>
                        <th>Ngày gửi</th>
                        <th style="text-align: right;">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><span style="font-weight: 700; color: var(--secondary);">#<?php echo $row['id']; ?></span></td>
                            <td>
                                <div style="font-weight: 700; color: var(--dark);"><?php echo htmlspecialchars($row['name']); ?></div>
                                <div style="font-size: 0.8rem; color: var(--primary);"><?php echo htmlspecialchars($row['email']); ?></div>
                            </td>
                            <td style="max-width: 400px; white-space: normal;">
                                <div style="font-size: 0.9rem; line-height: 1.4;">
                                    <?php echo nl2br(htmlspecialchars($row['message'])); ?>
                                </div>
                            </td>
                            <td>
                                <span style="font-size: 0.85rem; color: var(--secondary);">
                                    <?php echo date('d/m/Y H:i', strtotime($row['created_at'])); ?>
                                </span>
                            </td>
                            <td style="text-align: right;">
                                <button class="btn btn-sm btn-danger" onclick="confirmDelete(<?php echo $row['id']; ?>)" title="Xóa liên hệ">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="5" style="text-align:center; padding: 4rem;">
                            <i class="fas fa-envelope-open-text" style="font-size: 3rem; opacity: 0.2; margin-bottom: 1rem; display: block;"></i>
                            Không có tin nhắn liên hệ nào.
                        </td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<form id="deleteForm" action="" method="POST" style="display:none;">
    <input type="hidden" name="action" value="delete">
    <input type="hidden" name="contact_id" id="deleteId">
</form>

<script>
    function confirmDelete(id) {
        Swal.fire({
            title: 'Xóa tin nhắn này?',
            text: "Hành động này không thể hoàn tác!",
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
</script>
</body>
</html>
