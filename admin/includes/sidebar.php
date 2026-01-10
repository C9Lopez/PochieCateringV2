<?php
$currentPage = basename($_SERVER['PHP_SELF']);
$sidebarSettings = getSettings($conn);
$sidebarSiteName = $sidebarSettings['site_name'] ?? 'Pochie Catering Services';
$adminNotifs = getAdminNotificationCounts($conn);
$unreadBookingsAdmin = getBookingsWithUnreadMessages($conn, $_SESSION['user_id'], 'admin');
?>
<div class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <div class="logo-wrapper">
            <span class="logo-icon">🍲</span>
            <span class="logo-text"><?= htmlspecialchars($sidebarSiteName) ?></span>
        </div>
    </div>
    <nav class="sidebar-nav">
        <div class="nav-section">
            <span class="nav-section-title">MENU</span>
            <a href="<?= adminUrl('dashboard.php') ?>" class="nav-item <?= $currentPage == 'dashboard.php' ? 'active' : '' ?>">
                <i class="bi bi-speedometer2"></i>
                <span>Dashboard</span>
            </a>
            <a href="<?= adminUrl('bookings.php') ?>" class="nav-item <?= $currentPage == 'bookings.php' || $currentPage == 'booking-details.php' ? 'active' : '' ?>">
                <i class="bi bi-calendar-check"></i>
                <span>Bookings</span>
                <?php if ($adminNotifs['new_bookings'] > 0): ?>
                <span class="nav-badge"><?= $adminNotifs['new_bookings'] ?></span>
                <?php endif; ?>
            </a>
            <a href="<?= adminUrl('menu.php') ?>" class="nav-item <?= $currentPage == 'menu.php' ? 'active' : '' ?>">
                <i class="bi bi-list-ul"></i>
                <span>Menu Items</span>
            </a>
            <a href="<?= adminUrl('packages.php') ?>" class="nav-item <?= $currentPage == 'packages.php' ? 'active' : '' ?>">
                <i class="bi bi-gift"></i>
                <span>Packages</span>
            </a>
            <a href="<?= adminUrl('promotions.php') ?>" class="nav-item <?= $currentPage == 'promotions.php' ? 'active' : '' ?>">
                <i class="bi bi-megaphone"></i>
                <span>Promotions</span>
            </a>
            <a href="<?= adminUrl('payments.php') ?>" class="nav-item <?= $currentPage == 'payments.php' ? 'active' : '' ?>">
                <i class="bi bi-credit-card"></i>
                <span>Payments</span>
                <?php if ($adminNotifs['pending_payments'] > 0): ?>
                <span class="nav-badge warning"><?= $adminNotifs['pending_payments'] ?></span>
                <?php endif; ?>
            </a>
        </div>
        
        <div class="nav-section">
            <span class="nav-section-title">MESSAGES</span>
            <a href="#" class="nav-item" data-bs-toggle="collapse" data-bs-target="#messagesCollapse">
                <i class="bi bi-chat-dots"></i>
                <span>Messages</span>
                <?php if ($adminNotifs['messages'] > 0): ?>
                <span class="nav-badge danger"><?= $adminNotifs['messages'] ?></span>
                <?php endif; ?>
            </a>
            <div class="collapse <?= $adminNotifs['messages'] > 0 ? 'show' : '' ?>" id="messagesCollapse">
                <div class="message-list">
                    <?php if (count($unreadBookingsAdmin) > 0): ?>
                        <?php foreach($unreadBookingsAdmin as $ub): ?>
                        <a href="<?= adminUrl('booking-details.php?id=' . $ub['id']) ?>" class="message-item">
                            <span class="booking-num"><?= $ub['booking_number'] ?></span>
                            <span class="msg-count"><?= $ub['unread_count'] ?></span>
                        </a>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center text-muted py-3" style="font-size: 12px;">
                            No unread messages
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <?php if (getUserRole() === 'super_admin'): ?>
        <div class="nav-section">
            <span class="nav-section-title">SUPER ADMIN</span>
            <a href="<?= adminUrl('users.php') ?>" class="nav-item <?= $currentPage == 'users.php' ? 'active' : '' ?>">
                <i class="bi bi-people"></i>
                <span>Users</span>
            </a>
            <a href="<?= adminUrl('settings.php') ?>" class="nav-item <?= $currentPage == 'settings.php' ? 'active' : '' ?>">
                <i class="bi bi-gear"></i>
                <span>Settings</span>
            </a>
            <a href="<?= adminUrl('reports.php') ?>" class="nav-item <?= $currentPage == 'reports.php' ? 'active' : '' ?>">
                <i class="bi bi-graph-up"></i>
                <span>Reports</span>
            </a>
            <a href="<?= adminUrl('activity-logs.php') ?>" class="nav-item <?= $currentPage == 'activity-logs.php' ? 'active' : '' ?>">
                <i class="bi bi-clock-history"></i>
                <span>Activity Logs</span>
            </a>
        </div>
        <?php endif; ?>
        
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
            <div class="user-avatar">
                <i class="bi bi-person-circle"></i>
            </div>
            <div class="user-details">
                <span class="user-name"><?= htmlspecialchars($_SESSION['user_name'] ?? 'Admin') ?></span>
                <span class="user-role"><?= ucfirst(str_replace('_', ' ', getUserRole())) ?></span>
            </div>
        </div>
    </div>
