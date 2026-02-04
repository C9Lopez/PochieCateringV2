<?php
// Session security settings
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_samesite', 'Lax');

session_start();
date_default_timezone_set('Asia/Manila');
require_once __DIR__ . '/database.php';

// Auto-detect base URL for XAMPP compatibility
$scriptPath = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
$basePath = '';

// Check if we're inside an admin or staff subfolder
if (strpos($scriptPath, '/admin') !== false) {
    $basePath = substr($scriptPath, 0, strpos($scriptPath, '/admin'));
} elseif (strpos($scriptPath, '/staff') !== false) {
    $basePath = substr($scriptPath, 0, strpos($scriptPath, '/staff'));
} elseif (strpos($scriptPath, '/config') !== false) {
    $basePath = substr($scriptPath, 0, strpos($scriptPath, '/config'));
} else {
    $basePath = $scriptPath;
}

define('BASE_URL', rtrim($basePath, '/'));

function url($path) {
    return BASE_URL . '/' . ltrim($path, '/');
}

function adminUrl($path) {
    return BASE_URL . '/admin/' . ltrim($path, '/');
}

function staffUrl($path) {
    return BASE_URL . '/staff/' . ltrim($path, '/');
}

function sanitize($conn, $data) {
    return mysqli_real_escape_string($conn, htmlspecialchars(strip_tags(trim($data))));
}

function generateBookingNumber() {
    return 'BK' . date('Ymd') . strtoupper(substr(uniqid(), -5));
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function getUserRole() {
    return $_SESSION['user_role'] ?? null;
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ' . url('login.php'));
        exit();
    }
}

function requireRole($roles) {
    requireLogin();
    if (!in_array(getUserRole(), (array)$roles)) {
        header('Location: ' . url('unauthorized.php'));
        exit();
    }
}

// CSRF Token Functions
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function getCSRFTokenField() {
    return '<input type="hidden" name="csrf_token" value="' . generateCSRFToken() . '">';
}

function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function getCurrentUser($conn) {
    if (!isLoggedIn()) return null;
    $id = (int)$_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function getSettings($conn) {
    $settings = [];
    $result = $conn->query("SELECT setting_key, setting_value FROM settings");
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
    }
    return $settings;
}

function formatPrice($price) {
    return '₱' . number_format($price, 2);
}

function formatDate($date) {
    return date('F j, Y', strtotime($date));
}

function formatDateTime($datetime) {
    return date('F j, Y g:i A', strtotime($datetime));
}

function getStatusBadge($status) {
    $badges = [
        'new' => '<span class="badge bg-info">New</span>',
        'pending' => '<span class="badge bg-warning">Pending</span>',
        'negotiating' => '<span class="badge bg-primary">Negotiating</span>',
        'approved' => '<span class="badge bg-success">Approved</span>',
        'paid' => '<span class="badge bg-success">Paid</span>',
        'preparing' => '<span class="badge bg-info">Preparing</span>',
        'completed' => '<span class="badge bg-secondary">Completed</span>',
        'cancelled' => '<span class="badge bg-danger">Cancelled</span>'
    ];
    return $badges[$status] ?? '<span class="badge bg-secondary">' . ucfirst($status) . '</span>';
}

function getPaymentBadge($status) {
    $badges = [
        'unpaid' => '<span class="badge bg-danger">Unpaid</span>',
        'partial' => '<span class="badge bg-warning">Partial</span>',
        'paid' => '<span class="badge bg-success">Paid</span>'
    ];
    return $badges[$status] ?? '<span class="badge bg-secondary">' . ucfirst($status) . '</span>';
}

function uploadImage($file, $folder = 'uploads') {
    $targetDir = __DIR__ . "/../$folder/";
    if (!file_exists($targetDir)) {
        mkdir($targetDir, 0755, true);
    }
    
    // Check file size (max 5MB)
    $maxSize = 5 * 1024 * 1024;
    if ($file['size'] > $maxSize) {
        return ['success' => false, 'error' => 'File too large. Maximum size is 5MB'];
    }
    
    // Sanitize filename - remove special characters, keep only alphanumeric and dots
    $originalName = preg_replace('/[^a-zA-Z0-9_.-]/', '', basename($file['name']));
    $fileName = uniqid() . '_' . $originalName;
    $targetFile = $targetDir . $fileName;
    $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
    
    // Check extension
    $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    if (!in_array($imageFileType, $allowedTypes)) {
        return ['success' => false, 'error' => 'Invalid file type. Allowed: JPG, PNG, GIF, WEBP'];
    }
    
    // Verify MIME type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array($mimeType, $allowedMimes)) {
        return ['success' => false, 'error' => 'Invalid file type'];
    }
    
    if (move_uploaded_file($file['tmp_name'], $targetFile)) {
        return ['success' => true, 'filename' => $fileName, 'path' => url("$folder/$fileName")];
    }
    
    return ['success' => false, 'error' => 'Upload failed'];
}

