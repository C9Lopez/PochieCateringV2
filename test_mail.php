<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config/smtp_config.php';
require_once 'config/mail.php';

echo "<h2>SMTP Configuration Test</h2>";
echo "<pre>";
echo "Host: " . SMTP_HOST . "\n";
echo "Port: " . SMTP_PORT . "\n";
echo "Username: " . SMTP_USERNAME . "\n";
echo "Secure: " . SMTP_SECURE . "\n";
echo "</pre>";

echo "<h2>Sending Test Email...</h2>";

$testEmail = isset($_GET['email']) ? $_GET['email'] : 'pochiecatering@pochiecatering.store';

try {
    $mail = createMailer();
    $mail->SMTPDebug = 2;
    $mail->Debugoutput = function($str, $level) {
        echo "<pre>Debug: $str</pre>";
    };
    
    $mail->addAddress($testEmail, 'Test User');
    $mail->isHTML(false);
    $mail->Subject = 'Test Email - ' . date('Y-m-d H:i:s');
    $mail->Body = "This is a test email from Pochie Catering.\n\nIf you receive this, the SMTP is working correctly!";
    
    $mail->send();
    echo "<h3 style='color:green;'>SUCCESS: Email sent to $testEmail!</h3>";
} catch (Exception $e) {
    echo "<h3 style='color:red;'>ERROR: " . $e->getMessage() . "</h3>";
}
?>
