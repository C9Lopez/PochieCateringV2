<?php
/**
 * Create PayMongo Checkout Session for GCash/Maya Payment
 * This endpoint creates a checkout session and redirects to PayMongo
 */
require_once 'config/functions.php';
require_once 'config/paymongo.php';
requireLogin();

$bookingId = isset($_GET['booking']) ? (int)$_GET['booking'] : 0;
$paymentType = isset($_GET['type']) ? $_GET['type'] : 'full'; // 'downpayment' or 'full'

// Get booking details
$booking = $conn->query("SELECT b.*, p.name as package_name, u.first_name, u.last_name, u.email, u.phone 
                         FROM bookings b 
                         LEFT JOIN packages p ON b.package_id = p.id 
                         LEFT JOIN users u ON b.customer_id = u.id
                         WHERE b.id = $bookingId AND b.customer_id = {$_SESSION['user_id']}")->fetch_assoc();

if (!$booking) {
    header('Location: ' . url('my-bookings.php?error=booking_not_found'));
    exit();
}

// Check if booking is eligible for payment
if (!in_array($booking['status'], ['approved', 'paid', 'preparing'])) {
    header('Location: ' . url('booking-details.php?id=' . $bookingId . '&error=not_approved'));
    exit();
}

// Calculate amount based on payment type
$totalAmount = (float)$booking['total_amount'];
$paidAmount = 0;

// Get existing payments
$paymentsResult = $conn->query("SELECT SUM(amount) as paid FROM payments WHERE booking_id = $bookingId AND status = 'verified'");
if ($paymentsResult) {
    $paidAmount = (float)($paymentsResult->fetch_assoc()['paid'] ?? 0);
}

$remainingAmount = $totalAmount - $paidAmount;

if ($paymentType === 'downpayment') {
    // 50% downpayment
    $amount = $totalAmount * 0.5;
    if ($amount <= $paidAmount) {
        // Already paid downpayment, redirect to remaining
        $amount = $remainingAmount;
    }
} else {
    // Full remaining amount
    $amount = $remainingAmount;
}

// Ensure minimum amount
if ($amount < 20) {
    header('Location: ' . url('booking-details.php?id=' . $bookingId . '&error=min_amount'));
    exit();
}

// Check if already fully paid
if ($remainingAmount <= 0) {
    header('Location: ' . url('booking-details.php?id=' . $bookingId . '&success=already_paid'));
    exit();
}

// Create checkout session
$result = createCheckoutSession([
    'amount' => $amount,
    'description' => $booking['package_name'] . ' - ' . $booking['event_type'],
    'booking_id' => $bookingId,
    'booking_number' => $booking['booking_number'],
    'customer_email' => $booking['email'],
    'customer_name' => $booking['first_name'] . ' ' . $booking['last_name'],
    'customer_phone' => $booking['phone'],
    'payment_methods' => ['gcash', 'grab_pay', 'paymaya'] // Enable all e-wallet options
]);

if ($result['success']) {
    // Store session in database for tracking
    $sessionId = $conn->real_escape_string($result['session_id']);
    $stmt = $conn->prepare("INSERT INTO payments (booking_id, amount, payment_method, reference_number, status, paymongo_session_id) VALUES (?, ?, 'PayMongo', ?, 'pending', ?)");
    $refNumber = 'PM-' . time();
    $stmt->bind_param("idss", $bookingId, $amount, $refNumber, $sessionId);
    $stmt->execute();
    
    // Redirect to PayMongo checkout
    header('Location: ' . $result['checkout_url']);
    exit();
} else {
    // Error creating session
    $error = urlencode($result['error'] ?? 'Failed to create payment session');
    header('Location: ' . url('submit-payment.php?booking=' . $bookingId . '&error=' . $error));
    exit();
}
?>
