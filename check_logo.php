<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once 'config/database.php';
$db = new Database();
$conn = $db->getConnection();
$stmt = $conn->query("SELECT * FROM site_settings");
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "<pre>";
print_r($results);
echo "</pre>";
?>