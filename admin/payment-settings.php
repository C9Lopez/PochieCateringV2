<?php
require_once '../config/functions.php';
requireRole(['admin', 'super_admin']);

$message = '';
$messageType = '';

// Auto-create table if not exists
$conn->query("CREATE TABLE IF NOT EXISTS `payment_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `payment_type` varchar(50) NOT NULL,
  `account_name` varchar(255) DEFAULT NULL,
  `account_number` varchar(100) DEFAULT NULL,
  `qr_code_image` varchar(500) DEFAULT NULL,
  `is_enabled` tinyint(1) DEFAULT 1,
  `display_order` int(11) DEFAULT 0,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `payment_type` (`payment_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");

// Add missing columns if table was created earlier without them
$conn->query("ALTER TABLE `payment_settings` ADD COLUMN IF NOT EXISTS `updated_by` int(11) DEFAULT NULL");

// Insert default rows if empty
$checkRows = $conn->query("SELECT COUNT(*) as cnt FROM payment_settings")->fetch_assoc()['cnt'];
if ($checkRows == 0) {
    $conn->query("INSERT INTO `payment_settings` (`payment_type`, `account_name`, `account_number`, `is_enabled`, `display_order`) VALUES
        ('gcash', 'Your Name', '0917-XXX-XXXX', 1, 1),
        ('maya', 'Your Name', '0917-XXX-XXXX', 1, 2),
        ('bdo', 'Your Name', '1234-5678-9012', 1, 3),
        ('bpi', 'Your Name', '9876-5432-1098', 1, 4)
    ");
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $paymentType = sanitize($conn, $_POST['payment_type']);
    $accountName = sanitize($conn, $_POST['account_name']);
    $accountNumber = sanitize($conn, $_POST['account_number']);
    $isEnabled = isset($_POST['is_enabled']) ? 1 : 0;
    $qrCodePath = null;
    
    // Handle QR code upload
    if (isset($_FILES['qr_code']) && $_FILES['qr_code']['error'] === 0) {
        $upload = uploadImage($_FILES['qr_code'], 'uploads/payment_qr');
        if ($upload['success']) {
            $qrCodePath = $upload['path'];
        }
    }
    
    // Update or insert payment setting
    if ($qrCodePath) {
        $stmt = $conn->prepare("UPDATE payment_settings SET account_name = ?, account_number = ?, qr_code_image = ?, is_enabled = ?, updated_by = ? WHERE payment_type = ?");
        $stmt->bind_param("ssssis", $accountName, $accountNumber, $qrCodePath, $isEnabled, $_SESSION['user_id'], $paymentType);
    } else {
        $stmt = $conn->prepare("UPDATE payment_settings SET account_name = ?, account_number = ?, is_enabled = ?, updated_by = ? WHERE payment_type = ?");
        $stmt->bind_param("ssiis", $accountName, $accountNumber, $isEnabled, $_SESSION['user_id'], $paymentType);
    }
    
    if ($stmt->execute()) {
        $message = ucfirst($paymentType) . ' settings updated successfully!';
        $messageType = 'success';
        logActivity($conn, $_SESSION['user_id'], 'Update Payment Settings', "Updated $paymentType payment settings");
    } else {
        $message = 'Error updating settings: ' . $conn->error;
        $messageType = 'danger';
    }
}

// Fetch all payment settings
$paymentSettings = [];
$result = $conn->query("SELECT * FROM payment_settings ORDER BY display_order ASC");
while ($row = $result->fetch_assoc()) {
    $paymentSettings[$row['payment_type']] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="<?= url('uploads/settings/69838c04de6ad_281903bf-3ae0-48b7-a44e-acbb35e98438.jpg') ?>" type="image/jpg">
    <title>Payment Settings - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; }
        .qr-preview { 
            max-width: 150px; 
            max-height: 150px; 
            border: 2px dashed #ddd; 
            border-radius: 8px; 
            padding: 5px;
        }
        .payment-card {
            transition: all 0.3s ease;
        }
        .payment-card:hover {
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .payment-icon {
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
            font-size: 24px;
        }
        .gcash-color { background: #007bff; color: white; }
        .maya-color { background: #00d66c; color: white; }
        .bdo-color { background: #003366; color: white; }
        .bpi-color { background: #c8102e; color: white; }
    </style>
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="mb-0"><i class="bi bi-credit-card me-2"></i>Payment Settings</h3>
                <small class="text-muted">Manage payment methods, account numbers, and QR codes</small>
            </div>
        </div>
        
        <?php if ($message): ?>
            <div class="alert alert-<?= $messageType ?> alert-dismissible fade show">
                <?= htmlspecialchars($message) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <div class="row">
            <!-- GCash Settings -->
            <div class="col-md-6 mb-4">
                <div class="card payment-card h-100">
                    <div class="card-header bg-white d-flex align-items-center">
                        <div class="payment-icon gcash-color me-3">
                            <i class="bi bi-phone"></i>
                        </div>
                        <div>
                            <h5 class="mb-0">GCash</h5>
                            <small class="text-muted">E-wallet payment</small>
                        </div>
                        <div class="ms-auto">
                            <?php if ($paymentSettings['gcash']['is_enabled'] ?? false): ?>
                                <span class="badge bg-success">Enabled</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">Disabled</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="card-body">
                        <form method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="payment_type" value="gcash">
                            
                            <div class="mb-3">
                                <label class="form-label">Account Name</label>
                                <input type="text" name="account_name" class="form-control" 
                                       value="<?= htmlspecialchars($paymentSettings['gcash']['account_name'] ?? '') ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">GCash Number</label>
                                <input type="text" name="account_number" class="form-control" 
                                       value="<?= htmlspecialchars($paymentSettings['gcash']['account_number'] ?? '') ?>" 
                                       placeholder="0917-XXX-XXXX" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">QR Code Image</label>
                                <input type="file" name="qr_code" class="form-control" accept="image/*">
                                <small class="text-muted">Upload your GCash QR code image</small>
                                
                                <?php if (!empty($paymentSettings['gcash']['qr_code_image'])): ?>
                                <div class="mt-2">
                                    <p class="small text-success mb-1"><i class="bi bi-check-circle me-1"></i>Current QR Code:</p>
                                    <img src="<?= url($paymentSettings['gcash']['qr_code_image']) ?>" class="qr-preview" alt="GCash QR">
                                </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="mb-3 form-check">
                                <input type="checkbox" name="is_enabled" class="form-check-input" id="gcashEnabled" 
                                       <?= ($paymentSettings['gcash']['is_enabled'] ?? 1) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="gcashEnabled">Enable GCash payments</label>
                            </div>
                            
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-save me-1"></i>Save GCash Settings
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- Maya Settings -->
            <div class="col-md-6 mb-4">
                <div class="card payment-card h-100">
                    <div class="card-header bg-white d-flex align-items-center">
                        <div class="payment-icon maya-color me-3">
                            <i class="bi bi-phone"></i>
                        </div>
                        <div>
                            <h5 class="mb-0">Maya</h5>
                            <small class="text-muted">E-wallet payment</small>
                        </div>
                        <div class="ms-auto">
                            <?php if ($paymentSettings['maya']['is_enabled'] ?? false): ?>
                                <span class="badge bg-success">Enabled</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">Disabled</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="card-body">
                        <form method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="payment_type" value="maya">
                            
                            <div class="mb-3">
                                <label class="form-label">Account Name</label>
                                <input type="text" name="account_name" class="form-control" 
                                       value="<?= htmlspecialchars($paymentSettings['maya']['account_name'] ?? '') ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Maya Number</label>
                                <input type="text" name="account_number" class="form-control" 
                                       value="<?= htmlspecialchars($paymentSettings['maya']['account_number'] ?? '') ?>" 
                                       placeholder="0917-XXX-XXXX" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">QR Code Image</label>
                                <input type="file" name="qr_code" class="form-control" accept="image/*">
                                <small class="text-muted">Upload your Maya QR code image</small>
                                
                                <?php if (!empty($paymentSettings['maya']['qr_code_image'])): ?>
                                <div class="mt-2">
                                    <p class="small text-success mb-1"><i class="bi bi-check-circle me-1"></i>Current QR Code:</p>
                                    <img src="<?= url($paymentSettings['maya']['qr_code_image']) ?>" class="qr-preview" alt="Maya QR">
                                </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="mb-3 form-check">
                                <input type="checkbox" name="is_enabled" class="form-check-input" id="mayaEnabled" 
                                       <?= ($paymentSettings['maya']['is_enabled'] ?? 1) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="mayaEnabled">Enable Maya payments</label>
                            </div>
                            
                            <button type="submit" class="btn btn-success w-100">
                                <i class="bi bi-save me-1"></i>Save Maya Settings
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- BDO Settings -->
            <div class="col-md-6 mb-4">
                <div class="card payment-card h-100">
                    <div class="card-header bg-white d-flex align-items-center">
                        <div class="payment-icon bdo-color me-3">
                            <i class="bi bi-bank"></i>
                        </div>
                        <div>
                            <h5 class="mb-0">BDO</h5>
                            <small class="text-muted">Bank transfer</small>
                        </div>
                        <div class="ms-auto">
                            <?php if ($paymentSettings['bdo']['is_enabled'] ?? false): ?>
                                <span class="badge bg-success">Enabled</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">Disabled</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="card-body">
                        <form method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="payment_type" value="bdo">
                            
                            <div class="mb-3">
                                <label class="form-label">Account Name</label>
                                <input type="text" name="account_name" class="form-control" 
                                       value="<?= htmlspecialchars($paymentSettings['bdo']['account_name'] ?? '') ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Account Number</label>
                                <input type="text" name="account_number" class="form-control" 
                                       value="<?= htmlspecialchars($paymentSettings['bdo']['account_number'] ?? '') ?>" 
                                       placeholder="1234-5678-9012" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">QR Code Image (Optional)</label>
                                <input type="file" name="qr_code" class="form-control" accept="image/*">
                                <small class="text-muted">Upload QR code if available</small>
                                
                                <?php if (!empty($paymentSettings['bdo']['qr_code_image'])): ?>
                                <div class="mt-2">
                                    <p class="small text-success mb-1"><i class="bi bi-check-circle me-1"></i>Current QR Code:</p>
                                    <img src="<?= url($paymentSettings['bdo']['qr_code_image']) ?>" class="qr-preview" alt="BDO QR">
                                </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="mb-3 form-check">
                                <input type="checkbox" name="is_enabled" class="form-check-input" id="bdoEnabled" 
                                       <?= ($paymentSettings['bdo']['is_enabled'] ?? 1) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="bdoEnabled">Enable BDO payments</label>
                            </div>
                            
                            <button type="submit" class="btn btn-dark w-100">
                                <i class="bi bi-save me-1"></i>Save BDO Settings
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- BPI Settings -->
            <div class="col-md-6 mb-4">
                <div class="card payment-card h-100">
                    <div class="card-header bg-white d-flex align-items-center">
                        <div class="payment-icon bpi-color me-3">
                            <i class="bi bi-bank"></i>
                        </div>
                        <div>
                            <h5 class="mb-0">BPI</h5>
                            <small class="text-muted">Bank transfer</small>
                        </div>
                        <div class="ms-auto">
                            <?php if ($paymentSettings['bpi']['is_enabled'] ?? false): ?>
                                <span class="badge bg-success">Enabled</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">Disabled</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="card-body">
                        <form method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="payment_type" value="bpi">
                            
                            <div class="mb-3">
                                <label class="form-label">Account Name</label>
                                <input type="text" name="account_name" class="form-control" 
                                       value="<?= htmlspecialchars($paymentSettings['bpi']['account_name'] ?? '') ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Account Number</label>
                                <input type="text" name="account_number" class="form-control" 
                                       value="<?= htmlspecialchars($paymentSettings['bpi']['account_number'] ?? '') ?>" 
                                       placeholder="9876-5432-1098" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">QR Code Image (Optional)</label>
                                <input type="file" name="qr_code" class="form-control" accept="image/*">
                                <small class="text-muted">Upload QR code if available</small>
                                
                                <?php if (!empty($paymentSettings['bpi']['qr_code_image'])): ?>
                                <div class="mt-2">
                                    <p class="small text-success mb-1"><i class="bi bi-check-circle me-1"></i>Current QR Code:</p>
                                    <img src="<?= url($paymentSettings['bpi']['qr_code_image']) ?>" class="qr-preview" alt="BPI QR">
                                </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="mb-3 form-check">
                                <input type="checkbox" name="is_enabled" class="form-check-input" id="bpiEnabled" 
                                       <?= ($paymentSettings['bpi']['is_enabled'] ?? 1) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="bpiEnabled">Enable BPI payments</label>
                            </div>
                            
                            <button type="submit" class="btn btn-danger w-100">
                                <i class="bi bi-save me-1"></i>Save BPI Settings
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Help Section -->
        <div class="card mt-4">
            <div class="card-body">
                <h5><i class="bi bi-info-circle me-2"></i>Tips</h5>
                <ul class="mb-0 text-muted">
                    <li>Upload clear QR code images for faster customer payments</li>
                    <li>Use formatted numbers (e.g., 0917-123-4567) for better readability</li>
                    <li>Disable payment methods that you don't want to accept</li>
                    <li>Changes will reflect immediately on the customer payment page</li>
                </ul>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
