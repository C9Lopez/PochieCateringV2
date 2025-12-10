<?php
require_once '../config/functions.php';
requireRole(['staff']);

$bookingId = (int)($_GET['id'] ?? 0);
$staffId = $_SESSION['user_id'];

if (!$bookingId) {
    header('Location: ' . staffUrl('dashboard.php'));
    exit();
}

$booking = $conn->query("SELECT b.*, u.first_name, u.last_name, u.email, u.phone, 
                         p.name as package_name, p.base_price, p.description as package_description, p.inclusions, p.min_pax, p.max_pax
                         FROM bookings b 
                         LEFT JOIN users u ON b.customer_id = u.id 
                         LEFT JOIN packages p ON b.package_id = p.id 
                         WHERE b.id = $bookingId AND b.assigned_staff_id = $staffId")->fetch_assoc();

if (!$booking) {
    header('Location: ' . staffUrl('dashboard.php'));
    exit();
}

$selectedMenuItems = $conn->query("SELECT bmi.*, mi.name, mi.description as item_description, mc.name as category_name
                                   FROM booking_menu_items bmi 
                                   LEFT JOIN menu_items mi ON bmi.menu_item_id = mi.id 
                                   LEFT JOIN menu_categories mc ON mi.category_id = mc.id
                                   WHERE bmi.booking_id = $bookingId
                                   ORDER BY mc.name, mi.name");

$menuItemsTotal = $conn->query("SELECT SUM(price * quantity) as total FROM booking_menu_items WHERE booking_id = $bookingId")->fetch_assoc()['total'] ?? 0;
$packageTotal = ($booking['base_price'] ?? 0) * $booking['number_of_guests'];

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['send_message'])) {
        $messageText = sanitize($conn, $_POST['message'] ?? '');
        $imageFile = null;
        
        if (isset($_FILES['chat_image']) && $_FILES['chat_image']['error'] === UPLOAD_ERR_OK) {
            $upload = uploadImage($_FILES['chat_image'], 'uploads/chat');
            if ($upload['success']) {
                $imageFile = $upload['filename'];
            }
        }
        
        if ($messageText || $imageFile) {
            $stmt = $conn->prepare("INSERT INTO chat_messages (booking_id, sender_id, message, image) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("iiss", $bookingId, $staffId, $messageText, $imageFile);
            $stmt->execute();
            $message = 'Message sent!';
            $messageType = 'success';
        }
    }
    
    if (isset($_POST['update_status'])) {
        $newStatus = sanitize($conn, $_POST['status']);
        $conn->query("UPDATE bookings SET status = '$newStatus' WHERE id = $bookingId");
        $booking['status'] = $newStatus;
        $message = 'Status updated!';
        $messageType = 'success';
    }
}

