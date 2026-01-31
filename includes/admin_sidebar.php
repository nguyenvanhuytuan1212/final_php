<?php
// Lấy tên file hiện tại để xác định mục active
$current_page = basename($_SERVER['PHP_SELF']);
// Lấy tên thư mục hiện tại để xử lý đường dẫn tương đối
$current_dir = basename(dirname($_SERVER['PHP_SELF']));

// Quy tắc đường dẫn: 
// Nếu đang ở thư mục 'actions', các file cùng cấp không cần '../actions/'
// Nếu đang ở thư mục 'HTML', các file trong 'actions' cần '../actions/'
$prefix_actions = ($current_dir == 'actions') ? '' : '../actions/';
$prefix_html = ($current_dir == 'HTML' || $current_dir == 'html') ? '' : '../HTML/';
?>

<div class="sidebar">
    <div class="sidebar-brand-container">
        <a href="<?php echo $prefix_html; ?>dashboard.php" class="sidebar-brand">
            <i class="fas fa-camera-retro"></i> BinBin Admin
        </a>
    </div>

    <div class="sidebar-user" id="adminUserDropdown">
        <div class="user-avatar">
            <i class="fas fa-user-shield"></i>
        </div>
        <div class="user-info">
            <span class="user-name"><?php echo htmlspecialchars($_SESSION['username'] ?? 'Admin'); ?></span>
            <span class="user-role"><i class="fas fa-circle"></i> Trực tuyến</span>
        </div>
        <div class="user-dropdown-menu">
            <a href="<?php echo $prefix_html; ?>admin_change_password.php"><i class="fas fa-key"></i> Đổi mật khẩu</a>
            <a href="<?php echo $prefix_html; ?>trang_chu.php" target="_blank"><i class="fas fa-home"></i> Trang chủ</a>
            <div class="dropdown-divider"></div>
            <a href="<?php echo $prefix_actions; ?>logout.php" class="admin-logout-btn"><i class="fas fa-sign-out-alt"></i> Đăng xuất</a>
        </div>
    </div>
    <ul class="sidebar-nav">
        <li>
            <a href="<?php echo $prefix_html; ?>dashboard.php" class="<?php echo ($current_page == 'dashboard.php') ? 'active' : ''; ?>">
                <i class="fas fa-tachometer-alt"></i> Bảng điều khiển
            </a>
        </li>
        <li>
            <a href="<?php echo $prefix_actions; ?>quan_ly_san_pham.php" class="<?php echo ($current_page == 'quan_ly_san_pham.php' && !isset($_GET['action'])) ? 'active' : ''; ?>">
                <i class="fas fa-box"></i> Sản Phẩm
            </a>
        </li>
        <li>
            <a href="<?php echo $prefix_actions; ?>quan_ly_don_hang.php" class="<?php echo ($current_page == 'quan_ly_don_hang.php') ? 'active' : ''; ?>">
                <i class="fas fa-shopping-cart"></i> Đơn Hàng
                <span id="order-badge" class="nav-badge" style="display: none;">0</span>
            </a>
        </li>
        <li>
            <a href="<?php echo $prefix_actions; ?>quan_ly_thong_tin_thue.php" class="<?php echo ($current_page == 'quan_ly_thong_tin_thue.php') ? 'active' : ''; ?>">
                <i class="fas fa-id-card"></i> Hồ Sơ Thuê
            </a>
        </li>
        <li>
            <a href="<?php echo $prefix_actions; ?>quan_ly_khach_hang.php" class="<?php echo ($current_page == 'quan_ly_khach_hang.php') ? 'active' : ''; ?>">
                <i class="fas fa-users"></i> Khách Hàng
            </a>
        </li>
        <li>
            <a href="<?php echo $prefix_actions; ?>quan_ly_lien_he.php" class="<?php echo ($current_page == 'quan_ly_lien_he.php') ? 'active' : ''; ?>">
                <i class="fas fa-envelope"></i> Liên Hệ
            </a>
        </li>
        <li>
            <a href="<?php echo $prefix_html; ?>admin_change_password.php" class="<?php echo ($current_page == 'admin_change_password.php') ? 'active' : ''; ?>">
                <i class="fas fa-key"></i> Đổi Mật Khẩu
            </a>
        </li>
        <li>
            <a href="<?php echo $prefix_html; ?>trang_chu.php" target="_blank">
                <i class="fas fa-home"></i> Xem Trang Chủ
            </a>
        </li>
        <li>
            <a href="<?php echo $prefix_actions; ?>logout.php">
                <i class="fas fa-sign-out-alt"></i> Đăng Xuất
            </a>
        </li>
    </ul>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const adminUserDropdown = document.getElementById('adminUserDropdown');
    
    if (adminUserDropdown) {
        adminUserDropdown.addEventListener('click', function(e) {
            e.stopPropagation();
            this.classList.toggle('active');
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', function() {
            adminUserDropdown.classList.remove('active');
        });

        // Prevent closing when clicking inside the menu
        const menu = adminUserDropdown.querySelector('.user-dropdown-menu');
        if (menu) {
            menu.addEventListener('click', function(e) {
                e.stopPropagation();
            });
        }
    }

    // --- HỆ THỐNG THÔNG BÁO ĐƠN HÀNG MỚI (NÂNG CẤP) ---
    let lastOrderId = localStorage.getItem('last_processed_order_id') || 0;
    const checkInterval = 5000; // 5 giây check 1 lần để giảm tải server
    const checkUrl = '<?php echo $prefix_actions; ?>check_new_orders.php';
    const ordersUrl = '<?php echo $prefix_actions; ?>quan_ly_don_hang.php';

    // Cấu hình Toast
    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 5000,
        timerProgressBar: true,
        didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer)
            toast.addEventListener('mouseleave', Swal.resumeTimer)
        }
    });

    function checkNewOrders() {
        if (typeof Swal === 'undefined') return;

        fetch(`${checkUrl}?last_id=${lastOrderId}`)
            .then(response => response.json())
            .then(data => {
                // 1. Cập nhật Badge
                const badge = document.getElementById('order-badge');
                if (badge) {
                    if (data.total_pending > 0) {
                        badge.innerText = data.total_pending;
                        badge.style.display = 'flex';
                    } else {
                        badge.style.display = 'none';
                    }
                }

                // 2. Xử lý thông báo
                if (lastOrderId == 0) {
                    // Lần đầu vào trang
                    if (data.total_pending > 0) {
                        Toast.fire({
                            icon: 'info',
                            title: `Bạn có ${data.total_pending} đơn hàng đang chờ xử lý`
                        });
                    }
                    // Đồng bộ ID mới nhất
                    lastOrderId = data.latest_id;
                    localStorage.setItem('last_processed_order_id', lastOrderId);
                } else if (data.new_orders > 0) {
                    // CÓ ĐƠN HÀNG MỚI PHÁT SINH
                    lastOrderId = data.latest_id;
                    localStorage.setItem('last_processed_order_id', lastOrderId);

                    // Phát âm thanh báo động
                    playNotificationSound();

                    // Hiển thị Popup
                    let orderInfo = data.orders.map(o => `<div style="text-align:left; padding:5px 0; border-bottom:1px solid #eee;"><b>#${o.id}</b>: ${o.customer} (${o.total})</div>`).join('');
                    
                    Swal.fire({
                        title: '<span style="color:#e11d48">🔔 ĐƠN HÀNG MỚI!</span>',
                        html: `<div style="margin:15px 0; font-size:16px;">Bạn vừa nhận được <b>${data.new_orders}</b> đơn hàng mới:</div>
                               <div style="background:#f8fafc; padding:10px; border-radius:10px; max-height:200px; overflow-y:auto;">${orderInfo}</div>`,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#4f46e5',
                        cancelButtonColor: '#94a3b8',
                        confirmButtonText: 'Xem danh sách đơn',
                        cancelButtonText: 'Để sau',
                        backdrop: `rgba(79, 70, 229, 0.1)`,
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = ordersUrl;
                        }
                    });
                } else if (data.latest_id > lastOrderId) {
                    // Cập nhật ID nếu có đơn nhưng không phải 'Mới đặt'
                    lastOrderId = data.latest_id;
                    localStorage.setItem('last_processed_order_id', lastOrderId);
                }
            })
            .catch(error => console.error('Lỗi kiểm tra đơn hàng:', error));
    }

    function playNotificationSound() {
        try {
            // Thử dùng nhiều nguồn âm thanh khác nhau để đảm bảo hoạt động
            const soundUrls = [
                'https://assets.mixkit.co/active_storage/sfx/2358/2358-preview.mp3', // Ding
                'https://notificationsounds.com/storage/sounds/file-sounds-1150-pristine.mp3'
            ];
            let audio = new Audio(soundUrls[0]);
            audio.play().catch(e => {
                console.log('Chặn âm thanh: Trình duyệt yêu cầu tương tác trước');
                // Hiển thị một thông báo nhỏ nhắc nhở bật âm thanh nếu cần
            });
        } catch(e) {}
    }

    // Load SweetAlert2 nếu chưa có
    if (typeof Swal === 'undefined') {
        let swalScript = document.createElement('script');
        swalScript.src = 'https://cdn.jsdelivr.net/npm/sweetalert2@11';
        swalScript.onload = () => {
            checkNewOrders();
            setInterval(checkNewOrders, checkInterval);
        };
        document.head.appendChild(swalScript);
    } else {
        checkNewOrders();
        setInterval(checkNewOrders, checkInterval);
    }
});
</script>
