<?php
require_once '../config/functions.php';
requireRole(['admin', 'super_admin']);

$message = '';
$messageType = '';

// Handle delete booking
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_booking'])) {
    $deleteId = (int)$_POST['booking_id'];
    // Temporarily disable exception mode to allow error suppression
    mysqli_report(MYSQLI_REPORT_OFF);
    // Delete related records first (tables may not exist)
    $delMsgStmt = $conn->prepare("DELETE FROM chat_messages WHERE booking_id = ?");
    $delMsgStmt->bind_param("i", $deleteId);
    $delMsgStmt->execute();
    
    $delMenuStmt = $conn->prepare("DELETE FROM booking_menu_items WHERE booking_id = ?");
    $delMenuStmt->bind_param("i", $deleteId);
    $delMenuStmt->execute();
    
    $delPayStmt = $conn->prepare("DELETE FROM payment_records WHERE booking_id = ?");
    $delPayStmt->bind_param("i", $deleteId);
    @$delPayStmt->execute(); // May fail if table doesn't exist
    
    // Re-enable exception mode
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    
    $delBookingStmt = $conn->prepare("DELETE FROM bookings WHERE id = ?");
    $delBookingStmt->bind_param("i", $deleteId);
    $delBookingStmt->execute();
    
    $message = 'Booking deleted successfully!';
    $messageType = 'success';
}

$statusFilter = $_GET['status'] ?? '';
$searchQuery = $_GET['search'] ?? '';
$userId = (int)$_SESSION['user_id'];

// Build query with prepared statement parameters
$params = [];
$types = "";

$sql = "SELECT b.*, u.first_name, u.last_name, u.email, u.phone, p.name as package_name,
        (SELECT COUNT(*) FROM chat_messages cm WHERE cm.booking_id = b.id AND cm.sender_id != ? AND cm.is_read = 0) as unread_messages,
        (SELECT COALESCE(SUM(amount), 0) FROM payments WHERE booking_id = b.id AND status = 'verified') as total_paid
        FROM bookings b 
        LEFT JOIN users u ON b.customer_id = u.id 
        LEFT JOIN packages p ON b.package_id = p.id 
        WHERE 1=1";

$params[] = $userId;
$types .= "i";

