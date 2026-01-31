<?php
session_start();
include '../includes/db_connect.php';

// Bảo vệ trang: chỉ admin mới có quyền truy cập
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    die('Truy cập bị từ chối. Bạn không phải là quản trị viên.');
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đổi Mật Khẩu - BinBin Admin</title>
    <link rel="stylesheet" href="../CSS/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>

<!-- Sidebar -->
<?php include '../includes/admin_sidebar.php'; ?>

<div class="main-content">
    <div class="page-header">
        <div>
            <h2>Bảo Mật & Mật Khẩu</h2>
            <p style="color: var(--secondary); margin-top: 0.5rem;">Thay đổi mật khẩu đăng nhập để bảo vệ tài khoản quản trị.</p>
        </div>
    </div>

    <div style="max-width: 600px; margin: 0 auto; animation: slideUp 0.5s ease-out;">
        <div class="admin-container">
            <h3 style="background: #fcfdfe;"><i class="fas fa-shield-alt"></i> Thay đổi mật khẩu</h3>
            <div style="padding: 2.5rem;">
                <?php if (isset($_SESSION['password_error'])): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle"></i>
                        <?php echo $_SESSION['password_error']; unset($_SESSION['password_error']); ?>
                    </div>
                <?php endif; ?>
                <?php if (isset($_SESSION['password_success'])): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        <?php echo $_SESSION['password_success']; unset($_SESSION['password_success']); ?>
                    </div>
                <?php endif; ?>

                <form action="../actions/xu_ly_doi_mat_khau.php" method="POST">
                    <div class="form-group">
                        <label><i class="fas fa-lock-open" style="width: 20px;"></i> Mật khẩu hiện tại</label>
                        <input type="password" name="current_password" class="form-control" placeholder="••••••••" required>
                    </div>

                    <div style="height: 1px; background: #f1f5f9; margin: 2rem 0;"></div>

                    <div class="form-group">
                        <label><i class="fas fa-key" style="width: 20px;"></i> Mật khẩu mới</label>
                        <input type="password" name="new_password" class="form-control" placeholder="Tối thiểu 6 ký tự" required>
                    </div>

                    <div class="form-group">
                        <label><i class="fas fa-check-double" style="width: 20px;"></i> Xác nhận mật khẩu mới</label>
                        <input type="password" name="confirm_password" class="form-control" placeholder="Nhập lại mật khẩu mới" required>
                    </div>

                    <div style="margin-top: 3rem;">
                        <button type="submit" class="btn btn-primary" style="width: 100%; height: 50px; font-size: 1rem;">
                            <i class="fas fa-save"></i> Cập Nhật Mật Khẩu
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div style="margin-top: 2rem; padding: 1.5rem; background: #fffbeb; border: 1px solid #fef3c7; border-radius: var(--radius-lg); display: flex; gap: 1rem; align-items: flex-start;">
            <div style="color: #f59e0b; font-size: 1.25rem;"><i class="fas fa-lightbulb"></i></div>
            <div style="font-size: 0.85rem; color: #92400e; line-height: 1.6;">
                <strong>Gợi ý bảo mật:</strong> Hãy sử dụng mật khẩu mạnh bao gồm chữ cái viết hoa, chữ thường, số và ký tự đặc biệt để đảm bảo an toàn tối đa cho hệ thống quản trị của bạn.
            </div>
        </div>
    </div>
</div>

</body>
</html>
