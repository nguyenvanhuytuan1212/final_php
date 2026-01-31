# HƯỚNG DẪN SỬ DỤNG TÍNH NĂNG MÔ TẢ CHI TIẾT SẢN PHẨM

## 📋 Tổng quan
Tính năng này cho phép admin thêm mô tả chi tiết và nhiều ảnh minh họa cho sản phẩm từ trang quản lý sản phẩm.

## 🔧 Cài đặt Database

### Bước 1: Chạy SQL Migration
1. Mở **phpMyAdmin** (http://localhost/phpmyadmin)
2. Chọn database của bạn (thường là `camera_rental` hoặc tên database bạn đang dùng)
3. Click vào tab **SQL**
4. Copy và paste nội dung từ file `database/add_product_details_columns.sql`
5. Click **Go** để thực thi

**Hoặc** bạn có thể chạy lệnh SQL này trực tiếp:

```sql
ALTER TABLE `products` 
ADD COLUMN `description` TEXT NULL AFTER `image_back`,
ADD COLUMN `detailed_description` TEXT NULL AFTER `description`,
ADD COLUMN `detail_image_1` VARCHAR(255) NULL AFTER `detailed_description`,
ADD COLUMN `detail_image_2` VARCHAR(255) NULL AFTER `detail_image_1`,
ADD COLUMN `detail_image_3` VARCHAR(255) NULL AFTER `detail_image_2`,
ADD COLUMN `detail_image_4` VARCHAR(255) NULL AFTER `detail_image_3`;
```

## 📝 Cách sử dụng

### Thêm sản phẩm mới với mô tả chi tiết:

1. Đăng nhập vào trang Admin
2. Vào **Quản lý sản phẩm**
3. Click nút **"Thêm Sản Phẩm Mới"**
4. Điền thông tin cơ bản:
   - Tên sản phẩm
   - Danh mục
   - Hãng sản xuất
   - Giá thuê theo buổi/ngày
   - Ảnh đại diện (Mặt trước)
   - Ảnh chi tiết (Mặt sau)

5. **MỚI**: Điền thông tin mô tả:
   - **Mô tả ngắn**: Mô tả ngắn gọn về sản phẩm (hiển thị trong danh sách)
   - **Mô tả chi tiết**: Mô tả đầy đủ về sản phẩm, tính năng, thông số kỹ thuật
   
6. **MỚI**: Upload ảnh chi tiết (tối đa 4 ảnh):
   - **Ảnh chi tiết 1**: Ảnh minh họa cho phần mô tả đầu tiên
   - **Ảnh chi tiết 2**: Ảnh minh họa cho phần mô tả thứ hai
   - **Ảnh chi tiết 3**: Ảnh minh họa cho phần mô tả thứ ba
   - **Ảnh chi tiết 4**: Ảnh minh họa cho phần mô tả thứ tư

7. Click **"Lưu Lại"**

### Chỉnh sửa sản phẩm:

1. Trong danh sách sản phẩm, click nút **Edit** (biểu tượng bút)
2. Cập nhật thông tin mô tả và ảnh chi tiết
3. Click **"Lưu Lại"**

**Lưu ý**: Khi chỉnh sửa, nếu không upload ảnh mới, ảnh cũ sẽ được giữ nguyên.

## 🎨 Hiển thị trên trang chi tiết sản phẩm

Khi khách hàng xem chi tiết sản phẩm (`chi_tiet_san_pham.php`):

### Nếu có ảnh chi tiết từ admin:
- Hiển thị **mô tả chi tiết** từ database
- Hiển thị **4 ảnh chi tiết** (nếu có upload)
- Mỗi ảnh đi kèm với tiêu đề và mô tả

### Nếu không có ảnh chi tiết:
- Hiển thị nội dung mặc định
- Sử dụng ảnh đại diện và ảnh mặt sau của sản phẩm

## 📂 Cấu trúc File đã thay đổi

### File đã cập nhật:
1. **`actions/quan_ly_san_pham.php`**
   - Thêm form fields cho mô tả và ảnh chi tiết
   - Cập nhật JavaScript để load dữ liệu khi edit

2. **`actions/xu_ly_san_pham.php`**
   - Xử lý upload 4 ảnh chi tiết
   - Lưu mô tả ngắn và mô tả chi tiết vào database
   - Cập nhật logic cho cả thêm mới và chỉnh sửa

3. **`HTML/chi_tiet_san_pham.php`**
   - Hiển thị mô tả chi tiết từ database
   - Hiển thị ảnh chi tiết động từ database
   - Fallback về nội dung mặc định nếu không có dữ liệu

4. **`CSS/san_pham.css`**
   - Thêm style cho bảng lịch thuê sản phẩm

### File mới:
1. **`database/add_product_details_columns.sql`**
   - SQL migration để thêm cột mới vào bảng products

## 🗄️ Cấu trúc Database mới

Bảng `products` giờ có thêm các cột:
- `description` (TEXT): Mô tả ngắn
- `detailed_description` (TEXT): Mô tả chi tiết đầy đủ
- `detail_image_1` (VARCHAR 255): Đường dẫn ảnh chi tiết 1
- `detail_image_2` (VARCHAR 255): Đường dẫn ảnh chi tiết 2
- `detail_image_3` (VARCHAR 255): Đường dẫn ảnh chi tiết 3
- `detail_image_4` (VARCHAR 255): Đường dẫn ảnh chi tiết 4

## ✅ Ví dụ sử dụng

### Ví dụ mô tả chi tiết:
```
Thiết kế của Nikon Z6 II là mẫu máy ảnh mirrorless kế nhiệm của Nikon Z6. 
Giống như Nikon Z6, Nikon Z6 II được thiết kế hoàn toàn bằng hợp kim Magie 
tạo độ chắc chắn, bền bỉ mang lại cảm giác thoải mái cho người dùng. 

Máy ảnh được trang bị màn hình LCD 3.2 inch, 2.1 triệu điểm ảnh kết hợp 
với các nút bấm thuận tiện trong quá trình sử dụng.

Cảm biến Full-frame BSI CMOS 24.5MP mang lại hình ảnh với độ phân giải cao.
```

### Ví dụ upload ảnh:
- **Ảnh chi tiết 1**: Ảnh cảm biến và bộ xử lý
- **Ảnh chi tiết 2**: Ảnh hệ thống lấy nét
- **Ảnh chi tiết 3**: Ảnh khả năng quay video
- **Ảnh chi tiết 4**: Ảnh cổng kết nối

## 🎯 Lợi ích

1. ✅ **Quản lý dễ dàng**: Admin có thể thêm/sửa mô tả và ảnh từ một nơi
2. ✅ **Linh hoạt**: Mỗi sản phẩm có thể có mô tả và ảnh riêng
3. ✅ **Chuyên nghiệp**: Trang chi tiết sản phẩm trông chuyên nghiệp hơn
4. ✅ **SEO tốt hơn**: Nội dung chi tiết giúp SEO tốt hơn
5. ✅ **Trải nghiệm người dùng**: Khách hàng có đủ thông tin để quyết định thuê

## 🔒 Bảo mật

- Tất cả file upload được kiểm tra định dạng (chỉ cho phép JPG, PNG, GIF, WEBP)
- Giới hạn kích thước file tối đa 5MB
- Kiểm tra quyền admin trước khi cho phép upload
- Sanitize filename để tránh lỗi bảo mật

## 📞 Hỗ trợ

Nếu có vấn đề, kiểm tra:
1. Database đã chạy migration chưa
2. Thư mục `uploads/` có quyền ghi (chmod 755)
3. PHP có bật extension GD để xử lý ảnh
4. Kiểm tra log lỗi trong PHP error log

---
**Phiên bản**: 1.0
**Ngày cập nhật**: 29/01/2026
