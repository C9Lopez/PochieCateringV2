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
    $mail->Encoding = 'quoted-printable';
    $mail->XMailer = ' ';
    
    $siteName = getSiteName();
    $mail->setFrom('pochiecatering@gmail.com', $siteName);
    
    return $mail;
}

function getPlainTextEmail($name, $code, $siteName) {
    return "Hello " . $name . ",

Your verification code is:

" . $code . "

This code is valid for 10 minutes and can only be used once.

Please don't share this code with anyone.

Thanks,
The " . $siteName . " Team";
}

function getPasswordResetEmail($name, $code, $siteName) {
    return "Hello " . $name . ",

Your password reset code is:

" . $code . "

This code is valid for 10 minutes and can only be used once.

Please don't share this code with anyone.

Thanks,
The " . $siteName . " Team";
}

function sendVerificationEmail($email, $code, $name) {
    try {
        $mail = createMailer();
        $siteName = getSiteName();
        
        $mail->addAddress($email, $name);
        $mail->isHTML(false);
        $mail->Subject = 'Verify your email - ' . $siteName;
        $mail->Body = getPlainTextEmail($name, $code, $siteName);
        
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
        $mail->isHTML(false);
        $mail->Subject = 'Reset your password - ' . $siteName;
        $mail->Body = getPasswordResetEmail($name, $code, $siteName);
        
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log(date('Y-m-d H:i:s') . ' SMTP Error: ' . $mail->ErrorInfo);
        return false;
    }
}
?>
