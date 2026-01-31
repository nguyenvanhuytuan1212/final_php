<?php
session_start();
include '../includes/db_connect.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    
    if ($product_id > 0) {
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
        
        if (isset($_SESSION['cart'][$product_id])) {
            echo json_encode(['status' => 'error', 'message' => 'Sản phẩm này đã có trong giỏ hàng!']);
            exit;
        } else {
            // Fetch product details
            $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
            $stmt->bind_param("i", $product_id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($row = $result->fetch_assoc()) {
                // Check if product is available
                if (isset($row['rental_status']) && $row['rental_status'] === 'rented') {
                    echo json_encode(['status' => 'error', 'message' => 'Sản phẩm này hiện đang được thuê và không có sẵn!']);
                    exit;
                }

                $_SESSION['cart'][$product_id] = [
                    'id' => $row['id'],
                    'name' => $row['name'],
                    'price' => $row['price_day'], // Default to day price
                    'image' => $row['image'],
                    'quantity' => 1
                ];
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Product not found']);
                exit;
            }
        }
        
        $total_items = 0;
        foreach($_SESSION['cart'] as $item) {
            $total_items += $item['quantity'];
        }
        
        echo json_encode(['status' => 'success', 'count' => $total_items]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid ID']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid Request Method']);
}
?>
