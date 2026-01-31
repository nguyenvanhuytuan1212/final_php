# 🚀 QUICK START - XỬ LÝ LẠI DATABASE

## ⚡ Cách nhanh nhất (3 bước)

### Bước 1: Mở trình duyệt
```
http://localhost/php/database/
```

### Bước 2: Click nút "Chạy Migration"
Chọn công cụ **Reset Database** và click **🚀 Chạy Migration**

### Bước 3: Xem kết quả
Trang sẽ hiển thị chi tiết quá trình migration

---

## ✅ Kết quả mong đợi

Sau khi chạy xong, bạn sẽ thấy:

✅ Bảng `users` đã sẵn sàng  
✅ Bảng `products` đã sẵn sàng (với các cột mới)  
✅ Bảng `orders` đã sẵn sàng  
✅ Bảng `order_details` đã sẵn sàng  
✅ Bảng `contacts` đã sẵn sàng  
✅ Tài khoản admin đã được tạo/reset  

---

## 🔐 Đăng nhập Admin

```
URL: http://localhost/php/HTML/dang_nhap.php
Username: admin
Password: admin123
```

**⚠️ Nhớ đổi mật khẩu ngay!**

---

## 🆘 Nếu gặp lỗi

### Lỗi kết nối database
```
1. Kiểm tra XAMPP → MySQL đã chạy chưa?
2. Kiểm tra file: includes/db_connect.php
```

### Lỗi "Table doesn't exist"
```
Chạy lại migration: http://localhost/php/database/reset_database.php
```

### Lỗi "Access denied"
```
Sửa file includes/db_connect.php:
$username = "root";
$password = "";  // Để trống với XAMPP mặc định
```

---

## 📚 Đọc thêm

- **Hướng dẫn chi tiết:** `README.md`
- **Tổng kết:** `SUMMARY.md`
- **File SQL:** `complete_database_setup.sql`

---

**Chúc bạn thành công! 🎉**
