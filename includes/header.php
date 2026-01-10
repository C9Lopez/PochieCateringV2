<?php
ob_start();
require_once __DIR__ . '/../config/functions.php';
$settings = getSettings($conn);
$currentUser = getCurrentUser($conn);
$customerNotifs = null;
$unreadBookings = [];
if (isLoggedIn() && getUserRole() === 'customer') {
    $customerNotifs = getCustomerNotificationCounts($conn, $_SESSION['user_id']);
    $unreadBookings = getBookingsWithUnreadMessages($conn, $_SESSION['user_id'], 'customer');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? $settings['site_name'] ?? 'Filipino Catering' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #f97316;
            --primary-dark: #ea580c;
            --secondary: #1e3a5f;
            --accent: #22c55e;
            --cream: #fffbeb;
            --dark: #1e293b;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #fff7ed 0%, #ffedd5 100%);
            min-height: 100vh;
        }
        
        h1, h2, h3, h4, h5, h6 {
            font-family: 'Playfair Display', serif;
        }
        
        .top-bar {
            background: #0f172a;
            color: white;
            padding: 8px 0;
            font-size: 13px;
            font-weight: 500;
        }
        .top-bar i { color: var(--primary); }
        
        header.sticky-top {
            z-index: 1060;
        }
        
        .navbar {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(10px);
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            padding: 12px 0;
            z-index: 1050 !important;
        }
        
        .navbar-brand {
            font-family: 'Playfair Display', serif;
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary) !important;
        }
        
        .nav-link {
            color: var(--dark) !important;
            font-weight: 500;
            margin: 0 8px;
            transition: all 0.3s;
        }
        
        .nav-link:hover {
            color: var(--primary) !important;
            transform: translateY(-2px);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            border: none;
            font-weight: 500;
            border-radius: 10px;
            padding: 10px 25px;
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, var(--primary-dark) 0%, #c2410c 100%);
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(249, 115, 22, 0.3);
        }
        
        .btn-secondary {
            background: linear-gradient(135deg, var(--secondary) 0%, #0d1b2a 100%);
            border: none;
            color: white;
            font-weight: 500;
            border-radius: 10px;
        }
        
        .card {
            border: none;
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.08);
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 50px rgba(0,0,0,0.15);
        }
        
        .section-title {
            position: relative;
            display: inline-block;
            color: var(--dark);
            font-weight: 700;
        }
        
        .section-title::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 60px;
            height: 4px;
            background: var(--primary);
            border-radius: 2px;
        }
        
        .footer {
            background: var(--dark);
            color: white;
            padding: 50px 0 20px;
        }
        
        .badge {
            padding: 8px 15px;
            border-radius: 8px;
            font-weight: 500;
        }
        
        .dropdown-menu {
            border: none;
            box-shadow: 0 10px 40px rgba(0,0,0,0.15);
            border-radius: 12px;
            padding: 10px;
        }
        
        .dropdown-item {
            border-radius: 8px;
            padding: 10px 15px;
            transition: all 0.2s;
        }
        
        .dropdown-item:hover {
            background: #fff7ed;
            color: var(--primary);
        }
        
        .form-control, .form-select {
            border-radius: 10px;
            border: 2px solid #e2e8f0;
            padding: 12px 16px;
            transition: all 0.2s;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(249, 115, 22, 0.1);
        }
        
        .table {
            border-radius: 12px;
            overflow: hidden;
        }
        
        .table thead th {
            background: #f8fafc;
            font-weight: 600;
            color: #475569;
            border-bottom: 2px solid #e2e8f0;
        }
        
        .notification-bell {
            position: relative;
            cursor: pointer;
        }
        
        .notification-badge {
            position: absolute;
            top: -5px;
            right: -8px;
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
            font-size: 10px;
            font-weight: 700;
            min-width: 18px;
            height: 18px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0 4px;
            box-shadow: 0 2px 8px rgba(239, 68, 68, 0.4);
            animation: pulse-badge 2s infinite;
        }
        
        @keyframes pulse-badge {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }
        
        .notification-dropdown {
            min-width: 320px;
            max-height: 400px;
            overflow-y: auto;
            padding: 0;
        }
        
        .notification-header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            padding: 15px 20px;
            font-weight: 600;
            font-size: 14px;
        }
        
        .notification-item {
            padding: 15px 20px;
            border-bottom: 1px solid #f1f5f9;
            transition: all 0.2s;
            display: block;
            text-decoration: none;
            color: inherit;
        }
        
        .notification-item:hover {
            background: #fff7ed;
            color: var(--primary);
        }
        
        .notification-item .booking-num {
            font-weight: 600;
            color: var(--dark);
        }
        
        .notification-item .msg-count {
            background: var(--primary);
            color: white;
            font-size: 11px;
            padding: 2px 8px;
            border-radius: 10px;
            font-weight: 600;
        }
        
        .notification-footer {
            padding: 12px 20px;
            text-align: center;
            background: #f8fafc;
        }
        
        .notification-footer a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
            font-size: 13px;
        }
        
        .notification-empty {
            padding: 30px 20px;
            text-align: center;
            color: #94a3b8;
        }
        
        .notification-empty i {
            font-size: 40px;
            margin-bottom: 10px;
            display: block;
        }
    </style>
