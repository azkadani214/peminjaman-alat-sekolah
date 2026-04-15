<?php
require '../../config/config.php';
session_start();

if (!isset($_SESSION["login"]) || $_SESSION["role"] != "siswa") {
    header("Location: ../../login.php");
    exit;
}

$id_user = $_SESSION["id_user"];
$siswaName = htmlspecialchars($_SESSION['nama'] ?? 'Siswa');

/* HANDLE ACTION */
if(isset($_POST['checkout'])){
    $waktu_pinjam = $_POST['waktu_pinjam'];
    $batas_kembali = $_POST['batas_kembali'];
    
    $result = checkoutKeranjang($id_user, $waktu_pinjam, $batas_kembali);
    if(isset($result['success'])){
        echo "<script>alert('Berhasil checkout! Silakan tunggu konfirmasi.'); window.location='../transaksi/transaksi.php';</script>";
        exit;
    } else {
        $error = $result['error'];
    }
}

$keranjang = queryReadData("SELECT k.*, a.nama_alat_olahraga, a.foto_alat_olahraga, a.stok 
                            FROM keranjang k 
                            JOIN alat_olahraga a ON k.id_alat_olahraga = a.id_alat_olahraga 
                            WHERE k.id_user = $id_user");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Keranjang Saya - PopFit</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; background: #AFC2B2; margin: 0; }
        .navbar { background: #2F4A39; padding: 12px 30px; position: fixed; width: 100%; top: 0; z-index: 1000; }
        .content { padding: 100px 30px 40px; max-width: 1000px; margin: auto; }
        .card-keranjang { background: white; border-radius: 20px; padding: 25px; box-shadow: 0 8px 25px rgba(0,0,0,0.1); }
        .item-row { border-bottom: 1px solid #eee; padding: 15px 0; display: flex; align-items: center; gap: 20px; }
        .item-img { width: 80px; height: 80px; object-fit: contain; background: #f9f9f9; border-radius: 12px; }
        .btn-qty { width: 30px; height: 30px; border-radius: 50%; border: none; background: #2F4A39; color: white; }
        .checkout-box { background: #DFE8E2; border-radius: 20px; padding: 25px; margin-top: 30px; }
        .btn-main { background: #2F4A39; color: white; border-radius: 12px; font-weight: 600; }
        .btn-main:hover { background: #1f3727; color: white; }
    </style>
</head>
<body>

<nav class="navbar text-white">
    <img src="../../asset/logonav.png" alt="Logo" height="40">
    <div><i class="fa-solid fa-cart-shopping me-2"></i> Keranjang Pinjaman</div>
</nav>

<div class="content">
    <h3 class="fw-bold mb-4" style="color: #2F4A39;">Keranjang Pinjaman Anda</h3>

    <?php if(isset($error)): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <div class="card-keranjang">
        <?php if(!empty($keranjang)): ?>
            <?php foreach($keranjang as $item): ?>
                <div class="item-row">
                    <img src="../../asset/<?= $item['foto_alat_olahraga'] ?: 'default.png' ?>" class="item-img">
                    <div class="flex-grow-1">
                        <h6 class="fw-bold mb-1"><?= htmlspecialchars($item['nama_alat_olahraga']) ?></h6>
                        <small class="text-muted">Stok tersedia: <?= $item['stok'] ?></small>
                    </div>
                    <div class="d-flex align-items-center gap-3">
                        <button class="btn-qty" onclick="location.href='updateKeranjang.php?id=<?= $item['id_keranjang'] ?>&aksi=kurang'">-</button>
                        <span class="fw-bold"><?= $item['jumlah'] ?></span>
                        <button class="btn-qty" onclick="location.href='updateKeranjang.php?id=<?= $item['id_keranjang'] ?>&aksi=tambah'">+</button>
                    </div>
                    <a href="hapusKeranjang.php?id=<?= $item['id_keranjang'] ?>" class="text-danger ms-3"><i class="fa-solid fa-trash"></i></a>
                </div>
            <?php endforeach; ?>

            <div class="checkout-box">
                <form method="POST">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Tanggal Pinjam</label>
                            <input type="datetime-local" name="waktu_pinjam" class="form-control" required value="<?= date('Y-m-d\TH:i') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Batas Kembali</label>
                            <input type="datetime-local" name="batas_kembali" class="form-control" required value="<?= date('Y-m-d\TH:i', strtotime('+3 days')) ?>">
                        </div>
                    </div>
                    <hr>
                    <button type="submit" name="checkout" class="btn btn-main w-100 py-3 mt-2">AJUKAN PEMINJAMAN SEKARANG</button>
                </form>
            </div>
        <?php else: ?>
            <div class="text-center py-5">
                <i class="fa-solid fa-cart-plus fa-4x mb-3" style="opacity: 0.2;"></i>
                <p class="text-muted">Keranjang Anda masih kosong. Ayo cari alat olahraga!</p>
                <a href="../alat/daftarAlat.php" class="btn btn-main mt-2">Cari Alat</a>
            </div>
        <?php endif; ?>
    </div>
    
    <div class="mt-4 text-center">
        <a href="../dashboardSiswa.php" class="text-decoration-none text-dark fw-bold"><i class="fa-solid fa-arrow-left"></i> Kembali ke Dashboard</a>
    </div>
</div>

</body>
</html>
