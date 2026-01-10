<?php
require_once 'config/functions.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Access Denied - Pochie Catering Services</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Poppins', sans-serif;
            min-height: 100vh;
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .error-container { text-align: center; color: white; }
        .error-icon { font-size: 100px; margin-bottom: 20px; opacity: 0.9; }
        .error-code { font-size: 80px; font-weight: 700; margin-bottom: 10px; }
        .error-title { font-size: 28px; font-weight: 600; margin-bottom: 15px; }
        .error-message { font-size: 16px; opacity: 0.9; margin-bottom: 30px; max-width: 400px; }
        .btn-back {
            display: inline-block;
            padding: 14px 30px;
            background: white;
            color: #dc2626;
            text-decoration: none;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-back:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
            color: #dc2626;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-icon"><i class="bi bi-shield-exclamation"></i></div>
        <div class="error-code">403</div>
        <div class="error-title">Access Denied</div>
        <p class="error-message">Sorry, you don't have permission to access this page. Please contact an administrator if you believe this is an error.</p>
        <a href="<?= url('index.php') ?>" class="btn-back">
            <i class="bi bi-house me-2"></i>Back to Homepage
        </a>
    </div>
</body>
</html>
