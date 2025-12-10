<?php
require_once '../config/functions.php';
requireRole(['super_admin']);

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['save_settings'])) {
        $settings = [
            'site_name' => sanitize($conn, $_POST['site_name']),
            'site_email' => sanitize($conn, $_POST['site_email']),
            'site_phone' => sanitize($conn, $_POST['site_phone']),
            'site_address' => sanitize($conn, $_POST['site_address']),
            'gcash_number' => sanitize($conn, $_POST['gcash_number']),
            'bank_name' => sanitize($conn, $_POST['bank_name']),
            'bank_account_name' => sanitize($conn, $_POST['bank_account_name']),
            'bank_account_number' => sanitize($conn, $_POST['bank_account_number']),
        ];
        
        foreach ($settings as $key => $value) {
            $stmt = $conn->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
            $stmt->bind_param("sss", $key, $value, $value);
            $stmt->execute();
        }
        
        $message = 'Settings saved successfully!';
        $messageType = 'success';
    }
}

$settings = getSettings($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>body { font-family: 'Poppins', sans-serif; }</style>
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
        
        <h3 class="mb-4">System Settings</h3>
        
        <form method="POST">
            <div class="row">
                <div class="col-lg-6">
                    <div class="card mb-4">
                        <div class="card-header"><h5 class="mb-0"><i class="bi bi-globe me-2"></i>Site Information</h5></div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label">Site Name</label>
                                <input type="text" name="site_name" class="form-control" value="<?= htmlspecialchars($settings['site_name'] ?? 'Filipino Catering') ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Contact Email</label>
                                <input type="email" name="site_email" class="form-control" value="<?= htmlspecialchars($settings['site_email'] ?? '') ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Contact Phone</label>
                                <input type="text" name="site_phone" class="form-control" value="<?= htmlspecialchars($settings['site_phone'] ?? '') ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Address</label>
                                <textarea name="site_address" class="form-control" rows="2"><?= htmlspecialchars($settings['site_address'] ?? '') ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-6">
                    <div class="card mb-4">
                        <div class="card-header"><h5 class="mb-0"><i class="bi bi-credit-card me-2"></i>Payment Settings</h5></div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label">GCash Number</label>
                                <input type="text" name="gcash_number" class="form-control" value="<?= htmlspecialchars($settings['gcash_number'] ?? '') ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Bank Name</label>
                                <input type="text" name="bank_name" class="form-control" value="<?= htmlspecialchars($settings['bank_name'] ?? '') ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Bank Account Name</label>
                                <input type="text" name="bank_account_name" class="form-control" value="<?= htmlspecialchars($settings['bank_account_name'] ?? '') ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Bank Account Number</label>
                                <input type="text" name="bank_account_number" class="form-control" value="<?= htmlspecialchars($settings['bank_account_number'] ?? '') ?>">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <button type="submit" name="save_settings" class="btn btn-primary btn-lg">
                <i class="bi bi-check-lg me-2"></i>Save Settings
            </button>
        </form>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
