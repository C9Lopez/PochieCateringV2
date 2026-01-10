<?php
require_once 'config/functions.php';
require_once 'config/mail.php';

if (isLoggedIn()) {
    header('Location: ' . url('index.php'));
    exit();
}

$step = $_GET['step'] ?? 1;
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    
    if ($_POST['action'] === 'request_reset') {
        $email = sanitize($conn, $_POST['email']);
        
        $stmt = $conn->prepare("SELECT id, first_name, email FROM users WHERE email = ? AND is_active = 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($user = $result->fetch_assoc()) {
            $resetCode = generateVerificationCode();
            $expiresAt = date('Y-m-d H:i:s', strtotime('+10 minutes'));
            
            $conn->query("DELETE FROM password_resets WHERE user_id = " . $user['id']);
            
            $stmt = $conn->prepare("INSERT INTO password_resets (user_id, code, expires_at) VALUES (?, ?, ?)");
            $stmt->bind_param("iss", $user['id'], $resetCode, $expiresAt);
            $stmt->execute();
            
            $_SESSION['reset_user_id'] = $user['id'];
            $_SESSION['reset_email'] = $email;
            
            if (sendPasswordResetEmail($email, $resetCode, $user['first_name'])) {
                header('Location: ' . url('forgot-password.php?step=2'));
                exit();
            } else {
                header('Location: ' . url('forgot-password.php?step=2'));
                exit();
            }
        } else {
            $error = 'Email not found or account is inactive';
        }
    }
    
    if ($_POST['action'] === 'verify_code') {
        $code = sanitize($conn, $_POST['code']);
        $userId = $_SESSION['reset_user_id'] ?? null;
        
        if (!$userId) {
            $error = 'Session expired. Please start over.';
            $step = 1;
        } else {
            $stmt = $conn->prepare("SELECT * FROM password_resets WHERE user_id = ? AND code = ? AND used = 0 AND expires_at > NOW() ORDER BY id DESC LIMIT 1");
            $stmt->bind_param("is", $userId, $code);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $_SESSION['reset_verified'] = true;
                header('Location: ' . url('forgot-password.php?step=3'));
                exit();
            } else {
                $error = 'Invalid or expired code';
            }
        }
    }
    
    if ($_POST['action'] === 'reset_password') {
        $password = $_POST['password'];
        $confirmPassword = $_POST['confirm_password'];
        $userId = $_SESSION['reset_user_id'] ?? null;
        $verified = $_SESSION['reset_verified'] ?? false;
        
        if (!$userId || !$verified) {
            $error = 'Session expired. Please start over.';
            $step = 1;
        } elseif ($password !== $confirmPassword) {
            $error = 'Passwords do not match';
        } elseif (strlen($password) < 6) {
            $error = 'Password must be at least 6 characters';
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->bind_param("si", $hashedPassword, $userId);
            
            if ($stmt->execute()) {
                $conn->query("UPDATE password_resets SET used = 1 WHERE user_id = $userId");
                
                unset($_SESSION['reset_user_id']);
                unset($_SESSION['reset_email']);
                unset($_SESSION['reset_verified']);
                
                $success = 'Password reset successful! You can now login with your new password.';
                $step = 4;
            } else {
                $error = 'Failed to reset password. Please try again.';
            }
        }
    }
    
    if ($_POST['action'] === 'resend') {
        $userId = $_SESSION['reset_user_id'] ?? null;
        $email = $_SESSION['reset_email'] ?? null;
        
        if ($userId && $email) {
            $stmt = $conn->prepare("SELECT first_name FROM users WHERE id = ?");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $user = $stmt->get_result()->fetch_assoc();
            
            $resetCode = generateVerificationCode();
            $expiresAt = date('Y-m-d H:i:s', strtotime('+10 minutes'));
            
            $conn->query("DELETE FROM password_resets WHERE user_id = $userId");
            $stmt = $conn->prepare("INSERT INTO password_resets (user_id, code, expires_at) VALUES (?, ?, ?)");
            $stmt->bind_param("iss", $userId, $resetCode, $expiresAt);
            $stmt->execute();
            
            if (sendPasswordResetEmail($email, $resetCode, $user['first_name'])) {
                $success = 'New reset code sent to your email!';
            } else {
                $error = 'Failed to send email. Please check your email settings.';
            }
        }
    }
}

