<?php
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
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'pochiecatering@gmail.com';
    $mail->Password = 'wagyoueuurkzcxzp';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;
    $mail->CharSet = 'UTF-8';
    
    $mail->setFrom('pochiecatering@gmail.com', 'Pochie Catering Services');
    $mail->addReplyTo('pochiecatering@gmail.com', 'Pochie Catering Services');
    
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