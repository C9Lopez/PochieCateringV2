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

$payments = $conn->query("SELECT p.*, b.booking_number, b.total_amount as booking_total, u.first_name, u.last_name 
                          FROM payments p 
                          JOIN bookings b ON p.booking_id = b.id 
                          LEFT JOIN users u ON b.customer_id = u.id 
                          ORDER BY p.created_at DESC");
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
                            <tr>
                                <td><strong><?= $p['booking_number'] ?></strong></td>
                                <td><?= htmlspecialchars($p['first_name'] . ' ' . $p['last_name']) ?></td>
                                <td><?= formatPrice($p['amount']) ?></td>
                                <td><?= htmlspecialchars($p['payment_method'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($p['reference_number'] ?? '-') ?></td>
                                <td>
                                    <?php if (!empty($p['proof_image'])): ?>
                                        <a href="<?= $p['proof_image'] ?>" target="_blank" class="btn btn-sm btn-outline-primary"><i class="bi bi-image"></i></a>
                                    <?php else: ?>-<?php endif; ?>
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
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
