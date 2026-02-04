<?php
require_once '../config/functions.php';
requireRole(['admin', 'super_admin']);

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['verify_payment'])) {
        $paymentId = (int)$_POST['payment_id'];
        $status = sanitize($conn, $_POST['status']);
        $conn->query("UPDATE payments SET status = '$status', processed_by = {$_SESSION['user_id']} WHERE id = $paymentId");
        
        $payment = $conn->query("SELECT p.*, b.booking_number, b.customer_id FROM payments p JOIN bookings b ON p.booking_id = b.id WHERE p.id = $paymentId")->fetch_assoc();
        
        if ($status === 'verified') {
            $conn->query("UPDATE bookings SET payment_status = 'paid', status = 'paid' WHERE id = {$payment['booking_id']}");
            addNotification($conn, $payment['customer_id'], 'Payment Verified', "Your payment for booking #{$payment['booking_number']} has been verified!", 'success');
            $message = 'Payment verified successfully!';
        } else {
            $message = 'Payment rejected.';
        }
        $messageType = $status === 'verified' ? 'success' : 'warning';
    }
}

$payments = $conn->query("SELECT p.*, b.booking_number, b.total_amount as booking_total, b.event_date, u.first_name, u.last_name, u.phone 
                          FROM payments p 
                          JOIN bookings b ON p.booking_id = b.id 
                          LEFT JOIN users u ON b.customer_id = u.id 
                          ORDER BY p.created_at DESC");

// Get payment summary stats
$stats = $conn->query("SELECT 
    COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_count,
    COUNT(CASE WHEN status = 'verified' THEN 1 END) as verified_count,
    COUNT(CASE WHEN status = 'rejected' THEN 1 END) as rejected_count,
    SUM(CASE WHEN status = 'verified' THEN amount ELSE 0 END) as total_verified
    FROM payments")->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Payments - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>body { font-family: 'Poppins', sans-serif; }</style>
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="main-content">
        <?php if ($message): ?>
            <div class="alert alert-<?= $messageType ?> alert-dismissible fade show">
                <?= htmlspecialchars($message) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <h3 class="mb-4">Payment Management</h3>
        
        <!-- Payment Stats Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card border-warning">
                    <div class="card-body text-center">
                        <h3 class="text-warning mb-0"><?= $stats['pending_count'] ?? 0 ?></h3>
                        <small class="text-muted">Pending Verification</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-success">
                    <div class="card-body text-center">
                        <h3 class="text-success mb-0"><?= $stats['verified_count'] ?? 0 ?></h3>
                        <small class="text-muted">Verified Payments</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-danger">
                    <div class="card-body text-center">
                        <h3 class="text-danger mb-0"><?= $stats['rejected_count'] ?? 0 ?></h3>
                        <small class="text-muted">Rejected</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-primary">
                    <div class="card-body text-center">
                        <h3 class="text-primary mb-0"><?= formatPrice($stats['total_verified'] ?? 0) ?></h3>
                        <small class="text-muted">Total Verified Amount</small>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Booking #</th>
                                <th>Customer</th>
                                <th>Amount</th>
                                <th>Method</th>
                                <th>Reference</th>
                                <th>Proof</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($payments && $payments->num_rows > 0): ?>
                            <?php while($p = $payments->fetch_assoc()): ?>
                            <tr class="<?= $p['status'] === 'pending' ? 'table-warning' : '' ?>">
                                <td>
                                    <strong><?= $p['booking_number'] ?></strong>
                                    <br><small class="text-muted"><?= date('M d, Y', strtotime($p['event_date'])) ?></small>
                                </td>
                                <td>
                                    <?= htmlspecialchars($p['first_name'] . ' ' . $p['last_name']) ?>
                                    <?php if (!empty($p['phone'])): ?>
                                    <br><small class="text-muted"><i class="bi bi-phone"></i> <?= htmlspecialchars($p['phone']) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong><?= formatPrice($p['amount']) ?></strong>
                                    <br><small class="text-muted">of <?= formatPrice($p['booking_total']) ?></small>
                                </td>
                                <td>
                                    <span class="badge bg-<?= $p['payment_method'] === 'GCash' ? 'primary' : ($p['payment_method'] === 'Maya' ? 'success' : 'secondary') ?>">
                                        <?= htmlspecialchars($p['payment_method'] ?? 'N/A') ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if (!empty($p['reference_number'])): ?>
                                    <code class="bg-light px-2 py-1 rounded"><?= htmlspecialchars($p['reference_number']) ?></code>
                                    <?php else: ?>
                                    <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($p['proof_image'])): ?>
                                        <button type="button" class="btn btn-sm btn-outline-primary" 
                                                data-bs-toggle="modal" data-bs-target="#proofModal"
                                                data-image="<?= htmlspecialchars($p['proof_image']) ?>"
                                                data-booking="<?= $p['booking_number'] ?>"
                                                data-amount="<?= formatPrice($p['amount']) ?>"
                                                data-method="<?= htmlspecialchars($p['payment_method']) ?>"
                                                data-reference="<?= htmlspecialchars($p['reference_number']) ?>">
                                            <i class="bi bi-image"></i> View
                                        </button>
                                    <?php else: ?>
                                    <span class="text-muted">No proof</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-<?= $p['status'] == 'verified' ? 'success' : ($p['status'] == 'rejected' ? 'danger' : 'warning') ?>">
                                        <?= ucfirst($p['status']) ?>
                                    </span>
                                </td>
                                <td><?= formatDateTime($p['created_at']) ?></td>
                                <td>
                                    <?php if ($p['status'] === 'pending'): ?>
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="payment_id" value="<?= $p['id'] ?>">
                                        <input type="hidden" name="status" value="verified">
                                        <button type="submit" name="verify_payment" class="btn btn-sm btn-success" title="Approve">
                                            <i class="bi bi-check"></i>
                                        </button>
                                    </form>
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="payment_id" value="<?= $p['id'] ?>">
                                        <input type="hidden" name="status" value="rejected">
                                        <button type="submit" name="verify_payment" class="btn btn-sm btn-danger" title="Reject">
                                            <i class="bi bi-x"></i>
                                        </button>
                                    </form>
                                    <?php else: ?>
                                    <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                            <?php else: ?>
                            <tr><td colspan="9" class="text-center text-muted py-4">No payments found</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Proof Image Modal -->
    <div class="modal fade" id="proofModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-receipt me-2"></i>Payment Proof - <span id="modalBooking"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-8">
                            <img id="modalImage" src="" class="img-fluid rounded border w-100" alt="Payment Proof">
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="card-title mb-3">Payment Details</h6>
                                    <p class="mb-2">
                                        <small class="text-muted d-block">Amount</small>
                                        <strong id="modalAmount" class="fs-5 text-success"></strong>
                                    </p>
                                    <p class="mb-2">
                                        <small class="text-muted d-block">Method</small>
                                        <span id="modalMethod" class="badge bg-primary"></span>
                                    </p>
                                    <p class="mb-0">
                                        <small class="text-muted d-block">Reference Number</small>
                                        <code id="modalReference" class="fs-6"></code>
                                    </p>
                                </div>
                            </div>
                            <div class="alert alert-info mt-3 small">
                                <i class="bi bi-info-circle me-1"></i>
                                I-verify kung tama ang <strong>Reference Number</strong> at <strong>Amount</strong> sa iyong GCash/Maya/Bank app bago i-approve.
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <a id="modalImageLink" href="" target="_blank" class="btn btn-outline-secondary">
                        <i class="bi bi-box-arrow-up-right me-1"></i>Open Full Image
                    </a>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    // Handle proof modal
    document.getElementById('proofModal').addEventListener('show.bs.modal', function(event) {
        const button = event.relatedTarget;
        const image = button.getAttribute('data-image');
        const booking = button.getAttribute('data-booking');
        const amount = button.getAttribute('data-amount');
        const method = button.getAttribute('data-method');
        const reference = button.getAttribute('data-reference');
        
        document.getElementById('modalImage').src = image;
        document.getElementById('modalImageLink').href = image;
        document.getElementById('modalBooking').textContent = booking;
        document.getElementById('modalAmount').textContent = amount;
        document.getElementById('modalMethod').textContent = method;
        document.getElementById('modalReference').textContent = reference || '-';
    });
    </script>
</body>
</html>
