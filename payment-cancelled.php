<?php
/**
 * Payment Cancelled Handler
 * Called when customer cancels payment via PayMongo
 */
$pageTitle = "Payment Cancelled";
require_once 'config/functions.php';
requireLogin();

$bookingId = isset($_GET['booking_id']) ? (int)$_GET['booking_id'] : 0;
$sessionId = isset($_GET['session_id']) ? $_GET['session_id'] : '';

// Clean up the pending payment record that was created when initiating PayMongo checkout
// This prevents cancelled/abandoned payments from showing in Payment Management
if ($sessionId) {
    $cleanupStmt = $conn->prepare("DELETE FROM payments WHERE paymongo_session_id = ? AND status = 'pending'");
    $cleanupStmt->bind_param("s", $sessionId);
    $cleanupStmt->execute();
} elseif ($bookingId) {
    // If no session_id, delete recent pending PayMongo payments for this booking (within last 30 minutes)
    $cleanupStmt = $conn->prepare("DELETE FROM payments WHERE booking_id = ? AND payment_method = 'PayMongo' AND status = 'pending' AND created_at > DATE_SUB(NOW(), INTERVAL 30 MINUTE)");
    $cleanupStmt->bind_param("i", $bookingId);
    $cleanupStmt->execute();
}

require_once 'includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card border-secondary">
                <div class="card-body text-center py-5">
                    <div class="mb-4">
                        <i class="bi bi-x-circle-fill text-secondary" style="font-size: 5rem;"></i>
                    </div>
                    <h2 class="text-secondary mb-3">Payment Cancelled</h2>
                    <p class="text-muted mb-4">
                        Ang iyong payment ay na-cancel. Hindi pa nabawasan ang iyong GCash/Maya account.
                    </p>
                    
                    <div class="alert alert-info text-start">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>Gusto mo pa ring magbayad?</strong><br>
                        Pwede kang bumalik at subukang muli, o mag-upload ng manual payment proof.
                    </div>
                    
                    <div class="d-grid gap-2">
                        <?php if ($bookingId): ?>
                        <a href="<?= url('submit-payment.php?booking=' . $bookingId) ?>" class="btn btn-primary btn-lg">
                            <i class="bi bi-arrow-repeat me-2"></i>Try Again
                        </a>
                        <a href="<?= url('booking-details.php?id=' . $bookingId) ?>" class="btn btn-outline-secondary">
                            <i class="bi bi-eye me-2"></i>View Booking
                        </a>
                        <?php endif; ?>
                        <a href="<?= url('my-bookings.php') ?>" class="btn btn-outline-secondary">
                            <i class="bi bi-list me-2"></i>My Bookings
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
