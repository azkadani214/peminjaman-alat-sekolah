<?php
require 'koneksi.php';

$tables = mysqli_query($connect, "SHOW TABLES");
while ($row = mysqli_fetch_array($tables)) {
    $table = $row[0];
    echo "Table: $table\n";
    $columns = mysqli_query($connect, "DESCRIBE $table");
    while ($col = mysqli_fetch_assoc($columns)) {
        echo "  - {$col['Field']} ({$col['Type']})\n";
    }
    echo "\n";
}
?>
