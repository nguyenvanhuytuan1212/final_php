<?php
/**
 * Migration Script: Add Product Detail Columns
 * Run this file once to add new columns to products table
 */

// Include database connection
include '../includes/db_connect.php';

echo "<h2>🔧 Running Database Migration...</h2>";
echo "<hr>";

// Check if columns already exist
$check_sql = "SHOW COLUMNS FROM products LIKE 'description'";
$result = $conn->query($check_sql);

if ($result->num_rows > 0) {
    echo "<p style='color: orange;'>⚠️ Columns already exist! Migration has been run before.</p>";
    echo "<p>If you want to re-run, please drop the columns first.</p>";
} else {
    // Run migration
    $migration_sql = "
        ALTER TABLE `products` 
        ADD COLUMN `description` TEXT NULL AFTER `image_back`,
        ADD COLUMN `detailed_description` TEXT NULL AFTER `description`,
        ADD COLUMN `detail_image_1` VARCHAR(255) NULL AFTER `detailed_description`,
        ADD COLUMN `detail_image_2` VARCHAR(255) NULL AFTER `detail_image_1`,
        ADD COLUMN `detail_image_3` VARCHAR(255) NULL AFTER `detail_image_2`,
        ADD COLUMN `detail_image_4` VARCHAR(255) NULL AFTER `detail_image_3`
    ";
    
    if ($conn->query($migration_sql) === TRUE) {
        echo "<p style='color: green; font-size: 18px;'>✅ <strong>Migration completed successfully!</strong></p>";
        echo "<hr>";
        echo "<h3>Added columns:</h3>";
        echo "<ul>";
        echo "<li>✅ <code>description</code> (TEXT) - Mô tả ngắn</li>";
        echo "<li>✅ <code>detailed_description</code> (TEXT) - Mô tả chi tiết</li>";
        echo "<li>✅ <code>detail_image_1</code> (VARCHAR 255) - Ảnh chi tiết 1</li>";
        echo "<li>✅ <code>detail_image_2</code> (VARCHAR 255) - Ảnh chi tiết 2</li>";
        echo "<li>✅ <code>detail_image_3</code> (VARCHAR 255) - Ảnh chi tiết 3</li>";
        echo "<li>✅ <code>detail_image_4</code> (VARCHAR 255) - Ảnh chi tiết 4</li>";
        echo "</ul>";
        echo "<hr>";
        echo "<p style='color: green;'>🎉 <strong>You can now use the new product detail features!</strong></p>";
        echo "<p><a href='../actions/quan_ly_san_pham.php' style='padding: 10px 20px; background: #28a745; color: white; text-decoration: none; border-radius: 5px;'>Go to Product Management</a></p>";
    } else {
        echo "<p style='color: red;'>❌ <strong>Error running migration:</strong></p>";
        echo "<pre style='background: #f8d7da; padding: 10px; border-radius: 5px;'>" . $conn->error . "</pre>";
        echo "<hr>";
        echo "<h3>Troubleshooting:</h3>";
        echo "<ul>";
        echo "<li>Make sure the <code>products</code> table exists</li>";
        echo "<li>Check if you have ALTER TABLE permissions</li>";
        echo "<li>Verify database connection settings</li>";
        echo "</ul>";
    }
}

// Show current table structure
echo "<hr>";
echo "<h3>📋 Current Products Table Structure:</h3>";
$columns_sql = "SHOW COLUMNS FROM products";
$columns_result = $conn->query($columns_sql);

if ($columns_result) {
    echo "<table border='1' cellpadding='10' cellspacing='0' style='border-collapse: collapse; width: 100%;'>";
    echo "<thead style='background: #007bff; color: white;'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    echo "</thead>";
    echo "<tbody>";
    
    while ($row = $columns_result->fetch_assoc()) {
        echo "<tr>";
        echo "<td><strong>" . htmlspecialchars($row['Field']) . "</strong></td>";
        echo "<td>" . htmlspecialchars($row['Type']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Null']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Key']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Default'] ?? 'NULL') . "</td>";
        echo "<td>" . htmlspecialchars($row['Extra']) . "</td>";
        echo "</tr>";
    }
    
    echo "</tbody>";
    echo "</table>";
}

$conn->close();

echo "<hr>";
echo "<p style='text-align: center; color: gray;'>Migration script completed at " . date('Y-m-d H:i:s') . "</p>";
?>

<style>
    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        max-width: 1000px;
        margin: 50px auto;
        padding: 20px;
        background: #f5f5f5;
    }
    h2 {
        color: #333;
    }
    code {
        background: #e9ecef;
        padding: 2px 6px;
        border-radius: 3px;
        font-family: 'Courier New', monospace;
    }
    table {
        background: white;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    table td, table th {
        text-align: left;
    }
</style>
