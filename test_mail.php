<?php
require_once 'config/mail.php';

echo "Testing SMTP connection...\n";
echo "Host: " . SMTP_HOST . "\n";
echo "Port: " . SMTP_PORT . "\n";
echo "Username: " . SMTP_USERNAME . "\n";

try {
    $mail = createMailer();
    $mail->addAddress('pochiecatering@pochiecatering.store', 'Test');
    $mail->isHTML(false);
    $mail->Subject = 'Test Email';
    $mail->Body = 'This is a test email from Pochie Catering.';
    
    $mail->send();
    echo "SUCCESS: Email sent!\n";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
