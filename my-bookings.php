<?php
$pageTitle = "My Bookings";
require_once 'includes/header.php';
requireLogin();

$bookings = $conn->query("SELECT b.*, p.name as package_name,
                          (SELECT COUNT(*) FROM chat_messages cm WHERE cm.booking_id = b.id AND cm.sender_id != {$_SESSION['user_id']} AND cm.is_read = 0) as unread_messages,
                          (SELECT id FROM reviews r WHERE r.booking_id = b.id) as review_id
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
                                <?php if ($booking['status'] == 'completed'): ?>
                                    <?php if ($booking['review_id']): ?>
                                        <button class="btn btn-sm btn-success" disabled>
                                            <i class="bi bi-star-fill"></i> Reviewed
                                        </button>
                                    <?php else: ?>
                                        <button class="btn btn-sm btn-warning" onclick="openReviewModal(<?= $booking['id'] ?>, '<?= $booking['booking_number'] ?>')">
                                            <i class="bi bi-star"></i> Review
                                        </button>
                                    <?php endif; ?>
                                <?php endif; ?>
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

<!-- Review Modal -->
<div class="modal fade" id="reviewModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Rate Your Experience</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="reviewForm">
                <div class="modal-body">
                    <input type="hidden" name="booking_id" id="review_booking_id">
                    <div class="mb-3 text-center">
                        <h6 id="review_booking_number" class="text-muted mb-3"></h6>
                        <div class="star-rating h2">
                            <i class="bi bi-star rating-star" data-rating="1"></i>
                            <i class="bi bi-star rating-star" data-rating="2"></i>
                            <i class="bi bi-star rating-star" data-rating="3"></i>
                            <i class="bi bi-star rating-star" data-rating="4"></i>
                            <i class="bi bi-star rating-star" data-rating="5"></i>
                        </div>
                        <input type="hidden" name="rating" id="selected_rating" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Your Feedback</label>
                        <textarea class="form-control" name="comment" rows="4" placeholder="How was our service?"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">Submit Review</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.rating-star {
    cursor: pointer;
    color: #ccc;
    transition: color 0.2s;
}
.rating-star.active {
    color: #ffc107;
}
@keyframes pulse {
    0%, 100% { transform: scale(1); opacity: 1; }
    50% { transform: scale(1.1); opacity: 0.8; }
}

.btn-pulse {
    animation: btn-pulse 2s infinite;
}

@keyframes btn-pulse {
    0%, 100% { box-shadow: 0 0 0 0 rgba(249, 115, 22, 0.4); }
    50% { box-shadow: 0 0 0 8px rgba(249, 115, 22, 0); }
}
</style>

<script>
let reviewModal;
document.addEventListener('DOMContentLoaded', function() {
    reviewModal = new bootstrap.Modal(document.getElementById('reviewModal'));
    
    // Star rating logic
    const stars = document.querySelectorAll('.rating-star');
    stars.forEach(star => {
        star.addEventListener('click', function() {
            const rating = this.dataset.rating;
            document.getElementById('selected_rating').value = rating;
            
            stars.forEach(s => {
                if (s.dataset.rating <= rating) {
                    s.classList.replace('bi-star', 'bi-star-fill');
                    s.classList.add('active');
                } else {
                    s.classList.replace('bi-star-fill', 'bi-star');
                    s.classList.remove('active');
                }
            });
        });
    });

    // Form submission
    document.getElementById('reviewForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const rating = document.getElementById('selected_rating').value;
        if (!rating) {
            alert('Please select a rating');
            return;
        }

        const formData = new FormData(this);
        fetch('submit-review.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || 'Error submitting review');
            }
        });
    });
});

function openReviewModal(id, number) {
    document.getElementById('review_booking_id').value = id;
    document.getElementById('review_booking_number').innerText = 'Booking #' + number;
    reviewModal.show();
}
</script>

<?php require_once 'includes/footer.php'; ?>