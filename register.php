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
    
    if ($_POST['action'] === 'register') {
        $email = sanitize($conn, $_POST['email']);
        $password = $_POST['password'];
        $confirmPassword = $_POST['confirm_password'];
        $firstName = sanitize($conn, $_POST['first_name']);
        $lastName = sanitize($conn, $_POST['last_name']);
        $phone = sanitize($conn, $_POST['phone']);
        $ageConfirm = isset($_POST['age_confirm']) ? 1 : 0;
        $privacyConsent = isset($_POST['privacy_consent']) ? 1 : 0;
        
        if (!$ageConfirm) {
            $error = 'You must be 18 years old or above to register';
        } elseif (!$privacyConsent) {
            $error = 'You must agree to the Data Privacy Policy and Terms of Use to continue';
        } elseif ($password !== $confirmPassword) {
            $error = 'Passwords do not match';
        } elseif (strlen($password) < 6) {
            $error = 'Password must be at least 6 characters';
        } else {
            $checkEmail = $conn->prepare("SELECT id FROM users WHERE email = ?");
            $checkEmail->bind_param("s", $email);
            $checkEmail->execute();
            
            if ($checkEmail->get_result()->num_rows > 0) {
                $error = 'Email already registered';
            } else {
                $verificationCode = generateVerificationCode();
                $expiresAt = date('Y-m-d H:i:s', strtotime('+10 minutes'));
                
                $conn->query("DELETE FROM email_verifications WHERE email = '$email'");
                
                $stmt = $conn->prepare("INSERT INTO email_verifications (email, code, expires_at) VALUES (?, ?, ?)");
                $stmt->bind_param("sss", $email, $verificationCode, $expiresAt);
                $stmt->execute();
                
                $_SESSION['pending_registration'] = [
                    'email' => $email,
                    'password' => password_hash($password, PASSWORD_DEFAULT),
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'phone' => $phone
                ];
                
                if (sendVerificationEmail($email, $verificationCode, $firstName)) {
                    header('Location: ' . url('register.php?step=2'));
                    exit();
                } else {
                    header('Location: ' . url('register.php?step=2'));
                    exit();
                }
            }
        }
    }
    
    if ($_POST['action'] === 'verify') {
        $code = trim(sanitize($conn, $_POST['code']));
        $pendingReg = $_SESSION['pending_registration'] ?? null;
        
        if (!$pendingReg) {
            $error = 'Session expired. Please register again.';
            $step = 1;
        } else {
            $email = $pendingReg['email'];
            $currentTime = date('Y-m-d H:i:s');
            
            $stmt = $conn->prepare("SELECT id, code, expires_at, used FROM email_verifications WHERE email = ? ORDER BY id DESC LIMIT 1");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            $latestCode = $result->fetch_assoc();
            
            if ($latestCode && $latestCode['code'] === $code && $latestCode['used'] == 0 && $latestCode['expires_at'] > $currentTime) {
                $updateStmt = $conn->prepare("UPDATE email_verifications SET used = 1 WHERE id = ?");
                $updateStmt->bind_param("i", $latestCode['id']);
                $updateStmt->execute();
                
                $stmt = $conn->prepare("INSERT INTO users (email, password, first_name, last_name, phone, role, email_verified) VALUES (?, ?, ?, ?, ?, 'customer', 1)");
                $stmt->bind_param("sssss", $pendingReg['email'], $pendingReg['password'], $pendingReg['first_name'], $pendingReg['last_name'], $pendingReg['phone']);
                
                if ($stmt->execute()) {
                    unset($_SESSION['pending_registration']);
                    $success = 'Email verified! Your account has been created. You can now login.';
                    $step = 3;
                } else {
                    $error = 'Failed to create account. Please try again.';
                }
            } else {
                $error = 'Invalid or expired verification code';
            }
        }
    }
    
    if ($_POST['action'] === 'resend') {
        $pendingReg = $_SESSION['pending_registration'] ?? null;
        
        if ($pendingReg) {
            $email = $pendingReg['email'];
            $verificationCode = generateVerificationCode();
            $expiresAt = date('Y-m-d H:i:s', strtotime('+10 minutes'));
            
            $stmt = $conn->prepare("UPDATE email_verifications SET used = 1 WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            
            $stmt = $conn->prepare("INSERT INTO email_verifications (email, code, expires_at, used) VALUES (?, ?, ?, 0)");
            $stmt->bind_param("sss", $email, $verificationCode, $expiresAt);
            $stmt->execute();
            
            if (sendVerificationEmail($email, $verificationCode, $pendingReg['first_name'])) {
                $success = 'New verification code sent to your email!';
            } else {
                $error = 'Failed to send email. Please try again.';
            }
        }
    }
}

