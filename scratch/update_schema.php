<?php
include 'config/config.php';
$queries = [
    "ALTER TABLE transaksi MODIFY COLUMN pembayaran ENUM('belum bayar', 'pending', 'lunas', 'ditolak') DEFAULT 'belum bayar'"
];

foreach ($queries as $query) {
    if (mysqli_query($connect, $query)) {
        echo "Success: $query\n";
    } else {
        echo "Error: " . mysqli_error($connect) . "\n";
    }
}
?>
