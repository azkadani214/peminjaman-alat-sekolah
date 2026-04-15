<?php
require '../../config/config.php';
session_start();

if (!isset($_SESSION["login"]) || !isset($_SESSION["role"])) {
    header("Location: ../login.php");
    exit;
}

$role = strtolower($_SESSION["role"]);

if ($role != "petugas" && $role != "admin") {
    header("Location: ../login.php");
    exit;
}

// ✅ FIX: ambil dari id_user
$id_petugas = $_SESSION['id_user'];

// CEK ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: transaksi.php");
    exit;
}

$id_transaksi = mysqli_real_escape_string($connect, $_GET['id']);

// CEK DATA
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

// VALIDASI
if ($data['status'] != 'menunggu') {
    echo "<script>alert('Transaksi tidak bisa disetujui'); window.location.href='detailTransaksi.php?id=$id_transaksi';</script>";
    exit;
}

// UPDATE + PETUGAS
$update = mysqli_query($connect, "
    UPDATE transaksi 
    SET status = 'disetujui',
        id_petugas = '$id_petugas'
    WHERE id_transaksi = '$id_transaksi'
");

// HASIL
if ($update) {
    echo "<script>
        alert('Transaksi berhasil disetujui');
        window.location.href='detailTransaksi.php?id=$id_transaks