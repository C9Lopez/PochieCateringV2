<?php
$pageTitle = "Submit Payment";
require_once 'includes/header.php';
requireLogin();

$bookingId = isset($_GET['booking']) ? (int)$_GET['booking'] : 0;

$booking = $conn->query("SELECT b.*, p.name as package_name FROM bookings b 
                         LEFT JOIN packages p ON b.package_id = p.id 
                         WHERE b.id = $bookingId AND b.customer_id = {$_SESSION['user_id']}")->fetch_assoc();

if (!$booking || $booking['status'] !== 'approved') {
    header('Location: ' . url('my-bookings.php'));
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $amount = (float)$_POST['amount'];
    $method = $conn->real_escape_string($_POST['payment_method']);
    $reference = $conn->real_escape_string($_POST['reference_number']);
    $proofPath = null;
    
    if (isset($_FILES['proof_image']) && $_FILES['proof_image']['size'] > 0) {
        $upload = uploadImage($_FILES['proof_image'], 'uploads/payments');
        if ($upload['success']) {
            $proofPath = $upload['path'];
        }
    }
    
    $stmt = $conn->prepare("INSERT INTO payments (booking_id, amount, payment_method, reference_number, proof_image) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("idsss", $bookingId, $amount, $method, $reference, $proofPath);
    
    if ($stmt->execute()) {
        $admins = $conn->query("SELECT id FROM users WHERE role IN ('admin', 'super_admin')");
        while ($admin = $admins->fetch_assoc()) {
            addNotification($conn, $admin['id'], 'Payment Submitted', "Payment submitted for booking #{$booking['booking_number']}", 'info', adminUrl("payments.php"));
        }
        
        header("Location: " . url("booking-details.php?id=$bookingId&payment_success=1"));
        exit();
    } else {
        $error = 'Failed to submit payment. Please try again.';
    }
}
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <a href="<?= url('booking-details.php?id=' . $bookingId) ?>" class="btn btn-outline-secondary mb-3"><i class="bi bi-arrow-left me-1"></i>Back to Booking</a>
            
            <div class="card">
                <div class="card-header text-white" style="background: linear-gradient(135deg, #f97316, #ea580c);">
                    <h5 class="mb-0"><i class="bi bi-credit-card me-2"></i>Submit Payment</h5>
                </div>
                <div class="card-body">
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?= $error ?></div>
                    <?php endif; ?>
                    
                    <div class="alert alert-info">
                        <strong>Booking:</strong> #<?= $booking['booking_number'] ?><br>
                        <strong>Package:</strong> <?= htmlspecialchars($booking['package_name']) ?><br>
                        <strong>Total Amount:</strong> <?= formatPrice($booking['total_amount']) ?>
                    </div>
                    
                    <div class="card bg-light mb-4">
                        <div class="card-body">
                            <h6>Payment Methods Accepted:</h6>
                            <ul class="mb-0">
                                <li><strong>GCash:</strong> 09123456789 (Pochie Catering Services)</li>
                                <li><strong>Bank Transfer:</strong> BDO 1234-5678-9012 (Pochie Catering Services)</li>
                                <li><strong>Maya:</strong> 09123456789</li>
                            </ul>
                        </div>
                    </div>
                    
                    <form method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label class="form-label">Payment Method *</label>
                            <select name="payment_method" class="form-select" required>
                                <option value="">Select payment method...</option>
                                <option value="GCash">GCash</option>
                                <option value="Maya">Maya</option>
                                <option value="BDO Bank Transfer">BDO Bank Transfer</option>
                                <option value="BPI Bank Transfer">BPI Bank Transfer</option>
                                <option value="Cash">Cash Payment</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Amount Paid *</label>
                            <div class="input-group">
                                <span class="input-group-text">₱</span>
                                <input type="number" name="amount" class="form-control" step="0.01" required 
                                       value="<?= $booking['total_amount'] * 0.5 ?>" min="1">
                            </div>
                            <small class="text-muted">Minimum 50% down payment: <?= formatPrice($booking['total_amount'] * 0.5) ?></small>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Reference Number *</label>
                            <input type="text" name="reference_number" class="form-control" required placeholder="Enter transaction reference number">
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label">Proof of Payment (Screenshot) *</label>
                            <input type="file" name="proof_image" class="form-control" accept="image/*" required>
                            <small class="text-muted">Upload screenshot of your payment confirmation</small>
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-lg w-100">
                            <i class="bi bi-send me-2"></i>Submit Payment
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
