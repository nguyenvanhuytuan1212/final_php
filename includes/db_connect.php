<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "binbincamera";

// Create connection
$conn = new mysqli($servername, $username, $password);

// Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

// Create database if not exists
$sql = "CREATE DATABASE IF NOT EXISTS $dbname CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
if ($conn->query($sql) === TRUE) {
  // echo "Database created successfully";
} else {
  echo "Error creating database: " . $conn->error;
}

// Select database
$conn->select_db($dbname);
$conn->set_charset("utf8mb4"); // Đảm bảo hiển thị tiếng Việt đúng
?>
