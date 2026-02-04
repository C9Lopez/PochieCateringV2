<?php
require_once '../config/functions.php';
requireRole(['admin', 'super_admin']);

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_package'])) {
        $name = sanitize($conn, $_POST['name']);
        $description = sanitize($conn, $_POST['description']);
        $basePrice = (float)$_POST['base_price'];
        $minPax = (int)$_POST['min_pax'];
        $maxPax = (int)$_POST['max_pax'];
        $inclusions = sanitize($conn, $_POST['inclusions']);
        
        $stmt = $conn->prepare("INSERT INTO packages (name, description, base_price, min_pax, max_pax, inclusions) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssdiis", $name, $description, $basePrice, $minPax, $maxPax, $inclusions);
        if ($stmt->execute()) {
            $message = 'Package added successfully!';
            $messageType = 'success';
        }
    }
    
    if (isset($_POST['edit_package'])) {
        $id = (int)$_POST['package_id'];
        $name = sanitize($conn, $_POST['name']);
        $description = sanitize($conn, $_POST['description']);
        $basePrice = (float)$_POST['base_price'];
        $minPax = (int)$_POST['min_pax'];
        $maxPax = (int)$_POST['max_pax'];
        $inclusions = sanitize($conn, $_POST['inclusions']);
        
        $stmt = $conn->prepare("UPDATE packages SET name=?, description=?, base_price=?, min_pax=?, max_pax=?, inclusions=? WHERE id=?");
        $stmt->bind_param("ssdiisi", $name, $description, $basePrice, $minPax, $maxPax, $inclusions, $id);
        if ($stmt->execute()) {
            $message = 'Package updated successfully!';
            $messageType = 'success';
        }
    }
    
    if (isset($_POST['toggle_package'])) {
        $id = (int)$_POST['package_id'];
        $conn->query("UPDATE packages SET is_active = NOT is_active WHERE id = $id");
        $message = 'Package status updated!';
        $messageType = 'info';
    }
    
    if (isset($_POST['delete_package'])) {
        $id = (int)$_POST['package_id'];
        $conn->query("DELETE FROM packages WHERE id = $id");
        $message = 'Package deleted successfully!';
        $messageType = 'success';
    }
}

$packages = $conn->query("SELECT * FROM packages ORDER BY base_price");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="<?= url('uploads/settings/69838c04de6ad_281903bf-3ae0-48b7-a44e-acbb35e98438.jpg') ?>" type="image/jpg">
    <title>Manage Packages - Admin</title>
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
        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3>Manage Packages</h3>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addPackageModal">
                <i class="bi bi-plus-lg me-2"></i>Add Package
            </button>
        </div>
        
        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Name</th>
                                <th>Price/Head</th>
                                <th>Min Pax</th>
                                <th>Max Pax</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($packages && $packages->num_rows > 0): ?>
                            <?php while($pkg = $packages->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($pkg['name']) ?></strong><br>
                                    <small class="text-muted"><?= htmlspecialchars(substr($pkg['description'] ?? '', 0, 60)) ?>...</small>
                                </td>
                                <td><?= formatPrice($pkg['base_price']) ?></td>
                                <td><?= $pkg['min_pax'] ?></td>
                                <td><?= $pkg['max_pax'] ?></td>
                                <td><span class="badge bg-<?= $pkg['is_active'] ? 'success' : 'danger' ?>"><?= $pkg['is_active'] ? 'Active' : 'Inactive' ?></span></td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#editPackageModal<?= $pkg['id'] ?>">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="package_id" value="<?= $pkg['id'] ?>">
                                        <button type="submit" name="toggle_package" class="btn btn-sm btn-<?= $pkg['is_active'] ? 'warning' : 'success' ?>">
                                            <i class="bi bi-<?= $pkg['is_active'] ? 'pause' : 'play' ?>"></i>
                                        </button>
                                    </form>
                                    <form method="POST" class="d-inline" onsubmit="return confirm('Delete this package?')">
                                        <input type="hidden" name="package_id" value="<?= $pkg['id'] ?>">
                                        <button type="submit" name="delete_package" class="btn btn-sm btn-danger"><i class="bi bi-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                            
                            <div class="modal fade" id="editPackageModal<?= $pkg['id'] ?>" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form method="POST">
                                            <input type="hidden" name="package_id" value="<?= $pkg['id'] ?>">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Edit Package</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <label class="form-label">Package Name</label>
                                                    <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($pkg['name']) ?>" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Description</label>
                                                    <textarea name="description" class="form-control" rows="2"><?= htmlspecialchars($pkg['description'] ?? '') ?></textarea>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Base Price (per head)</label>
                                                    <input type="number" name="base_price" class="form-control" step="0.01" value="<?= $pkg['base_price'] ?>" required>
                                                </div>
                                                <div class="row">
                                                    <div class="col-6 mb-3">
                                                        <label class="form-label">Min Pax</label>
                                                        <input type="number" name="min_pax" class="form-control" value="<?= $pkg['min_pax'] ?>" required>
                                                    </div>
                                                    <div class="col-6 mb-3">
                                                        <label class="form-label">Max Pax</label>
                                                        <input type="number" name="max_pax" class="form-control" value="<?= $pkg['max_pax'] ?>" required>
                                                    </div>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Inclusions</label>
                                                    <textarea name="inclusions" class="form-control" rows="3"><?= htmlspecialchars($pkg['inclusions'] ?? '') ?></textarea>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" name="edit_package" class="btn btn-primary">Save Changes</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <?php endwhile; ?>
                            <?php else: ?>
                            <tr><td colspan="6" class="text-center text-muted py-4">No packages yet. Add one to get started!</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <div class="modal fade" id="addPackageModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title">Add Package</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Package Name</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="2"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Base Price (per head)</label>
                            <input type="number" name="base_price" class="form-control" step="0.01" required>
                        </div>
                        <div class="row">
                            <div class="col-6 mb-3">
                                <label class="form-label">Min Pax</label>
                                <input type="number" name="min_pax" class="form-control" value="50" required>
                            </div>
                            <div class="col-6 mb-3">
                                <label class="form-label">Max Pax</label>
                                <input type="number" name="max_pax" class="form-control" value="500" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Inclusions (comma separated)</label>
                            <textarea name="inclusions" class="form-control" rows="3" placeholder="Table setup, Serving utensils, Wait staff..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="add_package" class="btn btn-primary">Add Package</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