</head>
  <body>
    <header class="sticky-top shadow-sm">
      <div class="top-bar">
          <div class="container d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                    <i class="bi bi-clock me-2"></i>
                    <span id="ph-time-display" class="me-2">Loading time...</span>
                    <select id="timezone-selector" class="form-select form-select-sm bg-dark text-white border-secondary py-0 px-2" style="width: auto; height: 24px; font-size: 11px; cursor: pointer;">
                        <option value="Asia/Manila">PH (PHT)</option>
                        <option value="America/New_York">US (EST)</option>
                        <option value="Europe/London">UK (GMT)</option>
                        <option value="Asia/Tokyo">JP (JST)</option>
                        <option value="Australia/Sydney">AU (AEST)</option>
                        <option value="Asia/Dubai">AE (GST)</option>
                        <option value="Europe/Paris">FR (CET)</option>
                        <option value="Asia/Singapore">SG (SGT)</option>
                    </select>
                </div>
              <div class="d-none d-md-block">
                  <i class="bi bi-geo-alt me-1"></i> <?= htmlspecialchars($settings['site_address'] ?? 'Philippines') ?>
              </div>
          </div>
      </div>
      <nav class="navbar navbar-expand-lg border-bottom">

        <div class="container">
            <a class="navbar-brand" href="<?= url('index.php') ?>">
                🍲 <?= $settings['site_name'] ?? 'Filipino Catering' ?>
            </a>

            <div class="d-flex align-items-center order-lg-last">
                <?php if (isLoggedIn()): ?>
                    <?php if (getUserRole() === 'customer' && $customerNotifs): ?>
                        <div class="dropdown me-3">
                            <a class="nav-link notification-bell" href="#" data-bs-toggle="dropdown">
                                <i class="bi bi-bell fs-5"></i>
                                <?php if ($customerNotifs['total'] > 0): ?>
                                <span class="notification-badge"><?= $customerNotifs['total'] > 9 ? '9+' : $customerNotifs['total'] ?></span>
                                <?php endif; ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end notification-dropdown">
                                <li class="notification-header">
                                    <i class="bi bi-bell me-2"></i>Notifications
                                </li>
                                <?php if ($customerNotifs['messages'] > 0 && count($unreadBookings) > 0): ?>
                                    <?php foreach($unreadBookings as $ub): ?>
                                    <li>
                                        <a class="notification-item" href="<?= url('booking-details.php?id=' . $ub['id']) ?>">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <i class="bi bi-chat-dots text-primary me-2"></i>
                                                    <span class="booking-num"><?= $ub['booking_number'] ?></span>
                                                </div>
                                                <span class="msg-count"><?= $ub['unread_count'] ?> new</span>
                                            </div>
                                            <small class="text-muted d-block mt-1">You have unread messages</small>
                                        </a>
                                    </li>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                <li class="notification-empty">
                                    <i class="bi bi-bell-slash"></i>
                                    <p class="mb-0">No new notifications</p>
                                </li>
                                <?php endif; ?>
                                <li class="notification-footer">
                                    <a href="<?= url('my-bookings.php') ?>">View All Bookings <i class="bi bi-arrow-right"></i></a>
                                </li>
                            </ul>
                        </div>
                    <?php endif; ?>
                    
                    <div class="dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle fs-5 me-1"></i>
                            <span class="d-none d-sm-inline"><?= htmlspecialchars($currentUser['first_name'] ?? 'User') ?></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="<?= url('my-bookings.php') ?>"><i class="bi bi-calendar-check me-2"></i>My Bookings</a></li>
                            <li><a class="dropdown-item" href="<?= url('profile.php') ?>"><i class="bi bi-person me-2"></i>Profile</a></li>
                            <?php if (in_array(getUserRole(), ['admin', 'super_admin'])): ?>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="<?= adminUrl('dashboard.php') ?>"><i class="bi bi-speedometer2 me-2"></i>Admin Dashboard</a></li>
                            <?php elseif (getUserRole() === 'staff'): ?>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="<?= staffUrl('dashboard.php') ?>"><i class="bi bi-speedometer2 me-2"></i>Staff Dashboard</a></li>
                            <?php endif; ?>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="<?= url('logout.php') ?>"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                        </ul>
                    </div>
                <?php else: ?>
                    <div class="auth-buttons d-flex align-items-center">
                        <a href="<?= url('login.php') ?>" class="nav-link d-none d-sm-block me-2">Login</a>
                        <a href="<?= url('register.php') ?>" class="btn btn-primary btn-sm px-3">Sign Up</a>
                    </div>
                <?php endif; ?>
                
                <button class="navbar-toggler ms-2" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>
            </div>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link" href="<?= url('index.php') ?>"><i class="bi bi-house me-1"></i>Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= url('menu.php') ?>"><i class="bi bi-menu-button-wide me-1"></i>Menu</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= url('packages.php') ?>"><i class="bi bi-gift me-1"></i>Packages</a>
                    </li>
                    <?php if (isLoggedIn()): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= url('book.php') ?>"><i class="bi bi-calendar-plus me-1"></i>Book Now</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
</header>
