<?php
require_once '../config/functions.php';
requireRole(['staff']);

$staffId = $_SESSION['user_id'];

$staffNotifs = getStaffNotificationCounts($conn, $staffId);
$unreadBookingsStaff = getBookingsWithUnreadMessages($conn, $staffId, 'staff');

$assignedBookings = $conn->query("SELECT b.*, u.first_name, u.last_name, p.name as package_name,
                                   (SELECT COUNT(*) FROM chat_messages cm WHERE cm.booking_id = b.id AND cm.sender_id != $staffId AND cm.is_read = 0) as unread_messages
                                   FROM bookings b 
                                   LEFT JOIN users u ON b.customer_id = u.id 
                                   LEFT JOIN packages p ON b.package_id = p.id 
                                   WHERE b.assigned_staff_id = $staffId AND b.status NOT IN ('completed', 'cancelled')
                                   ORDER BY b.event_date ASC");

$todayBookings = $conn->query("SELECT COUNT(*) as count FROM bookings 
                               WHERE assigned_staff_id = $staffId AND event_date = CURDATE()")->fetch_assoc()['count'];

$upcomingBookings = $conn->query("SELECT COUNT(*) as count FROM bookings 
                                  WHERE assigned_staff_id = $staffId AND event_date > CURDATE() AND status NOT IN ('completed', 'cancelled')")->fetch_assoc()['count'];

$completedBookings = $conn->query("SELECT COUNT(*) as count FROM bookings 
                                   WHERE assigned_staff_id = $staffId AND status = 'completed'")->fetch_assoc()['count'];

$thisWeekBookings = $conn->query("SELECT COUNT(*) as count FROM bookings 
                                  WHERE assigned_staff_id = $staffId 
                                  AND event_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)
                                  AND status NOT IN ('completed', 'cancelled')")->fetch_assoc()['count'];

$settings = getSettings($conn);
$siteName = $settings['site_name'] ?? 'Filipino Catering';
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Dashboard - <?= htmlspecialchars($siteName) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --sidebar-bg: linear-gradient(180deg, #1e3a5f 0%, #0d1b2a 100%);
            --sidebar-width: 260px;
            --nav-item-hover: rgba(255, 255, 255, 0.1);
            --nav-item-active: linear-gradient(90deg, #f97316 0%, #ea580c 100%);
            --accent-color: #f97316;
            --text-primary: #ffffff;
            --text-secondary: rgba(255, 255, 255, 0.6);
        }
        
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Poppins', sans-serif; background: #f1f5f9; }
        
        .sidebar {
            width: var(--sidebar-width);
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            background: var(--sidebar-bg);
            display: flex;
            flex-direction: column;
            z-index: 1000;
            box-shadow: 4px 0 20px rgba(0, 0, 0, 0.15);
            transition: transform 0.3s ease;
        }
        .sidebar-header {
            padding: 24px 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        .logo-wrapper {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .logo-icon {
            font-size: 32px;
            filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.3));
        }
        .logo-text {
            font-size: 18px;
            font-weight: 600;
            color: var(--text-primary);
            letter-spacing: 0.5px;
        }
        .sidebar-nav {
            flex: 1;
            overflow-y: auto;
            padding: 16px 12px;
        }
        .nav-section { margin-bottom: 24px; }
        .nav-section-title {
            display: block;
            font-size: 11px;
            font-weight: 600;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 1.5px;
            padding: 0 12px;
            margin-bottom: 8px;
        }
        .nav-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            color: var(--text-secondary);
            text-decoration: none;
            border-radius: 10px;
            margin-bottom: 4px;
            transition: all 0.2s ease;
            font-size: 14px;
            font-weight: 500;
        }
        .nav-item i {
            font-size: 18px;
            width: 24px;
            text-align: center;
        }
        .nav-item:hover {
            background: var(--nav-item-hover);
            color: var(--text-primary);
            transform: translateX(4px);
        }
        .nav-item.active {
            background: var(--nav-item-active);
            color: var(--text-primary);
            box-shadow: 0 4px 15px rgba(249, 115, 22, 0.4);
        }
        .nav-item.logout { color: #ef4444; }
        .nav-item.logout:hover {
            background: rgba(239, 68, 68, 0.15);
            color: #f87171;
        }
        .sidebar-footer {
            padding: 16px 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            background: rgba(0, 0, 0, 0.2);
        }
        .user-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            background: var(--nav-item-active);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 20px;
        }
        .user-name {
            font-size: 14px;
            font-weight: 600;
            color: var(--text-primary);
        }
        .user-role {
            font-size: 12px;
            color: var(--text-secondary);
        }
        
        .nav-badge {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
            font-size: 10px;
            font-weight: 700;
            min-width: 20px;
            height: 20px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0 6px;
            margin-left: auto;
            box-shadow: 0 2px 8px rgba(239, 68, 68, 0.4);
            animation: pulse-nav-badge 2s infinite;
        }
        
        @keyframes pulse-nav-badge {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }
        
        .message-list {
            padding: 8px 12px 8px 40px;
        }
        .message-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 14px;
            margin-bottom: 4px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 8px;
            color: rgba(255, 255, 255, 0.7);
            text-decoration: none;
            font-size: 13px;
            transition: all 0.2s;
        }
        .message-item:hover {
            background: rgba(249, 115, 22, 0.2);
            color: white;
        }
        .message-item .msg-count {
            background: #ef4444;
            color: white;
            font-size: 10px;
            font-weight: 700;
            min-width: 18px;
            height: 18px;
            border-radius: 9px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .sidebar-toggle {
            position: fixed;
            top: 16px;
            left: 16px;
            z-index: 1001;
            width: 44px;
            height: 44px;
            border-radius: 10px;
            border: none;
            background: var(--nav-item-active);
            color: white;
            font-size: 20px;
            cursor: pointer;
            box-shadow: 0 4px 15px rgba(249, 115, 22, 0.4);
            display: none;
        }
        
        .main-content {
            margin-left: var(--sidebar-width);
            padding: 28px;
            min-height: 100vh;
        }
        
        .main-content h3 {
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 8px;
        }
        
        .card {
            border: none;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            overflow: hidden;
        }
        .card-header {
            background: white;
            border-bottom: 1px solid #e2e8f0;
            padding: 16px 20px;
            font-weight: 600;
        }
        .card-body { padding: 20px; }
        
        .btn-primary {
            background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
            border: none;
            padding: 10px 20px;
            font-weight: 500;
            border-radius: 10px;
            transition: all 0.2s ease;
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #ea580c 0%, #c2410c 100%);
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(249, 115, 22, 0.4);
        }
        
        .stat-card {
            border-radius: 16px;
            border: none;
            transition: all 0.3s ease;
            overflow: hidden;
        }
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
        }
        .stat-icon {
            width: 56px;
            height: 56px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
        }
        
        .table thead th {
            background: #f8fafc;
            border-bottom: 2px solid #e2e8f0;
            font-weight: 600;
            color: #475569;
            text-transform: uppercase;
            font-size: 12px;
            letter-spacing: 0.5px;
            padding: 14px 16px;
        }
        .table tbody td {
            padding: 14px 16px;
            vertical-align: middle;
            border-bottom: 1px solid #f1f5f9;
        }
        .table tbody tr:hover { background: #f8fafc; }
        
        .status-badge {
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        .status-new { background: #dbeafe; color: #1d4ed8; }
        .status-pending { background: #fef3c7; color: #92400e; }
        .status-negotiating { background: #e0e7ff; color: #4338ca; }
        .status-approved { background: #d1fae5; color: #059669; }
        .status-paid { background: #cffafe; color: #0891b2; }
        .status-preparing { background: #fed7aa; color: #c2410c; }
        .status-completed { background: #bbf7d0; color: #15803d; }
        
        @media (max-width: 991.98px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.active { transform: translateX(0); }
            .sidebar-toggle { display: flex; align-items: center; justify-content: center; }
            .main-content { margin-left: 0; padding: 20px; padding-top: 70px; }
        }
    </style>
</head>
<body>
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="logo-wrapper">
                <span class="logo-icon">🍲</span>
                <span class="logo-text"><?= htmlspecialchars($siteName) ?></span>
            </div>
        </div>
        <nav class="sidebar-nav">
            <div class="nav-section">
                <span class="nav-section-title">MENU</span>
                <a href="<?= staffUrl('dashboard.php') ?>" class="nav-item <?= $currentPage == 'dashboard.php' ? 'active' : '' ?>">
                    <i class="bi bi-speedometer2"></i>
                    <span>Dashboard</span>
                </a>
                <a href="#" class="nav-item" data-bs-toggle="collapse" data-bs-target="#messagesCollapse">
                    <i class="bi bi-chat-dots"></i>
                    <span>Messages</span>
                    <?php if ($staffNotifs['messages'] > 0): ?>
                    <span class="nav-badge"><?= $staffNotifs['messages'] ?></span>
                    <?php endif; ?>
                </a>
                <div class="collapse <?= $staffNotifs['messages'] > 0 ? 'show' : '' ?>" id="messagesCollapse">
                    <div class="message-list">
                        <?php if (count($unreadBookingsStaff) > 0): ?>
                            <?php foreach($unreadBookingsStaff as $ub): ?>
                            <a href="<?= staffUrl('booking-details.php?id=' . $ub['id']) ?>" class="message-item">
                                <span class="booking-num"><?= $ub['booking_number'] ?></span>
                                <span class="msg-count"><?= $ub['unread_count'] ?></span>
                            </a>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="text-center py-3" style="font-size: 12px; color: rgba(255,255,255,0.5);">
                                No unread messages
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="nav-section">
                <span class="nav-section-title">OTHER</span>
                <a href="<?= url('index.php') ?>" class="nav-item">
                    <i class="bi bi-house"></i>
                    <span>View Website</span>
                </a>
                <a href="<?= url('logout.php') ?>" class="nav-item logout">
                    <i class="bi bi-box-arrow-right"></i>
                    <span>Logout</span>
                </a>
            </div>
        </nav>
        <div class="sidebar-footer">
            <div class="user-info">
                <div class="user-avatar"><i class="bi bi-person-circle"></i></div>
                <div class="user-details">
                    <span class="user-name"><?= htmlspecialchars($_SESSION['user_name'] ?? 'Staff') ?></span>
                    <span class="user-role">Staff Member</span>
                </div>
            </div>
        </div>
    </div>
    
    <button class="sidebar-toggle d-lg-none" onclick="toggleSidebar()">
        <i class="bi bi-list"></i>
    </button>
    
    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="mb-1">Staff Dashboard</h3>
                <p class="text-muted mb-0">Welcome back, <?= htmlspecialchars($_SESSION['user_name']) ?>!</p>
            </div>
            <div>
                <span class="badge bg-secondary">Staff Member</span>
            </div>
        </div>
        
        <div class="row g-4 mb-4">
            <div class="col-md-6 col-lg-3">
                <div class="card stat-card">
                    <div class="card-body d-flex align-items-center">
                        <div class="stat-icon me-3" style="background: linear-gradient(135deg, #eab308 0%, #ca8a04 100%); color: white;">
                            <i class="bi bi-calendar-check"></i>
                        </div>
                        <div>
                            <h3 class="mb-0"><?= $todayBookings ?></h3>
                            <small class="text-muted">Today's Events</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="card stat-card">
                    <div class="card-body d-flex align-items-center">
                        <div class="stat-icon me-3" style="background: linear-gradient(135deg, #f97316 0%, #ea580c 100%); color: white;">
                            <i class="bi bi-calendar-event"></i>
                        </div>
                        <div>
                            <h3 class="mb-0"><?= $upcomingBookings ?></h3>
                            <small class="text-muted">Upcoming Events</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="card stat-card">
                    <div class="card-body d-flex align-items-center">
                        <div class="stat-icon me-3" style="background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%); color: white;">
                            <i class="bi bi-calendar-week"></i>
                        </div>
                        <div>
                            <h3 class="mb-0"><?= $thisWeekBookings ?></h3>
                            <small class="text-muted">This Week</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="card stat-card">
                    <div class="card-body d-flex align-items-center">
                        <div class="stat-icon me-3" style="background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%); color: white;">
                            <i class="bi bi-check-circle"></i>
                        </div>
                        <div>
                            <h3 class="mb-0"><?= $completedBookings ?></h3>
                            <small class="text-muted">Completed</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-list-task me-2" style="color: var(--accent-color);"></i>My Assigned Bookings</h5>
                <?php if ($assignedBookings && $assignedBookings->num_rows > 0): ?>
                <span class="badge" style="background: var(--accent-color);"><?= $assignedBookings->num_rows ?> active</span>
                <?php endif; ?>
            </div>
            <div class="card-body p-0">
                <?php if ($assignedBookings && $assignedBookings->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Booking #</th>
                                <th>Customer</th>
                                <th>Package</th>
                                <th>Event Date</th>
                                <th>Guests</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($b = $assignedBookings->fetch_assoc()): 
                                $eventDate = new DateTime($b['event_date']);
                                $today = new DateTime();
                                $diff = $today->diff($eventDate);
                                $daysLabel = $diff->invert ? 'Passed' : ($diff->days == 0 ? 'Today' : $diff->days . ' days');
                            ?>
                            <tr>
                                <td>
                                    <strong><?= $b['booking_number'] ?></strong>
                                    <?php if ($b['unread_messages'] > 0): ?>
                                    <span class="badge bg-danger ms-1" style="font-size: 10px; animation: pulse-nav-badge 2s infinite;">
                                        <?= $b['unread_messages'] ?> <i class="bi bi-chat-dots-fill"></i>
                                    </span>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($b['first_name'] . ' ' . $b['last_name']) ?></td>
                                <td><span class="badge bg-light text-dark"><?= htmlspecialchars($b['package_name'] ?? 'Custom') ?></span></td>
                                <td>
                                    <strong><?= formatDate($b['event_date']) ?></strong><br>
                                    <small class="text-muted"><?= $daysLabel ?></small>
                                </td>
                                <td><?= $b['number_of_guests'] ?> pax</td>
                                <td>
                                    <span class="status-badge status-<?= $b['status'] ?>"><?= ucfirst($b['status']) ?></span>
                                </td>
                                <td>
                                    <a href="<?= staffUrl('booking-details.php?id=' . $b['id']) ?>" class="btn btn-sm btn-primary <?= $b['unread_messages'] > 0 ? 'btn-pulse' : '' ?>">
                                        <i class="bi bi-eye"></i> View
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="text-center py-5">
                    <i class="bi bi-calendar-x text-muted" style="font-size: 64px;"></i>
                    <h4 class="text-muted mt-3">No Active Bookings</h4>
                    <p class="text-muted">You don't have any assigned bookings at the moment.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function toggleSidebar() {
        document.getElementById('sidebar').classList.toggle('active');
    }
    
    document.addEventListener('click', function(e) {
        const sidebar = document.getElementById('sidebar');
        const toggle = document.querySelector('.sidebar-toggle');
        
        if (window.innerWidth < 992) {
            if (!sidebar.contains(e.target) && !toggle.contains(e.target)) {
                sidebar.classList.remove('active');
            }
        }
    });
    </script>
</body>
</html>