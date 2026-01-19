<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config/mail.php';

echo "<pre>";
echo "=== SMTP Configuration Test ===\n\n";
echo "Host: smtp.hostinger.com\n";
echo "Port: 465\n";
echo "Username: pochiecatering@pochiecatering.store\n\n";

try {
    $mail = createMailer();
    $mail->SMTPDebug = 2;
    $mail->addAddress('pochiecatering@pochiecatering.store', 'Test');
    $mail->isHTML(false);
    $mail->Subject = 'Test Email - ' . date('Y-m-d H:i:s');
    $mail->Body = 'This is a test email from Pochie Catering. Time: ' . date('Y-m-d H:i:s');
    
    $mail->send();
    echo "\n\nSUCCESS: Email sent!\n";
} catch (Exception $e) {
    echo "\n\nERROR: " . $e->getMessage() . "\n";
}
echo "</pre>";
