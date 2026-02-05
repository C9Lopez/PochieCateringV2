<?php
require_once '../config/functions.php';
requireRole(['super_admin']);

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['save_settings'])) {
        $settingsData = [
            'site_name' => sanitize($conn, $_POST['site_name']),
            'site_email' => sanitize($conn, $_POST['site_email']),
            'site_phone' => sanitize($conn, $_POST['site_phone']),
            'site_address' => sanitize($conn, $_POST['site_address']),
            'facebook_url' => sanitize($conn, $_POST['facebook_url']),
            'gcash_number' => sanitize($conn, $_POST['gcash_number']),
            'bank_name' => sanitize($conn, $_POST['bank_name']),
            'bank_account_name' => sanitize($conn, $_POST['bank_account_name']),
            'bank_account_number' => sanitize($conn, $_POST['bank_account_number']),
        ];

        // Handle Site Logo Upload
        if (isset($_FILES['site_logo']) && $_FILES['site_logo']['error'] === 0) {
            $upload = uploadImage($_FILES['site_logo'], 'uploads/settings');
            if ($upload['success']) {
                $settingsData['site_logo'] = $upload['filename'];
            }
        }
        
        foreach ($settingsData as $key => $value) {
            $stmt = $conn->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
            $stmt->bind_param("sss", $key, $value, $value);
            $stmt->execute();
        }
        
        logActivity($conn, $_SESSION['user_id'], 'Update Settings', 'Updated general system settings');
        $message = 'Settings saved successfully!';
        $messageType = 'success';
    }

    if (isset($_POST['update_terms'])) {
        $terms = $_POST['terms_of_use']; // Don't sanitize too strictly as it might contain HTML or formatted text
        $userId = $_SESSION['user_id'];
        
        // Save to history
        $stmt = $conn->prepare("INSERT INTO terms_history (type, content, updated_by, status) VALUES ('terms_of_use', ?, ?, 'published')");
        $stmt->bind_param("si", $terms, $userId);
        
        if ($stmt->execute()) {
            // Update current settings
            $stmt = $conn->prepare("INSERT INTO settings (setting_key, setting_value) VALUES ('terms_of_use', ?) ON DUPLICATE KEY UPDATE setting_value = ?");
            $stmt->bind_param("ss", $terms, $terms);
            $stmt->execute();
            
            logActivity($conn, $userId, 'Update Terms of Use', 'Updated the Terms of Use content');
            $message = 'Terms of Use updated and published successfully!';
            $messageType = 'success';
        } else {
            $message = 'Error updating Terms of Use: ' . $conn->error;
            $messageType = 'danger';
        }
    }

    if (isset($_POST['update_privacy'])) {
        $privacy = $_POST['privacy_policy'];
        $userId = $_SESSION['user_id'];
        
        // Save to history
        $stmt = $conn->prepare("INSERT INTO terms_history (type, content, updated_by, status) VALUES ('privacy_policy', ?, ?, 'published')");
        $stmt->bind_param("si", $privacy, $userId);
        
        if ($stmt->execute()) {
            // Update current settings
            $stmt = $conn->prepare("INSERT INTO settings (setting_key, setting_value) VALUES ('privacy_policy', ?) ON DUPLICATE KEY UPDATE setting_value = ?");
            $stmt->bind_param("ss", $privacy, $privacy);
            $stmt->execute();
            
            logActivity($conn, $userId, 'Update Privacy Policy', 'Updated the Data Privacy Policy content');
            $message = 'Privacy Policy updated and published successfully!';
            $messageType = 'success';
        } else {
            $message = 'Error updating Privacy Policy: ' . $conn->error;
            $messageType = 'danger';
        }
    }
}

$settings = getSettings($conn);

