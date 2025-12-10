<?php
require_once '../config/functions.php';
requireRole(['admin', 'super_admin']);

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_item'])) {
        $name = sanitize($conn, $_POST['name']);
        $categoryId = (int)$_POST['category_id'];
        $description = sanitize($conn, $_POST['description']);
        $price = (float)$_POST['price'];
        $isFeatured = isset($_POST['is_featured']) ? 1 : 0;
        $image = null;
        
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $upload = uploadImage($_FILES['image'], 'uploads/menu');
            if ($upload['success']) {
                $image = $upload['filename'];
            }
        }
        
        $stmt = $conn->prepare("INSERT INTO menu_items (category_id, name, description, price, image, is_featured) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("issdsi", $categoryId, $name, $description, $price, $image, $isFeatured);
        if ($stmt->execute()) {
            $message = 'Menu item added successfully!';
            $messageType = 'success';
        } else {
            $message = 'Error adding item: ' . $conn->error;
            $messageType = 'danger';
        }
    }
    
    if (isset($_POST['edit_item'])) {
        $itemId = (int)$_POST['item_id'];
        $name = sanitize($conn, $_POST['name']);
        $categoryId = (int)$_POST['category_id'];
        $description = sanitize($conn, $_POST['description']);
        $price = (float)$_POST['price'];
        $isFeatured = isset($_POST['is_featured']) ? 1 : 0;
        
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $upload = uploadImage($_FILES['image'], 'uploads/menu');
            if ($upload['success']) {
                $image = $upload['filename'];
                $stmt = $conn->prepare("UPDATE menu_items SET category_id=?, name=?, description=?, price=?, image=?, is_featured=? WHERE id=?");
                $stmt->bind_param("issdsis", $categoryId, $name, $description, $price, $image, $isFeatured, $itemId);
            } else {
                $stmt = $conn->prepare("UPDATE menu_items SET category_id=?, name=?, description=?, price=?, is_featured=? WHERE id=?");
                $stmt->bind_param("issdii", $categoryId, $name, $description, $price, $isFeatured, $itemId);
            }
        } else {
            $stmt = $conn->prepare("UPDATE menu_items SET category_id=?, name=?, description=?, price=?, is_featured=? WHERE id=?");
            $stmt->bind_param("issdii", $categoryId, $name, $description, $price, $isFeatured, $itemId);
        }
        
        if ($stmt->execute()) {
            $message = 'Menu item updated successfully!';
            $messageType = 'success';
        }
    }
    
    if (isset($_POST['toggle_item'])) {
        $itemId = (int)$_POST['item_id'];
        $conn->query("UPDATE menu_items SET is_available = NOT is_available WHERE id = $itemId");
        $message = 'Item status updated!';
        $messageType = 'info';
    }
    
    if (isset($_POST['delete_item'])) {
        $itemId = (int)$_POST['item_id'];
        $conn->query("DELETE FROM menu_items WHERE id = $itemId");
        $message = 'Item deleted successfully!';
        $messageType = 'success';
    }
    
    if (isset($_POST['add_category'])) {
        $name = sanitize($conn, $_POST['cat_name']);
        $description = sanitize($conn, $_POST['cat_description']);
        $stmt = $conn->prepare("INSERT INTO menu_categories (name, description) VALUES (?, ?)");
        $stmt->bind_param("ss", $name, $description);
        if ($stmt->execute()) {
            $message = 'Category added successfully!';
            $messageType = 'success';
        }
    }
}

