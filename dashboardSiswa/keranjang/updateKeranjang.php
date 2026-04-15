<?php
require '../../config/config.php';
session_start();

// CEK LOGIN
if (!isset($_SESSION["login"]) || $_SESSION["role"] != "siswa") {
    exit("Akses ditolak");
}

// AMBIL USER
$username = $_SESSION['username'];
$user = mysqli_fetch_assoc(mysqli_query($connect, "
    SELECT id_user FROM users 
    WHERE username='$username' AND role='siswa'
"));

if(!$user){
    exit("User tidak valid");
}

$id_user = $user['id_user'];

// VALIDASI INPUT
if (!isset($_GET['id']) || !isset($_GET['aksi'])) {
    exit("Data tidak lengkap");
}

$id = (int) $_GET['id'];
$aksi = (int) $_GET['aksi'];

// AMBIL DATA KERANJANG + STOK
$data = mysqli_fetch_assoc(mysqli_query($connect, "
    SELECT k.*, a.stok 
    FROM keranjang k
    JOIN alat_olahraga a ON k.id_alat_olahraga = a.id_alat_olahraga
    WHERE k.id_keranjang='$id' 
    AND k.id_user='$id_user'
"));

if(!$data){
    exit("Data tidak ditemukan");
}

// HITUNG JUMLAH BARU
$jumlah = $data['jumlah'] + $aksi;

if($jumlah < 1) $jumlah = 1;
if($jumlah > $data['stok']) $jumlah = $data['stok'];

// UPDATE
mysqli_query($connect, "
    UPDATE keranjang 
    SET jumlah='$jumlah' 
    WHERE id_keranjang='$id' 
    AND id_user='$id_user'
");

// RESPONSE JSON (BIAR JS UPDATE)
echo json_encode([
    "jumlah" => $jumlah
]);