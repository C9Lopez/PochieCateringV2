<?php
require_once 'config/functions.php';

if (isLoggedIn()) {
    logActivity($conn, $_SESSION['user_id'], 'logout', 'User logged out');
}

session_destroy();
header('Location: ' . url('login.php'));
exit();
?>
