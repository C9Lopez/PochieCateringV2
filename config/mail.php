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
    $mail->Encoding = 'base64';
    $mail->XMailer = ' ';
    
    $siteName = getSiteName();
    $mail->setFrom('pochiecatering@gmail.com', $siteName);
    
    return $mail;
}

function getEmailTemplate($title, $content, $code, $name = '') {
    $siteName = getSiteName();
    
    $displayName = $name ? htmlspecialchars($name) : 'there';
    
    return '<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>' . htmlspecialchars($title) . '</title>
</head>
<body style="margin:0;padding:0;background-color:#f6f8fa;font-family:-apple-system,BlinkMacSystemFont,Segoe UI,Helvetica,Arial,sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#f6f8fa;padding:45px 0;">
<tr>
<td align="center">
<table width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width:544px;">
<tr>
<td align="center" style="padding-bottom:24px;">
<span style="font-size:32px;font-weight:600;color:#24292f;">' . htmlspecialchars($siteName) . '</span>
</td>
</tr>
<tr>
<td align="center" style="padding-bottom:24px;">
<span style="font-size:24px;color:#24292f;">Please verify your identity, ' . $displayName . '</span>
</td>
</tr>
<tr>
<td>
<table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#ffffff;border:1px solid #d0d7de;border-radius:6px;">
<tr>
<td style="padding:24px;">
<p style="margin:0 0 16px 0;font-size:14px;line-height:1.5;color:#24292f;">Here is your verification code:</p>
<p style="margin:0 0 24px 0;font-size:32px;font-weight:600;letter-spacing:6px;color:#24292f;text-align:center;">' . htmlspecialchars($code) . '</p>
<p style="margin:0 0 16px 0;font-size:14px;line-height:1.5;color:#24292f;">This code is valid for <strong>10 minutes</strong> and can only be used once.</p>
<p style="margin:0 0 16px 0;font-size:14px;line-height:1.5;color:#24292f;"><strong>Please don\'t share this code with anyone:</strong> we\'ll never ask for it on the phone or via email.</p>
<p style="margin:16px 0 0 0;font-size:14px;line-height:1.5;color:#24292f;">Thanks,<br>The ' . htmlspecialchars($siteName) . ' Team</p>
</td>
</tr>
</table>
</td>
</tr>
<tr>
<td style="padding-top:24px;">
<p style="margin:0;font-size:12px;line-height:1.5;color:#57606a;">You\'re receiving this email because a verification code was requested for your account. If this wasn\'t you, please ignore this email.</p>
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
        $mail->ContentType = 'text/html';
        
        $htmlBody = getEmailTemplate('Verify your email', '', $code, $name);
        $mail->msgHTML($htmlBody);
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
        $mail->ContentType = 'text/html';
        
        $htmlBody = getEmailTemplate('Reset your password', '', $code, $name);
        $mail->msgHTML($htmlBody);
        $mail->AltBody = 'Hello ' . $name . ', Your password reset code is: ' . $code . '. This code will expire in 10 minutes. - ' . $siteName;
        
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log(date('Y-m-d H:i:s') . ' SMTP Error: ' . $mail->ErrorInfo);
        return false;
    }
}
?>