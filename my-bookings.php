<?php
$pageTitle = "My Bookings";
require_once 'includes/header.php';
requireLogin();

$bookings = $conn->query("SELECT b.*, p.name as package_name,
                          (SELECT COUNT(*) FROM chat_messages cm WHERE cm.booking_id = b.id AND cm.sender_id != {$_SESSION['user_id']} AND cm.is_read = 0) as unread_messages
                          FROM bookings b 
                          LEFT JOIN packages p ON b.package_id = p.id 
                          WHERE b.customer_id = {$_SESSION['user_id']} 
                          ORDER BY b.created_at DESC");
?>

<style>
/* Mobile-friendly booking cards */
@media (max-width: 767.98px) {
    .bookings-table { display: none; }
    .bookings-mobile { display: block; }
}
@media (min-width: 768px) {
    .bookings-table { display: block; }
    .bookings-mobile { display: none; }
}

.booking-card {
    background: white;
    border-radius: 16px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.08);
    margin-bottom: 15px;
    overflow: hidden;
}

.booking-card-header {
    background: linear-gradient(135deg, #f97316, #ea580c);
    color: white;
    padding: 15px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.booking-card-header .booking-num {
    font-weight: 700;
    font-size: 14px;
}

.booking-card-body {
    padding: 15px;
}

.booking-card-row {
    display: flex;
    justify-content: space-between;
    padding: 8px 0;
    border-bottom: 1px solid #f1f5f9;
    font-size: 13px;
}

.booking-card-row:last-child {
    border-bottom: none;
}

.booking-card-row .label {
    color: #64748b;
    font-weight: 500;
}

.booking-card-row .value {
    font-weight: 600;
    color: #1e293b;
    text-align: right;
}

.booking-card-footer {
    padding: 15px;
    background: #f8fafc;
    display: flex;
    gap: 10px;
}

.booking-card-footer .btn {
    flex: 1;
    text-align: center;
    font-size: 13px;
    padding: 10px;
}

@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.6; }
}

.btn-pulse {
    animation: pulse 2s infinite;
}
</style>

<div class="container py-5">
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center mb-4 gap-2">
        <h2 class="mb-0"><i class="bi bi-calendar-check me-2"></i>My Bookings</h2>
        <a href="<?= url('book.php') ?>" class="btn btn-primary"><i class="bi bi-plus-lg me-2"></i>New Booking</a>
    </div>
    
    <?php if ($bookings->num_rows > 0): ?>
        <!-- Desktop Table View -->
        <div class="bookings-table">
            <div class="table-responsive">
                <table class="table table-hover bg-white rounded-4 shadow-sm overflow-hidden">
                    <thead class="table-light">
                        <tr>
                            <th>Booking #</th>
                            <th>Package</th>
                            <th>Event Date</th>
                            <th>Guests</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Payment</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $bookings->data_seek(0);
                        while($booking = $bookings->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <strong><?= htmlspecialchars($booking['booking_number']) ?></strong>
                                <?php if ($booking['unread_messages'] > 0): ?>
                                <span class="badge bg-danger ms-1" style="font-size: 10px; animation: pulse 2s infinite;">
                                    <?= $booking['unread_messages'] ?> new
                                </span>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($booking['package_name']) ?></td>
                            <td><?= formatDate($booking['event_date']) ?></td>
                            <td><?= $booking['number_of_guests'] ?> pax</td>
                            <td><?= formatPrice($booking['total_amount']) ?></td>
                            <td><?= getStatusBadge($booking['status']) ?></td>
                            <td><?= getPaymentBadge($booking['payment_status']) ?></td>
                            <td>
                                <div class="btn-group">
                                    <a href="<?= url('booking-details.php?id=' . $booking['id']) ?>" class="btn btn-sm btn-primary <?= $booking['unread_messages'] > 0 ? 'btn-pulse' : '' ?>">
                                        <i class="bi bi-eye"></i> View
                                        <?php if ($booking['unread_messages'] > 0): ?>
                                        <i class="bi bi-chat-dots-fill ms-1"></i>
                                        <?php endif; ?>
                                    </a>
                                      <a href="<?= url('export_booking_pdf.php?id=' . $booking['id']) ?>" class="btn btn-sm btn-danger" title="Download PDF Copy">
                                          <i class="bi bi-file-earmark-pdf"></i>
                                      </a>
                                  </div>
                              </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Mobile Card View -->
        <div class="bookings-mobile">
            <?php 
            $bookings->data_seek(0);
            while($booking = $bookings->fetch_assoc()): ?>
            <div class="booking-card">
                <div class="booking-card-header">
                    <span class="booking-num"><?= htmlspecialchars($booking['booking_number']) ?></span>
                    <?php if ($booking['unread_messages'] > 0): ?>
                    <span class="badge bg-danger" style="font-size: 10px;">
                        <?= $booking['unread_messages'] ?> new message<?= $booking['unread_messages'] > 1 ? 's' : '' ?>
                    </span>
                    <?php else: ?>
                    <?= getStatusBadge($booking['status']) ?>
                    <?php endif; ?>
                </div>
                <div class="booking-card-body">
                    <div class="booking-card-row">
                        <span class="label">Package</span>
                        <span class="value"><?= htmlspecialchars($booking['package_name']) ?></span>
                    </div>
                    <div class="booking-card-row">
                        <span class="label">Event Date</span>
                        <span class="value"><?= formatDate($booking['event_date']) ?></span>
                    </div>
                    <div class="booking-card-row">
                        <span class="label">Guests</span>
                        <span class="value"><?= $booking['number_of_guests'] ?> pax</span>
                    </div>
                    <div class="booking-card-row">
                        <span class="label">Total</span>
                        <span class="value" style="color: #f97316;"><?= formatPrice($booking['total_amount']) ?></span>
                    </div>
                    <div class="booking-card-row">
                        <span class="label">Status</span>
                        <span class="value"><?= getStatusBadge($booking['status']) ?></span>
                    </div>
                    <div class="booking-card-row">
                        <span class="label">Payment</span>
                        <span class="value"><?= getPaymentBadge($booking['payment_status']) ?></span>
                    </div>
                </div>
                <div class="booking-card-footer">
                    <a href="<?= url('booking-details.php?id=' . $booking['id']) ?>" class="btn btn-primary <?= $booking['unread_messages'] > 0 ? 'btn-pulse' : '' ?>">
                        <i class="bi bi-eye me-1"></i>View Details
                        <?php if ($booking['unread_messages'] > 0): ?>
                        <i class="bi bi-chat-dots-fill ms-1"></i>
                        <?php endif; ?>
                    </a>
                    <a href="<?= url('export_booking_pdf.php?id=' . $booking['id']) ?>" class="btn btn-danger">
                        <i class="bi bi-file-earmark-pdf me-1"></i>PDF
                    </a>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="text-center py-5 bg-white rounded-4 shadow-sm">
            <i class="bi bi-calendar-x display-1 text-muted"></i>
            <h4 class="mt-3">No Bookings Yet</h4>
            <p class="text-muted">You haven't made any bookings. Start by creating your first one!</p>
            <a href="<?= url('book.php') ?>" class="btn btn-primary btn-lg"><i class="bi bi-plus-lg me-2"></i>Create Booking</a>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>