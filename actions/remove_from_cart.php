<?php
session_start();
if (isset($_POST['id'])) {
    $id = $_POST['id'];
    if (isset($_SESSION['cart'][$id])) {
        unset($_SESSION['cart'][$id]);
    }
}
echo json_encode(['status' => 'success']);
?>
