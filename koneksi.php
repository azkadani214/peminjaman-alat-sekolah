<?php
$host = "127.0.0.1";
$username = "root";
$password = "";
$database = "peminjaman_alat";

// Koneksi ke database
$connect = mysqli_connect($host, $username, $password, $database);

// Cek koneksi
if (!$connect) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

// Setting timezone
date_default_timezone_set('Asia/Jakarta');

// Aktifkan error MySQL (biar gampang debug)
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
?>