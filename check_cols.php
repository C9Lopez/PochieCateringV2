<?php
require_once 'config/database.php';
$result = $conn->query("SHOW COLUMNS FROM reviews");
$cols = [];
while($row = $result->fetch_assoc()) {
    $cols[] = $row;
}
echo json_encode($cols);
?>