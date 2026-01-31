<?php
session_start();
session_unset();
session_destroy();
header("Location: ../HTML/trang_chu.php");
exit();
?>
