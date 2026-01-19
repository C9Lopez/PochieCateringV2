<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config/mail.php';

echo "<h2>Gmail SMTP Test</h2>";
echo "<pre>";
echo "Host: smtp.gmail.com\n";
echo "Port: 587\n";
echo "Username: pochiecatering@gmail.com\n";
echo "</pre>";

echo "<h2>Sending Test Email...</h2>";

$testEmail = isset($_GET['email']) ? $_GET['email'] : 'pochiecatering@gmail.com';

try {
    $mail = createMailer();
    $mail->SMTPDebug = 2;
    $mail->Debugoutput = function($str, $level) {
        echo "<pre>Debug: $str</pre>";
    };
    
    $mail->addAddress($testEmail, 'Test User');
    $mail->isHTML(false);
    $mail->Subject = 'Test Email - ' . date('Y-m-d H:i:s');
    $mail->Body = "This is a test email from Pochie Catering.\n\nIf you receive this, the Gmail SMTP is working correctly!";
    
    $mail->send();
    echo "<h3 style='color:green;'>SUCCESS: Email sent to $testEmail!</h3>";
} catch (Exception $e) {
    echo "<h3 style='color:red;'>ERROR: " . $e->getMessage() . "</h3>";
}
?>
