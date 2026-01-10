<?php
require_once '../config/functions.php';
requireRole(['admin', 'super_admin']);

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_promotion'])) {
        $title = sanitize($conn, $_POST['title']);
        $description = sanitize($conn, $_POST['description']);
        $discountPercentage = (int)$_POST['discount_percentage'];
        $startDate = $_POST['start_date'] ?: null;
        $endDate = $_POST['end_date'] ?: null;
        $isActive = isset($_POST['is_active']) ? 1 : 0;
        $createdBy = $_SESSION['user_id'];
        $image = null;
        
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $upload = uploadImage($_FILES['image'], 'uploads/promotions');
            if ($upload['success']) {
                $image = $upload['filename'];
            }
        }
        
        $stmt = $conn->prepare("INSERT INTO promotions (title, description, discount_percentage, image, start_date, end_date, is_active, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssisssii", $title, $description, $discountPercentage, $image, $startDate, $endDate, $isActive, $createdBy);
        if ($stmt->execute()) {
            $message = 'Promotion added successfully!';
            $messageType = 'success';
            logActivity($conn, $_SESSION['user_id'], 'Added Promotion', "Added promotion: $title");
        } else {
            $message = 'Error adding promotion: ' . $conn->error;
            $messageType = 'danger';
        }
    }
    
    if (isset($_POST['edit_promotion'])) {
        $promoId = (int)$_POST['promo_id'];
        $title = sanitize($conn, $_POST['title']);
        $description = sanitize($conn, $_POST['description']);
        $discountPercentage = (int)$_POST['discount_percentage'];
        $startDate = $_POST['start_date'] ?: null;
        $endDate = $_POST['end_date'] ?: null;
        $isActive = isset($_POST['is_active']) ? 1 : 0;
        
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $upload = uploadImage($_FILES['image'], 'uploads/promotions');
            if ($upload['success']) {
                $image = $upload['filename'];
                $stmt = $conn->prepare("UPDATE promotions SET title=?, description=?, discount_percentage=?, image=?, start_date=?, end_date=?, is_active=? WHERE id=?");
                $stmt->bind_param("ssisssii", $title, $description, $discountPercentage, $image, $startDate, $endDate, $isActive, $promoId);
            } else {
                $stmt = $conn->prepare("UPDATE promotions SET title=?, description=?, discount_percentage=?, start_date=?, end_date=?, is_active=? WHERE id=?");
                $stmt->bind_param("ssisssi", $title, $description, $discountPercentage, $startDate, $endDate, $isActive, $promoId);
            }
        } else {
            $stmt = $conn->prepare("UPDATE promotions SET title=?, description=?, discount_percentage=?, start_date=?, end_date=?, is_active=? WHERE id=?");
            $stmt->bind_param("ssisssi", $title, $description, $discountPercentage, $startDate, $endDate, $isActive, $promoId);
        }
        
        if ($stmt->execute()) {
            $message = 'Promotion updated successfully!';
            $messageType = 'success';
            logActivity($conn, $_SESSION['user_id'], 'Updated Promotion', "Updated promotion: $title");
        }
    }
    
    if (isset($_POST['toggle_promotion'])) {
        $promoId = (int)$_POST['promo_id'];
        $conn->query("UPDATE promotions SET is_active = NOT is_active WHERE id = $promoId");
        $message = 'Promotion status updated!';
        $messageType = 'info';
    }
    
    if (isset($_POST['delete_promotion'])) {
        $promoId = (int)$_POST['promo_id'];
        $conn->query("DELETE FROM promotions WHERE id = $promoId");
        $message = 'Promotion deleted successfully!';
        $messageType = 'success';
        logActivity($conn, $_SESSION['user_id'], 'Deleted Promotion', "Deleted promotion ID: $promoId");
    }
}

