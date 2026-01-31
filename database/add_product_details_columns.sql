-- Migration: Add description and detail images columns to products table
-- Run this SQL in your phpMyAdmin or MySQL client

-- Add description column
ALTER TABLE `products` 
ADD COLUMN `description` TEXT NULL AFTER `image_back`,
ADD COLUMN `detailed_description` TEXT NULL AFTER `description`,
ADD COLUMN `detail_image_1` VARCHAR(255) NULL AFTER `detailed_description`,
ADD COLUMN `detail_image_2` VARCHAR(255) NULL AFTER `detail_image_1`,
ADD COLUMN `detail_image_3` VARCHAR(255) NULL AFTER `detail_image_2`,
ADD COLUMN `detail_image_4` VARCHAR(255) NULL AFTER `detail_image_3`;
