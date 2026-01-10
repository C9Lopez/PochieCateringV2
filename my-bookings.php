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

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-calendar-check me-2"></i>My Bookings</h2>
        <a href="<?= url('book.php') ?>" class="btn btn-primary"><i class="bi bi-plus-lg me-2"></i>New Booking</a>
    </div>
    
    <?php if ($bookings->num_rows > 0): ?>
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
                    <?php while($booking = $bookings->fetch_assoc()): ?>
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