-- =====================================================
-- BINBINCAMERA - COMPLETE DATABASE SETUP
-- =====================================================
-- File này tạo lại toàn bộ cấu trúc database
-- Chạy file này trong phpMyAdmin để thiết lập database hoàn chỉnh
-- =====================================================

-- Tạo database nếu chưa có
CREATE DATABASE IF NOT EXISTS `binbincamera` 
DEFAULT CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE `binbincamera`;

-- =====================================================
-- 1. BẢNG USERS (Người dùng)
-- =====================================================
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `phone` VARCHAR(20) DEFAULT NULL,
  `full_name` VARCHAR(100) DEFAULT NULL,
  `role` VARCHAR(20) DEFAULT 'user',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_username` (`username`),
  INDEX `idx_email` (`email`),
  INDEX `idx_role` (`role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 2. BẢNG PRODUCTS (Sản phẩm)
-- =====================================================
CREATE TABLE IF NOT EXISTS `products` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `category` VARCHAR(50) DEFAULT NULL,
  `brand` VARCHAR(50) DEFAULT NULL,
  `price_session` DECIMAL(15,2) DEFAULT NULL,
  `price_day` DECIMAL(15,2) DEFAULT NULL,
  `image` VARCHAR(255) DEFAULT NULL,
  `image_back` VARCHAR(255) DEFAULT NULL,
  `description` TEXT DEFAULT NULL,
  `detailed_description` TEXT DEFAULT NULL,
  `detail_image_1` VARCHAR(255) DEFAULT NULL,
  `detail_image_2` VARCHAR(255) DEFAULT NULL,
  `detail_image_3` VARCHAR(255) DEFAULT NULL,
  `detail_image_4` VARCHAR(255) DEFAULT NULL,
  `stock` INT(11) DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_category` (`category`),
  INDEX `idx_brand` (`brand`),
  INDEX `idx_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 3. BẢNG ORDERS (Đơn hàng)
-- =====================================================
CREATE TABLE IF NOT EXISTS `orders` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) DEFAULT NULL,
  `customer_name` VARCHAR(100) NOT NULL,
  `phone` VARCHAR(20) NOT NULL,
  `email` VARCHAR(100) DEFAULT NULL,
  `address` VARCHAR(255) DEFAULT NULL,
  `note` TEXT DEFAULT NULL,
  `facebook` VARCHAR(255) DEFAULT NULL,
  `images` TEXT DEFAULT NULL,
  `total_money` DECIMAL(15,2) DEFAULT 0.00,
  `status` VARCHAR(50) DEFAULT 'Mới đặt',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_user_id` (`user_id`),
  INDEX `idx_status` (`status`),
  INDEX `idx_created_at` (`created_at`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 4. BẢNG ORDER_DETAILS (Chi tiết đơn hàng)
-- =====================================================
CREATE TABLE IF NOT EXISTS `order_details` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `order_id` INT(11) NOT NULL,
  `product_id` INT(11) NOT NULL,
  `product_name` VARCHAR(255) NOT NULL,
  `quantity` INT(11) NOT NULL DEFAULT 1,
  `price` DECIMAL(15,2) NOT NULL,
  `subtotal` DECIMAL(15,2) GENERATED ALWAYS AS (`quantity` * `price`) STORED,
  PRIMARY KEY (`id`),
  INDEX `idx_order_id` (`order_id`),
  INDEX `idx_product_id` (`product_id`),
  FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 5. BẢNG CONTACTS (Liên hệ)
-- =====================================================
CREATE TABLE IF NOT EXISTS `contacts` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL,
  `phone` VARCHAR(20) DEFAULT NULL,
  `message` TEXT NOT NULL,
  `status` VARCHAR(20) DEFAULT 'new',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_status` (`status`),
  INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 6. BẢNG CART (Giỏ hàng - Optional)
-- =====================================================
CREATE TABLE IF NOT EXISTS `cart` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) DEFAULT NULL,
  `session_id` VARCHAR(255) DEFAULT NULL,
  `product_id` INT(11) NOT NULL,
  `quantity` INT(11) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_user_id` (`user_id`),
  INDEX `idx_session_id` (`session_id`),
  INDEX `idx_product_id` (`product_id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- DỮ LIỆU MẶC ĐỊNH
-- =====================================================

-- Tạo tài khoản admin mặc định
-- Username: admin
-- Password: admin123 (đã được hash)
INSERT INTO `users` (`username`, `password`, `email`, `role`, `full_name`) 
VALUES (
  'admin', 
  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 
  'admin@binbincamera.com', 
  'admin',
  'Administrator'
) ON DUPLICATE KEY UPDATE 
  `role` = 'admin',
  `password` = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';

-- =====================================================
-- HOÀN TẤT
-- =====================================================
-- Database đã được thiết lập hoàn chỉnh!
-- Tài khoản admin: admin / admin123
-- =====================================================
