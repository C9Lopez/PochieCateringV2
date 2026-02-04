<?php
$pageTitle = "Submit Payment";
require_once 'includes/header.php';
requireLogin();

$bookingId = isset($_GET['booking']) ? (int)$_GET['booking'] : 0;
$paymongoError = isset($_GET['error']) ? $_GET['error'] : '';

$bookingStmt = $conn->prepare("SELECT b.*, p.name as package_name FROM bookings b 
                         LEFT JOIN packages p ON b.package_id = p.id 
                         WHERE b.id = ? AND b.customer_id = ?");
$bookingStmt->bind_param("ii", $bookingId, $_SESSION['user_id']);
$bookingStmt->execute();
$booking = $bookingStmt->get_result()->fetch_assoc();

if (!$booking || !in_array($booking['status'], ['approved', 'paid', 'preparing'])) {
    header('Location: ' . url('my-bookings.php'));
    exit();
}

// Calculate paid and remaining amounts
$paidStmt = $conn->prepare("SELECT SUM(amount) as paid FROM payments WHERE booking_id = ? AND status = 'verified'");
$paidStmt->bind_param("i", $bookingId);
$paidStmt->execute();
$paidAmount = (float)($paidStmt->get_result()->fetch_assoc()['paid'] ?? 0);
$remainingAmount = $booking['total_amount'] - $paidAmount;
$downpaymentAmount = $booking['total_amount'] * 0.5;

// Fetch payment settings from database (create table if not exists)
$paymentMethods = [];
$tableExists = $conn->query("SHOW TABLES LIKE 'payment_settings'")->num_rows > 0;

if (!$tableExists) {
    // Create the table
    $conn->query("CREATE TABLE IF NOT EXISTS `payment_settings` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `payment_type` varchar(50) NOT NULL,
        `account_name` varchar(255) DEFAULT NULL,
        `account_number` varchar(100) DEFAULT NULL,
        `qr_code_image` varchar(255) DEFAULT NULL,
        `is_enabled` tinyint(1) DEFAULT 1,
        `display_order` int(11) DEFAULT 0,
        `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
        `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
        PRIMARY KEY (`id`),
        UNIQUE KEY `payment_type` (`payment_type`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    
    // Insert default values
    $conn->query("INSERT INTO `payment_settings` (`payment_type`, `account_name`, `account_number`, `is_enabled`, `display_order`) VALUES
        ('gcash', 'Pochie Catering Services', '09XX XXX XXXX', 1, 1),
        ('maya', 'Pochie Catering Services', '09XX XXX XXXX', 1, 2),
        ('bdo', 'Pochie Catering Services', 'XXXX XXXX XXXX', 1, 3),
        ('bpi', 'Pochie Catering Services', 'XXXX XXXX XXXX', 1, 4)");
}

$pmResult = $conn->query("SELECT * FROM payment_settings WHERE is_enabled = 1 ORDER BY display_order ASC");
if ($pmResult) {
    while ($pm = $pmResult->fetch_assoc()) {
        $paymentMethods[$pm['payment_type']] = $pm;
    }
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $amount = (float)$_POST['amount'];
    $method = $conn->real_escape_string($_POST['payment_method']);
    $reference = $conn->real_escape_string($_POST['reference_number']);
    $proofPath = null;
    
    if (isset($_FILES['proof_image']) && $_FILES['proof_image']['size'] > 0) {
        $upload = uploadImage($_FILES['proof_image'], 'uploads/payments');
        if ($upload['success']) {
            $proofPath = $upload['path'];
        }
    }
    
    $stmt = $conn->prepare("INSERT INTO payments (booking_id, amount, payment_method, reference_number, proof_image) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("idsss", $bookingId, $amount, $method, $reference, $proofPath);
    
    if ($stmt->execute()) {
        $admins = $conn->query("SELECT id FROM users WHERE role IN ('admin', 'super_admin')");
        while ($admin = $admins->fetch_assoc()) {
            addNotification($conn, $admin['id'], 'Payment Submitted', "Payment submitted for booking #{$booking['booking_number']}", 'info', adminUrl("payments.php"));
        }
        
        header("Location: " . url("booking-details.php?id=$bookingId&payment_success=1"));
        exit();
    } else {
        $error = 'Failed to submit payment. Please try again.';
    }
}
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <a href="<?= url('booking-details.php?id=' . $bookingId) ?>" class="btn btn-outline-secondary mb-3"><i class="bi bi-arrow-left me-1"></i>Back to Booking</a>
            
            <div class="card">
                <div class="card-header text-white" style="background: linear-gradient(135deg, #f97316, #ea580c);">
                    <h5 class="mb-0"><i class="bi bi-credit-card me-2"></i>Submit Payment</h5>
                </div>
                <div class="card-body">
                    <?php if ($paymongoError): ?>
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            <?= htmlspecialchars(urldecode($paymongoError)) ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?= $error ?></div>
                    <?php endif; ?>
                    
                    <div class="alert alert-info">
                        <strong>Booking:</strong> #<?= $booking['booking_number'] ?><br>
                        <strong>Package:</strong> <?= htmlspecialchars($booking['package_name']) ?><br>
                        <strong>Total Amount:</strong> <?= formatPrice($booking['total_amount']) ?>
                        <?php if ($paidAmount > 0): ?>
                        <br><strong class="text-success">Paid:</strong> <?= formatPrice($paidAmount) ?>
                        <br><strong class="text-danger">Remaining:</strong> <?= formatPrice($remainingAmount) ?>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Online Payment Option (PayMongo) -->
                    <div class="card border-primary mb-4">
                        <div class="card-header bg-primary text-white">
                            <h6 class="mb-0"><i class="bi bi-lightning-charge me-2"></i>Pay Online (Instant Verification)</h6>
                        </div>
                        <div class="card-body">
                            <p class="text-muted small mb-3">
                                Magbayad gamit ang GCash, GrabPay, o Maya. Automatic ang verification - walang need mag-upload ng screenshot!
                            </p>
                            
                            <div class="row g-2">
                                <div class="col-md-6">
                                    <a href="<?= url('pay-online.php?booking=' . $bookingId . '&type=downpayment') ?>" 
                                       class="btn btn-outline-primary w-100 <?= $paidAmount >= $downpaymentAmount ? 'disabled' : '' ?>">
                                        <i class="bi bi-wallet2 me-2"></i>
                                        50% Downpayment<br>
                                        <small><?= formatPrice($downpaymentAmount) ?></small>
                                    </a>
                                </div>
                                <div class="col-md-6">
                                    <a href="<?= url('pay-online.php?booking=' . $bookingId . '&type=full') ?>" 
                                       class="btn btn-primary w-100 <?= $remainingAmount <= 0 ? 'disabled' : '' ?>">
                                        <i class="bi bi-credit-card me-2"></i>
                                        Pay Full Amount<br>
                                        <small><?= formatPrice($remainingAmount > 0 ? $remainingAmount : $booking['total_amount']) ?></small>
                                    </a>
                                </div>
                            </div>
                            
                            <div class="mt-3 text-center">
                                <img src="https://www.paymongo.com/assets/gcash.svg" alt="GCash" height="24" class="mx-1">
                                <img src="https://www.paymongo.com/assets/grab_pay.svg" alt="GrabPay" height="24" class="mx-1">
                                <img src="https://www.paymongo.com/assets/paymaya.svg" alt="Maya" height="24" class="mx-1">
                            </div>
                        </div>
                    </div>
                    
                    <div class="text-center my-4">
                        <span class="text-muted">— o kaya —</span>
                    </div>
                    
                    <div class="card bg-light mb-4">
                        <div class="card-body">
                            <h6 class="mb-3"><i class="bi bi-wallet2 me-2"></i>Payment Methods Accepted:</h6>
                            
                            <?php 
                            $gcash = $paymentMethods['gcash'] ?? null;
                            $maya = $paymentMethods['maya'] ?? null;
                            $bdo = $paymentMethods['bdo'] ?? null;
                            $bpi = $paymentMethods['bpi'] ?? null;
                            $hasEwallet = $gcash || $maya;
                            $hasBank = $bdo || $bpi;
                            ?>
                            
                            <ul class="nav nav-tabs" id="paymentTabs" role="tablist">
                                <?php if ($gcash): ?>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="gcash-tab" data-bs-toggle="tab" data-bs-target="#gcash" type="button" role="tab">
                                        <i class="bi bi-phone me-1"></i>GCash
                                    </button>
                                </li>
                                <?php endif; ?>
                                <?php if ($maya): ?>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link <?= !$gcash ? 'active' : '' ?>" id="maya-tab" data-bs-toggle="tab" data-bs-target="#maya" type="button" role="tab">
                                        <i class="bi bi-phone me-1"></i>Maya
                                    </button>
                                </li>
                                <?php endif; ?>
                                <?php if ($hasBank): ?>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link <?= !$hasEwallet ? 'active' : '' ?>" id="bank-tab" data-bs-toggle="tab" data-bs-target="#bank" type="button" role="tab">
                                        <i class="bi bi-bank me-1"></i>Bank Transfer
                                    </button>
                                </li>
                                <?php endif; ?>
                            </ul>
                            
                            <div class="tab-content p-3 border border-top-0 rounded-bottom" id="paymentTabsContent">
                                <?php if ($gcash): ?>
                                <!-- GCash Tab -->
                                <div class="tab-pane fade show active" id="gcash" role="tabpanel">
                                    <div class="row align-items-center">
                                        <div class="col-md-6">
                                            <p class="mb-2"><strong>GCash Number:</strong></p>
                                            <h4 class="text-primary mb-1"><?= htmlspecialchars($gcash['account_number']) ?></h4>
                                            <p class="text-muted mb-3"><?= htmlspecialchars($gcash['account_name']) ?></p>
                                            <button type="button" class="btn btn-sm btn-outline-primary copy-btn" data-copy="<?= htmlspecialchars(preg_replace('/[^0-9]/', '', $gcash['account_number'])) ?>">
                                                <i class="bi bi-clipboard me-1"></i>Copy Number
                                            </button>
                                        </div>
                                        <div class="col-md-6 text-center">
                                              <?php if (!empty($gcash['qr_code_image'])): ?>
                                              <div class="border rounded p-3 bg-white d-inline-block qr-clickable" data-bs-toggle="modal" data-bs-target="#qrModal" data-qr-src="<?= url($gcash['qr_code_image']) ?>" data-qr-title="GCash QR Code" role="button">
                                                  <img src="<?= url($gcash['qr_code_image']) ?>" alt="GCash QR Code" class="img-fluid" style="max-width: 150px;">
                                              </div>
                                              <p class="small text-muted mt-2"><i class="bi bi-zoom-in me-1"></i>Click to enlarge</p>
                                              <?php else: ?>
                                            <div class="border rounded p-3 bg-white d-inline-block text-muted">
                                                <i class="bi bi-qr-code" style="font-size: 4rem;"></i>
                                                <br><small>QR Code not set</small>
                                            </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                <?php endif; ?>
                                
                                <?php if ($maya): ?>
                                <!-- Maya Tab -->
                                <div class="tab-pane fade <?= !$gcash ? 'show active' : '' ?>" id="maya" role="tabpanel">
                                    <div class="row align-items-center">
                                        <div class="col-md-6">
                                            <p class="mb-2"><strong>Maya Number:</strong></p>
                                            <h4 class="text-success mb-1"><?= htmlspecialchars($maya['account_number']) ?></h4>
                                            <p class="text-muted mb-3"><?= htmlspecialchars($maya['account_name']) ?></p>
                                            <button type="button" class="btn btn-sm btn-outline-success copy-btn" data-copy="<?= htmlspecialchars(preg_replace('/[^0-9]/', '', $maya['account_number'])) ?>">
                                                <i class="bi bi-clipboard me-1"></i>Copy Number
                                            </button>
                                        </div>
                                        <div class="col-md-6 text-center">
                                              <?php if (!empty($maya['qr_code_image'])): ?>
                                              <div class="border rounded p-3 bg-white d-inline-block qr-clickable" data-bs-toggle="modal" data-bs-target="#qrModal" data-qr-src="<?= url($maya['qr_code_image']) ?>" data-qr-title="Maya QR Code" role="button">
                                                  <img src="<?= url($maya['qr_code_image']) ?>" alt="Maya QR Code" class="img-fluid" style="max-width: 150px;">
                                              </div>
                                              <p class="small text-muted mt-2"><i class="bi bi-zoom-in me-1"></i>Click to enlarge</p>
                                              <?php else: ?>
                                            <div class="border rounded p-3 bg-white d-inline-block text-muted">
                                                <i class="bi bi-qr-code" style="font-size: 4rem;"></i>
                                                <br><small>QR Code not set</small>
                                            </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                <?php endif; ?>
                                
                                <?php if ($hasBank): ?>
                                <!-- Bank Transfer Tab -->
                                <div class="tab-pane fade <?= !$hasEwallet ? 'show active' : '' ?>" id="bank" role="tabpanel">
                                    <div class="row">
                                        <?php if ($bdo): ?>
                                        <div class="col-md-6 mb-3">
                                            <div class="border rounded p-3 bg-white">
                                                <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/1/19/BDO_Unibank_%28logo%29.svg/120px-BDO_Unibank_%28logo%29.svg.png" alt="BDO" height="25" class="mb-2" onerror="this.style.display='none'">
                                                <p class="mb-1"><strong>BDO Savings Account</strong></p>
                                                <h5 class="text-primary mb-1"><?= htmlspecialchars($bdo['account_number']) ?></h5>
                                                <p class="text-muted small mb-2"><?= htmlspecialchars($bdo['account_name']) ?></p>
                                                <button type="button" class="btn btn-sm btn-outline-primary copy-btn" data-copy="<?= htmlspecialchars(preg_replace('/[^0-9]/', '', $bdo['account_number'])) ?>">
                                                    <i class="bi bi-clipboard me-1"></i>Copy
                                                </button>
                                            </div>
                                        </div>
                                        <?php endif; ?>
                                        <?php if ($bpi): ?>
                                        <div class="col-md-6 mb-3">
                                            <div class="border rounded p-3 bg-white">
                                                <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/e/e8/BPI_logo_2022.svg/120px-BPI_logo_2022.svg.png" alt="BPI" height="25" class="mb-2" onerror="this.style.display='none'">
                                                <p class="mb-1"><strong>BPI Savings Account</strong></p>
                                                <h5 class="text-danger mb-1"><?= htmlspecialchars($bpi['account_number']) ?></h5>
                                                <p class="text-muted small mb-2"><?= htmlspecialchars($bpi['account_name']) ?></p>
                                                <button type="button" class="btn btn-sm btn-outline-danger copy-btn" data-copy="<?= htmlspecialchars(preg_replace('/[^0-9]/', '', $bpi['account_number'])) ?>">
                                                    <i class="bi bi-clipboard me-1"></i>Copy
                                                </button>
                                            </div>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="alert alert-warning mt-3 mb-0 small">
                                <i class="bi bi-exclamation-triangle me-1"></i>
                                <strong>Importante:</strong> I-screenshot ang iyong payment confirmation at isama ang <strong>Reference Number</strong> sa form sa ibaba.
                            </div>
                        </div>
                    </div>
                    
                    <form method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label class="form-label">Payment Method *</label>
                            <select name="payment_method" class="form-select" required>
                                <option value="">Select payment method...</option>
                                <option value="GCash">GCash</option>
                                <option value="Maya">Maya</option>
                                <option value="BDO Bank Transfer">BDO Bank Transfer</option>
                                <option value="BPI Bank Transfer">BPI Bank Transfer</option>
                                <option value="Cash">Cash Payment</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Payment Type *</label>
                            
                            <?php if ($paidAmount > 0): ?>
                            <!-- Already has downpayment - only show remaining balance option -->
                            <div class="alert alert-success small mb-2">
                                <i class="bi bi-check-circle me-1"></i>Downpayment of <?= formatPrice($paidAmount) ?> already paid.
                            </div>
                            <div class="payment-option selected p-3 border rounded bg-light">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="payment_type" id="payRemaining" value="remaining" checked>
                                    <label class="form-check-label w-100" for="payRemaining">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <strong>Remaining Balance</strong>
                                                <br><small class="text-muted">Complete your payment</small>
                                            </div>
                                            <span class="badge bg-success fs-5"><?= formatPrice($remainingAmount) ?></span>
                                        </div>
                                    </label>
                                </div>
                            </div>
                            <input type="hidden" name="amount" value="<?= $remainingAmount ?>">
                            
                            <?php else: ?>
                            <!-- No payment yet - show downpayment and full payment options -->
                            <div class="payment-options">
                                <div class="payment-option p-3 border rounded mb-2" onclick="selectPaymentType('downpayment')">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="payment_type" id="payDownpayment" value="downpayment" checked>
                                        <label class="form-check-label w-100" for="payDownpayment">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <strong>50% Downpayment</strong>
                                                    <br><small class="text-muted">Minimum required to confirm booking</small>
                                                </div>
                                                <span class="badge bg-warning text-dark fs-5"><?= formatPrice($downpaymentAmount) ?></span>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                                
                                <div class="payment-option p-3 border rounded" onclick="selectPaymentType('full')">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="payment_type" id="payFull" value="full">
                                        <label class="form-check-label w-100" for="payFull">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <strong>Full Payment</strong>
                                                    <br><small class="text-muted">Pay the complete amount now</small>
                                                </div>
                                                <span class="badge bg-success fs-5"><?= formatPrice($booking['total_amount']) ?></span>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Hidden amount field that updates based on selection -->
                            <input type="hidden" name="amount" id="paymentAmount" value="<?= $downpaymentAmount ?>">
                            <?php endif; ?>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Reference Number *</label>
                            <input type="text" name="reference_number" class="form-control" required placeholder="Enter transaction reference number">
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label">Proof of Payment (Screenshot) *</label>
                            <input type="file" name="proof_image" class="form-control" accept="image/*" required>
                            <small class="text-muted">Upload screenshot of your payment confirmation</small>
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-lg w-100">
                            <i class="bi bi-send me-2"></i>Submit Payment
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- QR Code Zoom Modal -->
<div class="modal fade" id="qrModal" tabindex="-1" aria-labelledby="qrModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="qrModalLabel">QR Code</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center p-4">
                <img src="" id="qrModalImage" alt="QR Code" class="img-fluid" style="max-width: 100%;">
            </div>
            <div class="modal-footer justify-content-center">
                <p class="text-muted small mb-0"><i class="bi bi-phone me-1"></i>Scan this QR code using your e-wallet app</p>
            </div>
        </div>
    </div>
</div>

<script>
// QR Code Modal - show larger image when clicked
document.querySelectorAll('.qr-clickable').forEach(function(el) {
    el.addEventListener('click', function() {
        const qrSrc = this.getAttribute('data-qr-src');
        const qrTitle = this.getAttribute('data-qr-title');
        document.getElementById('qrModalImage').src = qrSrc;
        document.getElementById('qrModalLabel').textContent = qrTitle;
    });
});

// Payment type selection
const downpaymentAmount = <?= $downpaymentAmount ?>;
const fullAmount = <?= $booking['total_amount'] ?>;

function selectPaymentType(type) {
    const options = document.querySelectorAll('.payment-option');
    options.forEach(opt => opt.classList.remove('selected', 'border-primary', 'bg-light'));
    
    if (type === 'downpayment') {
        document.getElementById('payDownpayment').checked = true;
        document.getElementById('paymentAmount').value = downpaymentAmount;
        document.getElementById('payDownpayment').closest('.payment-option').classList.add('selected', 'border-primary', 'bg-light');
    } else {
        document.getElementById('payFull').checked = true;
        document.getElementById('paymentAmount').value = fullAmount;
        document.getElementById('payFull').closest('.payment-option').classList.add('selected', 'border-primary', 'bg-light');
    }
}

// Initialize selected state
document.addEventListener('DOMContentLoaded', function() {
    const checkedOption = document.querySelector('input[name="payment_type"]:checked');
    if (checkedOption) {
        checkedOption.closest('.payment-option').classList.add('selected', 'border-primary', 'bg-light');
    }
});

// Copy to clipboard functionality
document.querySelectorAll('.copy-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const text = this.getAttribute('data-copy');
        navigator.clipboard.writeText(text).then(() => {
            const originalHTML = this.innerHTML;
            this.innerHTML = '<i class="bi bi-check me-1"></i>Copied!';
            this.classList.add('btn-success');
            this.classList.remove('btn-outline-primary', 'btn-outline-success', 'btn-outline-danger');
            setTimeout(() => {
                this.innerHTML = originalHTML;
                this.classList.remove('btn-success');
                this.classList.add('btn-outline-primary');
            }, 2000);
        });
    });
});

// Image preview before upload
document.querySelector('input[name="proof_image"]').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            let preview = document.getElementById('image-preview');
            if (!preview) {
                preview = document.createElement('div');
                preview.id = 'image-preview';
                preview.className = 'mt-2 text-center';
                document.querySelector('input[name="proof_image"]').parentElement.appendChild(preview);
            }
            preview.innerHTML = `
                <img src="${e.target.result}" class="img-fluid rounded border" style="max-height: 200px;">
                <p class="small text-muted mt-1">Preview ng iyong proof of payment</p>
            `;
        };
        reader.readAsDataURL(file);
    }
});
</script>

<style>
.qr-clickable {
    cursor: pointer;
    transition: all 0.2s ease;
}
.qr-clickable:hover {
    transform: scale(1.05);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}
.payment-option {
    cursor: pointer;
    transition: all 0.2s ease;
}
.payment-option:hover {
    border-color: #f97316 !important;
    background-color: #fff8f5;
}
.payment-option.selected {
    border-color: #f97316 !important;
    background-color: #fff8f5 !important;
}
.payment-option .form-check-input:checked {
    background-color: #f97316;
    border-color: #f97316;
}
</style>

<?php require_once 'includes/footer.php'; ?>
