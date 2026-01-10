<?php
require_once 'config/database.php';
require_once 'config/functions.php';
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$booking_id = isset($_POST['booking_id']) ? (int)$_POST['booking_id'] : 0;
$rating = isset($_POST['rating']) ? (int)$_POST['rating'] : 0;
$comment = isset($_POST['comment']) ? trim($_POST['comment']) : '';
$user_id = $_SESSION['user_id'];

// Validate input
if ($booking_id <= 0 || $rating < 1 || $rating > 5) {
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    exit;
}

// Check if booking exists, is completed, and belongs to user
$stmt = $conn->prepare("SELECT id FROM bookings WHERE id = ? AND customer_id = ? AND status = 'completed'");
$stmt->bind_param("ii", $booking_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Booking not found or not eligible for review']);
    exit;
}

// Check if already reviewed
$stmt = $conn->prepare("SELECT id FROM reviews WHERE booking_id = ?");
$stmt->bind_param("i", $booking_id);
$stmt->execute();
if ($stmt->get_result()->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'You have already reviewed this booking']);
    exit;
}

// Insert review
$stmt = $conn->prepare("INSERT INTO reviews (booking_id, customer_id, rating, comment) VALUES (?, ?, ?, ?)");
$stmt->bind_param("iiis", $booking_id, $user_id, $rating, $comment);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Review submitted successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error saving review']);
}
?>
