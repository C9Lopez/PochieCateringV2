<?php
$conn = new mysqli('localhost', 'root', '', 'filipino_catering');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Recreate settings table trick
$conn->query("DROP TABLE IF EXISTS `settings` CASCADE");
$conn->query("CREATE TABLE `settings` (id INT) ENGINE=MyISAM");
$conn->query("DROP TABLE `settings` CASCADE");
$sql = "CREATE TABLE `settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";

if ($conn->query($sql) === TRUE) {
    echo "Table 'settings' created or already exists\n";
} else {
    echo "Error creating table: " . $conn->error . "\n";
}

// Insert initial data
$inserts = [
    [1, 'site_name', 'Pochie Catering Services'],
    [2, 'site_email', 'info@filipinocatering.com'],
    [3, 'site_phone', '09123456789'],
    [4, 'site_address', 'Manila, Philippines'],
    [5, 'minimum_advance_booking', '3'],
    [6, 'down_payment_percentage', '50'],
    [11, 'gcash_number', '09223334456'],
    [12, 'bank_name', ''],
    [13, 'bank_account_name', ''],
    [14, 'bank_account_number', ''],
    [31, 'terms_of_use', 'Maligayang pagdating sa aming serbisyo ng catering...'],
    [32, 'privacy_policy', 'Patakaran sa Pagkapribado ng Data...']
];

foreach ($inserts as $data) {
    $id = $data[0];
    $key = $data[1];
    $val = $conn->real_escape_string($data[2]);
    $sql = "INSERT IGNORE INTO `settings` (`id`, `setting_key`, `setting_value`) VALUES ($id, '$key', '$val')";
    $conn->query($sql);
}

echo "Initial data inserted/checked\n";

$conn->close();
?>