$chatMessages = $conn->query("SELECT cm.*, u.first_name, u.last_name, u.role FROM chat_messages cm 
                              LEFT JOIN users u ON cm.sender_id = u.id 
                              WHERE cm.booking_id = $bookingId ORDER BY cm.created_at ASC");

markMessagesAsRead($conn, $bookingId, $staffId);

$settings = getSettings($conn);
$siteName = $settings['site_name'] ?? 'Filipino Catering';
$currentPage = basename($_SERVER['PHP_SELF']);
$staffNotifs = getStaffNotificationCounts($conn, $staffId);
$unreadBookingsStaff = getBookingsWithUnreadMessages($conn, $staffId, 'staff');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Details - Staff | <?= $booking['booking_number'] ?></title>
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
            margin-bottom: 24px;
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
        
        .form-control, .form-select {
            border-radius: 10px;
            border: 2px solid #e2e8f0;
            padding: 10px 16px;
            transition: all 0.2s ease;
        }
        .form-control:focus, .form-select:focus {
            border-color: #f97316;
            box-shadow: 0 0 0 4px rgba(249, 115, 22, 0.1);
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
        .status-cancelled { background: #fee2e2; color: #dc2626; }
        
        .info-section {
            background: linear-gradient(135deg, #fff7ed 0%, #fef3c7 100%);
            border-radius: 16px;
            padding: 24px;
            margin-bottom: 24px;
        }
        .info-section h5 {
            font-size: 14px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #92400e;
            margin-bottom: 16px;
        }
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
        }
        .info-item label {
            font-size: 12px;
            color: #78716c;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: block;
            margin-bottom: 4px;
        }
        .info-item p {
            font-size: 15px;
            font-weight: 600;
            color: #1c1917;
            margin: 0;
        }
        
        .package-card {
            background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
            color: white;
            border-radius: 16px;
            padding: 24px;
            margin-bottom: 24px;
        }
        .package-card h5 { font-weight: 600; margin-bottom: 8px; }
        
        .summary-card {
            background: linear-gradient(135deg, #1e3a5f 0%, #0d1b2a 100%);
            color: white;
            border-radius: 16px;
            padding: 24px;
        }
        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        .summary-row:last-child { border-bottom: none; }
        .summary-row.total {
            margin-top: 16px;
            padding-top: 16px;
            border-top: 2px solid rgba(255, 255, 255, 0.2);
            border-bottom: none;
        }
        .summary-row.total .value {
            font-size: 24px;
            font-weight: 700;
            color: var(--accent-color);
        }
        
        .chat-box {
            max-height: 400px;
            overflow-y: auto;
            padding: 20px;
            background: #f8fafc;
            border-radius: 12px;
            margin-bottom: 20px;
        }
        .chat-message {
            padding: 14px 18px;
            margin-bottom: 12px;
            border-radius: 16px;
            max-width: 85%;
        }
        .chat-message.staff {
            background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
            color: white;
            margin-left: auto;
            border-bottom-right-radius: 4px;
        }
        .chat-message.customer {
            background: white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
            margin-right: auto;
            border-bottom-left-radius: 4px;
        }
        .chat-message.admin {
            background: linear-gradient(135deg, #1e3a5f 0%, #0d1b2a 100%);
            color: white;
            margin-left: auto;
            border-bottom-right-radius: 4px;
        }
        .chat-message .sender {
            font-size: 12px;
            opacity: 0.8;
            margin-bottom: 4px;
        }
        .chat-message .text {
            font-size: 14px;
            line-height: 1.5;
        }
        
        .alert {
            border-radius: 12px;
            border: none;
            padding: 16px 20px;
        }
        
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
                <a href="<?= staffUrl('dashboard.php') ?>" class="nav-item">
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
        <?php if ($message): ?>
        <div class="alert alert-<?= $messageType ?> alert-dismissible fade show">
            <i class="bi bi-check-circle me-2"></i>
            <?= htmlspecialchars($message) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>
        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <a href="<?= staffUrl('dashboard.php') ?>" class="text-muted text-decoration-none mb-2 d-inline-block">
                    <i class="bi bi-arrow-left me-1"></i> Back to Dashboard
                </a>
                <h3 class="mb-0">Booking #<?= $booking['booking_number'] ?></h3>
            </div>
            <span class="status-badge status-<?= $booking['status'] ?>"><?= ucfirst($booking['status']) ?></span>
        </div>
        
        <div class="row">
            <div class="col-lg-8">
                <div class="info-section">
                    <h5><i class="bi bi-person-badge me-2"></i>Customer Information</h5>
                    <div class="info-grid">
                        <div class="info-item">
                            <label>Full Name</label>
                            <p><?= htmlspecialchars($booking['first_name'] . ' ' . $booking['last_name']) ?></p>
                        </div>
                        <div class="info-item">
                            <label>Phone Number</label>
                            <p><?= htmlspecialchars($booking['phone']) ?></p>
                        </div>
                        <div class="info-item">
                            <label>Email Address</label>
                            <p><?= htmlspecialchars($booking['email']) ?></p>
                        </div>
                        <div class="info-item">
                            <label>Event Type</label>
                            <p><?= htmlspecialchars($booking['event_type']) ?></p>
                        </div>
                    </div>
                </div>
                
                <div class="info-section" style="background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%);">
                    <h5 style="color: #065f46;"><i class="bi bi-geo-alt me-2"></i>Event Details</h5>
                    <div class="info-grid">
                        <div class="info-item">
                            <label>Event Date</label>
                            <p><?= formatDate($booking['event_date']) ?></p>
                        </div>
                        <div class="info-item">
                            <label>Event Time</label>
                            <p><?= $booking['event_time'] ?></p>
                        </div>
                        <div class="info-item">
                            <label>Number of Guests</label>
                            <p><?= $booking['number_of_guests'] ?> pax</p>
                        </div>
                        <div class="info-item" style="grid-column: 1 / -1;">
                            <label>Venue Address</label>
                            <p><?= htmlspecialchars($booking['venue_address'] ?? 'N/A') ?></p>
                        </div>
                        <?php if ($booking['special_requests']): ?>
                        <div class="info-item" style="grid-column: 1 / -1;">
                            <label>Special Requests / Notes</label>
                            <p style="white-space: pre-line;"><?= htmlspecialchars($booking['special_requests']) ?></p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <?php if ($booking['package_name']): ?>
                <div class="package-card">
                    <h5><i class="bi bi-box-seam me-2"></i><?= htmlspecialchars($booking['package_name']) ?></h5>
                    <p class="mb-3 opacity-75"><?= htmlspecialchars($booking['package_description'] ?? '') ?></p>
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <strong style="font-size: 24px;"><?= formatPrice($booking['base_price']) ?></strong>
                            <span class="opacity-75">/ person</span>
                        </div>
                        <div class="text-end opacity-75">
                            <?= $booking['number_of_guests'] ?> guests
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if ($selectedMenuItems && $selectedMenuItems->num_rows > 0): ?>
                <div class="card">
                    <div class="card-header">
                        <i class="bi bi-list-check me-2" style="color: var(--accent-color);"></i>Selected Menu Items
                    </div>
                    <div class="card-body p-0">
                        <table class="table mb-0">
                            <thead>
                                <tr>
                                    <th>Item</th>
                                    <th>Price/Tray</th>
                                    <th>Qty</th>
                                    <th class="text-end">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($item = $selectedMenuItems->fetch_assoc()): ?>
                                <tr>
                                    <td>
                                        <strong><?= htmlspecialchars($item['name']) ?></strong><br>
                                        <small class="text-muted"><?= htmlspecialchars($item['category_name'] ?? 'General') ?></small>
                                    </td>
                                    <td><?= formatPrice($item['price']) ?></td>
                                    <td><?= $item['quantity'] ?> tray(s)</td>
                                    <td class="text-end"><strong><?= formatPrice($item['price'] * $item['quantity']) ?></strong></td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                            <tfoot>
                                <tr class="table-light">
                                    <td colspan="3" class="text-end"><strong>Menu Items Subtotal</strong></td>
                                    <td class="text-end"><strong><?= formatPrice($menuItemsTotal) ?></strong></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                <?php endif; ?>
                
                <div class="summary-card">
                    <h5 class="mb-3"><i class="bi bi-receipt me-2"></i>Order Summary</h5>
                    
                    <?php if ($booking['package_name']): ?>
                    <div class="summary-row">
                        <span>Package (<?= $booking['number_of_guests'] ?> pax x <?= formatPrice($booking['base_price']) ?>)</span>
                        <span><?= formatPrice($packageTotal) ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($menuItemsTotal > 0): ?>
                    <div class="summary-row">
                        <span>Additional Menu Items</span>
                        <span><?= formatPrice($menuItemsTotal) ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <div class="summary-row total">
                        <span style="font-size: 18px;">Total Amount</span>
                        <span class="value"><?= formatPrice($booking['total_amount']) ?></span>
                    </div>
                </div>
                
                <div class="card mt-4">
                    <div class="card-header">
                        <i class="bi bi-chat-dots me-2" style="color: var(--accent-color);"></i>Communication
                    </div>
                    <div class="card-body">
                        <div class="chat-box" id="chatBox">
                            <?php if ($chatMessages && $chatMessages->num_rows > 0): ?>
                            <?php while($msg = $chatMessages->fetch_assoc()): ?>
                            <div class="chat-message <?= $msg['role'] ?>">
                                <div class="sender">
                                    <?= htmlspecialchars($msg['first_name']) ?> (<?= ucfirst($msg['role']) ?>) 
                                    • <?= formatDateTime($msg['created_at']) ?>
                                </div>
                                <div class="text"><?= nl2br(htmlspecialchars($msg['message'])) ?></div>
                                <?php if ($msg['image']): ?>
                                <div class="chat-image mt-2">
                                    <a href="<?= url('uploads/chat/' . $msg['image']) ?>" target="_blank">
                                        <img src="<?= url('uploads/chat/' . $msg['image']) ?>" alt="Chat image" style="max-width: 200px; border-radius: 8px;">
                                    </a>
                                </div>
                                <?php endif; ?>
                            </div>
                            <?php endwhile; ?>
                            <?php else: ?>
                            <div class="text-center py-5">
                                <i class="bi bi-chat-dots text-muted" style="font-size: 48px;"></i>
                                <p class="text-muted mt-3">No messages yet. Start the conversation!</p>
                            </div>
                            <?php endif; ?>
                        </div>
                        <form method="POST" enctype="multipart/form-data">
                            <div class="mb-2">
                                <textarea name="message" class="form-control" placeholder="Type your message..." rows="2"></textarea>
                            </div>
                            <div class="d-flex gap-2 align-items-center flex-wrap">
                                <input type="file" name="chat_image" class="form-control" accept="image/*" style="max-width: 250px;">
                                <button type="submit" name="send_message" class="btn btn-primary">
                                    <i class="bi bi-send me-1"></i> Send
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="bi bi-arrow-repeat me-2" style="color: var(--accent-color);"></i>Update Status
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="mb-3">
                                <select name="status" class="form-select">
                                    <option value="preparing" <?= $booking['status'] == 'preparing' ? 'selected' : '' ?>>Preparing</option>
                                    <option value="completed" <?= $booking['status'] == 'completed' ? 'selected' : '' ?>>Completed</option>
                                </select>
                            </div>
                            <button type="submit" name="update_status" class="btn btn-primary w-100">
                                <i class="bi bi-check2-circle me-1"></i> Update Status
                            </button>
                        </form>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <i class="bi bi-info-circle me-2" style="color: var(--accent-color);"></i>Quick Info
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between py-2 border-bottom">
                            <span class="text-muted">Payment Status</span>
                            <span class="badge <?= $booking['payment_status'] == 'paid' ? 'bg-success' : ($booking['payment_status'] == 'partial' ? 'bg-warning' : 'bg-secondary') ?>">
                                <?= ucfirst($booking['payment_status']) ?>
                            </span>
                        </div>
                        <div class="d-flex justify-content-between py-2 border-bottom">
                            <span class="text-muted">Booking Date</span>
                            <span><?= formatDate($booking['created_at']) ?></span>
                        </div>
                        <div class="d-flex justify-content-between py-2">
                            <span class="text-muted">Days Until Event</span>
                            <?php
                            $eventDate = new DateTime($booking['event_date']);
                            $today = new DateTime();
                            $diff = $today->diff($eventDate);
                            $daysLeft = $diff->invert ? 'Event Passed' : $diff->days . ' days';
                            ?>
                            <span class="fw-bold"><?= $daysLeft ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('chatBox').scrollTop = document.getElementById('chatBox').scrollHeight;
        
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