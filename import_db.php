<?php
$host = 'localhost';
$user = 'root';
$pass = '';
$db = 'filipino_catering';

$conn = new mysqli($host, $user, $pass);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$conn->query("CREATE DATABASE IF NOT EXISTS $db");
$conn->select_db($db);

$sql = file_get_contents('database/filipino_catering.sql');

// Split SQL into individual statements
$statements = explode(";\n", $sql);

foreach ($statements as $statement) {
    $statement = trim($statement);
    if (!empty($statement)) {
        if (!$conn->query($statement)) {
            echo "Error: " . $conn->error . "\nStatement: " . substr($statement, 0, 50) . "...\n";
        }
    }
}

echo "Database import completed.\n";
$conn->close();
?>
