<?php
require 'config/config.php';

$queries = [
    "ALTER TABLE transaksi ADD COLUMN bukti_kartu VARCHAR(255) DEFAULT NULL AFTER id_user"
];

foreach ($queries as $q) {
    if (mysqli_query($connect, $q)) {
        echo "[SUCCESS] $q\n";
    } else {
        echo "[ERROR] " . mysqli_error($connect) . "\n";
    }
}
