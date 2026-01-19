<?php
require_once __DIR__ . '/smtp_config.php';
require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/Exception.php';
require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/PHPMailer.php';
require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

function generateVerificationCode() {
    return str_pad(random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
}

function createMailer() {
    $mail = new PHPMailer(true);
    
    $mail->isSMTP();
    $mail->Host = SMTP_HOST;
    $mail->SMTPAuth = SMTP_AUTH;
    $mail->Username = SMTP_USERNAME;
    $mail->Password = SMTP_PASSWORD;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port = SMTP_PORT;
    $mail->CharSet = 'UTF-8';
    
    $mail->SMTPOptions = array(
        'ssl' => array(
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
        )
    );
    
    $mail->setFrom(SMTP_USERNAME, SMTP_FROM_NAME);
    $mail->addReplyTo(SMTP_USERNAME, SMTP_FROM_NAME);
    
    return $mail;
}

function sendVerificationEmail($email, $code, $name) {
    try {
        $mail = createMailer();
        
        $mail->addAddress($email, $name);
        $mail->isHTML(false);
        $mail->Subject = 'Your Verification Code: ' . $code;
        $mail->Body = 'Hello ' . $name . ',' . "\r\n\r\n" . 'Your verification code is: ' . $code . "\r\n\r\n" . 'This code will expire in 10 minutes.' . "\r\n\r\n" . '- Pochie Catering Services';
        
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
        
        $mail->addAddress($email, $name);
        $mail->isHTML(false);
        $mail->Subject = 'Password Reset Code: ' . $code;
        $mail->Body = 'Hello ' . $name . ',' . "\r\n\r\n" . 'Your password reset code is: ' . $code . "\r\n\r\n" . 'This code will expire in 10 minutes.' . "\r\n\r\n" . '- Pochie Catering Services';
        
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log(date('Y-m-d H:i:s') . ' SMTP Error: ' . $mail->ErrorInfo);
        return false;
    }
}
?>