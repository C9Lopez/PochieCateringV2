<?php
require_once 'config/database.php';
$result = $conn->query("SELECT * FROM reviews");
$rows = [];
while($row = $result->fetch_assoc()) {
    $rows[] = $row;
}
echo json_encode($rows);
?>