<?php
$conn = new mysqli('localhost', 'root', '', 'filipino_catering');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sqlFile = 'database/filipino_catering_myisam.sql';
if (!file_exists($sqlFile)) {
    die("SQL file not found");
}

$sql = file_get_contents($sqlFile);

// Disable foreign key checks
$conn->query("SET FOREIGN_KEY_CHECKS = 0");

echo "Starting multi_query restoration...\n";
if ($conn->multi_query($sql)) {
    do {
        // Store first result set
        if ($result = $conn->store_result()) {
            $result->free();
        }
        // Prepare next result set
        if (!$conn->more_results()) {
            break;
        }
    } while ($conn->next_result());
    
    if ($conn->errno) {
        echo "Multi-query error: " . $conn->error . "\n";
    } else {
        echo "Restoration complete!\n";
    }
} else {
    echo "Initial multi_query failed: " . $conn->error . "\n";
}

$conn->query("SET FOREIGN_KEY_CHECKS = 1");
$conn->close();
?>
