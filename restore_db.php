<?php
$conn = new mysqli('localhost', 'root', '', 'filipino_catering');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sqlFile = 'database/filipino_catering.sql';
if (!file_exists($sqlFile)) {
    die("SQL file not found");
}

$sql = file_get_contents($sqlFile);

// Replace InnoDB with MyISAM to bypass tablespace issues
$sql = str_replace('ENGINE=InnoDB', 'ENGINE=MyISAM', $sql);

// Disable foreign key checks just in case
$conn->query("SET FOREIGN_KEY_CHECKS = 0");

// Split SQL into individual queries
// Note: This is a simple split, it might fail on complex SQL but should work for standard dumps
$queries = explode(";\n", $sql);

echo "Starting restoration...\n";
foreach ($queries as $query) {
    $query = trim($query);
    if (!empty($query)) {
        // Extract table name from CREATE TABLE for logging
        if (preg_match('/CREATE TABLE `(.*?)`/', $query, $matches)) {
            $tableName = $matches[1];
            $conn->query("DROP TABLE IF EXISTS `$tableName` CASCADE");
            echo "Restoring table $tableName...\n";
        }
        
        if (!$conn->query($query)) {
            // Some queries might fail (like SET commands), we ignore them if they are not critical
            if (strpos($query, 'CREATE TABLE') !== false || strpos($query, 'INSERT INTO') !== false) {
                echo "Error in query: " . $conn->error . "\n";
            }
        }
    }
}

$conn->query("SET FOREIGN_KEY_CHECKS = 1");
echo "Restoration complete!\n";
$conn->close();
?>
