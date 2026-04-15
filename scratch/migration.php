<?php
require 'koneksi.php';

// Table for activity logs
$q1 = "CREATE TABLE IF NOT EXISTS log_aktivitas (
    id_log INT AUTO_INCREMENT PRIMARY KEY,
    id_user INT NOT NULL,
    aktivitas VARCHAR(255) NOT NULL,
    waktu_aktivitas DATETIME NOT NULL,
    FOREIGN KEY (id_user) REFERENCES users(id_user) ON DELETE CASCADE
)";

// Table for notifications
$q2 = "CREATE TABLE IF NOT EXISTS notifikasi (
    id_notif INT AUTO_INCREMENT PRIMARY KEY,
    id_user INT NOT NULL,
    pesan TEXT NOT NULL,
    link VARCHAR(255) DEFAULT NULL,
    is_read TINYINT(1) DEFAULT 0,
    waktu_notif DATETIME NOT NULL,
    FOREIGN KEY (id_user) REFERENCES users(id_user) ON DELETE CASCADE
)";

// Adjusting users table roles if needed (we'll just use them as strings, but let's make sure the column exists)
// We'll also check if there is an 'admin' user and rename their role to 'admin utama'
$q3 = "UPDATE users SET role = 'admin utama' WHERE role = 'admin'";

if (mysqli_query($connect, $q1)) echo "Table log_aktivitas checked/created.\n";
if (mysqli_query($connect, $q2)) echo "Table notifikasi checked/created.\n";
if (mysqli_query($connect, $q3)) echo "Roles updated to admin utama.\n";

?>