</div>

<button class="sidebar-toggle d-lg-none" onclick="toggleSidebar()">
    <i class="bi bi-list"></i>
</button>

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

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Poppins', sans-serif;
    background: #f1f5f9;
}

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

.sidebar-nav::-webkit-scrollbar {
    width: 4px;
}

.sidebar-nav::-webkit-scrollbar-thumb {
    background: rgba(255, 255, 255, 0.2);
    border-radius: 2px;
}

.nav-section {
    margin-bottom: 24px;
}

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

.nav-item.active i {
    color: var(--text-primary);
}

.nav-item.logout {
    color: #ef4444;
}

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

.user-details {
    display: flex;
    flex-direction: column;
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
    background: #f1f5f9;
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

.card-body {
    padding: 20px;
}

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

.btn-success {
    background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
    border: none;
}

.btn-danger {
    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    border: none;
}

.btn-warning {
    background: linear-gradient(135deg, #eab308 0%, #ca8a04 100%);
    border: none;
}

.table {
    margin-bottom: 0;
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

.table tbody tr:hover {
    background: #f8fafc;
}

.badge {
    padding: 6px 12px;
    font-weight: 500;
    font-size: 12px;
    border-radius: 6px;
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

.alert {
    border-radius: 12px;
    border: none;
    padding: 16px 20px;
}

.modal-content {
    border-radius: 16px;
    border: none;
}

.modal-header {
    border-bottom: 1px solid #e2e8f0;
    padding: 20px 24px;
}

.modal-body {
    padding: 24px;
}

.modal-footer {
    border-top: 1px solid #e2e8f0;
    padding: 16px 24px;
}

.nav-badge {
    background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
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
    box-shadow: 0 2px 8px rgba(249, 115, 22, 0.4);
    animation: pulse-nav-badge 2s infinite;
}

.nav-badge.warning {
    background: linear-gradient(135deg, #eab308 0%, #ca8a04 100%);
    box-shadow: 0 2px 8px rgba(234, 179, 8, 0.4);
}

.nav-badge.danger {
    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    box-shadow: 0 2px 8px rgba(239, 68, 68, 0.4);
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

.message-item .booking-num {
    font-weight: 500;
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

@media (max-width: 991.98px) {
    .sidebar {
        transform: translateX(-100%);
    }
    
    .sidebar.active {
        transform: translateX(0);
    }
    
    .sidebar-toggle {
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .main-content {
        margin-left: 0;
        padding: 20px;
        padding-top: 70px;
    }
}
</style>

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