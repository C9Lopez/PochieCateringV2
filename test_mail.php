<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config/mail.php';

echo "<h2>Gmail SMTP Test - New Design</h2>";

$testEmail = isset($_GET['email']) ? $_GET['email'] : 'pochiecatering@gmail.com';

if (isset($_GET['send'])) {
    echo "<h3>Sending Test Email to: " . htmlspecialchars($testEmail) . "</h3>";
    
    try {
        $code = generateVerificationCode();
        $result = sendVerificationEmail($testEmail, $code, 'Test User');
        
        if ($result) {
            echo "<h3 style='color:green;'>SUCCESS: Email sent! Check your inbox.</h3>";
        } else {
            echo "<h3 style='color:red;'>ERROR: Failed to send email.</h3>";
        }
    } catch (Exception $e) {
        echo "<h3 style='color:red;'>ERROR: " . $e->getMessage() . "</h3>";
    }
} else {
    echo "<p>Click the button below to send a test email with the new design.</p>";
    echo "<form method='get'>";
    echo "<input type='hidden' name='send' value='1'>";
    echo "<input type='email' name='email' value='" . htmlspecialchars($testEmail) . "' style='padding: 10px; width: 300px;'>";
    echo "<button type='submit' style='padding: 10px 20px; background: #4285f4; color: white; border: none; cursor: pointer;'>Send Test Email</button>";
    echo "</form>";
    
    echo "<h3>Preview Email Template:</h3>";
    $code = '123456';
    $content = getSiteName() . ' received a request to use <strong>' . htmlspecialchars($testEmail) . '</strong> as a registered email for your account.';
    echo getEmailTemplate('Verify your email', $content, $code);
}
?>
