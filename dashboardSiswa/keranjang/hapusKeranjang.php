<?php
require '../../config/config.php';
session_start();

// CEK LOGIN
if (!isset($_SESSION["login"]) || $_SESSION["role"] != "siswa") {
    header("Location: ../../login.php");
    exit;
}

// AMBIL ID USER
$username = $_SESSION['username'];
$user = mysqli_fetch_assoc(mysqli_query($connect, "
    SELECT id_user FROM users 
    WHERE username='$username' AND role='siswa'
"));

if(!$user){
    session_destroy();
    header("Location: ../../login.php");
    exit;
}

$id_user = $user['id_user'];

// VALIDASI ID KERANJANG
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: keranjang.php");
    exit;
}

$id = (int) $_GET['id'];

// HAPUS DATA (AMAN: hanya milik user sendiri)
mysqli_query($connect, "
    DELETE FROM keranjang 
    WHERE id_keranjang='$id' 
    AND id_user='$id_user'
");

header("Location: keranjang.php");
exit;