if ($statusFilter) {
    $statusFilter = $conn->real_escape_string($statusFilter);
    $sql .= " AND b.status = ?";
    $params[] = $statusFilter;
    $types .= "s";
}
if ($searchQuery) {
    $searchLike = "%" . $conn->real_escape_string($searchQuery) . "%";
    $sql .= " AND (b.booking_number LIKE ? OR u.first_name LIKE ? OR u.last_name LIKE ?)";
    $params[] = $searchLike;
    $params[] = $searchLike;
    $params[] = $searchLike;
    $types .= "sss";
}
$sql .= " ORDER BY b.created_at DESC";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$bookings = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="<?= url('uploads/settings/69838c04de6ad_281903bf-3ae0-48b7-a44e-acbb35e98438.jpg') ?>" type="image/jpg">
    <title>Manage Bookings - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; }
        @keyframes pulse {
            0%, 100% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.1); opacity: 0.8; }
        }
        .btn-pulse {
            animation: btn-pulse 2s infinite;
        }
        @keyframes btn-pulse {
            0%, 100% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.4); }
            50% { box-shadow: 0 0 0 8px rgba(239, 68, 68, 0); }
        }
    </style>
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
        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3>Manage Bookings</h3>
        </div>
        
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-4">
                        <input type="text" name="search" class="form-control" placeholder="Search booking # or customer..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
                    </div>
                    <div class="col-md-3">
                        <select name="status" class="form-select">
                            <option value="">All Statuses</option>
                            <option value="new" <?= ($statusFilter ?? '') == 'new' ? 'selected' : '' ?>>New</option>
                            <option value="pending" <?= ($statusFilter ?? '') == 'pending' ? 'selected' : '' ?>>Pending</option>
                            <option value="negotiating" <?= ($statusFilter ?? '') == 'negotiating' ? 'selected' : '' ?>>Negotiating</option>
                            <option value="approved" <?= ($statusFilter ?? '') == 'approved' ? 'selected' : '' ?>>Approved</option>
                            <option value="paid" <?= ($statusFilter ?? '') == 'paid' ? 'selected' : '' ?>>Paid</option>
                            <option value="completed" <?= ($statusFilter ?? '') == 'completed' ? 'selected' : '' ?>>Completed</option>
                            <option value="cancelled" <?= ($statusFilter ?? '') == 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100"><i class="bi bi-search me-1"></i>Filter</button>
                    </div>
                    <div class="col-md-2">
                        <a href="<?= adminUrl('bookings.php') ?>" class="btn btn-outline-secondary w-100">Reset</a>
                    </div>
                </form>
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
                                <th>Package</th>
                                <th>Event Date</th>
                                <th>Guests</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Payment</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($bookings && $bookings->num_rows > 0): ?>
                            <?php while($b = $bookings->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <strong><?= $b['booking_number'] ?></strong>
                                    <?php if ($b['unread_messages'] > 0): ?>
                                    <span class="badge bg-danger ms-1" style="font-size: 10px; animation: pulse 2s infinite;">
                                        <?= $b['unread_messages'] ?> <i class="bi bi-chat-dots-fill"></i>
                                    </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?= htmlspecialchars($b['first_name'] . ' ' . $b['last_name']) ?><br>
                                    <small class="text-muted"><?= $b['phone'] ?></small>
                                </td>
                                <td><?= htmlspecialchars($b['package_name'] ?? 'N/A') ?></td>
                                <td><?= formatDate($b['event_date']) ?></td>
                                <td><?= $b['number_of_guests'] ?></td>
                                <td><?= formatPrice($b['total_amount']) ?></td>
                                <td><?= getStatusBadge($b['status']) ?></td>
                                <td>
                                    <?php 
                                    $totalPaid = (float)$b['total_paid'];
                                    $totalAmount = (float)$b['total_amount'];
                                    $remaining = $totalAmount - $totalPaid;
                                    
                                    if ($totalPaid >= $totalAmount) {
                                        echo '<span class="badge bg-success">Fully Paid</span>';
                                    } elseif ($totalPaid > 0) {
                                        $percentage = round(($totalPaid / $totalAmount) * 100);
                                        echo '<span class="badge bg-warning text-dark">Partial ' . $percentage . '%</span>';
                                        echo '<br><small class="text-muted">' . formatPrice($totalPaid) . ' paid</small>';
                                    } else {
                                        echo '<span class="badge bg-danger">Unpaid</span>';
                                    }
                                    ?>
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <a href="<?= adminUrl('booking-details.php?id=' . $b['id']) ?>" class="btn btn-sm btn-primary <?= $b['unread_messages'] > 0 ? 'btn-pulse' : '' ?>">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-danger" onclick="confirmDelete(<?= $b['id'] ?>, '<?= $b['booking_number'] ?>')">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                            <?php else: ?>
                            <tr><td colspan="9" class="text-center text-muted py-4">No bookings found</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title"><i class="bi bi-exclamation-triangle me-2"></i>Delete Booking</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete booking <strong id="deleteBookingNumber"></strong>?</p>
                    <p class="text-danger mb-0"><small>This will also delete all related chat messages, menu items, and payment records. This action cannot be undone!</small></p>
                </div>
                <div class="modal-footer">
                    <form method="POST">
                        <input type="hidden" name="booking_id" id="deleteBookingId">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="delete_booking" class="btn btn-danger">Delete Booking</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function confirmDelete(id, bookingNumber) {
            document.getElementById('deleteBookingId').value = id;
            document.getElementById('deleteBookingNumber').textContent = '#' + bookingNumber;
            new bootstrap.Modal(document.getElementById('deleteModal')).show();
        }
    </script>
</body>
</html>