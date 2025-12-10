<?php
require_once '../config/functions.php';
requireRole(['admin', 'super_admin']);

$bookingId = (int)($_GET['id'] ?? 0);
if (!$bookingId) {
    header('Location: ' . adminUrl('bookings.php'));
    exit();
}

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_status'])) {
        $newStatus = sanitize($conn, $_POST['status']);
        $conn->query("UPDATE bookings SET status = '$newStatus' WHERE id = $bookingId");
        $message = 'Booking status updated!';
        $messageType = 'success';
    }
    
    if (isset($_POST['assign_staff'])) {
        $staffId = (int)$_POST['staff_id'];
        $conn->query("UPDATE bookings SET assigned_staff_id = $staffId WHERE id = $bookingId");
        $message = 'Staff assigned successfully!';
        $messageType = 'success';
    }
    
    if (isset($_POST['update_amount'])) {
        $newAmount = (float)$_POST['total_amount'];
        $conn->query("UPDATE bookings SET total_amount = $newAmount WHERE id = $bookingId");
        $message = 'Amount updated!';
        $messageType = 'success';
    }
    
    if (isset($_POST['update_payment'])) {
        $paymentStatus = sanitize($conn, $_POST['payment_status']);
        $conn->query("UPDATE bookings SET payment_status = '$paymentStatus' WHERE id = $bookingId");
        $message = 'Payment status updated!';
        $messageType = 'success';
    }
    
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
            $stmt->bind_param("iiss", $bookingId, $_SESSION['user_id'], $messageText, $imageFile);
            $stmt->execute();
            $message = 'Message sent!';
            $messageType = 'success';
        }
    }
}

