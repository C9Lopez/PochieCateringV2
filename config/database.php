<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'u964669618_Lopez');
define('DB_PASS', 'Huncho2003_');
define('DB_NAME', 'u964669618_Catering');

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");
?>