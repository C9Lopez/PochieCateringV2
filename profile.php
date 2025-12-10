<?php
$pageTitle = "My Profile";
require_once 'includes/header.php';
requireLogin();

$user = getCurrentUser($conn);
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile'])) {
        $firstName = $conn->real_escape_string($_POST['first_name']);
        $lastName = $conn->real_escape_string($_POST['last_name']);
        $phone = $conn->real_escape_string($_POST['phone']);
        $address = $conn->real_escape_string($_POST['address']);
        
        $stmt = $conn->prepare("UPDATE users SET first_name = ?, last_name = ?, phone = ?, address = ? WHERE id = ?");
        $stmt->bind_param("ssssi", $firstName, $lastName, $phone, $address, $_SESSION['user_id']);
        $stmt->execute();
        
        $_SESSION['user_name'] = "$firstName $lastName";
        $success = 'Profile updated successfully!';
        $user = getCurrentUser($conn);
    }
    
    if (isset($_POST['change_password'])) {
        $currentPassword = $_POST['current_password'];
        $newPassword = $_POST['new_password'];
        $confirmPassword = $_POST['confirm_password'];
        
        if (!password_verify($currentPassword, $user['password'])) {
            $error = 'Current password is incorrect';
        } elseif ($newPassword !== $confirmPassword) {
            $error = 'New passwords do not match';
        } elseif (strlen($newPassword) < 6) {
            $error = 'Password must be at least 6 characters';
        } else {
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $conn->query("UPDATE users SET password = '$hashedPassword' WHERE id = {$_SESSION['user_id']}");
            $success = 'Password changed successfully!';
        }
    }
}
?>

<div class="container py-5">
    <h2 class="mb-4"><i class="bi bi-person-circle me-2"></i>My Profile</h2>
    
    <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?= $error ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?= $success ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <div class="row">
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header bg-white"><h5 class="mb-0">Personal Information</h5></div>
                <div class="card-body">
                    <form method="POST">
                        <div class="row mb-3">
                            <div class="col-6">
                                <label class="form-label">First Name</label>
                                <input type="text" name="first_name" class="form-control" value="<?= htmlspecialchars($user['first_name']) ?>" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label">Last Name</label>
                                <input type="text" name="last_name" class="form-control" value="<?= htmlspecialchars($user['last_name']) ?>" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" disabled>
                            <small class="text-muted">Email cannot be changed</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Phone Number</label>
                            <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Address</label>
                            <textarea name="address" class="form-control" rows="2"><?= htmlspecialchars($user['address'] ?? '') ?></textarea>
                        </div>
                        <button type="submit" name="update_profile" class="btn btn-primary">
                            <i class="bi bi-save me-2"></i>Update Profile
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-white"><h5 class="mb-0">Change Password</h5></div>
                <div class="card-body">
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Current Password</label>
                            <input type="password" name="current_password" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">New Password</label>
                            <input type="password" name="new_password" class="form-control" required minlength="6">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Confirm New Password</label>
                            <input type="password" name="confirm_password" class="form-control" required>
                        </div>
                        <button type="submit" name="change_password" class="btn btn-warning">
                            <i class="bi bi-key me-2"></i>Change Password
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
