<?php
require '../../config/config.php';
session_start();

// CEK LOGIN PETUGAS
if (!isset($_SESSION["login"]) || !isset($_SESSION["role"])) {
    header("Location: ../login.php");
    exit;
}

$role = strtolower($_SESSION["role"]);

if ($role != "petugas" && $role != "admin") {
    header("Location: ../login.php");
    exit;
}

// AMBIL ID PETUGAS
if (!isset($_SESSION['id_petugas'])) {
    die("Session petugas tidak ditemukan");
}
$id_petugas = $_SESSION['id_petugas'];

// CEK ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: transaksi.php");
    exit;
}

$id_transaksi = mysqli_real_escape_string($connect, $_GET['id']);

// CEK DATA TRANSAKSI
$query = mysqli_query($connect, "
    SELECT status 
    FROM transaksi 
    WHERE id_transaksi = '$id_transaksi'
");

if (mysqli_num_rows($query) == 0) {
    echo "<script>alert('Transaksi tidak ditemukan'); window.location.href='transaksi.php';</script>";
    exit;
}

$data = mysqli_fetch_assoc($query);

// VALIDASI STATUS
if ($data['status'] != 'disetujui') {
    echo "<script>alert('Transaksi tidak bisa dibatalkan'); window.location.href='detailTransaksi.php?id=$id_transaksi';</script>";
    exit;
}

// UPDATE STATUS + PETUGAS
$update = mysqli_query($connect, "
    UPDATE transaksi 
    SET status = 'dibatalkan',
        id_petugas = '$id_petugas'
    WHERE id_transaksi = '$id_transaksi'
");

// HASIL
if ($update) {
    echo "<script>
      