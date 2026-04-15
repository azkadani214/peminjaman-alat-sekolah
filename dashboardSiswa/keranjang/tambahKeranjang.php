<?php
require '../../config/config.php';
session_start();

header('Content-Type: application/json');

// CEK LOGIN
if (!isset($_SESSION["login"]) || $_SESSION["role"] != "siswa") {
    echo json_encode(['status' => 'error', 'message' => 'Anda harus login terlebih dahulu']);
    exit;
}

$id_user = $_SESSION['id_user'];

// CEK ATURAN PEMINJAMAN (DENDA/LOAN AKTIF)
$cekAturan = canUserBorrow($id_user);
if (isset($cekAturan['error'])) {
    echo json_encode(['status' => 'error', 'message' => $cekAturan['error']]);
    exit;
}

// VALIDASI INPUT
if (!isset($_POST['id']) || !isset($_POST['jumlah'])) {
    echo json_encode(['status' => 'error', 'message' => 'Data tidak lengkap']);
    exit;
}

$id = mysqli_real_escape_string($connect, $_POST['id']);
$jumlah = (int) $_POST['jumlah'];

if ($jumlah < 1) {
    echo json_encode(['status' => 'error', 'message' => 'Jumlah minimal 1']);
    exit;
}

// CEK STOK
$cekAlat = mysqli_query($connect, "
    SELECT stok FROM alat_olahraga 
    WHERE id_alat_olahraga = '$id'
");

$dataAlat = mysqli_fetch_assoc($cekAlat);

if (!$dataAlat) {
    echo json_encode(['status' => 'error', 'message' => 'Alat tidak ditemukan']);
    exit;
}

$stok = (int) $dataAlat['stok'];

if ($jumlah > $stok) {
    echo json_encode(['status' => 'error', 'message' => 'Jumlah melebihi stok']);
    exit;
}

// CEK SUDAH ADA DI KERANJANG (PAKAI id_user)
$cek = mysqli_query($connect, "
    SELECT id_keranjang, jumlah FROM keranjang 
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

    echo json_encode(['status' => 'success', 'message' => 'Jumlah berhasil diperbarui di keranjang', 'new_count' => $jumlahBaru]);
} else {
    // INSERT BARU (PAKAI id_user)
    mysqli_query($connect, "
        INSERT INTO keranjang (id_user, id_alat_olahraga, jumlah)
        VALUES ('$id_user', '$id', '$jumlah')
    ");

    echo json_encode(['status' => 'success', 'message' => 'Alat berhasil ditambahkan ke keranjang']);
}
?>