<?php
require '../../config/config.php';
session_start();

if (!isset($_SESSION["login"]) || ($_SESSION["role"] != "admin utama" && $_SESSION["role"] != "admin")) {
    header("Location: ../../login.php");
    exit;
}

if(isset($_GET['hapus'])){
    $id = mysqli_real_escape_string($connect, $_GET['hapus']);
    
    // Check usage in logs or transactions
    $check_usage = mysqli_query($connect, "SELECT * FROM transaksi WHERE id_petugas = '$id'");
    if(mysqli_num_rows($check_usage) > 0){
        header("Location: petugas.php?msg=petugas_in_use");
        exit;
    }
    
    mysqli_query($connect, "DELETE FROM users WHERE id_user = '$id' AND role = 'petugas'");
    header("Location: petugas.php?msg=hapus_success");
} else {
    header("Location: petugas.php");
}
exit;
?>