<?php
$file = 'database/filipino_catering.sql';
$content = file_get_contents($file);
$content = str_replace('ENGINE=InnoDB', 'ENGINE=MyISAM', $content);
// Add DROP TABLE IF EXISTS before CREATE TABLE
$content = preg_replace('/CREATE TABLE `(.*?)`/', 'DROP TABLE IF EXISTS `$1`; CREATE TABLE `$1`', $content);
// Also remove TRANSACTION blocks as they might cause issues with mysql.exe if not handled correctly
$content = str_replace('START TRANSACTION;', '', $content);
$content = str_replace('COMMIT;', '', $content);
file_put_contents('database/filipino_catering_myisam.sql', $content);
echo "Created MyISAM version of SQL dump\n";
?>
