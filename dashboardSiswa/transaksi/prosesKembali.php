<?php
require '../../config/config.php';
session_start();

// CEK LOGIN SISWA
if (!isset($_SESSION["login"]) || $_SESSION["role"] != "siswa") {
    header("Location: ../../login.php");
    exit;
}

// VALIDASI INPUT
if (!isset($_POST['id']) || !isset($_POST['waktu_kembali'])) {
    echo "<script>alert('Data tidak lengkap'); window.location.href='../transaksi/transaksi.php';</script>";
    exit;
}

$id_transaksi = mysqli_real_escape_string($connect, $_POST['id']);
$waktu_kembali = mysqli_real_escape_string($connect, $_POST['waktu_kembali']);
$id_user = $_SESSION['id_user'];

// AMBIL DATA TRANSAKSI
$query = mysqli_query($connect, "
    SELECT * FROM transaksi 
    WHERE id_transaksi = '$id_transaksi' 
    AND id_user = '$id_user'
");

if (mysqli_num_rows($query) == 0) {
    echo "<script>alert('Transaksi tidak ditemukan'); window.location.href='../transaksi/transaksi.php';</script>";
    exit;
}

$data = mysqli_fetch_assoc($query);

// VALIDASI STATUS (HARUS DIPINJAM)
if ($data['status'] != 'dipinjam') {
    echo "<script>alert('Tidak bisa mengembalikan karena status bukan dipinjam'); window.location.href='../transaksi/transaksi.php';</script>";
    exit;
}

// CEK SUDAH INPUT ATAU BELUM
if (!empty($data['waktu_kembali']) && $data['waktu_kembali'] != '0000-00-00 00:00:00') {
    echo "<script>alert('Sudah mengajukan pengembalian sebelumnya'); window.location.href='../transaksi/transaksi.php';</script>";
    exit;
}

// =============================================
// 🔥 PERBAIKAN: HANYA UPDATE waktu_kembali
//    TIDAK mengubah status (tetap 'dipinjam')
// =============================================
$update = mysqli_query($connect, "
    UPDATE tr