$promotions = $conn->query("SELECT p.*, u.first_name, u.last_name FROM promotions p LEFT JOIN users u ON p.created_by = u.id ORDER BY p.created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Promotions - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; }
        .promo-img { width: 100px; height: 60px; object-fit: cover; border-radius: 8px; }
        .promo-img-placeholder { width: 100px; height: 60px; background: linear-gradient(135deg, #fde68a, #fbbf24); border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 24px; }
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
                <h3><i class="bi bi-megaphone me-2"></i>Manage Promotions</h3>
                <p class="text-muted mb-0">Create and manage promotional announcements for the home page</p>
            </div>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addPromoModal">
                <i class="bi bi-plus-lg me-2"></i>Add Promotion
            </button>
        </div>
        
        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Image</th>
                                <th>Title</th>
                                <th>Description</th>
                                <th>Duration</th>
                                <th>Status</th>
                                <th>Created By</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($promotions && $promotions->num_rows > 0): ?>
                            <?php while($promo = $promotions->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <?php if ($promo['image']): ?>
                                        <img src="<?= url('uploads/promotions/' . $promo['image']) ?>" alt="<?= htmlspecialchars($promo['title']) ?>" class="promo-img">
                                    <?php else: ?>
                                        <div class="promo-img-placeholder">🎉</div>
                                    <?php endif; ?>
                                </td>
                                <td><strong><?= htmlspecialchars($promo['title']) ?></strong></td>
                                <td>
                                    <small class="text-muted"><?= htmlspecialchars(substr($promo['description'] ?? '', 0, 80)) ?><?= strlen($promo['description'] ?? '') > 80 ? '...' : '' ?></small>
                                </td>
                                <td>
                                    <?php if ($promo['start_date'] && $promo['end_date']): ?>
                                        <small><?= date('M d', strtotime($promo['start_date'])) ?> - <?= date('M d, Y', strtotime($promo['end_date'])) ?></small>
                                    <?php elseif ($promo['start_date']): ?>
                                        <small>From <?= date('M d, Y', strtotime($promo['start_date'])) ?></small>
                                    <?php elseif ($promo['end_date']): ?>
                                        <small>Until <?= date('M d, Y', strtotime($promo['end_date'])) ?></small>
                                    <?php else: ?>
                                        <small class="text-muted">No dates set</small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php 
                                    $isExpired = $promo['end_date'] && strtotime($promo['end_date']) < strtotime('today');
                                    if ($isExpired): ?>
                                        <span class="badge bg-danger">Expired</span>
                                    <?php elseif ($promo['is_active']): ?>
                                        <span class="badge bg-success">Active</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td><small><?= htmlspecialchars($promo['first_name'] . ' ' . $promo['last_name']) ?></small></td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#editPromoModal<?= $promo['id'] ?>">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="promo_id" value="<?= $promo['id'] ?>">
                                        <button type="submit" name="toggle_promotion" class="btn btn-sm btn-<?= $promo['is_active'] ? 'warning' : 'success' ?>">
                                            <i class="bi bi-<?= $promo['is_active'] ? 'eye-slash' : 'eye' ?>"></i>
                                        </button>
                                    </form>
                                    <form method="POST" class="d-inline" onsubmit="return confirm('Delete this promotion?')">
                                        <input type="hidden" name="promo_id" value="<?= $promo['id'] ?>">
                                        <button type="submit" name="delete_promotion" class="btn btn-sm btn-danger"><i class="bi bi-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                            
                            <div class="modal fade" id="editPromoModal<?= $promo['id'] ?>" tabindex="-1">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <form method="POST" enctype="multipart/form-data">
                                            <input type="hidden" name="promo_id" value="<?= $promo['id'] ?>">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Edit Promotion</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <?php if ($promo['image']): ?>
                                                <div class="mb-3 text-center">
                                                    <img src="<?= url('uploads/promotions/' . $promo['image']) ?>" alt="Current image" style="max-width: 200px; border-radius: 8px;">
                                                    <p class="text-muted small mt-1">Current image</p>
                                                </div>
                                                <?php endif; ?>
                                                <div class="mb-3">
                                                    <label class="form-label">Promotion Image</label>
                                                    <input type="file" name="image" class="form-control" accept="image/*">
                                                    <small class="text-muted">Leave empty to keep current image (JPG, PNG, GIF, WEBP)</small>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Title</label>
                                                    <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($promo['title']) ?>" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Description</label>
                                                    <textarea name="description" class="form-control" rows="4"><?= htmlspecialchars($promo['description'] ?? '') ?></textarea>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Discount Percentage (%)</label>
                                                    <div class="input-group">
                                                        <input type="number" name="discount_percentage" class="form-control" value="<?= $promo['discount_percentage'] ?>" min="0" max="100">
                                                        <span class="input-group-text">%</span>
                                                    </div>
                                                    <small class="text-muted">Enter 0 for no automated discount</small>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-6 mb-3">
                                                        <label class="form-label">Start Date (Optional)</label>
                                                        <input type="date" name="start_date" class="form-control" value="<?= $promo['start_date'] ?>">
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <label class="form-label">End Date (Optional)</label>
                                                        <input type="date" name="end_date" class="form-control" value="<?= $promo['end_date'] ?>">
                                                    </div>
                                                </div>
                                                <div class="form-check">
                                                    <input type="checkbox" name="is_active" class="form-check-input" id="active<?= $promo['id'] ?>" <?= $promo['is_active'] ? 'checked' : '' ?>>
                                                    <label class="form-check-label" for="active<?= $promo['id'] ?>">Active (Show on homepage)</label>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" name="edit_promotion" class="btn btn-primary">Save Changes</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <?php endwhile; ?>
                            <?php else: ?>
                            <tr><td colspan="7" class="text-center text-muted py-4">No promotions yet. Add one to get started!</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <div class="modal fade" id="addPromoModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-header">
                        <h5 class="modal-title">Add New Promotion</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Promotion Image</label>
                            <input type="file" name="image" class="form-control" accept="image/*">
                            <small class="text-muted">Upload a banner/poster image (JPG, PNG, GIF, WEBP)</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Title</label>
                            <input type="text" name="title" class="form-control" placeholder="e.g., Holiday Special - 20% Off!" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="4" placeholder="Describe your promotion details here..."></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Discount Percentage (%)</label>
                            <div class="input-group">
                                <input type="number" name="discount_percentage" class="form-control" value="0" min="0" max="100">
                                <span class="input-group-text">%</span>
                            </div>
                            <small class="text-muted">Enter 0 for no automated discount</small>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Start Date (Optional)</label>
                                <input type="date" name="start_date" class="form-control">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">End Date (Optional)</label>
                                <input type="date" name="end_date" class="form-control">
                            </div>
                        </div>
                        <div class="form-check">
                            <input type="checkbox" name="is_active" class="form-check-input" id="isActive" checked>
                            <label class="form-check-label" for="isActive">Active (Show on homepage)</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="add_promotion" class="btn btn-primary">Add Promotion</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
