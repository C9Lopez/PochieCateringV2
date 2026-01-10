<?php
require_once 'config/database.php';
$result = $conn->query("SELECT * FROM users WHERE id = 1");
echo json_encode($result->fetch_assoc());
?>