$mailFailed = isset($_GET['mail_failed']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - Pochie Catering Services</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Poppins', sans-serif;
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .forgot-container { width: 100%; max-width: 440px; }
        .forgot-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            overflow: hidden;
        }
        .forgot-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 30px;
            text-align: center;
            color: white;
        }
        .forgot-header h1 { font-size: 24px; font-weight: 700; margin-bottom: 5px; }
        .forgot-header p { opacity: 0.9; font-size: 14px; }
        .forgot-body { padding: 30px; }
        .form-group { margin-bottom: 20px; }
        .form-label { font-weight: 500; color: #333; margin-bottom: 6px; display: block; font-size: 14px; }
        .form-control {
            width: 100%;
            padding: 12px 14px;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            font-size: 14px;
            transition: all 0.3s ease;
        }
        .form-control:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
        }
        .btn-primary-custom {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 12px;
            color: white;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .btn-primary-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }
        .alert { padding: 12px 16px; border-radius: 10px; margin-bottom: 20px; font-size: 14px; }
        .alert-danger { background: #fef2f2; border: 1px solid #fecaca; color: #dc2626; }
        .alert-success { background: #f0fdf4; border: 1px solid #bbf7d0; color: #16a34a; }
        .alert-info { background: #eff6ff; border: 1px solid #bfdbfe; color: #2563eb; }
        .forgot-footer {
            text-align: center;
            padding: 20px 30px;
            background: #f8fafc;
            border-top: 1px solid #e2e8f0;
        }
        .forgot-footer a { color: #667eea; text-decoration: none; font-weight: 500; }
        .forgot-footer a:hover { text-decoration: underline; }
        .back-link { text-align: center; margin-top: 20px; }
        .back-link a { color: white; text-decoration: none; opacity: 0.9; }
        .back-link a:hover { opacity: 1; }
        .step-indicator {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-bottom: 20px;
        }
        .step {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            background: #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 14px;
            color: #64748b;
            transition: all 0.3s ease;
        }
        .step.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .step.completed {
            background: #22c55e;
            color: white;
        }
        .verification-input {
            display: flex;
            gap: 8px;
            justify-content: center;
        }
        .verification-input input {
            width: 45px;
            height: 55px;
            text-align: center;
            font-size: 22px;
            font-weight: 700;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
        }
        .verification-input input:focus {
            outline: none;
            border-color: #667eea;
        }
        .code-display {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 15px;
            border-radius: 10px;
            text-align: center;
            font-size: 26px;
            letter-spacing: 8px;
            font-weight: 700;
            margin: 15px 0;
        }
        .resend-link {
            text-align: center;
            margin-top: 15px;
        }
        .resend-link button {
            background: none;
            border: none;
            color: #667eea;
            cursor: pointer;
            font-weight: 500;
        }
        .resend-link button:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="forgot-container">
        <div class="forgot-card">
            <div class="forgot-header">
                <h1><i class="bi bi-key me-2"></i>Password Recovery</h1>
                <p><?= $step == 1 ? 'Enter your email' : ($step == 2 ? 'Verify your identity' : ($step == 3 ? 'Create new password' : 'All done!')) ?></p>
            </div>
            <div class="forgot-body">
                <div class="step-indicator">
                    <div class="step <?= $step >= 1 ? ($step > 1 ? 'completed' : 'active') : '' ?>">
                        <?= $step > 1 ? '<i class="bi bi-check"></i>' : '1' ?>
                    </div>
                    <div class="step <?= $step >= 2 ? ($step > 2 ? 'completed' : 'active') : '' ?>">
                        <?= $step > 2 ? '<i class="bi bi-check"></i>' : '2' ?>
                    </div>
                    <div class="step <?= $step >= 3 ? ($step > 3 ? 'completed' : 'active') : '' ?>">
                        <?= $step > 3 ? '<i class="bi bi-check"></i>' : '3' ?>
                    </div>
                    <div class="step <?= $step >= 4 ? 'active' : '' ?>">4</div>
                </div>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger"><i class="bi bi-exclamation-circle me-2"></i><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>
                <?php if ($success): ?>
                    <div class="alert alert-success"><i class="bi bi-check-circle me-2"></i><?= htmlspecialchars($success) ?></div>
                <?php endif; ?>
                
                <?php if ($step == 1): ?>
                <!-- Step 1: Enter Email -->
                <div class="text-center mb-4">
                    <i class="bi bi-envelope-at" style="font-size: 50px; color: #667eea;"></i>
                    <p class="text-muted mt-3">Enter your registered email address and we'll send you a verification code to reset your password.</p>
                </div>
                
                <form method="POST">
                    <input type="hidden" name="action" value="request_reset">
                    <div class="form-group">
                        <label class="form-label">Email Address</label>
                        <input type="email" name="email" class="form-control" placeholder="Enter your email" required>
                    </div>
                    <button type="submit" class="btn-primary-custom">
                        <i class="bi bi-send me-2"></i>Send Reset Code
                    </button>
                </form>
                
                <?php elseif ($step == 2): ?>
                <!-- Step 2: Enter Code -->
                <div class="text-center mb-4">
                    <i class="bi bi-shield-lock" style="font-size: 50px; color: #667eea;"></i>
                    <h5 class="mt-3">Check your email</h5>
                    <p class="text-muted">Enter the 6-digit code sent to<br><strong><?= htmlspecialchars($_SESSION['reset_email'] ?? '') ?></strong></p>
                </div>
                
                <form method="POST">
                    <input type="hidden" name="action" value="verify_code">
                    <div class="form-group">
                        <div class="verification-input">
                            <input type="text" maxlength="1" class="code-input" data-index="0" required>
                            <input type="text" maxlength="1" class="code-input" data-index="1" required>
                            <input type="text" maxlength="1" class="code-input" data-index="2" required>
                            <input type="text" maxlength="1" class="code-input" data-index="3" required>
                            <input type="text" maxlength="1" class="code-input" data-index="4" required>
                            <input type="text" maxlength="1" class="code-input" data-index="5" required>
                        </div>
                        <input type="hidden" name="code" id="fullCode">
                    </div>
                    <button type="submit" class="btn-primary-custom">
                        <i class="bi bi-shield-check me-2"></i>Verify Code
                    </button>
                </form>
                
                <div class="resend-link">
                    <span class="text-muted">Didn't receive the code?</span>
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="action" value="resend">
                        <button type="submit">Resend Code</button>
                    </form>
                </div>
                
                <script>
                    const inputs = document.querySelectorAll('.code-input');
                    const fullCode = document.getElementById('fullCode');
                    
                    inputs.forEach((input, index) => {
                        input.addEventListener('input', (e) => {
                            if (e.target.value.length === 1 && index < 5) {
                                inputs[index + 1].focus();
                            }
                            updateFullCode();
                        });
                        
                        input.addEventListener('keydown', (e) => {
                            if (e.key === 'Backspace' && index > 0 && !e.target.value) {
                                inputs[index - 1].focus();
                            }
                        });
                        
                        input.addEventListener('paste', (e) => {
                            e.preventDefault();
                            const paste = (e.clipboardData || window.clipboardData).getData('text');
                            const chars = paste.replace(/\D/g, '').split('').slice(0, 6);
                            chars.forEach((char, i) => {
                                if (inputs[i]) inputs[i].value = char;
                            });
                            updateFullCode();
                            if (chars.length > 0) inputs[Math.min(chars.length, 5)].focus();
                        });
                    });
                    
                    function updateFullCode() {
                        fullCode.value = Array.from(inputs).map(i => i.value).join('');
                    }
                </script>
                
                <?php elseif ($step == 3): ?>
                <!-- Step 3: New Password -->
                <div class="text-center mb-4">
                    <i class="bi bi-lock-fill" style="font-size: 50px; color: #667eea;"></i>
                    <h5 class="mt-3">Create New Password</h5>
                    <p class="text-muted">Enter a new password for your account.</p>
                </div>
                
                <form method="POST">
                    <input type="hidden" name="action" value="reset_password">
                    <div class="form-group">
                        <label class="form-label">New Password</label>
                        <input type="password" name="password" class="form-control" placeholder="At least 6 characters" required minlength="6">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Confirm New Password</label>
                        <input type="password" name="confirm_password" class="form-control" placeholder="Re-enter password" required minlength="6">
                    </div>
                    <button type="submit" class="btn-primary-custom">
                        <i class="bi bi-check-lg me-2"></i>Reset Password
                    </button>
                </form>
                
                <?php else: ?>
                <!-- Step 4: Success -->
                <div class="text-center">
                    <i class="bi bi-check-circle-fill" style="font-size: 80px; color: #22c55e;"></i>
                    <h4 class="mt-3">Password Reset!</h4>
                    <p class="text-muted">Your password has been successfully reset. You can now login with your new password.</p>
                    <a href="<?= url('login.php') ?>" class="btn-primary-custom d-inline-block text-white text-decoration-none mt-3" style="padding: 14px 40px;">
                        <i class="bi bi-box-arrow-in-right me-2"></i>Login Now
                    </a>
                </div>
                <?php endif; ?>
            </div>
            <?php if ($step == 1): ?>
            <div class="forgot-footer">
                Remember your password? <a href="<?= url('login.php') ?>">Back to Login</a>
            </div>
            <?php endif; ?>
        </div>
        <div class="back-link">
            <a href="<?= url('index.php') ?>"><i class="bi bi-arrow-left me-1"></i>Back to Homepage</a>
        </div>
    </div>
</body>
</html>