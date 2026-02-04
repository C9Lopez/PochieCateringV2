<?php
require_once '../config/functions.php';
requireRole(['super_admin']);

$settings = getSettings($conn);
$startDate = $_GET['start'] ?? date('Y-m-01');
$endDate = $_GET['end'] ?? date('Y-m-t');

$totalBookings = $conn->query("SELECT COUNT(*) as count FROM bookings WHERE created_at BETWEEN '$startDate' AND '$endDate 23:59:59'")->fetch_assoc()['count'];
$completedBookings = $conn->query("SELECT COUNT(*) as count FROM bookings WHERE status = 'completed' AND created_at BETWEEN '$startDate' AND '$endDate 23:59:59'")->fetch_assoc()['count'];
$cancelledBookings = $conn->query("SELECT COUNT(*) as count FROM bookings WHERE status = 'cancelled' AND created_at BETWEEN '$startDate' AND '$endDate 23:59:59'")->fetch_assoc()['count'];
$paidBookings = $conn->query("SELECT COUNT(*) as count FROM bookings WHERE payment_status = 'paid' AND created_at BETWEEN '$startDate' AND '$endDate 23:59:59'")->fetch_assoc()['count'];

// Actual collected revenue from verified payments (includes partial/downpayments)
$collectedRevenue = $conn->query("SELECT SUM(p.amount) as total FROM payments p 
                                  JOIN bookings b ON p.booking_id = b.id 
                                  WHERE p.status = 'verified' 
                                  AND p.created_at BETWEEN '$startDate' AND '$endDate 23:59:59'")->fetch_assoc()['total'] ?? 0;

// Expected revenue from fully paid bookings
$paidRevenue = $conn->query("SELECT SUM(total_amount) as total FROM bookings WHERE payment_status = 'paid' AND created_at BETWEEN '$startDate' AND '$endDate 23:59:59'")->fetch_assoc()['total'] ?? 0;

// Pending/uncollected revenue (partial payments still pending)
$pendingRevenue = $conn->query("SELECT SUM(p.amount) as total FROM payments p 
                                WHERE p.status = 'pending' 
                                AND p.created_at BETWEEN '$startDate' AND '$endDate 23:59:59'")->fetch_assoc()['total'] ?? 0;

// Partial payments collected (downpayments on not-fully-paid bookings)
$partialCollected = $conn->query("SELECT SUM(p.amount) as total FROM payments p 
                                  JOIN bookings b ON p.booking_id = b.id 
                                  WHERE p.status = 'verified' 
                                  AND b.payment_status != 'paid'
                                  AND p.created_at BETWEEN '$startDate' AND '$endDate 23:59:59'")->fetch_assoc()['total'] ?? 0;

$popularPackages = $conn->query("SELECT p.name, COUNT(*) as booking_count, SUM(b.total_amount) as revenue
                                  FROM bookings b 
                                  LEFT JOIN packages p ON b.package_id = p.id 
                                  WHERE b.created_at BETWEEN '$startDate' AND '$endDate 23:59:59'
                                  GROUP BY p.id ORDER BY booking_count DESC LIMIT 5");

$monthlyRevenue = $conn->query("SELECT DATE_FORMAT(p.created_at, '%Y-%m') as month, 
                                 SUM(p.amount) as revenue, 
                                 COUNT(DISTINCT p.booking_id) as bookings,
                                 SUM(CASE WHEN p.amount >= b.total_amount * 0.95 THEN 1 ELSE 0 END) as full_payments,
                                 SUM(CASE WHEN p.amount < b.total_amount * 0.95 THEN 1 ELSE 0 END) as partial_payments
                                 FROM payments p
                                 JOIN bookings b ON p.booking_id = b.id
                                 WHERE p.status = 'verified' AND p.created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
                                 GROUP BY month ORDER BY month DESC");

$recentPaidBookings = $conn->query("SELECT b.*, u.first_name, u.last_name, p.name as package_name
                                    FROM bookings b 
                                    LEFT JOIN users u ON b.customer_id = u.id 
                                    LEFT JOIN packages p ON b.package_id = p.id
                                    WHERE (b.status = 'completed' OR b.payment_status = 'paid')
                                    AND b.created_at BETWEEN '$startDate' AND '$endDate 23:59:59'
                                    ORDER BY b.updated_at DESC LIMIT 10");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="<?= url('uploads/settings/69838c04de6ad_281903bf-3ae0-48b7-a44e-acbb35e98438.jpg') ?>" type="image/jpg">
    <title>Reports - <?= htmlspecialchars($settings['site_name'] ?? 'Admin') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>body { font-family: 'Poppins', sans-serif; }</style>
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="mb-0">Reports & Analytics</h3>
            <a href="export_all_pdf.php" class="btn btn-outline-danger">
                <i class="bi bi-file-earmark-pdf me-1"></i> Export Full Backup (PDF)
            </a>
        </div>
        
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label">Start Date</label>
                        <input type="date" name="start" class="form-control" value="<?= $startDate ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">End Date</label>
                        <input type="date" name="end" class="form-control" value="<?= $endDate ?>">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">Filter</button>
                    </div>
                </form>
            </div>
        </div>
        
        <div class="row g-4 mb-4">
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body text-center">
                        <h2 class="text-primary mb-1"><?= $totalBookings ?></h2>
                        <small class="text-muted">Total Bookings</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body text-center">
                        <h2 class="text-success mb-1"><?= $completedBookings ?></h2>
                        <small class="text-muted">Completed</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body text-center">
                        <h2 class="text-info mb-1"><?= $paidBookings ?></h2>
                        <small class="text-muted">Paid</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body text-center">
                        <h2 class="text-danger mb-1"><?= $cancelledBookings ?></h2>
                        <small class="text-muted">Cancelled</small>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row g-4 mb-4">
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body text-center">
                        <h3 class="mb-1"><?= formatPrice($collectedRevenue) ?></h3>
                        <small>Total Collected Revenue</small>
                        <div class="small opacity-75 mt-1">All verified payments</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body text-center">
                        <h3 class="mb-1"><?= formatPrice($paidRevenue) ?></h3>
                        <small>Fully Paid Bookings</small>
                        <div class="small opacity-75 mt-1">100% payment complete</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-dark">
                    <div class="card-body text-center">
                        <h3 class="mb-1"><?= formatPrice($partialCollected) ?></h3>
                        <small>Downpayments Collected</small>
                        <div class="small opacity-75 mt-1">50% partial payments</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-secondary text-white">
                    <div class="card-body text-center">
                        <h3 class="mb-1"><?= formatPrice($pendingRevenue) ?></h3>
                        <small>Pending Verification</small>
                        <div class="small opacity-75 mt-1">Awaiting admin approval</div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card mb-4">
            <div class="card-header bg-success text-white"><h5 class="mb-0"><i class="bi bi-cash-coin me-2"></i>Recent Paid/Completed Bookings</h5></div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Booking #</th>
                            <th>Customer</th>
                            <th>Package</th>
                            <th>Event Date</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Payment</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($recentPaidBookings && $recentPaidBookings->num_rows > 0): ?>
                        <?php while($b = $recentPaidBookings->fetch_assoc()): ?>
                        <tr>
                            <td><strong><?= $b['booking_number'] ?></strong></td>
                            <td><?= htmlspecialchars($b['first_name'] . ' ' . $b['last_name']) ?></td>
                            <td><?= htmlspecialchars($b['package_name'] ?? 'N/A') ?></td>
                            <td><?= formatDate($b['event_date']) ?></td>
                            <td><strong class="text-success"><?= formatPrice($b['total_amount']) ?></strong></td>
                            <td><?= getStatusBadge($b['status']) ?></td>
                            <td><?= getPaymentBadge($b['payment_status']) ?></td>
                        </tr>
                        <?php endwhile; ?>
                        <?php else: ?>
                        <tr><td colspan="7" class="text-center text-muted py-3">No paid/completed bookings in this period</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <div class="row">
            <div class="col-lg-6">
                <div class="card mb-4">
                    <div class="card-header"><h5 class="mb-0">Popular Packages</h5></div>
                    <div class="card-body p-0">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr><th>Package</th><th>Bookings</th><th>Revenue</th></tr>
                            </thead>
                            <tbody>
                                <?php if ($popularPackages && $popularPackages->num_rows > 0): ?>
                                <?php while($pkg = $popularPackages->fetch_assoc()): ?>
                                <tr>
                                    <td><?= htmlspecialchars($pkg['name'] ?? 'N/A') ?></td>
                                    <td><?= $pkg['booking_count'] ?></td>
                                    <td><?= formatPrice($pkg['revenue'] ?? 0) ?></td>
                                </tr>
                                <?php endwhile; ?>
                                <?php else: ?>
                                <tr><td colspan="3" class="text-center text-muted py-3">No data available</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card mb-4">
                    <div class="card-header"><h5 class="mb-0">Monthly Collected Revenue (Last 12 Months)</h5></div>
                    <div class="card-body p-0">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr><th>Month</th><th>Bookings</th><th>Full</th><th>Partial</th><th>Revenue</th></tr>
                            </thead>
                            <tbody>
                                <?php if ($monthlyRevenue && $monthlyRevenue->num_rows > 0): ?>
                                <?php while($m = $monthlyRevenue->fetch_assoc()): ?>
                                <tr>
                                    <td><?= date('F Y', strtotime($m['month'] . '-01')) ?></td>
                                    <td><?= $m['bookings'] ?></td>
                                    <td><span class="badge bg-success"><?= $m['full_payments'] ?></span></td>
                                    <td><span class="badge bg-warning text-dark"><?= $m['partial_payments'] ?></span></td>
                                    <td><strong class="text-success"><?= formatPrice($m['revenue']) ?></strong></td>
                                </tr>
                                <?php endwhile; ?>
                                <?php else: ?>
                                <tr><td colspan="5" class="text-center text-muted py-3">No verified payments yet</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>