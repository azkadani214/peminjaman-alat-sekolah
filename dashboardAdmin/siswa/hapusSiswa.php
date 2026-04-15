<?php
require '../../config/config.php';
session_start();

if (!isset($_SESSION["login"]) || ($_SESSION["role"] != "admin utama" && $_SESSION["role"] != "admin")) {
    header("Location: ../../login.php");
    exit;
}

if(isset($_GET['hapus'])){
    $nis = mysqli_real_escape_string($connect, $_GET['hapus']);
    
    // Check usage in transactions
    $check_user = mysqli_fetch_assoc(mysqli_query($connect, "SELECT id_user FROM users WHERE nis = '$nis' AND role = 'siswa'"));
    if($check_user){
        $uid = $check_user['id_user'];
        $check_usage = mysqli_query($connect, "SELECT * FROM transaksi WHERE id_user = $uid");
        if(mysqli_num_rows($check_usage) > 0){
            header("Location: siswa.php?msg=siswa_in_use");
            exit;
        }
        
        mysqli_query($connect, "DELETE FROM users WHERE id_user = $uid");
        header("Location: siswa.php?msg=hapus_success");
    } else {
        header("Location: siswa.php?msg=not_found");
    }
} else {
    header("Location: siswa.php");
}
exit;
?>