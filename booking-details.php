<?php
$pageTitle = "Booking Details";
require_once 'includes/header.php';
requireLogin();

$bookingId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$success = isset($_GET['success']);
$paymentSuccess = isset($_GET['payment_success']);

$stmt = $conn->prepare("SELECT b.*, p.name as package_name, p.base_price, p.description as package_description, p.inclusions,
                        u.first_name, u.last_name, u.email, u.phone 
                        FROM bookings b 
                        LEFT JOIN packages p ON b.package_id = p.id 
                        LEFT JOIN users u ON b.customer_id = u.id 
                        WHERE b.id = ? AND b.customer_id = ?");
$stmt->bind_param("ii", $bookingId, $_SESSION['user_id']);
$stmt->execute();
$booking = $stmt->get_result()->fetch_assoc();

if (!$booking) {
    header('Location: ' . url('my-bookings.php'));
    exit();
}

$menuItems = $conn->query("SELECT bm.*, m.name, m.description FROM booking_menu_items bm 
                           LEFT JOIN menu_items m ON bm.menu_item_id = m.id 
                           WHERE bm.booking_id = $bookingId");

$menuItemsTotal = $conn->query("SELECT SUM(price * quantity) as total FROM booking_menu_items WHERE booking_id = $bookingId")->fetch_assoc()['total'] ?? 0;
$packageTotal = ($booking['base_price'] ?? 0) * $booking['number_of_guests'];

$messages = $conn->query("SELECT cm.*, u.first_name, u.last_name, u.role FROM chat_messages cm 
                          LEFT JOIN users u ON cm.sender_id = u.id 
                          WHERE cm.booking_id = $bookingId ORDER BY cm.created_at ASC");

$conn->query("UPDATE chat_messages SET is_read = 1 WHERE booking_id = $bookingId AND sender_id != {$_SESSION['user_id']}");

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_message'])) {
    $message = $conn->real_escape_string($_POST['message']);
    $imagePath = null;
    
    if (isset($_FILES['chat_image']) && $_FILES['chat_image']['size'] > 0) {
        $upload = uploadImage($_FILES['chat_image'], 'uploads/chat');
        if ($upload['success']) {
            $imagePath = $upload['filename'];
        }
    }
    
    if ($message || $imagePath) {
        $stmt = $conn->prepare("INSERT INTO chat_messages (booking_id, sender_id, message, image) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiss", $bookingId, $_SESSION['user_id'], $message, $imagePath);
        $stmt->execute();
        
        header("Location: " . url("booking-details.php?id=$bookingId#chat"));
        exit();
    }
}

$payments = $conn->query("SELECT * FROM payments WHERE booking_id = $bookingId ORDER BY created_at DESC");
?>

<div class="container py-5">
    <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="bi bi-check-circle me-2"></i>
            <strong>Booking submitted successfully!</strong> Our team will review your booking and contact you soon via chat.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <?php if ($paymentSuccess): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="bi bi-check-circle me-2"></i>
            <strong>Payment submitted successfully!</strong> We will verify your payment shortly.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="<?= url('my-bookings.php') ?>" class="btn btn-outline-secondary mb-2"><i class="bi bi-arrow-left me-1"></i>Back to Bookings</a>
            <a href="<?= url('export_booking_pdf.php?id=' . $bookingId) ?>" class="btn btn-danger mb-2 ms-2">
                <i class="bi bi-file-earmark-pdf me-1"></i>Download PDF (Offline Copy)
            </a>
            <h2>Booking #<?= htmlspecialchars($booking['booking_number']) ?></h2>
            <p class="text-muted mb-0">Submitted on <?= formatDateTime($booking['created_at']) ?></p>
        </div>
        <div>
            <?= getStatusBadge($booking['status']) ?>
            <?= getPaymentBadge($booking['payment_status']) ?>
        </div>
    </div>
    
    <div class="row">
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-calendar-event me-2"></i>Event Details</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <strong>Event Type:</strong><br>
                            <?= htmlspecialchars($booking['event_type']) ?>
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Number of Guests:</strong><br>
                            <?= $booking['number_of_guests'] ?> pax
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Event Date:</strong><br>
                            <?= formatDate($booking['event_date']) ?>
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Event Time:</strong><br>
                            <?= date('g:i A', strtotime($booking['event_time'])) ?>
                        </div>
                        <div class="col-12 mb-3">
                            <strong>Venue Address:</strong><br>
                            <?= htmlspecialchars($booking['venue_address']) ?>
                        </div>
                        <?php if ($booking['special_requests']): ?>
                        <div class="col-12">
                            <strong>Special Requests:</strong><br>
                            <?= nl2br(htmlspecialchars($booking['special_requests'])) ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="card mb-4">
                <div class="card-header" style="background: linear-gradient(135deg, #f97316, #ea580c); color: white;">
                    <h5 class="mb-0"><i class="bi bi-box-seam me-2"></i>Package Details</h5>
                </div>
                <div class="card-body">
                    <h5 style="color: var(--primary);"><?= htmlspecialchars($booking['package_name']) ?></h5>
                    <p class="text-muted"><?= htmlspecialchars($booking['package_description'] ?? '') ?></p>
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Price per head:</strong> <?= formatPrice($booking['base_price']) ?></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Guests:</strong> <?= $booking['number_of_guests'] ?> pax</p>
                        </div>
                    </div>
                    <?php if ($booking['inclusions']): ?>
                    <p class="mb-2"><strong>Package Inclusions:</strong></p>
                    <ul class="text-muted">
                        <?php 
                        $inclusions = explode(',', $booking['inclusions']);
                        foreach($inclusions as $inc): 
                            if(trim($inc)):
                        ?>
                        <li><?= htmlspecialchars(trim($inc)) ?></li>
                        <?php endif; endforeach; ?>
                    </ul>
                    <?php endif; ?>
                    <hr>
                    <div class="d-flex justify-content-between">
                        <strong>Package Subtotal:</strong>
                        <strong><?= formatPrice($packageTotal) ?></strong>
                    </div>
                </div>
            </div>
            
            <?php if ($menuItems->num_rows > 0): ?>
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="bi bi-list-check me-2"></i>Selected Menu Items</h5>
                </div>
                <div class="card-body p-0">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Item</th>
                                <th>Price/Tray</th>
                                <th>Qty</th>
                                <th class="text-end">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($item = $menuItems->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($item['name']) ?></strong>
                                    <?php if($item['description']): ?>
                                    <br><small class="text-muted"><?= htmlspecialchars(substr($item['description'], 0, 40)) ?>...</small>
                                    <?php endif; ?>
                                </td>
                                <td><?= formatPrice($item['price']) ?></td>
                                <td><?= $item['quantity'] ?></td>
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
            
            <div class="card" id="chat">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-chat-dots me-2"></i>Chat with Our Team</h5>
                    <small class="text-muted">Negotiate pricing, send payment QR codes, and discuss details here</small>
                </div>
                <div class="card-body" style="max-height: 400px; overflow-y: auto;" id="chatBox">
                    <?php if ($messages->num_rows > 0): ?>
                        <?php while($msg = $messages->fetch_assoc()): ?>
                            <div class="mb-3 <?= $msg['sender_id'] == $_SESSION['user_id'] ? 'text-end' : '' ?>">
                                <div class="d-inline-block p-3 rounded-4 <?= $msg['sender_id'] == $_SESSION['user_id'] ? 'text-white' : 'bg-light' ?>" style="max-width: 80%; <?= $msg['sender_id'] == $_SESSION['user_id'] ? 'background: linear-gradient(135deg, #f97316, #ea580c);' : '' ?>">
                                    <small class="d-block <?= $msg['sender_id'] == $_SESSION['user_id'] ? 'text-white-50' : 'text-muted' ?>">
                                        <?= htmlspecialchars($msg['first_name'] . ' ' . $msg['last_name']) ?>
                                        <?php if (in_array($msg['role'], ['admin', 'super_admin', 'staff'])): ?>
                                            <span class="badge bg-warning text-dark">Staff</span>
                                        <?php endif; ?>
                                    </small>
                                    <?php if ($msg['image']): ?>
                                        <a href="<?= url('uploads/chat/' . $msg['image']) ?>" target="_blank">
                                            <img src="<?= url('uploads/chat/' . $msg['image']) ?>" class="img-fluid rounded mb-2" style="max-width: 200px;">
                                        </a><br>
                                    <?php endif; ?>
                                    <?= nl2br(htmlspecialchars($msg['message'])) ?>
                                    <small class="d-block <?= $msg['sender_id'] == $_SESSION['user_id'] ? 'text-white-50' : 'text-muted' ?> mt-1">
                                        <?= formatDateTime($msg['created_at']) ?>
                                    </small>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p class="text-muted text-center">No messages yet. Start a conversation with our team!</p>
                    <?php endif; ?>
                </div>
                <div class="card-footer bg-white">
                    <form method="POST" enctype="multipart/form-data">
                        <div class="mb-2">
                            <textarea name="message" class="form-control" placeholder="Type your message (optional if sending image)..." rows="2"></textarea>
                        </div>
                        <div class="d-flex gap-2">
                            <input type="file" name="chat_image" class="form-control" accept="image/*" style="max-width: 200px;">
                            <button type="submit" name="send_message" class="btn btn-primary">
                                <i class="bi bi-send me-1"></i>Send
                            </button>
                        </div>
                        <small class="text-muted">You can send a message, an image (payment proof, QR codes, etc.), or both</small>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header text-white" style="background: linear-gradient(135deg, #1e3a5f, #0d1b2a);">
                    <h5 class="mb-0"><i class="bi bi-receipt me-2"></i>Quotation Summary</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Package (<?= $booking['number_of_guests'] ?> pax):</span>
                        <span><?= formatPrice($packageTotal) ?></span>
                    </div>
                    <?php if ($menuItemsTotal > 0): ?>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Menu Items:</span>
                        <span><?= formatPrice($menuItemsTotal) ?></span>
                    </div>
                    <?php endif; ?>
                    <hr>
                    <div class="d-flex justify-content-between mb-2">
                        <strong>Total Amount:</strong>
                        <strong class="fs-4" style="color: var(--primary);"><?= formatPrice($booking['total_amount']) ?></strong>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <span>Payment Status:</span>
                        <?= getPaymentBadge($booking['payment_status']) ?>
                    </div>
                    
                    <div class="alert alert-info small mb-0">
                        <i class="bi bi-info-circle me-1"></i>
                        Use the chat to negotiate final price and send payment via QR code.
                    </div>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-clock-history me-2"></i>Booking Status</h5>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <div class="timeline-item <?= $booking['status'] !== 'cancelled' ? 'active' : '' ?>">
                            <i class="bi bi-check-circle-fill"></i>
                            <span>Submitted</span>
                        </div>
                        <div class="timeline-item <?= in_array($booking['status'], ['pending', 'negotiating', 'approved', 'paid', 'preparing', 'completed']) ? 'active' : '' ?>">
                            <i class="bi bi-hourglass-split"></i>
                            <span>Under Review</span>
                        </div>
                        <div class="timeline-item <?= in_array($booking['status'], ['negotiating', 'approved', 'paid', 'preparing', 'completed']) ? 'active' : '' ?>">
                            <i class="bi bi-chat-dots"></i>
                            <span>Negotiating</span>
                        </div>
                        <div class="timeline-item <?= in_array($booking['status'], ['approved', 'paid', 'preparing', 'completed']) ? 'active' : '' ?>">
                            <i class="bi bi-hand-thumbs-up"></i>
                            <span>Approved</span>
                        </div>
                        <div class="timeline-item <?= in_array($booking['status'], ['paid', 'preparing', 'completed']) ? 'active' : '' ?>">
                            <i class="bi bi-credit-card"></i>
                            <span>Paid</span>
                        </div>
                        <div class="timeline-item <?= $booking['status'] === 'completed' ? 'active' : '' ?>">
                            <i class="bi bi-star"></i>
                            <span>Completed</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.timeline { position: relative; padding-left: 30px; }
.timeline-item { position: relative; padding-bottom: 20px; color: #ccc; }
.timeline-item:before { content: ''; position: absolute; left: -23px; top: 0; bottom: 0; width: 2px; background: #ddd; }
.timeline-item i { position: absolute; left: -30px; background: white; padding: 5px 0; }
.timeline-item.active { color: var(--primary); }
.timeline-item.active:before { background: var(--primary); }
.timeline-item.active i { color: var(--primary); }
.timeline-item:last-child:before { display: none; }
</style>

<script>
document.getElementById('chatBox').scrollTop = document.getElementById('chatBox').scrollHeight;
</script>

<?php require_once 'includes/footer.php'; ?>