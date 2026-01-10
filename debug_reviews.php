<?php
require_once 'config/functions.php';
$reviews = $conn->query("SELECT r.*, u.first_name, u.last_name, u.profile_image 
                        FROM reviews r 
                        JOIN users u ON r.customer_id = u.id 
                        ORDER BY r.created_at DESC LIMIT 6");
if (!$reviews) {
    echo "Query Error: " . $conn->error;
} else {
    echo "Num Rows: " . $reviews->num_rows;
}
?>