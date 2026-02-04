<?php
/**
 * PayMongo Webhook Handler
 * Receives payment notifications from PayMongo
 * 
 * Setup: Register this URL in PayMongo Dashboard
 * URL: https://yourdomain.com/webhook-paymongo.php
 * Events: checkout_session.payment.paid, payment.paid, source.chargeable
 */
require_once 'config/database.php';
require_once 'config/paymongo.php';

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method not allowed');
}

// Get raw payload
$payload = file_get_contents('php://input');
$signatureHeader = $_SERVER['HTTP_PAYMONGO_SIGNATURE'] ?? '';

// Log webhook for debugging (remove in production)
$logFile = __DIR__ . '/logs/paymongo_webhook.log';
$logDir = dirname($logFile);
if (!file_exists($logDir)) {
    mkdir($logDir, 0777, true);
}
file_put_contents($logFile, date('Y-m-d H:i:s') . " - Received webhook\n" . $payload . "\n\n", FILE_APPEND);

// Verify signature (skip in development if no secret set)
if (!empty(PAYMONGO_WEBHOOK_SECRET) && !verifyWebhookSignature($payload, $signatureHeader)) {
    http_response_code(400);
    file_put_contents($logFile, date('Y-m-d H:i:s') . " - Invalid signature\n\n", FILE_APPEND);
    exit('Invalid signature');
}

// Parse event
$event = json_decode($payload, true);
if (!$event) {
    http_response_code(400);
    exit('Invalid JSON');
}

$eventType = $event['data']['attributes']['type'] ?? '';
$eventData = $event['data']['attributes']['data'] ?? [];

// Handle different event types
switch ($eventType) {
    case 'checkout_session.payment.paid':
        // Payment completed via checkout session
        $sessionId = $eventData['id'] ?? '';
        $attributes = $eventData['attributes'] ?? [];
        
        $amount = 0;
        if (!empty($attributes['line_items'])) {
            $amount = ($attributes['line_items'][0]['amount'] ?? 0) / 100;
        }
        
        $paymentMethod = $attributes['payment_method_used'] ?? 'PayMongo';
        $referenceNumber = $attributes['reference_number'] ?? $sessionId;
        
        if ($sessionId) {
            // Update payment record
            $stmt = $conn->prepare("UPDATE payments SET status = 'verified', payment_method = ?, reference_number = ? WHERE paymongo_session_id = ?");
            $stmt->bind_param("sss", $paymentMethod, $referenceNumber, $sessionId);
            $stmt->execute();
            
            // Get booking ID from payment
            $paymentStmt = $conn->prepare("SELECT booking_id, amount FROM payments WHERE paymongo_session_id = ?");
            $paymentStmt->bind_param("s", $sessionId);
            $paymentStmt->execute();
            $paymentResult = $paymentStmt->get_result();
            if ($paymentResult && $payment = $paymentResult->fetch_assoc()) {
                $bookingId = (int)$payment['booking_id'];
                
                // Check total paid and update booking
                $totalPaidStmt = $conn->prepare("SELECT SUM(amount) as paid FROM payments WHERE booking_id = ? AND status = 'verified'");
                $totalPaidStmt->bind_param("i", $bookingId);
                $totalPaidStmt->execute();
                $totalPaid = $totalPaidStmt->get_result()->fetch_assoc()['paid'] ?? 0;
                
                $bookingStmt = $conn->prepare("SELECT total_amount, customer_id, booking_number FROM bookings WHERE id = ?");
                $bookingStmt->bind_param("i", $bookingId);
                $bookingStmt->execute();
                $booking = $bookingStmt->get_result()->fetch_assoc();
                
                if ($booking) {
                    if ($totalPaid >= $booking['total_amount']) {
                        $updateStmt = $conn->prepare("UPDATE bookings SET payment_status = 'paid', status = 'paid' WHERE id = ?");
                        $updateStmt->bind_param("i", $bookingId);
                        $updateStmt->execute();
                    } else {
                        $updateStmt = $conn->prepare("UPDATE bookings SET payment_status = 'partial' WHERE id = ?");
                        $updateStmt->bind_param("i", $bookingId);
                        $updateStmt->execute();
                    }
                    
                    // Add notification
                    $notifStmt = $conn->prepare("INSERT INTO notifications (user_id, title, message, type, link) VALUES (?, ?, ?, 'success', ?)");
                    $title = 'Payment Received';
                    $message = "Your payment of PHP " . number_format($amount, 2) . " for booking #{$booking['booking_number']} has been verified!";
                    $link = "booking-details.php?id=$bookingId";
                    $notifStmt->bind_param("isss", $booking['customer_id'], $title, $message, $link);
                    $notifStmt->execute();
                    
                    // Notify admins
                    $admins = $conn->query("SELECT id FROM users WHERE role IN ('admin', 'super_admin')");
                    while ($admin = $admins->fetch_assoc()) {
                        $adminTitle = 'Online Payment Received';
                        $adminMessage = "Payment of PHP " . number_format($amount, 2) . " received for booking #{$booking['booking_number']} via $paymentMethod";
                        $adminLink = "admin/payments.php";
                        $notifStmt2 = $conn->prepare("INSERT INTO notifications (user_id, title, message, type, link) VALUES (?, ?, ?, 'info', ?)");
                        $notifStmt2->bind_param("isss", $admin['id'], $adminTitle, $adminMessage, $adminLink);
                        $notifStmt2->execute();
                    }
                }
            }
            
            file_put_contents($logFile, date('Y-m-d H:i:s') . " - Payment verified: $sessionId\n\n", FILE_APPEND);
        }
        break;
        
    case 'payment.paid':
        // Direct payment completed
        $paymentId = $eventData['id'] ?? '';
        $attributes = $eventData['attributes'] ?? [];
        $sourceId = $attributes['source']['id'] ?? '';
        
        if ($sourceId) {
            // Find payment by source ID and update
            $stmt = $conn->prepare("UPDATE payments SET status = 'verified' WHERE reference_number LIKE ?");
            $sourcePattern = '%' . $sourceId . '%';
            $stmt->bind_param("s", $sourcePattern);
            $stmt->execute();
            
            file_put_contents($logFile, date('Y-m-d H:i:s') . " - Direct payment verified: $paymentId\n\n", FILE_APPEND);
        }
        break;
        
    case 'payment.failed':
        // Payment failed
        $sessionId = $eventData['id'] ?? '';
        if ($sessionId) {
            $stmt = $conn->prepare("UPDATE payments SET status = 'rejected' WHERE paymongo_session_id = ?");
            $stmt->bind_param("s", $sessionId);
            $stmt->execute();
            
            file_put_contents($logFile, date('Y-m-d H:i:s') . " - Payment failed: $sessionId\n\n", FILE_APPEND);
        }
        break;
        
    default:
        file_put_contents($logFile, date('Y-m-d H:i:s') . " - Unknown event type: $eventType\n\n", FILE_APPEND);
}

// Return success response
http_response_code(200);
echo json_encode(['status' => 'success']);
?>
