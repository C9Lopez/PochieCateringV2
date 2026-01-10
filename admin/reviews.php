<?php
require_once '../config/functions.php';
requireRole(['admin', 'super_admin']);

$settings = getSettings($conn);

// Handle Delete Review
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM reviews WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $_SESSION['success'] = "Review deleted successfully.";
    } else {
        $_SESSION['error'] = "Error deleting review.";
    }
    header("Location: reviews.php");
    exit();
}

// Fetch all reviews
$reviews = $conn->query("SELECT r.*, u.first_name, u.last_name, b.booking_number 
                        FROM reviews r 
                        JOIN users u ON r.customer_id = u.id 
                        JOIN bookings b ON r.booking_id = b.id 
                        ORDER BY r.created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Reviews - <?= $settings['site_name'] ?? 'Pochie Catering Services' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; background: #f8f9fa; }
        .main-content { margin-left: 260px; padding: 28px; }
        .review-card { border-radius: 15px; border: none; box-shadow: 0 4px 20px rgba(0,0,0,0.05); }
        .stars { color: #ffc107; }
        @media (max-width: 991.98px) { .main-content { margin-left: 0; padding-top: 80px; } }
    </style>
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="mb-1">Customer Reviews</h3>
                <p class="text-muted mb-0">Manage all customer feedback and ratings</p>
            </div>
        </div>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <?= $_SESSION['success']; unset($_SESSION['success']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <?= $_SESSION['error']; unset($_SESSION['error']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="card review-card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Customer</th>
                                <th>Booking #</th>
                                <th>Rating</th>
                                <th>Comment</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($reviews && $reviews->num_rows > 0): ?>
                                <?php while($r = $reviews->fetch_assoc()): ?>
                                    <tr>
                                        <td><?= date('M d, Y', strtotime($r['created_at'])) ?></td>
                                        <td><?= htmlspecialchars($r['first_name'] . ' ' . $r['last_name']) ?></td>
                                        <td><a href="booking-details.php?id=<?= $r['booking_id'] ?>"><?= $r['booking_number'] ?></a></td>
                                        <td>
                                            <div class="stars">
                                                <?php for($i=1; $i<=5; $i++): ?>
                                                    <i class="bi bi-star<?= $i <= $r['rating'] ? '-fill' : '' ?>"></i>
                                                <?php endfor; ?>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="text-wrap" style="max-width: 300px;">
                                                <?= htmlspecialchars($r['comment']) ?>
                                            </div>
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmDelete(<?= $r['id'] ?>)">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">No reviews found</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        function confirmDelete(id) {
            if (confirm('Are you sure you want to delete this review?')) {
                window.location.href = 'reviews.php?delete=' + id;
            }
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>