<?php
/**
 * Payment Success Handler
 * Called when customer completes payment via PayMongo
 */
$pageTitle = "Payment Successful";
require_once 'config/functions.php';
require_once 'config/paymongo.php';
requireLogin();

$sessionId = isset($_GET['session_id']) ? $_GET['session_id'] : '';
$bookingId = isset($_GET['booking_id']) ? (int)$_GET['booking_id'] : 0;

$paymentVerified = false;
$paymentDetails = null;
$error = '';

if ($sessionId) {
    // Verify the payment with PayMongo API
    $result = getCheckoutSession($sessionId);
    
    if ($result['success']) {
        $session = $result['data'];
        $status = $session['attributes']['payment_intent']['attributes']['status'] ?? 
                  $session['attributes']['status'] ?? 'unknown';
        
        // Get payment details from PayMongo response
        $paymentDetails = [
            'amount' => ($session['attributes']['line_items'][0]['amount'] ?? 0) / 100,
            'method' => $session['attributes']['payment_method_used'] ?? 'E-Wallet',
            'reference' => $session['attributes']['reference_number'] ?? $sessionId,
            'paymongo_payment_id' => $session['attributes']['payments'][0]['id'] ?? null
        ];
        
        // Check if payment was successful
        if (in_array($status, ['succeeded', 'paid', 'processing'])) {
            $paymentVerified = true;
            
            // Update payment record in database - INSTANT VERIFICATION
            $stmt = $conn->prepare("UPDATE payments SET status = 'verified', reference_number = ?, payment_method = ? WHERE paymongo_session_id = ?");
            $refNumber = $paymentDetails['reference'];
            $paymentMethod = 'PayMongo (' . $paymentDetails['method'] . ')';
            $stmt->bind_param("sss", $refNumber, $paymentMethod, $sessionId);
            $stmt->execute();
            
            // Update booking status if needed
            if ($bookingId) {
                // Check total paid using prepared statement
                $paidStmt = $conn->prepare("SELECT SUM(amount) as paid FROM payments WHERE booking_id = ? AND status = 'verified'");
                $paidStmt->bind_param("i", $bookingId);
                $paidStmt->execute();
                $totalPaid = $paidStmt->get_result()->fetch_assoc()['paid'] ?? 0;
                
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
                    
                    // Notify customer
                    addNotification($conn, $booking['customer_id'], 'Payment Verified (Instant)', 
                        "Your payment of " . formatPrice($paymentDetails['amount']) . " for booking #{$booking['booking_number']} has been automatically verified!", 
                        'success', url("booking-details.php?id=$bookingId"));
                    
                    // Notify admins
                    $admins = $conn->query("SELECT id FROM users WHERE role IN ('admin', 'super_admin')");
                    while ($admin = $admins->fetch_assoc()) {
                        addNotification($conn, $admin['id'], 'Online Payment Received (Auto-Verified)', 
                            "Payment of " . formatPrice($paymentDetails['amount']) . " for booking #{$booking['booking_number']} via {$paymentDetails['method']} - Reference: {$paymentDetails['reference']}", 
                            'info', "admin/payments.php");
                    }
                }
            }
        } elseif (in_array($status, ['failed', 'cancelled', 'expired'])) {
            // Payment failed - record it as failed instead of deleting
            $failedStmt = $conn->prepare("UPDATE payments SET status = 'failed', reference_number = ? WHERE paymongo_session_id = ?");
            $failedRef = 'FAILED-' . ($paymentDetails['reference'] ?? $sessionId);
            $failedStmt->bind_param("ss", $failedRef, $sessionId);
            $failedStmt->execute();
            
            $error = "Payment failed (Status: $status). The payment record has been saved for your reference.";
        } else {
            $error = "Payment status: $status. Please wait a few moments or contact support if you believe this is an error.";
        }
    } else {
        $error = $result['error'] ?? 'Unable to verify payment. Please contact support.';
    }
} else {
    $error = 'No payment session found.';
}

require_once 'includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <?php if ($paymentVerified): ?>
            <div class="card border-success">
                <div class="card-body text-center py-5">
                    <div class="mb-4">
                        <i class="bi bi-check-circle-fill text-success" style="font-size: 5rem;"></i>
                    </div>
                    <h2 class="text-success mb-3">Payment Successful!</h2>
                    <p class="text-muted mb-4">Salamat! Ang iyong bayad ay natanggap na.</p>
                    
                    <div class="bg-light rounded p-4 mb-4 text-start">
                        <h6 class="mb-3">Payment Details:</h6>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Amount Paid:</span>
                            <strong class="text-success"><?= formatPrice($paymentDetails['amount']) ?></strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Payment Method:</span>
                            <strong><?= htmlspecialchars($paymentDetails['method']) ?></strong>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Reference:</span>
                            <code><?= htmlspecialchars($paymentDetails['reference']) ?></code>
                        </div>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <?php if ($bookingId): ?>
                        <a href="<?= url('booking-details.php?id=' . $bookingId) ?>" class="btn btn-primary btn-lg">
                            <i class="bi bi-eye me-2"></i>View Booking Details
                        </a>
                        <?php endif; ?>
                        <a href="<?= url('my-bookings.php') ?>" class="btn btn-outline-secondary">
                            <i class="bi bi-list me-2"></i>My Bookings
                        </a>
                    </div>
                </div>
            </div>
            <?php else: ?>
            <div class="card border-warning">
                <div class="card-body text-center py-5">
                    <div class="mb-4">
                        <i class="bi bi-exclamation-triangle-fill text-warning" style="font-size: 5rem;"></i>
                    </div>
                    <h2 class="text-warning mb-3">Payment Verification Pending</h2>
                    <p class="text-muted mb-4">
                        <?= htmlspecialchars($error) ?>
                    </p>
                    
                    <div class="alert alert-info text-start">
                        <i class="bi bi-info-circle me-2"></i>
                        Kung nabayaran mo na, i-refresh ang page o mag-antay ng ilang minuto. 
                        Kung may problema pa rin, makipag-ugnayan sa amin.
                    </div>
                    
                    <div class="d-grid gap-2">
                        <a href="<?= url('my-bookings.php') ?>" class="btn btn-primary">
                            <i class="bi bi-list me-2"></i>My Bookings
                        </a>
                        <?php if ($bookingId): ?>
                        <a href="<?= url('submit-payment.php?booking=' . $bookingId) ?>" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-2"></i>Try Again
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