$categories = $conn->query("SELECT * FROM menu_categories ORDER BY name");
$items = $conn->query("SELECT m.*, c.name as category_name FROM menu_items m LEFT JOIN menu_categories c ON m.category_id = c.id ORDER BY c.name, m.name");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Menu - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; }
        .menu-img { width: 60px; height: 60px; object-fit: cover; border-radius: 8px; }
        .menu-img-placeholder { width: 60px; height: 60px; background: linear-gradient(135deg, #fed7aa, #fdba74); border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 24px; }
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
            <h3>Manage Menu</h3>
            <div>
                <button class="btn btn-outline-primary me-2" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                    <i class="bi bi-folder-plus me-2"></i>Add Category
                </button>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addItemModal">
                    <i class="bi bi-plus-lg me-2"></i>Add Menu Item
                </button>
            </div>
        </div>
        
        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Image</th>
                                <th>Name</th>
                                <th>Category</th>
                                <th>Price</th>
                                <th>Featured</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($items && $items->num_rows > 0): ?>
                            <?php while($item = $items->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <?php if ($item['image']): ?>
                                        <img src="<?= url('uploads/menu/' . $item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>" class="menu-img">
                                    <?php else: ?>
                                        <div class="menu-img-placeholder">🍲</div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong><?= htmlspecialchars($item['name']) ?></strong><br>
                                    <small class="text-muted"><?= htmlspecialchars(substr($item['description'] ?? '', 0, 50)) ?>...</small>
                                </td>
                                <td><?= htmlspecialchars($item['category_name'] ?? 'Uncategorized') ?></td>
                                <td><?= formatPrice($item['price']) ?></td>
                                <td><?= $item['is_featured'] ? '<i class="bi bi-star-fill text-warning"></i>' : '-' ?></td>
                                <td>
                                    <span class="badge bg-<?= $item['is_available'] ? 'success' : 'danger' ?>">
                                        <?= $item['is_available'] ? 'Available' : 'Unavailable' ?>
                                    </span>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#editItemModal<?= $item['id'] ?>">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="item_id" value="<?= $item['id'] ?>">
                                        <button type="submit" name="toggle_item" class="btn btn-sm btn-<?= $item['is_available'] ? 'warning' : 'success' ?>">
                                            <i class="bi bi-<?= $item['is_available'] ? 'eye-slash' : 'eye' ?>"></i>
                                        </button>
                                    </form>
                                    <form method="POST" class="d-inline" onsubmit="return confirm('Delete this item?')">
                                        <input type="hidden" name="item_id" value="<?= $item['id'] ?>">
                                        <button type="submit" name="delete_item" class="btn btn-sm btn-danger"><i class="bi bi-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                            
                            <div class="modal fade" id="editItemModal<?= $item['id'] ?>" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form method="POST" enctype="multipart/form-data">
                                            <input type="hidden" name="item_id" value="<?= $item['id'] ?>">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Edit Menu Item</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <?php if ($item['image']): ?>
                                                <div class="mb-3 text-center">
                                                    <img src="<?= url('uploads/menu/' . $item['image']) ?>" alt="Current image" style="max-width: 150px; border-radius: 8px;">
                                                    <p class="text-muted small mt-1">Current image</p>
                                                </div>
                                                <?php endif; ?>
                                                <div class="mb-3">
                                                    <label class="form-label">Menu Item Image</label>
                                                    <input type="file" name="image" class="form-control" accept="image/*">
                                                    <small class="text-muted">Leave empty to keep current image</small>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Category</label>
                                                    <select name="category_id" class="form-select" required>
                                                        <?php 
                                                        $categories->data_seek(0);
                                                        while($c = $categories->fetch_assoc()): ?>
                                                            <option value="<?= $c['id'] ?>" <?= $c['id'] == $item['category_id'] ? 'selected' : '' ?>><?= htmlspecialchars($c['name']) ?></option>
                                                        <?php endwhile; ?>
                                                    </select>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Name</label>
                                                    <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($item['name']) ?>" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Description</label>
                                                    <textarea name="description" class="form-control" rows="2"><?= htmlspecialchars($item['description'] ?? '') ?></textarea>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Price (per tray)</label>
                                                    <input type="number" name="price" class="form-control" step="0.01" value="<?= $item['price'] ?>" required>
                                                </div>
                                                <div class="form-check">
                                                    <input type="checkbox" name="is_featured" class="form-check-input" id="featured<?= $item['id'] ?>" <?= $item['is_featured'] ? 'checked' : '' ?>>
                                                    <label class="form-check-label" for="featured<?= $item['id'] ?>">Featured Item</label>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" name="edit_item" class="btn btn-primary">Save Changes</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <?php endwhile; ?>
                            <?php else: ?>
                            <tr><td colspan="7" class="text-center text-muted py-4">No menu items yet. Add one to get started!</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <div class="modal fade" id="addItemModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-header">
                        <h5 class="modal-title">Add Menu Item</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Menu Item Image</label>
                            <input type="file" name="image" class="form-control" accept="image/*">
                            <small class="text-muted">Upload a photo of the dish (JPG, PNG, GIF, WEBP)</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Category</label>
                            <select name="category_id" class="form-select" required>
                                <option value="">Select Category</option>
                                <?php $categories->data_seek(0); while($c = $categories->fetch_assoc()): ?>
                                    <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Name</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="2"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Price (per tray)</label>
                            <input type="number" name="price" class="form-control" step="0.01" required>
                        </div>
                        <div class="form-check">
                            <input type="checkbox" name="is_featured" class="form-check-input" id="featured">
                            <label class="form-check-label" for="featured">Featured Item</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="add_item" class="btn btn-primary">Add Item</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="modal fade" id="addCategoryModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title">Add Category</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Category Name</label>
                            <input type="text" name="cat_name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="cat_description" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="add_category" class="btn btn-primary">Add Category</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>