$pageTitle = 'Register';
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
        .register-container { width: 100%; max-width: 520px; }
        .register-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            overflow: hidden;
        }
        .register-header {
            background: linear-gradient(135deg, var(--secondary) 0%, #0d1b2a 100%);
            padding: 30px;
            text-align: center;
            color: white;
        }
        .register-header .logo { font-size: 36px; margin-bottom: 8px; }
        .register-header h1 { font-family: 'Playfair Display', serif; font-size: 24px; font-weight: 700; margin-bottom: 5px; }
        .register-header p { opacity: 0.9; font-size: 14px; }
        .register-body { padding: 30px; }
        .form-group { margin-bottom: 20px; }
        .form-label { font-weight: 500; color: var(--dark); margin-bottom: 6px; display: block; font-size: 14px; }
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
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(249, 115, 22, 0.1);
        }
        .btn-register {
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
        .btn-register:hover { transform: translateY(-2px); box-shadow: 0 10px 20px rgba(249, 115, 22, 0.3); }
        .step-indicator { display: flex; justify-content: center; gap: 10px; margin-bottom: 20px; }
        .step {
            width: 40px; height: 40px; border-radius: 50%; background: #e2e8f0;
            display: flex; align-items: center; justify-content: center; font-weight: 600; color: #64748b;
        }
        .step.active { background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%); color: white; }
        .step.completed { background: var(--accent); color: white; }
        .verification-input { display: flex; gap: 10px; justify-content: center; }
        .verification-input input {
            width: 50px; height: 60px; text-align: center; font-size: 24px; font-weight: 700;
            border: 2px solid #e2e8f0; border-radius: 10px;
        }
        .verification-input input:focus {
            outline: none;
            border-color: var(--primary);
        }
        .consent-box {
            background: #f8fafc;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 20px;
        }
        .consent-box.age-box {
            background: linear-gradient(135deg, #fef3c7 0%, #fff7ed 100%);
            border-color: #fcd34d;
        }
        .consent-box.privacy-box {
            background: linear-gradient(135deg, #eff6ff 0%, #f0fdf4 100%);
            border-color: #93c5fd;
        }
        .consent-title {
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .consent-title i {
            color: var(--primary);
        }
        .consent-text {
            font-size: 13px;
            color: #64748b;
            line-height: 1.6;
            margin-bottom: 12px;
        }
        .form-check {
            display: flex;
            align-items: flex-start;
            gap: 10px;
        }
        .form-check-input {
            width: 20px;
            height: 20px;
            margin-top: 2px;
            border: 2px solid #cbd5e1;
            border-radius: 4px;
            cursor: pointer;
            flex-shrink: 0;
        }
        .form-check-input:checked {
            background-color: var(--primary);
            border-color: var(--primary);
        }
        .form-check-label {
            font-size: 14px;
            color: var(--dark);
            cursor: pointer;
            font-weight: 500;
        }
        .privacy-link {
            color: var(--primary);
            text-decoration: underline;
        }
    </style>

<div class="auth-page-wrapper">
    <div class="register-container">
        <div class="register-card">
            <div class="register-header">
                <div class="logo">🍲</div>
                <h1><?= htmlspecialchars($settings['site_name'] ?? 'Filipino Catering') ?></h1>
                <p><?= $step == 1 ? 'Create your account' : ($step == 2 ? 'Verify your email' : 'Registration complete') ?></p>
            </div>
            <div class="register-body">
                <div class="step-indicator">
                    <div class="step <?= $step >= 1 ? ($step > 1 ? 'completed' : 'active') : '' ?>">
                        <?= $step > 1 ? '<i class="bi bi-check"></i>' : '1' ?>
                    </div>
                    <div class="step <?= $step >= 2 ? ($step > 2 ? 'completed' : 'active') : '' ?>">
                        <?= $step > 2 ? '<i class="bi bi-check"></i>' : '2' ?>
                    </div>
                    <div class="step <?= $step >= 3 ? 'active' : '' ?>">3</div>
                </div>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger"><i class="bi bi-exclamation-circle me-2"></i><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>
                <?php if ($success): ?>
                    <div class="alert alert-success"><i class="bi bi-check-circle me-2"></i><?= htmlspecialchars($success) ?></div>
                <?php endif; ?>
                
                <?php if ($step == 1): ?>
                <form method="POST">
                    <input type="hidden" name="action" value="register">
                    <div class="row">
                        <div class="col-6">
                            <div class="form-group">
                                <label class="form-label">First Name</label>
                                <input type="text" name="first_name" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label class="form-label">Last Name</label>
                                <input type="text" name="last_name" class="form-control" required>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Email Address</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Phone Number</label>
                        <input type="text" name="phone" class="form-control">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" required minlength="6">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Confirm Password</label>
                        <input type="password" name="confirm_password" class="form-control" required minlength="6">
                    </div>
                    
                    <div class="consent-box age-box">
                        <div class="consent-title">
                            <i class="bi bi-shield-exclamation"></i>
                            Age Verification
                        </div>
                        <div class="consent-text">
                            Ayon sa Republic Act No. 10173 o Data Privacy Act ng Pilipinas, ang pagkolekta ng personal na impormasyon mula sa mga menor de edad ay nangangailangan ng pahintulot ng magulang. Ang website na ito ay para sa mga may edad na 18 taong gulang pataas lamang.
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="age_confirm" id="ageConfirm" required>
                            <label class="form-check-label" for="ageConfirm">
                                Kinukumpirma ko na ako ay 18 taong gulang o higit pa
                            </label>
                        </div>
                    </div>
                    
                    <div class="consent-box privacy-box">
                        <div class="consent-title">
                            <i class="bi bi-file-lock"></i>
                            Data Privacy Consent
                        </div>
                        <div class="consent-text">
                            Alinsunod sa Republic Act No. 10173 (Data Privacy Act of 2012), ang iyong personal na impormasyon ay kokolektahin at gagamitin lamang para sa mga layunin ng aming serbisyo ng catering. Ang iyong data ay mapoprotektahan at hindi ibabahagi sa mga third party nang walang iyong pahintulot.
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="privacy_consent" id="privacyConsent" required>
                            <label class="form-check-label" for="privacyConsent">
                                Nabasa ko at sumasang-ayon ako sa <a href="#" class="privacy-link" data-bs-toggle="modal" data-bs-target="#privacyModal">Data Privacy Policy</a> at <a href="#" class="privacy-link" data-bs-toggle="modal" data-bs-target="#termsModal">Terms of Use</a>
                            </label>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn-register">Send Verification Code</button>
                </form>
                
                <?php elseif ($step == 2): ?>
                <form method="POST">
                    <input type="hidden" name="action" value="verify">
                    <div class="form-group text-center">
                        <p>Enter the code sent to your email</p>
                        <div class="verification-input mb-3">
                            <input type="text" maxlength="1" class="code-input" data-index="0" required>
                            <input type="text" maxlength="1" class="code-input" data-index="1" required>
                            <input type="text" maxlength="1" class="code-input" data-index="2" required>
                            <input type="text" maxlength="1" class="code-input" data-index="3" required>
                            <input type="text" maxlength="1" class="code-input" data-index="4" required>
                            <input type="text" maxlength="1" class="code-input" data-index="5" required>
                        </div>
                        <input type="hidden" name="code" id="fullCode">
                    </div>
                    <button type="submit" class="btn-register">Verify Email</button>
                </form>
                <div class="text-center mt-3">
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="action" value="resend">
                        <button type="submit" class="btn btn-link text-primary">Resend Code</button>
                    </form>
                </div>
                <script>
                    const inputs = document.querySelectorAll('.code-input');
                    const fullCode = document.getElementById('fullCode');
                    inputs.forEach((input, index) => {
                        input.addEventListener('input', (e) => {
                            if (e.target.value.length === 1 && index < 5) inputs[index + 1].focus();
                            fullCode.value = Array.from(inputs).map(i => i.value).join('');
                        });
                        input.addEventListener('keydown', (e) => {
                            if (e.key === 'Backspace' && index > 0 && !e.target.value) inputs[index - 1].focus();
                        });
                    });
                </script>
                
                <?php else: ?>
                <div class="text-center">
                    <i class="bi bi-check-circle-fill text-success" style="font-size: 60px;"></i>
                    <h4 class="mt-3">Verified!</h4>
                    <a href="<?= url('login.php') ?>" class="btn-register d-inline-block text-white text-decoration-none mt-3">Login Now</a>
                </div>
                <?php endif; ?>
            </div>
            <?php if ($step == 1): ?>
            <div class="text-center pb-4">
                Already have an account? <a href="<?= url('login.php') ?>" class="text-primary fw-bold">Sign in</a>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Data Privacy Modal -->
<div class="modal fade" id="privacyModal" tabindex="-1" aria-labelledby="privacyModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="privacyModalLabel">Data Privacy Policy</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="policy-content" style="white-space: pre-wrap;">
                    <?= $settings['privacy_policy'] ?? 'No privacy policy has been set.' ?>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Terms of Use Modal -->
<div class="modal fade" id="termsModal" tabindex="-1" aria-labelledby="termsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="termsModalLabel">Terms of Use</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="policy-content" style="white-space: pre-wrap;">
                    <?= $settings['terms_of_use'] ?? 'No terms of use have been set.' ?>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>