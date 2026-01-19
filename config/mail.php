<?php
require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/Exception.php';
require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/PHPMailer.php';
require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/SMTP.php';
require_once __DIR__ . '/database.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

function generateVerificationCode() {
    return str_pad(random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
}

function getMailSettings() {
    global $conn;
    $settings = [];
    $result = $conn->query("SELECT setting_key, setting_value FROM settings WHERE setting_key IN ('site_name', 'site_logo')");
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
    }
    return $settings;
}

function getSiteLogoUrl() {
    $settings = getMailSettings();
    if (!empty($settings['site_logo'])) {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost:8000';
        $basePath = dirname(dirname($_SERVER['SCRIPT_NAME']));
        $basePath = rtrim(str_replace('\\', '/', $basePath), '/');
        return $protocol . '://' . $host . $basePath . '/uploads/settings/' . $settings['site_logo'];
    }
    return '';
}

function getSiteName() {
    $settings = getMailSettings();
    return $settings['site_name'] ?? 'Pochie Catering Services';
}

function createMailer() {
    $mail = new PHPMailer(true);
    
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'pochiecatering@gmail.com';
    $mail->Password = 'wagyoueuurkzcxzp';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;
    $mail->CharSet = 'UTF-8';
    $mail->XMailer = ' ';
    
    $uniqueId = bin2hex(random_bytes(16));
    $mail->MessageID = '<' . $uniqueId . '@pochiecatering.com>';
    
    $siteName = getSiteName();
    $mail->setFrom('pochiecatering@gmail.com', $siteName);
    
    return $mail;
}

function getEmailTemplate($title, $content, $code) {
    $siteName = getSiteName();
    $logoUrl = getSiteLogoUrl();
    
    $logoHtml = '';
    if (!empty($logoUrl)) {
        $logoHtml = '<img src="' . htmlspecialchars($logoUrl) . '" alt="' . htmlspecialchars($siteName) . '" style="max-height: 60px; margin-bottom: 10px;">';
    } else {
        $logoHtml = '<span style="font-size: 24px; font-weight: bold; color: #4285f4;">' . htmlspecialchars($siteName) . '</span>';
    }
    
    return '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, \'Helvetica Neue\', Arial, sans-serif; background-color: #f5f5f5;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f5f5f5; padding: 40px 20px;">
        <tr>
            <td align="center">
                <table width="100%" cellpadding="0" cellspacing="0" style="max-width: 500px; background-color: #ffffff; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                    <!-- Logo -->
                    <tr>
                        <td align="center" style="padding: 40px 40px 20px 40px;">
                            ' . $logoHtml . '
                        </td>
                    </tr>
                    
                    <!-- Title -->
                    <tr>
                        <td align="center" style="padding: 0 40px 30px 40px;">
                            <h1 style="margin: 0; font-size: 24px; font-weight: 400; color: #202124;">' . htmlspecialchars($title) . '</h1>
                        </td>
                    </tr>
                    
                    <!-- Divider -->
                    <tr>
                        <td style="padding: 0 40px;">
                            <hr style="border: none; border-top: 1px solid #e0e0e0; margin: 0;">
                        </td>
                    </tr>
                    
                    <!-- Content -->
                    <tr>
                        <td style="padding: 30px 40px;">
                            <p style="margin: 0 0 20px 0; font-size: 14px; line-height: 1.6; color: #5f6368;">' . $content . '</p>
                            <p style="margin: 0 0 10px 0; font-size: 14px; color: #5f6368;">Use this code to complete the process:</p>
                        </td>
                    </tr>
                    
                    <!-- Code -->
                    <tr>
                        <td align="center" style="padding: 0 40px 30px 40px;">
                            <div style="font-size: 36px; font-weight: 500; letter-spacing: 8px; color: #202124;">' . htmlspecialchars($code) . '</div>
                        </td>
                    </tr>
                    
                    <!-- Expiry -->
                    <tr>
                        <td style="padding: 0 40px 30px 40px;">
                            <p style="margin: 0; font-size: 14px; color: #5f6368;">This code will expire in 10 minutes.</p>
                        </td>
                    </tr>
                    
                    <!-- Footer Note -->
                    <tr>
                        <td style="padding: 0 40px 40px 40px;">
                            <p style="margin: 0; font-size: 12px; color: #9aa0a6;">If you didn\'t request this, you can safely ignore this email.</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>';
}

function sendVerificationEmail($email, $code, $name) {
    try {
        $mail = createMailer();
        $siteName = getSiteName();
        
        $mail->addAddress($email, $name);
        $mail->isHTML(true);
        $mail->Subject = 'Verify your email';
        
        $content = htmlspecialchars($siteName) . ' received a request to use <strong>' . htmlspecialchars($email) . '</strong> as a registered email for your account.';
        $mail->Body = getEmailTemplate('Verify your email', $content, $code);
        $mail->AltBody = 'Hello ' . $name . ', Your verification code is: ' . $code . '. This code will expire in 10 minutes. - ' . $siteName;
        
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log(date('Y-m-d H:i:s') . ' SMTP Error: ' . $mail->ErrorInfo);
        return false;
    }
}

function sendPasswordResetEmail($email, $code, $name) {
    try {
        $mail = createMailer();
        $siteName = getSiteName();
        
        $mail->addAddress($email, $name);
        $mail->isHTML(true);
        $mail->Subject = 'Reset your password';
        
        $content = htmlspecialchars($siteName) . ' received a password reset request for the account associated with <strong>' . htmlspecialchars($email) . '</strong>.';
        $mail->Body = getEmailTemplate('Reset your password', $content, $code);
        $mail->AltBody = 'Hello ' . $name . ', Your password reset code is: ' . $code . '. This code will expire in 10 minutes. - ' . $siteName;
        
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log(date('Y-m-d H:i:s') . ' SMTP Error: ' . $mail->ErrorInfo);
        return false;
    }
}
?>