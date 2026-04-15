<?php
require 'koneksi.php';

$sql = [
    "ALTER TABLE transaksi MODIFY COLUMN pembayaran ENUM('belum bayar', 'pending', 'lunas') DEFAULT 'belum bayar'",
    "ALTER TABLE transaksi ADD COLUMN bukti_pembayaran VARCHAR(255) DEFAULT NULL",
    "ALTER TABLE transaksi ADD COLUMN metode_pembayaran_denda VARCHAR(50) DEFAULT NULL",
    "ALTER TABLE transaksi ADD COLUMN denda_kerusakan INT DEFAULT 0"
];

foreach ($sql as $query) {
    try {
        if (mysqli_query($connect, $query)) {
            echo "Success: $query\n";
        } else {
            echo "Error: " . mysqli_error($connect) . " on query: $query\n";
        }
    } catch (Exception $e) {
        echo "Cought: " . $e->getMessage() . " on query: $query (This might be because the column already exists)\n";
    }
}
?>