function addNotification($conn, $userId, $title, $message, $type = 'info', $link = null) {
    $stmt = $conn->prepare("INSERT INTO notifications (user_id, title, message, type, link) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("issss", $userId, $title, $message, $type, $link);
    return $stmt->execute();
}

function logActivity($conn, $userId, $action, $description = '') {
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
    $stmt = $conn->prepare("INSERT INTO activity_logs (user_id, action, description, ip_address) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $userId, $action, $description, $ip);
    return $stmt->execute();
}

function getUnreadNotifications($conn, $userId) {
    $stmt = $conn->prepare("SELECT * FROM notifications WHERE user_id = ? AND is_read = 0 ORDER BY created_at DESC LIMIT 10");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function getUnreadMessageCount($conn, $userId) {
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM chat_messages WHERE sender_id != ? AND is_read = 0 AND booking_id IN (SELECT id FROM bookings WHERE customer_id = ? OR assigned_staff_id = ?)");
    $stmt->bind_param("iii", $userId, $userId, $userId);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc()['count'];
}

function getCustomerNotificationCounts($conn, $userId) {
    $userId = (int)$userId;
    
    $msgStmt = $conn->prepare("
        SELECT COUNT(*) as count FROM chat_messages cm
        JOIN bookings b ON cm.booking_id = b.id
        WHERE b.customer_id = ? AND cm.sender_id != ? AND cm.is_read = 0
    ");
    $msgStmt->bind_param("ii", $userId, $userId);
    $msgStmt->execute();
    $unreadMessages = $msgStmt->get_result()->fetch_assoc()['count'] ?? 0;
    
    $notifStmt = $conn->prepare("
        SELECT COUNT(*) as count FROM notifications 
        WHERE user_id = ? AND is_read = 0
    ");
    $notifStmt->bind_param("i", $userId);
    $notifStmt->execute();
    $unreadNotifications = $notifStmt->get_result()->fetch_assoc()['count'] ?? 0;
    
    return [
        'messages' => $unreadMessages,
        'notifications' => $unreadNotifications,
        'total' => $unreadMessages + $unreadNotifications
    ];
}

function getStaffNotificationCounts($conn, $staffId) {
    $staffId = (int)$staffId;
    
    $msgStmt = $conn->prepare("
        SELECT COUNT(*) as count FROM chat_messages cm
        JOIN bookings b ON cm.booking_id = b.id
        WHERE b.assigned_staff_id = ? AND cm.sender_id != ? AND cm.is_read = 0
    ");
    $msgStmt->bind_param("ii", $staffId, $staffId);
    $msgStmt->execute();
    $unreadMessages = $msgStmt->get_result()->fetch_assoc()['count'] ?? 0;
    
    $bookingsStmt = $conn->prepare("
        SELECT COUNT(*) as count FROM bookings 
        WHERE assigned_staff_id = ? AND status IN ('new', 'pending') AND DATE(created_at) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
    ");
    $bookingsStmt->bind_param("i", $staffId);
    $bookingsStmt->execute();
    $newBookings = $bookingsStmt->get_result()->fetch_assoc()['count'] ?? 0;
    
    return [
        'messages' => $unreadMessages,
        'new_bookings' => $newBookings,
        'total' => $unreadMessages + $newBookings
    ];
}

function getAdminNotificationCounts($conn) {
    $userId = (int)($_SESSION['user_id'] ?? 0);
    
    $msgStmt = $conn->prepare("
        SELECT COUNT(*) as count FROM chat_messages 
        WHERE sender_id != ? AND is_read = 0
    ");
    $msgStmt->bind_param("i", $userId);
    $msgStmt->execute();
    $unreadMessages = $msgStmt->get_result()->fetch_assoc()['count'] ?? 0;
    
    $newBookings = $conn->query("
        SELECT COUNT(*) as count FROM bookings WHERE status = 'new'
    ")->fetch_assoc()['count'] ?? 0;
    
    $pendingPayments = $conn->query("
        SELECT COUNT(*) as count FROM payments WHERE status = 'pending'
    ")->fetch_assoc()['count'] ?? 0;
    
    return [
        'messages' => $unreadMessages,
        'new_bookings' => $newBookings,
        'pending_payments' => $pendingPayments,
        'total' => $unreadMessages + $newBookings + $pendingPayments
    ];
}

function getBookingsWithUnreadMessages($conn, $userId, $role) {
    $userId = (int)$userId;
    
    if ($role === 'customer') {
        $query = "
            SELECT b.id, b.booking_number, COUNT(cm.id) as unread_count
            FROM bookings b
            JOIN chat_messages cm ON cm.booking_id = b.id
            WHERE b.customer_id = ? AND cm.sender_id != ? AND cm.is_read = 0
            GROUP BY b.id
            ORDER BY MAX(cm.created_at) DESC
            LIMIT 5
        ";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ii", $userId, $userId);
    } elseif ($role === 'staff') {
        $query = "
            SELECT b.id, b.booking_number, COUNT(cm.id) as unread_count
            FROM bookings b
            JOIN chat_messages cm ON cm.booking_id = b.id
            WHERE b.assigned_staff_id = ? AND cm.sender_id != ? AND cm.is_read = 0
            GROUP BY b.id
            ORDER BY MAX(cm.created_at) DESC
            LIMIT 5
        ";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ii", $userId, $userId);
    } else {
        $query = "
            SELECT b.id, b.booking_number, COUNT(cm.id) as unread_count
            FROM bookings b
            JOIN chat_messages cm ON cm.booking_id = b.id
            WHERE cm.sender_id != ? AND cm.is_read = 0
            GROUP BY b.id
            ORDER BY MAX(cm.created_at) DESC
            LIMIT 10
        ";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $userId);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

function markMessagesAsRead($conn, $bookingId, $userId) {
    $stmt = $conn->prepare("UPDATE chat_messages SET is_read = 1 WHERE booking_id = ? AND sender_id != ?");
    $stmt->bind_param("ii", $bookingId, $userId);
    return $stmt->execute();
}
?>