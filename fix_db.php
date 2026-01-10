<?php
require_once 'config/database.php';

// Check if reviews table has created_at
$result = $conn->query("SHOW COLUMNS FROM reviews LIKE 'created_at'");
if ($result->num_rows == 0) {
    $conn->query("ALTER TABLE reviews ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP");
    echo "Added created_at column to reviews table.\n";
} else {
    echo "created_at column already exists.\n";
}

// Check if reviews table exists (just in case)
$result = $conn->query("SHOW TABLES LIKE 'reviews'");
if ($result->num_rows == 0) {
    $conn->query("CREATE TABLE reviews (
        id INT AUTO_INCREMENT PRIMARY KEY,
        booking_id INT NOT NULL,
        customer_id INT NOT NULL,
        rating INT NOT NULL,
        comment TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    echo "Created reviews table.\n";
} else {
    echo "reviews table already exists.\n";
}
?>