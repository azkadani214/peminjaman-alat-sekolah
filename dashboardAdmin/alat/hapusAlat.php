<?php
require '../../config/config.php';
session_start();

if (!isset($_SESSION["login"]) || ($_SESSION["role"] != "admin utama" && $_SESSION["role"] != "admin")) {
    header("Location: ../../login.php");
    exit;
}

if(isset($_GET['id'])){
    $id = mysqli_real_escape_string($connect, $_GET['id']);

    // Check if equipment is being used in transactions
    $check_usage = mysqli_query($connect, "SELECT * FROM detail_transaksi WHERE id_alat_olahraga = '$id'");
    if(mysqli_num_rows($check_usage) > 0){
        header("Location: daftarAlat.php?msg=alat_in_use");
        exit;
    }

    $hapus = mysqli_query($connect, "DELETE FROM alat_olahraga WHERE id_alat_olahraga = '$id'");
    if($hapus){
        header("Location: daftarAlat.php?msg=hapus_success");
    } else {
        header("Location: daftarAlat.php?msg=hapus_failed");
    }
} else {
    header("Location: daftarAlat.php");
}
exit;
?>