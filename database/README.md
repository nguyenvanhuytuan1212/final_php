# 🔧 HƯỚNG DẪN XỬ LÝ LẠI DATABASE

## 📋 Tổng quan

Thư mục này chứa các công cụ để xử lý lại và quản lý database của hệ thống BinBinCamera.

---

## 📁 Các file trong thư mục

### 1. `complete_database_setup.sql`
**File SQL hoàn chỉnh** để tạo lại toàn bộ database từ đầu.

**Cách sử dụng:**
1. Mở **phpMyAdmin** (http://localhost/phpmyadmin)
2. Chọn database `binbincamera` (hoặc tạo mới)
3. Click tab **SQL**
4. Copy toàn bộ nội dung file `complete_database_setup.sql`
5. Paste vào và click **Go**

**Lưu ý:** File này sẽ tạo các bảng nếu chưa có, không xóa dữ liệu cũ.

---

### 2. `reset_database.php` ⭐ **KHUYÊN DÙNG**
**File PHP tự động** với giao diện đẹp để xử lý database.

**Cách sử dụng:**
1. Mở trình duyệt
2. Truy cập: `http://localhost/php/database/reset_database.php`
3. Xem kết quả migration tự động

**Ưu điểm:**
- ✅ Giao diện trực quan, dễ theo dõi
- ✅ Tự động kiểm tra và thêm cột thiếu
- ✅ Không xóa dữ liệu cũ
- ✅ Tạo/reset tài khoản admin tự động
- ✅ Hiển thị chi tiết từng bước

---

### 3. `add_product_details_columns.sql`
File SQL để thêm các cột mô tả chi tiết cho sản phẩm.

**Cột được thêm:**
- `description` - Mô tả ngắn
- `detailed_description` - Mô tả chi tiết
- `detail_image_1` đến `detail_image_4` - Ảnh chi tiết

---

### 4. `run_migration.php`
File PHP cũ để chạy migration cho product details.

---

### 5. `setup_admin_version.sql` ⭐ **MỚI**
Script SQL để tạo một database riêng biệt dành cho Admin (**binbincamera_admin**).

**Mục đích:**
- Tách biệt môi trường Test/Admin với môi trường người dùng thật.
- Giúp admin thoải mái test dữ liệu mà không sợ ảnh hưởng đến database chính.
- Tạo bản sao cấu trúc và dữ liệu admin từ database gốc.

**Cách sử dụng:**
1. Chạy file này trong PHPMyAdmin
2. Database `binbincamera_admin` sẽ được tạo
3. Sửa file kết nối database (nếu cần test) để trỏ vào `binbincamera_admin`

---

### 6. Thư mục `backups/` ⭐ **MỚI**
Nơi lưu trữ các bản sao lưu database theo từng phần hợp lý:

- `structure.sql`: Chỉ sao lưu CẤU TRÚC (bảng, cột), không chứa dữ liệu.
- `admin_data.sql`: Chỉ sao lưu dữ liệu quản trị viên (bảng `users` where role='admin').
- `full_backup.sql`: Sao lưu TOÀN BỘ database (Cấu trúc + Dữ liệu).

---

## 🎯 Khi nào cần xử lý lại database?

### Trường hợp 1: Lỗi "Unknown column"
**Triệu chứng:** Báo lỗi thiếu cột trong database

**Giải pháp:**
```
Chạy: http://localhost/php/database/reset_database.php
```

### Trường hợp 2: Muốn thêm tính năng mới
**Triệu chứng:** Cần thêm cột mới cho bảng

**Giải pháp:**
1. Sửa file `reset_database.php`
2. Thêm cột vào phần CREATE TABLE
3. Chạy lại file

### Trường hợp 3: Quên mật khẩu admin
**Triệu chứng:** Không đăng nhập được admin

**Giải pháp:**
```
Chạy: http://localhost/php/database/reset_database.php
Tài khoản admin sẽ được reset về:
- Username: admin
- Password: admin123
```

### Trường hợp 4: Database bị lỗi hoàn toàn
**Triệu chứng:** Nhiều lỗi, không hoạt động

**Giải pháp:**
1. Backup dữ liệu quan trọng (nếu có)
2. Xóa database `binbincamera` trong phpMyAdmin
3. Chạy `reset_database.php` để tạo lại từ đầu

---

## 📊 Cấu trúc Database

### Bảng `users`
Lưu thông tin người dùng và admin
```
- id (PK)
- username (UNIQUE)
- password (hashed)
- email (UNIQUE)
- phone
- full_name
- role (user/admin)
- created_at
```

### Bảng `products`
Lưu thông tin sản phẩm cho thuê
```
- id (PK)
- name
- category (camera/lens/gimbal/den/phu_kien)
- brand
- price_session
- price_day
- image
- image_back
- description ⭐ MỚI
- detailed_description ⭐ MỚI
- detail_image_1 đến 4 ⭐ MỚI
- stock
- created_at
- updated_at
```

### Bảng `orders`
Lưu thông tin đơn hàng
```
- id (PK)
- user_id (FK)
- customer_name
- phone
- email
- address
- note
- facebook
- images
- total_money
- status
- created_at
- updated_at
```

### Bảng `order_details`
Lưu chi tiết sản phẩm trong đơn hàng
```
- id (PK)
- order_id (FK)
- product_id (FK)
- product_name ⭐ QUAN TRỌNG
- quantity
- price
```

### Bảng `contacts`
Lưu thông tin liên hệ từ khách hàng
```
- id (PK)
- name
- email
- phone
- message
- status
- created_at
```

---

## 🔐 Tài khoản mặc định

Sau khi chạy migration, hệ thống sẽ tạo tài khoản admin:

```
Username: admin
Password: admin123
Email: admin@binbincamera.com
Role: admin
```

**⚠️ QUAN TRỌNG:** Đổi mật khẩu ngay sau khi đăng nhập lần đầu!

---

## 🚀 Quy trình xử lý database khuyên dùng

### Bước 1: Backup (nếu có dữ liệu quan trọng)
```
1. Vào phpMyAdmin
2. Chọn database binbincamera
3. Click Export
4. Lưu file .sql
```

### Bước 2: Chạy Migration
```
Truy cập: http://localhost/php/database/reset_database.php
```

### Bước 3: Kiểm tra
```
1. Đăng nhập admin (admin/admin123)
2. Kiểm tra các trang:
   - Quản lý sản phẩm
   - Quản lý đơn hàng
   - Thêm sản phẩm mới
```

### Bước 4: Đổi mật khẩu admin
```
1. Đăng nhập admin
2. Vào trang đổi mật khẩu
3. Đổi sang mật khẩu mạnh
```

---

## ⚠️ Lưu ý quan trọng

1. **Không xóa dữ liệu:** File `reset_database.php` chỉ cập nhật cấu trúc, không xóa dữ liệu cũ

2. **Charset UTF8MB4:** Tất cả bảng dùng `utf8mb4_unicode_ci` để hỗ trợ tiếng Việt đầy đủ

3. **Foreign Keys:** Các bảng có liên kết với nhau:
   - `orders.user_id` → `users.id`
   - `order_details.order_id` → `orders.id`
   - `order_details.product_id` → `products.id`

4. **Indexes:** Đã tạo indexes cho các cột thường xuyên tìm kiếm để tăng tốc độ

---

## 🆘 Troubleshooting

### Lỗi: "Access denied for user"
**Nguyên nhân:** Sai thông tin kết nối database

**Giải pháp:**
```php
// Kiểm tra file: includes/db_connect.php
$servername = "localhost";
$username = "root";
$password = "";  // Để trống nếu dùng XAMPP mặc định
$dbname = "binbincamera";
```

### Lỗi: "Table doesn't exist"
**Nguyên nhân:** Chưa chạy migration

**Giải pháp:**
```
Chạy: http://localhost/php/database/reset_database.php
```

### Lỗi: "Duplicate column name"
**Nguyên nhân:** Cột đã tồn tại

**Giải pháp:**
```
Không cần làm gì, file migration tự động kiểm tra
```

### XAMPP không khởi động được MySQL
**Nguyên nhân:** Port 3306 bị chiếm

**Giải pháp:**
```
1. Mở XAMPP Control Panel
2. Click Config (MySQL)
3. Đổi port sang 3307
4. Sửa db_connect.php thêm port
```

---

## 📞 Hỗ trợ

Nếu gặp vấn đề, kiểm tra:
1. ✅ XAMPP đã bật MySQL chưa?
2. ✅ File `db_connect.php` có đúng thông tin?
3. ✅ Đã chạy `reset_database.php` chưa?
4. ✅ Có lỗi hiển thị trên màn hình không?

---

## 📝 Changelog

### Version 2.0 (2026-01-29)
- ✨ Thêm file `complete_database_setup.sql`
- ✨ Tạo file `reset_database.php` với giao diện đẹp
- ✨ Thêm cột `description` và `detailed_description` cho products
- ✨ Thêm 4 cột `detail_image` cho ảnh chi tiết sản phẩm
- ✨ Thêm bảng `contacts` cho form liên hệ
- ✨ Cải thiện indexes và foreign keys
- ✨ Tự động tạo/reset tài khoản admin

### Version 1.0
- 🎉 Phiên bản đầu tiên với các bảng cơ bản

---

**Made with ❤️ for BinBinCamera**