// Fetch history
$termsHistory = $conn->query("
    SELECT th.*, u.first_name, u.last_name 
    FROM terms_history th 
    JOIN users u ON th.updated_by = u.id 
    ORDER BY th.updated_at DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="<?= url('uploads/settings/69838c04de6ad_281903bf-3ae0-48b7-a44e-acbb35e98438.jpg') ?>" type="image/jpg">
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
        
        <form method="POST" enctype="multipart/form-data">
            <div class="row">
                <div class="col-lg-6">
                    <div class="card mb-4">
                        <div class="card-header"><h5 class="mb-0"><i class="bi bi-globe me-2"></i>Site Information</h5></div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label d-block">Current Logo</label>
                                <?php if (!empty($settings['site_logo'])): ?>
                                    <img src="<?= url('uploads/settings/' . $settings['site_logo']) ?>" alt="Logo" class="img-thumbnail mb-2" style="max-height: 80px;">
                                <?php else: ?>
                                    <div class="bg-light p-3 rounded mb-2 text-center text-muted" style="max-width: 80px;">No Logo</div>
                                <?php endif; ?>
                                <input type="file" name="site_logo" class="form-control" accept="image/*">
                                <small class="text-muted">Recommended: PNG or SVG with transparent background</small>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Site Name</label>
                                <input type="text" name="site_name" class="form-control" value="<?= htmlspecialchars($settings['site_name'] ?? ' ') ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Facebook Page URL</label>
                                <input type="url" name="facebook_url" class="form-control" placeholder="https://facebook.com/yourpage" value="<?= htmlspecialchars($settings['facebook_url'] ?? '') ?>">
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

        <hr class="my-5">

        <h3 class="mb-4">Legal & Policies</h3>
        
        <div class="row">
            <div class="col-lg-12">
                <div class="card mb-4">
                    <div class="card-header bg-white">
                        <ul class="nav nav-tabs card-header-tabs" id="policyTabs" role="tablist">
                            <li class="nav-item">
                                <button class="nav-link active" id="terms-tab" data-bs-toggle="tab" data-bs-target="#terms" type="button">Terms of Use</button>
                            </li>
                            <li class="nav-item">
                                <button class="nav-link" id="privacy-tab" data-bs-toggle="tab" data-bs-target="#privacy" type="button">Privacy Policy</button>
                            </li>
                            <li class="nav-item">
                                <button class="nav-link" id="history-tab" data-bs-toggle="tab" data-bs-target="#history" type="button">Update Logs</button>
                            </li>
                        </ul>
                    </div>
                    <div class="card-body">
                        <div class="tab-content" id="policyTabsContent">
                            <div class="tab-pane fade show active" id="terms" role="tabpanel">
                                <form method="POST">
                                    <div class="mb-3">
                                        <label class="form-label">Terms of Use Content</label>
                                        <textarea name="terms_of_use" class="form-control" rows="15"><?= htmlspecialchars($settings['terms_of_use'] ?? '') ?></textarea>
                                    </div>
                                    <div class="alert alert-info py-2 small">
                                        <i class="bi bi-info-circle me-2"></i>Ito ang lalabas sa registration page kapag pinindot ang "Terms of Use" link.
                                    </div>
                                    <button type="submit" name="update_terms" class="btn btn-success">
                                        <i class="bi bi-cloud-upload me-2"></i>Publish Updated Terms
                                    </button>
                                </form>
                            </div>
                            
                            <div class="tab-pane fade" id="privacy" role="tabpanel">
                                <form method="POST">
                                    <div class="mb-3">
                                        <label class="form-label">Data Privacy Policy Content</label>
                                        <textarea name="privacy_policy" class="form-control" rows="15"><?= htmlspecialchars($settings['privacy_policy'] ?? '') ?></textarea>
                                    </div>
                                    <div class="alert alert-info py-2 small">
                                        <i class="bi bi-info-circle me-2"></i>Ito ang lalabas sa registration page kapag pinindot ang "Data Privacy Policy" link.
                                    </div>
                                    <button type="submit" name="update_privacy" class="btn btn-success">
                                        <i class="bi bi-cloud-upload me-2"></i>Publish Updated Privacy Policy
                                    </button>
                                </form>
                            </div>
                            
                            <div class="tab-pane fade" id="history" role="tabpanel">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Date & Time</th>
                                                <th>Type</th>
                                                <th>Updated By</th>
                                                <th>Status</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if ($termsHistory && $termsHistory->num_rows > 0): ?>
                                                <?php while($log = $termsHistory->fetch_assoc()): ?>
                                                    <tr>
                                                        <td>
                                                            <div class="fw-bold"><?= date('M j, Y', strtotime($log['updated_at'])) ?></div>
                                                            <small class="text-muted"><?= date('g:i A', strtotime($log['updated_at'])) ?></small>
                                                        </td>
                                                        <td>
                                                            <span class="badge bg-light text-dark border">
                                                                <?= $log['type'] === 'terms_of_use' ? 'Terms of Use' : 'Privacy Policy' ?>
                                                            </span>
                                                        </td>
                                                        <td><?= htmlspecialchars($log['first_name'] . ' ' . $log['last_name']) ?></td>
                                                        <td><span class="badge bg-success">Published</span></td>
                                                        <td>
                                                            <button class="btn btn-sm btn-outline-primary" onclick="viewHistory(<?= $log['id'] ?>)">
                                                                <i class="bi bi-eye"></i> View
                                                            </button>
                                                        </td>
                                                    </tr>
                                                <?php endwhile; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="5" class="text-center py-4 text-muted">No update logs found.</td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- History Modal -->
    <div class="modal fade" id="viewHistoryModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Version Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="historyContent" style="white-space: pre-wrap; font-size: 14px; background: #f8fafc; padding: 20px; border-radius: 10px; border: 1px solid #e2e8f0;">
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function viewHistory(id) {
            // In a real app, you'd fetch this via AJAX. For now, we'll just show a message or find it in the table.
            fetch(`get_history_detail.php?id=${id}`)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('historyContent').innerText = data.content;
                    new bootstrap.Modal(document.getElementById('viewHistoryModal')).show();
                });
        }
    </script>
</body>
</html>
