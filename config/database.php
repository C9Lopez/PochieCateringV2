<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'u964669618_don');
define('DB_PASS', 'Don1@34567');
define('DB_NAME', 'u964669618_cateringko');

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");
?>
