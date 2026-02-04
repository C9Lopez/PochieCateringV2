<?php
require_once '../config/functions.php';
requireRole(['super_admin']);

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_user'])) {
        $email = sanitize($conn, $_POST['email']);
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $firstName = sanitize($conn, $_POST['first_name']);
        $lastName = sanitize($conn, $_POST['last_name']);
        $phone = sanitize($conn, $_POST['phone']);
        $role = sanitize($conn, $_POST['role']);
        
        $checkEmail = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $checkEmail->bind_param("s", $email);
        $checkEmail->execute();
        if ($checkEmail->get_result()->num_rows > 0) {
            $message = 'Email already exists!';
            $messageType = 'danger';
        } else {
            $stmt = $conn->prepare("INSERT INTO users (email, password, first_name, last_name, phone, role) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssss", $email, $password, $firstName, $lastName, $phone, $role);
            if ($stmt->execute()) {
                $message = 'User added successfully!';
                $messageType = 'success';
            }
        }
    }
    
    if (isset($_POST['edit_user'])) {
        $userId = (int)$_POST['user_id'];
        $firstName = sanitize($conn, $_POST['first_name']);
        $lastName = sanitize($conn, $_POST['last_name']);
        $phone = sanitize($conn, $_POST['phone']);
        $role = sanitize($conn, $_POST['role']);
        
        $stmt = $conn->prepare("UPDATE users SET first_name=?, last_name=?, phone=?, role=? WHERE id=?");
        $stmt->bind_param("ssssi", $firstName, $lastName, $phone, $role, $userId);
        if ($stmt->execute()) {
            $message = 'User updated successfully!';
            $messageType = 'success';
        }
    }
    
    if (isset($_POST['toggle_status'])) {
        $userId = (int)$_POST['user_id'];
        $conn->query("UPDATE users SET is_active = NOT is_active WHERE id = $userId");
        $message = 'User status updated!';
        $messageType = 'info';
    }
    
    if (isset($_POST['delete_user'])) {
        $userId = (int)$_POST['user_id'];
        $conn->query("DELETE FROM users WHERE id = $userId AND role != 'super_admin'");
        $message = 'User deleted successfully!';
        $messageType = 'success';
    }
    
    if (isset($_POST['reset_password'])) {
        $userId = (int)$_POST['user_id'];
        $newPassword = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->bind_param("si", $newPassword, $userId);
        if ($stmt->execute()) {
            $message = 'Password reset successfully!';
            $messageType = 'success';
        }
    }
}

$users = $conn->query("SELECT * FROM users ORDER BY role, created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="<?= url('uploads/settings/69838c04de6ad_281903bf-3ae0-48b7-a44e-acbb35e98438.jpg') ?>" type="image/jpg">
    <title>Manage Users - Admin</title>
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
            <h3>Manage Users</h3>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
                <i class="bi bi-plus-lg me-2"></i>Add User
            </button>
        </div>
        
        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Joined</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($users && $users->num_rows > 0): ?>
                            <?php while($u = $users->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($u['first_name'] . ' ' . $u['last_name']) ?></td>
                                <td><?= htmlspecialchars($u['email']) ?></td>
                                <td><?= htmlspecialchars($u['phone'] ?? '-') ?></td>
                                <td>
                                    <span class="badge bg-<?= $u['role'] == 'super_admin' ? 'danger' : ($u['role'] == 'admin' ? 'primary' : ($u['role'] == 'staff' ? 'info' : 'secondary')) ?>">
                                        <?= ucfirst(str_replace('_', ' ', $u['role'])) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-<?= $u['is_active'] ? 'success' : 'danger' ?>">
                                        <?= $u['is_active'] ? 'Active' : 'Inactive' ?>
                                    </span>
                                </td>
                                <td><?= formatDate($u['created_at']) ?></td>
                                <td>
                                    <?php if ($u['role'] != 'super_admin'): ?>
                                    <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#editUserModal<?= $u['id'] ?>">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-secondary" data-bs-toggle="modal" data-bs-target="#resetPasswordModal<?= $u['id'] ?>">
                                        <i class="bi bi-key"></i>
                                    </button>
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                        <button type="submit" name="toggle_status" class="btn btn-sm btn-<?= $u['is_active'] ? 'warning' : 'success' ?>">
                                            <i class="bi bi-<?= $u['is_active'] ? 'pause' : 'play' ?>"></i>
                                        </button>
                                    </form>
                                    <form method="POST" class="d-inline" onsubmit="return confirm('Delete this user?')">
                                        <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                        <button type="submit" name="delete_user" class="btn btn-sm btn-danger"><i class="bi bi-trash"></i></button>
                                    </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            
                            <div class="modal fade" id="editUserModal<?= $u['id'] ?>" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form method="POST">
                                            <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Edit User</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="row mb-3">
                                                    <div class="col-6">
                                                        <label class="form-label">First Name</label>
                                                        <input type="text" name="first_name" class="form-control" value="<?= htmlspecialchars($u['first_name']) ?>" required>
                                                    </div>
                                                    <div class="col-6">
                                                        <label class="form-label">Last Name</label>
                                                        <input type="text" name="last_name" class="form-control" value="<?= htmlspecialchars($u['last_name']) ?>" required>
                                                    </div>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Phone</label>
                                                    <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($u['phone'] ?? '') ?>">
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Role</label>
                                                    <select name="role" class="form-select" required>
                                                        <option value="customer" <?= $u['role'] == 'customer' ? 'selected' : '' ?>>Customer</option>
                                                        <option value="staff" <?= $u['role'] == 'staff' ? 'selected' : '' ?>>Staff</option>
                                                        <option value="admin" <?= $u['role'] == 'admin' ? 'selected' : '' ?>>Admin</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" name="edit_user" class="btn btn-primary">Save Changes</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="modal fade" id="resetPasswordModal<?= $u['id'] ?>" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form method="POST">
                                            <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Reset Password for <?= htmlspecialchars($u['first_name']) ?></h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <label class="form-label">New Password</label>
                                                    <input type="password" name="new_password" class="form-control" required minlength="6">
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" name="reset_password" class="btn btn-primary">Reset Password</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <?php endwhile; ?>
                            <?php else: ?>
                            <tr><td colspan="7" class="text-center text-muted py-4">No users found</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <div class="modal fade" id="addUserModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title">Add New User</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row mb-3">
                            <div class="col-6">
                                <label class="form-label">First Name</label>
                                <input type="text" name="first_name" class="form-control" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label">Last Name</label>
                                <input type="text" name="last_name" class="form-control" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Phone</label>
                            <input type="text" name="phone" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control" required minlength="6">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Role</label>
                            <select name="role" class="form-select" required>
                                <option value="customer">Customer</option>
                                <option value="staff">Staff</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="add_user" class="btn btn-primary">Add User</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
