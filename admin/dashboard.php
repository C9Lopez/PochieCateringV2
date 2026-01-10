<?php
require_once '../config/functions.php';
requireRole(['admin', 'super_admin']);

$settings = getSettings($conn);

$totalBookings = $conn->query("SELECT COUNT(*) as count FROM bookings")->fetch_assoc()['count'];
$newBookings = $conn->query("SELECT COUNT(*) as count FROM bookings WHERE status = 'new'")->fetch_assoc()['count'];
$totalRevenue = $conn->query("SELECT SUM(total_amount) as total FROM bookings WHERE status = 'completed' OR payment_status = 'paid'")->fetch_assoc()['total'] ?? 0;
$totalCustomers = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'customer'")->fetch_assoc()['count'];
$pendingPayments = $conn->query("SELECT COUNT(*) as count FROM payments WHERE status = 'pending'")->fetch_assoc()['count'];

$paidBookings = $conn->query("SELECT COUNT(*) as count FROM bookings WHERE payment_status = 'paid'")->fetch_assoc()['count'];
$completedBookings = $conn->query("SELECT COUNT(*) as count FROM bookings WHERE status = 'completed'")->fetch_assoc()['count'];

$allBookings = $conn->query("SELECT b.*, u.first_name, u.last_name, u.phone, p.name as package_name 
                             FROM bookings b 
                             LEFT JOIN users u ON b.customer_id = u.id 
                             LEFT JOIN packages p ON b.package_id = p.id 
                             ORDER BY b.created_at DESC");

$upcomingEvents = $conn->query("SELECT b.*, u.first_name, u.last_name, p.name as package_name 
                                FROM bookings b 
                                LEFT JOIN users u ON b.customer_id = u.id 
                                LEFT JOIN packages p ON b.package_id = p.id 
                                WHERE b.event_date >= CURDATE() AND b.status NOT IN ('cancelled', 'completed')
                                ORDER BY b.event_date ASC LIMIT 5");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - <?= $settings['site_name'] ?? 'Pochie Catering Services' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; background: #f8f9fa; }
        .stat-card { border-radius: 15px; border: none; transition: transform 0.3s; }
        .stat-card:hover { transform: translateY(-5px); }
        .stat-icon { width: 60px; height: 60px; border-radius: 15px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; }
    </style>
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="mb-1">Dashboard</h3>
                <p class="text-muted mb-0">Welcome back, <?= htmlspecialchars($_SESSION['user_name']) ?>!</p>
            </div>
            <div class="d-flex gap-2">
                <a href="export_all_pdf.php" class="btn btn-outline-danger">
                    <i class="bi bi-file-earmark-pdf me-1"></i> Export All Data (PDF)
                </a>
                <span class="badge bg-primary d-flex align-items-center"><?= ucfirst(str_replace('_', ' ', getUserRole())) ?></span>
            </div>
        </div>
        
        <div class="row g-4 mb-4">
            <div class="col-md-6 col-lg-3">
                <div class="card stat-card">
                    <div class="card-body d-flex align-items-center">
                        <div class="stat-icon bg-primary text-white me-3">
                            <i class="bi bi-calendar-check"></i>
                        </div>
                        <div>
                            <h3 class="mb-0"><?= $totalBookings ?></h3>
                            <small class="text-muted">Total Bookings</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="card stat-card">
                    <div class="card-body d-flex align-items-center">
                        <div class="stat-icon bg-warning text-white me-3">
                            <i class="bi bi-bell"></i>
                        </div>
                        <div>
                            <h3 class="mb-0"><?= $newBookings ?></h3>
                            <small class="text-muted">New Bookings</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="card stat-card">
                    <div class="card-body d-flex align-items-center">
                        <div class="stat-icon bg-success text-white me-3">
                            <i class="bi bi-currency-dollar"></i>
                        </div>
                        <div>
                            <h3 class="mb-0"><?= formatPrice($totalRevenue) ?></h3>
                            <small class="text-muted">Total Revenue</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="card stat-card">
                    <div class="card-body d-flex align-items-center">
                        <div class="stat-icon bg-info text-white me-3">
                            <i class="bi bi-people"></i>
                        </div>
                        <div>
                            <h3 class="mb-0"><?= $totalCustomers ?></h3>
                            <small class="text-muted">Customers</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row g-4 mb-4">
            <div class="col-md-4">
                <div class="card stat-card bg-light">
                    <div class="card-body text-center">
                        <h4 class="text-success mb-0"><?= $paidBookings ?></h4>
                        <small class="text-muted">Paid Bookings</small>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card stat-card bg-light">
                    <div class="card-body text-center">
                        <h4 class="text-secondary mb-0"><?= $completedBookings ?></h4>
                        <small class="text-muted">Completed Events</small>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card stat-card bg-light">
                    <div class="card-body text-center">
                        <h4 class="text-danger mb-0"><?= $pendingPayments ?></h4>
                        <small class="text-muted">Pending Payments</small>
                    </div>
                </div>
            </div>
        </div>
        
        <?php if ($pendingPayments > 0): ?>
        <div class="alert alert-warning">
            <i class="bi bi-exclamation-triangle me-2"></i>
            <strong><?= $pendingPayments ?></strong> payment(s) pending verification.
            <a href="<?= adminUrl('payments.php') ?>" class="alert-link">Review now</a>
        </div>
        <?php endif; ?>
        
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-list-ul me-2"></i>All Orders</h5>
                <a href="<?= adminUrl('bookings.php') ?>" class="btn btn-sm btn-primary">Manage All</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Booking #</th>
                                <th>Customer</th>
                                <th>Package</th>
                                <th>Event</th>
                                <th>Guests</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Payment</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($allBookings && $allBookings->num_rows > 0): ?>
                            <?php while($b = $allBookings->fetch_assoc()): 
                                $menuItems = $conn->query("SELECT mi.name, bmi.quantity FROM booking_menu_items bmi 
                                                          LEFT JOIN menu_items mi ON bmi.menu_item_id = mi.id 
                                                          WHERE bmi.booking_id = " . $b['id']);
                                $menuList = [];
                                while($mi = $menuItems->fetch_assoc()) {
                                    $menuList[] = $mi['name'] . ' x' . $mi['quantity'];
                                }
                            ?>
                            <tr>
                                <td>
                                    <strong><?= $b['booking_number'] ?></strong><br>
                                    <small class="text-muted"><?= formatDateTime($b['created_at']) ?></small>
                                </td>
                                <td>
                                    <?= htmlspecialchars($b['first_name'] . ' ' . $b['last_name']) ?><br>
                                    <small class="text-muted"><?= $b['phone'] ?></small>
                                </td>
                                <td>
                                    <strong><?= htmlspecialchars($b['package_name'] ?? 'N/A') ?></strong>
                                    <?php if (!empty($menuList)): ?>
                                    <br><small class="text-muted" title="<?= implode(', ', $menuList) ?>">
                                        + <?= count($menuList) ?> menu item(s)
                                    </small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong><?= htmlspecialchars($b['event_type']) ?></strong><br>
                                    <small class="text-muted"><?= formatDate($b['event_date']) ?></small>
                                </td>
                                <td><?= $b['number_of_guests'] ?> pax</td>
                                <td><strong><?= formatPrice($b['total_amount']) ?></strong></td>
                                <td><?= getStatusBadge($b['status']) ?></td>
                                <td><?= getPaymentBadge($b['payment_status']) ?></td>
                                <td>
                                    <a href="<?= adminUrl('booking-details.php?id=' . $b['id']) ?>" class="btn btn-sm btn-primary">
                                        <i class="bi bi-eye"></i> View
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                            <?php else: ?>
                            <tr><td colspan="9" class="text-center text-muted py-4">No bookings yet</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-calendar-event me-2"></i>Upcoming Events</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Event</th>
                                <th>Customer</th>
                                <th>Date</th>
                                <th>Guests</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($upcomingEvents && $upcomingEvents->num_rows > 0): ?>
                            <?php while($e = $upcomingEvents->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($e['event_type']) ?></strong><br>
                                    <small class="text-muted"><?= $e['package_name'] ?></small>
                                </td>
                                <td><?= $e['first_name'] . ' ' . $e['last_name'] ?></td>
                                <td><?= formatDate($e['event_date']) ?></td>
                                <td><?= $e['number_of_guests'] ?> pax</td>
                                <td><?= getStatusBadge($e['status']) ?></td>
                            </tr>
                            <?php endwhile; ?>
                            <?php else: ?>
                            <tr><td colspan="5" class="text-center text-muted py-4">No upcoming events</td></tr>
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