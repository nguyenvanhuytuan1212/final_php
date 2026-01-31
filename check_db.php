<?php
include 'includes/db_connect.php';
$result = $conn->query("DESCRIBE orders");
while($row = $result->fetch_assoc()) {
    print_r($row);
}
?>
