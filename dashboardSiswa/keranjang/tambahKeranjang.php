<?php
require '../../config/config.php';
session_start();

// CEK LOGIN
if (!isset($_SESSION["login"]) || $_SESSION["role"] != "siswa") {
    echo "Anda harus login terlebih dahulu";
    exit;
}

// AMBIL DATA USER (PAKAI users, BUKAN siswa!)
$username = $_SESSION['username'];

$queryUser = mysqli_query($connect, "
    SELECT id_user FROM users 
    WHERE username = '$username' AND role = 'siswa'
");

$dataUser = mysqli_fetch_assoc($queryUser);

if (!$dataUser) {
    echo "User tidak ditemukan";
    exit;
}

$id_user = $dataUser['id_user'];

// VALIDASI INPUT
if (!isset($_POST['id']) || !isset($_POST['jumlah'])) {
    echo "Data tidak lengkap";
    exit;
}

$id = mysqli_real_escape_string($connect, $_POST['id']);
$jumlah = (int) $_POST['jumlah'];

if ($jumlah < 1) {
    echo "Jumlah minimal 1";
    exit;
}

// CEK STOK
$cekAlat = mysqli_query($connect, "
    SELECT stok FROM alat_olahraga 
    WHERE id_alat_olahraga = '$id'
");

$dataAlat = mysqli_fetch_assoc($cekAlat);

if (!$dataAlat) {
    echo "Alat tidak ditemukan";
    exit;
}

$stok = (int) $dataAlat['stok'];

if ($jumlah > $stok) {
    echo "Jumlah melebihi stok";
    exit;
}

// CEK SUDAH ADA DI KERANJANG (PAKAI id_user)
$cek = mysqli_query($connect, "
    SELECT jumlah FROM keranjang 
    WHERE id_user = '$id_user' 
    AND id_alat_olahraga = '$id'
");

if (mysqli_num_rows($cek) > 0) {
    $dataKeranjang = mysqli_fetch_assoc($cek);
    $jumlahBaru = $dataKeranjang['jumlah'] + $jumlah;

    // BATASI AGAR TIDAK MELEBIHI STOK
    if ($jumlahBaru > $stok) {
        $jumlahBaru = $stok;
    }

    mysqli_query($connect, "
        UPDATE keranjang 
        SET jumlah = '$jumlahBaru' 
        WHERE id_user = '$id_user' 
        AND id_alat_olahraga = '$id'
    ");

    echo "Jumlah berhasil diperbarui di keranjang";
} else {
    // INSERT BARU (PAKAI id_user)
    mysqli_query($connect, "
        INSERT INTO keranjang (id_user, id_alat_olahraga, jumlah)
        VALUES ('$id_user', '$id', '$jumlah')
    ");

    echo "Alat berhasil ditambahkan ke keranjang";
}
?>