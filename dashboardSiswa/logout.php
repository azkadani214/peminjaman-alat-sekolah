<?php
require '../config/config.php';
session_start();
if(isset($_SESSION['id_user'])) {
    tambahLog($_SESSION['id_user'], "Keluar dari sistem (Siswa)");
}
session_unset();
session_destroy();
header("Location: ../login.php");
exit;
?>