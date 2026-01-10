<?php
require_once '../config/functions.php';
requireRole(['super_admin']);

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id > 0) {
    $stmt = $conn->prepare("SELECT content FROM terms_history WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
    
    header('Content-Type: application/json');
    echo json_encode($data);
} else {
    echo json_encode(['content' => 'Invalid ID']);
}
?>