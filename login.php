<?php
require_once 'config/functions.php';

if (isLoggedIn()) {
    $role = getUserRole();
    if ($role === 'admin' || $role === 'super_admin') {
        header('Location: ' . adminUrl('dashboard.php'));
    } elseif ($role === 'staff') {
        header('Location: ' . staffUrl('dashboard.php'));
    } else {
        header('Location: ' . url('index.php'));
    }
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($conn, $_POST['email']);
    $password = $_POST['password'];
    
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? AND is_active = 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($user = $result->fetch_assoc()) {
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
            $_SESSION['user_email'] = $user['email'];
            
            logActivity($conn, $user['id'], 'login', 'User logged in');
            
            if ($user['role'] === 'admin' || $user['role'] === 'super_admin') {
                header('Location: ' . adminUrl('dashboard.php'));
            } elseif ($user['role'] === 'staff') {
                header('Location: ' . staffUrl('dashboard.php'));
            } else {
                header('Location: ' . url('index.php'));
            }
            exit();
        } else {
            $error = 'Invalid email or password';
        }
    } else {
        $error = 'Invalid email or password';
    }
}

$pageTitle = 'Login';
include 'includes/header.php';
?>

<style>
    .auth-page-wrapper {
        min-height: calc(100vh - 120px);
        background: linear-gradient(135deg, rgba(249, 115, 22, 0.9), rgba(234, 88, 12, 0.9)), url('https://images.unsplash.com/photo-1555939594-58d7cb561ad1?w=1920') center/cover;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 40px 20px;
    }
    .login-container { width: 100%; max-width: 420px; }
    .login-card {
        background: white;
        border-radius: 20px;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        overflow: hidden;
    }
    .login-header {
        background: linear-gradient(135deg, var(--secondary) 0%, #0d1b2a 100%);
        padding: 40px 30px;
        text-align: center;
        color: white;
    }
    .login-header .logo { font-size: 40px; margin-bottom: 10px; }
    .login-header h1 { font-family: 'Playfair Display', serif; font-size: 28px; font-weight: 700; margin-bottom: 5px; }
    .login-header p { opacity: 0.9; font-size: 14px; }
    .login-body { padding: 40px 30px; }
    .form-group { margin-bottom: 25px; }
    .form-label { font-weight: 500; color: var(--dark); margin-bottom: 8px; display: block; }
    .btn-login {
        width: 100%;
        padding: 14px;
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
        border: none;
        border-radius: 12px;
        color: white;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    .btn-login:hover { transform: translateY(-2px); box-shadow: 0 10px 20px rgba(249, 115, 22, 0.3); }
    .forgot-link { text-align: right; margin-top: -15px; margin-bottom: 20px; }
    .forgot-link a { color: var(--primary); text-decoration: none; font-size: 13px; font-weight: 500; }
</style>

<div class="auth-page-wrapper">
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <div class="logo">
                    <?php if (!empty($settings['site_logo'])): ?>
                        <img src="<?= url('uploads/settings/' . $settings['site_logo']) ?>" alt="Logo" style="max-height: 60px;">
                    <?php else: ?>
                        🍲
                    <?php endif; ?>
                </div>
                <h1><?= htmlspecialchars($settings['site_name'] ?? 'Pochie Catering Services') ?></h1>
                <p>Sign in to your account</p>
            </div>
            <div class="login-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger"><i class="bi bi-exclamation-circle me-2"></i><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>
                
                <form method="POST">
                    <div class="form-group">
                        <label class="form-label">Email Address</label>
                        <input type="email" name="email" class="form-control" placeholder="Enter your email" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" placeholder="Enter your password" required>
                    </div>
                    <div class="forgot-link">
                        <a href="<?= url('forgot-password.php') ?>"><i class="bi bi-key me-1"></i>Forgot Password?</a>
                    </div>
                    <button type="submit" class="btn-login">
                        <i class="bi bi-box-arrow-in-right me-2"></i>Sign In
                    </button>
                </form>
            </div>
            <div class="login-footer text-center pb-4">
                Don't have an account? <a href="<?= url('register.php') ?>" class="text-primary fw-bold">Register here</a>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>