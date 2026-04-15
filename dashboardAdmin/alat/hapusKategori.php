<?php
require '../../config/config.php';
session_start();

if (!isset($_SESSION["login"]) || $_SESSION["role"] != "admin") {
    header("Location: ../../login.php");
    exit;
}

if(isset($_GET['kategori'])){
    $kategori = mysqli_real_escape_string($connect, $_GET['kategori']);

    // CEK APAKAH MASIH DIPAKAI DI ALAT
    $cek = mysqli_query($connect, "SELECT * FROM alat_olahraga WHERE kategori = '$kategori'");

    if(mysqli_num_rows($cek) > 0){
        echo "<script>
            alert('Kategori tidak bisa dihapus karena masih digunakan pada alat olahraga!');
            window.location.href='kategori.php';
        </script>";
        exit;
    }

    // HAPUS
    $hapus = mysqli_query($connect, "DELETE FROM kategori_alat_olahraga WHERE kategori = '$kategori'");

    if($hapus){
        echo "<script>
            alert('Kategori berhasil dihapus');
            window.location.href='kategori.php';
        </script>";
    } else {
        echo "<script>
            alert('Gagal menghapus kategori');
            window.location.href='kategori.php';
        </script>";
    }

} else {
    header("Location: kategori.php");
    exit;
}
?>