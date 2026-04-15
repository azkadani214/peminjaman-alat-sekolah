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

$id = $_GET['id'] ?? null;
$aksi = $_GET['aksi'] ?? null;

if(!$id || !$aksi){
    echo "<script>alert('Parameter tidak lengkap'); window.location.href='denda.php';</script>";
    exit;
}

// 🔥 AMBIL DATA PEMBAYARAN SEBELUM UPDATE
$cek = mysqli_fetch_assoc(mysqli_query($connect, "
    SELECT p.*, t.id_transaksi, t.denda, t.id_user
    FROM pembayaran_denda p
    JOIN transaksi t ON p.id_transaksi = t.id_transaksi
    WHERE p.id_pembayaran = '$id'
"));

if(!$cek){
    echo "<script>alert('Data tidak ditemukan'); window.location.href='denda.php';</script>";
    exit;
}

if($aksi == 'terima'){
    // 🔥 UPDATE STATUS PEMBAYARAN MENJADI 'disetujui'
    mysqli_query($connect,"
        UPDATE pembayaran_denda 
        SET status = 'disetujui' 
        WHERE id_pembayaran = '$id'
    ");
    
    // 🔥 UPDATE STATUS TRANSAKSI MENJADI 'dikembalikan' (karena denda sudah lunas)
    mysqli_query($connect,"
        UPDATE transaksi 
        SET status = 'dikembalikan' 
        WHERE id_transaksi = '{$cek['id_transaksi']}'
    ");
    
    echo "<script>
        alert('Pembayaran denda berhasil disetujui! Status transaksi telah selesai.');
        window.location.href='denda.php';
    </script>";
    
} elseif($aksi == 'tolak'){
    // 🔥 UPDATE STATUS PEMBAYARAN MENJADI 'ditolak'
    mysqli_query($connect,"
        UPDATE pembayaran_denda 
        SET status = 'ditolak' 
        WHERE id_pembayaran = '$id'
    ");
    
    // 🔥 HAPUS DATA PEMBAYARAN YANG DITOLAK? ATAU BIARKAN DENGAN STATUS DITOLAK
    // Siswa harus mengajukan ulang pembayaran
    
    echo "<script>
        alert('Pembayaran denda ditolak! Silakan upload ulang bukti pembayaran yang valid.');
        window.location.href='denda.php';
    </script>";
   