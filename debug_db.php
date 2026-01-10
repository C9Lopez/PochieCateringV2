<?php
require_once 'config/database.php';

header('Content-Type: application/json');

$debug = [];

// Promotions
$promotions = $conn->query("SELECT * FROM promotions");
$debug['promotions'] = $promotions ? $promotions->fetch_all(MYSQLI_ASSOC) : [];

// Packages
$packages = $conn->query("SELECT * FROM packages");
$debug['packages'] = $packages ? $packages->fetch_all(MYSQLI_ASSOC) : [];

// Menu Items
$menuItems = $conn->query("SELECT id, name, is_featured, is_available FROM menu_items WHERE is_featured = 1");
$debug['featured_menu_items'] = $menuItems ? $menuItems->fetch_all(MYSQLI_ASSOC) : [];

echo json_encode($debug, JSON_PRETTY_PRINT);
?>