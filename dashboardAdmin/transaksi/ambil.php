<?php
require '../../config/config.php';
session_start();

if (!isset($_SESSION["login"]) || !isset($_SESSION["role"])) {
    header("Location: ../login.php");
    exit;
}

$role = strtolower($_SESSION["role"]);

if ($role != "admin") {
    header("Location: ../login.php");
    exit;
}

// 🔥 USER LOGIN (ADMIN ATAU PETUGAS)
$id_user = $_SESSION['id_user'];

// CEK ID TRANSAKSI
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
    echo "<script>
        alert('Transaksi belum bisa diambil');
        window.location.href='detailTransaksi.php?id=$id_transaksi';
    </script>";
    exit;
}

// AMBIL DETAIL ALAT
$detail = mysqli_query($connect, "
    SELECT id_alat_olahraga, jumlah 
    FROM detail_transaksi 
    WHERE id_transaksi = '$id_transaksi'
");

// CEK STOK
while ($row = mysqli_fetch_assoc($detail)) {
    $cekStok = mysqli_query($connect, "
        SELECT stok 
        FROM alat_olahraga 
        WHERE id_alat_olahraga = '".$row['id_alat_olahraga']."'
    ");

    $stokData = mysqli_fetch_assoc($cekStok);

    if ($stokData['stok'] < $row['jumlah']) {
        echo "<script>
            alert('Stok tidak mencukupi!');
            window.location.href='detailTransaksi.php?id=$id_transaksi';
        </script>";
        exit;
    }
}

// RESET QUERY DETAIL
$detail = mysqli_query($connect, "
    SELECT id_alat_olahraga, jumlah 
    FROM detail_transaksi 
    WHERE id_transaksi = '$id_transaksi'
");

// KURANGI STOK
while ($row = mysqli_fetch_assoc($detail)) {
    mysqli_query($connect, "
        UPDATE alat_olahraga 
        SET stok = stok - ".$row['jumlah']." 
        WHERE id_alat_olahraga = '".$row['id_alat_olahraga']."'
    ");
}

// 🔥 UPDATE TRANSAKSI
// OPSI ADMIN: bisa assign petugas ATAU biarkan kosong

$update = mysqli_query($connect, "
    UPDATE transaksi 
    SET status = 'dipinjam',
        id_petugas = '$id_user'
    WHERE id_transaksi = '$id_transaksi'
");

// HASIL
if ($update) {
    echo "<script>
        alert('Alat berhasil diambil');
        window.location.href='detailTransaksi.php?id=$id_transaksi';
    </script>";
} else {
    echo "<script>
        alert('Gagal memproses');
        window.location.href='detailTransaksi.php?id=$id_transaksi';
    </script>";
}
?>