$booking = $conn->query("SELECT b.*, u.first_name, u.last_name, u.email, u.phone, 
                         p.name as package_name, p.base_price, p.description as package_description, p.inclusions,
                         s.first_name as staff_first, s.last_name as staff_last
                         FROM bookings b 
                         LEFT JOIN users u ON b.customer_id = u.id 
                         LEFT JOIN packages p ON b.package_id = p.id 
                         LEFT JOIN users s ON b.assigned_staff_id = s.id
                         WHERE b.id = $bookingId")->fetch_assoc();

if (!$booking) {
    header('Location: ' . adminUrl('bookings.php'));
    exit();
}

$selectedMenuItems = $conn->query("SELECT bmi.*, mi.name, mi.description as item_description 
                                   FROM booking_menu_items bmi 
                                   LEFT JOIN menu_items mi ON bmi.menu_item_id = mi.id 
                                   WHERE bmi.booking_id = $bookingId");

$menuItemsTotal = $conn->query("SELECT SUM(price * quantity) as total FROM booking_menu_items WHERE booking_id = $bookingId")->fetch_assoc()['total'] ?? 0;
$packageTotal = ($booking['base_price'] ?? 0) * $booking['number_of_guests'];

$staffList = $conn->query("SELECT id, first_name, last_name FROM users WHERE role = 'staff' AND is_active = 1");
$chatMessages = $conn->query("SELECT cm.*, u.first_name, u.last_name, u.role FROM chat_messages cm 
                              LEFT JOIN users u ON cm.sender_id = u.id 
                              WHERE cm.booking_id = $bookingId ORDER BY cm.created_at ASC");

markMessagesAsRead($conn, $bookingId, $_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Details - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; }
        .chat-box { max-height: 400px; overflow-y: auto; }
        .chat-message { padding: 10px 15px; margin-bottom: 10px; border-radius: 10px; }
        .chat-message.admin { background: #e3f2fd; margin-left: 20%; }
        .chat-message.customer { background: #f5f5f5; margin-right: 20%; }
        .chat-message img { max-width: 200px; border-radius: 8px; margin-top: 8px; }
    </style>
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="main-content">
        <?php if ($message): ?>
            <div class="alert alert-<?= $messageType ?> alert-dismissible fade show">
                <?= htmlspecialchars($message) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <a href="<?= adminUrl('bookings.php') ?>" class="btn btn-outline-secondary btn-sm mb-2">
                    <i class="bi bi-arrow-left me-1"></i>Back to Bookings
                </a>
                <h3 class="mb-0">Booking #<?= $booking['booking_number'] ?></h3>
            </div>
            <div>
                <?= getStatusBadge($booking['status']) ?>
                <?= getPaymentBadge($booking['payment_status']) ?>
            </div>
        </div>
        
        <div class="row">
            <div class="col-lg-8">
                <div class="card mb-4">
                    <div class="card-header"><h5 class="mb-0"><i class="bi bi-person me-2"></i>Customer Information</h5></div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Customer:</strong> <?= htmlspecialchars($booking['first_name'] . ' ' . $booking['last_name']) ?></p>
                                <p><strong>Email:</strong> <?= htmlspecialchars($booking['email']) ?></p>
                                <p><strong>Phone:</strong> <?= htmlspecialchars($booking['phone']) ?></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Event Type:</strong> <?= htmlspecialchars($booking['event_type']) ?></p>
                                <p><strong>Event Date:</strong> <?= formatDate($booking['event_date']) ?></p>
                                <p><strong>Event Time:</strong> <?= $booking['event_time'] ?></p>
                            </div>
                        </div>
                        <hr>
                        <p><strong>Venue:</strong> <?= htmlspecialchars($booking['venue_address'] ?? 'N/A') ?></p>
                        <p><strong>Number of Guests:</strong> <?= $booking['number_of_guests'] ?> pax</p>
                        <p><strong>Assigned Staff:</strong> <?= $booking['staff_first'] ? htmlspecialchars($booking['staff_first'] . ' ' . $booking['staff_last']) : '<span class="text-muted">Not assigned</span>' ?></p>
                        <?php if ($booking['special_requests']): ?>
                        <hr>
                        <p><strong>Special Requests:</strong></p>
                        <p class="text-muted"><?= nl2br(htmlspecialchars($booking['special_requests'])) ?></p>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white"><h5 class="mb-0"><i class="bi bi-box-seam me-2"></i>Package Details</h5></div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h5 class="text-primary"><?= htmlspecialchars($booking['package_name'] ?? 'No Package') ?></h5>
                                <p class="text-muted"><?= htmlspecialchars($booking['package_description'] ?? '') ?></p>
                                <p><strong>Price per head:</strong> <?= formatPrice($booking['base_price'] ?? 0) ?></p>
                                <p><strong>Guests:</strong> <?= $booking['number_of_guests'] ?> pax</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Package Inclusions:</strong></p>
                                <ul class="mb-0">
                                    <?php 
                                    $inclusions = explode(',', $booking['inclusions'] ?? '');
                                    foreach($inclusions as $inc): 
                                        if(trim($inc)):
                                    ?>
                                    <li class="text-muted"><?= htmlspecialchars(trim($inc)) ?></li>
                                    <?php endif; endforeach; ?>
                                </ul>
                            </div>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between">
                            <strong>Package Subtotal:</strong>
                            <strong><?= formatPrice($packageTotal) ?></strong>
                        </div>
                    </div>
                </div>
                
                <?php if ($selectedMenuItems && $selectedMenuItems->num_rows > 0): ?>
                <div class="card mb-4">
                    <div class="card-header bg-success text-white"><h5 class="mb-0"><i class="bi bi-list-check me-2"></i>Selected Menu Items</h5></div>
                    <div class="card-body p-0">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Item</th>
                                    <th>Price/Tray</th>
                                    <th>Quantity</th>
                                    <th class="text-end">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($item = $selectedMenuItems->fetch_assoc()): ?>
                                <tr>
                                    <td>
                                        <strong><?= htmlspecialchars($item['name']) ?></strong>
                                        <?php if($item['item_description']): ?>
                                        <br><small class="text-muted"><?= htmlspecialchars(substr($item['item_description'], 0, 50)) ?>...</small>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= formatPrice($item['price']) ?></td>
                                    <td><?= $item['quantity'] ?> tray(s)</td>
                                    <td class="text-end"><?= formatPrice($item['price'] * $item['quantity']) ?></td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <td colspan="3" class="text-end"><strong>Menu Items Subtotal:</strong></td>
                                    <td class="text-end"><strong><?= formatPrice($menuItemsTotal) ?></strong></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                <?php endif; ?>
                
                <div class="card mb-4">
                    <div class="card-header bg-warning"><h5 class="mb-0"><i class="bi bi-receipt me-2"></i>Order Summary</h5></div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Package (<?= $booking['number_of_guests'] ?> pax x <?= formatPrice($booking['base_price'] ?? 0) ?>):</span>
                            <span><?= formatPrice($packageTotal) ?></span>
                        </div>
                        <?php if ($menuItemsTotal > 0): ?>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Additional Menu Items:</span>
                            <span><?= formatPrice($menuItemsTotal) ?></span>
                        </div>
                        <?php endif; ?>
                        <hr>
                        <div class="d-flex justify-content-between">
                            <strong class="fs-5">Total Amount:</strong>
                            <strong class="fs-4 text-success"><?= formatPrice($booking['total_amount']) ?></strong>
                        </div>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-header"><h5 class="mb-0"><i class="bi bi-chat-dots me-2"></i>Chat with Customer</h5></div>
                    <div class="card-body">
                        <div class="chat-box mb-3" id="chatBox">
                            <?php if ($chatMessages && $chatMessages->num_rows > 0): ?>
                            <?php while($msg = $chatMessages->fetch_assoc()): ?>
                            <div class="chat-message <?= $msg['role'] == 'customer' ? 'customer' : 'admin' ?>">
                                <small class="text-muted"><?= htmlspecialchars($msg['first_name']) ?> (<?= ucfirst($msg['role']) ?>) - <?= formatDateTime($msg['created_at']) ?></small>
                                <p class="mb-0"><?= nl2br(htmlspecialchars($msg['message'])) ?></p>
                                <?php if ($msg['image']): ?>
                                <a href="<?= url('uploads/chat/' . $msg['image']) ?>" target="_blank">
                                    <img src="<?= url('uploads/chat/' . $msg['image']) ?>" alt="Chat image">
                                </a>
                                <?php endif; ?>
                            </div>
                            <?php endwhile; ?>
                            <?php else: ?>
                            <p class="text-muted text-center">No messages yet. Start the conversation!</p>
                            <?php endif; ?>
                        </div>
                        <form method="POST" enctype="multipart/form-data">
                            <div class="mb-2">
                                <textarea name="message" class="form-control" placeholder="Type your message (optional if sending image)..." rows="2"></textarea>
                            </div>
                            <div class="d-flex gap-2">
                                <input type="file" name="chat_image" class="form-control" accept="image/*" style="max-width: 250px;">
                                <button type="submit" name="send_message" class="btn btn-primary">
                                    <i class="bi bi-send me-1"></i>Send
                                </button>
                            </div>
                            <small class="text-muted">You can send a message, an image, or both</small>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="card mb-4">
                    <div class="card-header"><h5 class="mb-0">Update Status</h5></div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="mb-3">
                                <select name="status" class="form-select">
                                    <option value="new" <?= $booking['status'] == 'new' ? 'selected' : '' ?>>New</option>
                                    <option value="pending" <?= $booking['status'] == 'pending' ? 'selected' : '' ?>>Pending</option>
                                    <option value="negotiating" <?= $booking['status'] == 'negotiating' ? 'selected' : '' ?>>Negotiating</option>
                                    <option value="approved" <?= $booking['status'] == 'approved' ? 'selected' : '' ?>>Approved</option>
                                    <option value="paid" <?= $booking['status'] == 'paid' ? 'selected' : '' ?>>Paid</option>
                                    <option value="preparing" <?= $booking['status'] == 'preparing' ? 'selected' : '' ?>>Preparing</option>
                                    <option value="completed" <?= $booking['status'] == 'completed' ? 'selected' : '' ?>>Completed</option>
                                    <option value="cancelled" <?= $booking['status'] == 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                </select>
                            </div>
                            <button type="submit" name="update_status" class="btn btn-primary w-100">Update Status</button>
                        </form>
                    </div>
                </div>
                
                <div class="card mb-4">
                    <div class="card-header"><h5 class="mb-0">Update Payment Status</h5></div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="mb-3">
                                <select name="payment_status" class="form-select">
                                    <option value="unpaid" <?= $booking['payment_status'] == 'unpaid' ? 'selected' : '' ?>>Unpaid</option>
                                    <option value="partial" <?= $booking['payment_status'] == 'partial' ? 'selected' : '' ?>>Partial</option>
                                    <option value="paid" <?= $booking['payment_status'] == 'paid' ? 'selected' : '' ?>>Paid</option>
                                </select>
                            </div>
                            <button type="submit" name="update_payment" class="btn btn-success w-100">Update Payment</button>
                        </form>
                    </div>
                </div>
                
                <div class="card mb-4">
                    <div class="card-header"><h5 class="mb-0">Assign Staff</h5></div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="mb-3">
                                <select name="staff_id" class="form-select">
                                    <option value="">Select Staff</option>
                                    <?php if ($staffList && $staffList->num_rows > 0): ?>
                                    <?php while($s = $staffList->fetch_assoc()): ?>
                                    <option value="<?= $s['id'] ?>" <?= $booking['assigned_staff_id'] == $s['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($s['first_name'] . ' ' . $s['last_name']) ?>
                                    </option>
                                    <?php endwhile; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                            <button type="submit" name="assign_staff" class="btn btn-info w-100">Assign Staff</button>
                        </form>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-header"><h5 class="mb-0">Update Amount</h5></div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">Total Amount</label>
                                <input type="number" name="total_amount" class="form-control" step="0.01" value="<?= $booking['total_amount'] ?>">
                                <small class="text-muted">Adjust after negotiation</small>
                            </div>
                            <button type="submit" name="update_amount" class="btn btn-warning w-100">Update Amount</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('chatBox').scrollTop = document.getElementById('chatBox').scrollHeight;
    </script>
</body>
</html>