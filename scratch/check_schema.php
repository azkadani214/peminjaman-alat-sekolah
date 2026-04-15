<?php
include 'config/config.php';
$result = mysqli_query($connect, "DESCRIBE transaksi");
while($row = mysqli_fetch_assoc($result)) {
    echo $row['Field'] . " - " . $row['Type'] . "\n";
}
?>
