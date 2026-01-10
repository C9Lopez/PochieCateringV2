<?php
// Prevent any error reporting from breaking the JSON response
error_reporting(0);
ini_set('display_errors', 0);

require_once 'config/database.php';
require_once 'config/functions.php';
session_start();

header('Content-Type: application/json');

try {
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('Unauthorized');
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    $booking_id = isset($_POST['booking_id']) ? (int)$_POST['booking_id'] : 0;
    $rating = isset($_POST['rating']) ? (int)$_POST['rating'] : 0;
    $comment = isset($_POST['comment']) ? trim($_POST['comment']) : '';
    $user_id = $_SESSION['user_id'];

    // Validate input
    if ($booking_id <= 0 || $rating < 1 || $rating > 5) {
        throw new Exception('Invalid input');
    }

    // Check if booking exists, is completed, and belongs to user
    $stmt = $conn->prepare("SELECT id FROM bookings WHERE id = ? AND customer_id = ? AND status = 'completed'");
    if (!$stmt) throw new Exception("Database error: " . $conn->error);
    
    $stmt->bind_param("ii", $booking_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        throw new Exception('Booking not found or not eligible for review');
    }

    // Check if already reviewed
    $stmt = $conn->prepare("SELECT id FROM reviews WHERE booking_id = ?");
    if (!$stmt) throw new Exception("Database error: " . $conn->error);
    
    $stmt->bind_param("i", $booking_id);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        throw new Exception('You have already reviewed this booking');
    }

    // Insert review
    $stmt = $conn->prepare("INSERT INTO reviews (booking_id, customer_id, rating, comment) VALUES (?, ?, ?, ?)");
    if (!$stmt) throw new Exception("Database error: " . $conn->error);
    
    $stmt->bind_param("iiis", $booking_id, $user_id, $rating, $comment);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Review submitted successfully']);
    } else {
        throw new Exception('Error saving review: ' . $stmt->error);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
exit;
?>
