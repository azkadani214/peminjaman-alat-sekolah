<?php
require '../../config/config.php';
session_start();

if (!isset($_SESSION["login"]) || $_SESSION["role"] != "petugas") {
    header("Location: ../../login.php");
    exit;
}

$id_alat = mysqli_real_escape_string($connect, $_GET['id'] ?? '');
$res = mysqli_query($connect, "SELECT * FROM alat_olahraga WHERE id_alat_olahraga = '$id_alat'");
$alat = mysqli_fetch_assoc($res);

if(!$alat){
    header("Location: daftarAlat.php");
    exit;
}

if(isset($_POST['update'])){
    $stok = (int)$_POST['stok'];
    $deskripsi = mysqli_real_escape_string($connect, $_POST['deskripsi']);

    $query = "UPDATE alat_olahraga SET stok = $stok, deskripsi = '$deskripsi' WHERE id_alat_olahraga = '$id_alat'";
    if(mysqli_query($connect, $query)){
        header("Location: daftarAlat.php?msg=edit_success");
        exit;
    } else {
        $error = "Gagal memperbarui data: " . mysqli_error($connect);
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Stok - PopFit Staff</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Plus Jakarta Sans', 'sans-serif'] },
                    colors: {
                        popfit: {
                            dark: '#2A4736', light: '#3E614C', accent: '#F5C460', bg: '#F4F4F5', border: '#E4E4E7'
                        }
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-popfit-bg font-sans min-h-screen flex items-center justify-center p-6">
    <div class="w-full max-w-lg bg-white border border-popfit-border rounded-sm p-8">
        <div class="flex items-center space-x-4 mb-8">
            <a href="daftarAlat.php" class="text-popfit-dark"><i class="ph ph-arrow-left text-2xl"></i></a>
            <h2 class="text-xl font-black text-popfit-dark uppercase tracking-tight">Update Stok Alat</h2>
        </div>

        <?php if(isset($error)): ?>
        <div class="mb-6 bg-red-50 p-4 text-[10px] font-black uppercase text-red-600 border-l-4 border-red-500"><?= $error ?></div>
        <?php endif; ?>

        <div class="flex items-center space-x-6 mb-8 p-4 bg-popfit-bg border border-popfit-border rounded-sm">
            <div class="w-20 h-20 bg-white border border-popfit-border rounded-sm p-2">
                <img src="../../asset/<?= $alat['foto_alat_olahraga'] ?? 'default.png' ?>" class="w-full h-full object-contain">
            </div>
            <div>
                <h3 class="font-black text-popfit-dark uppercase text-sm"><?= htmlspecialchars($alat['nama_alat_olahraga']) ?></h3>
                <p class="text-[10px] font-bold text-popfit-textMuted uppercase mb-1">ID: #<?= $alat['id_alat_olahraga'] ?></p>
                <span class="px-2 py-0.5 bg-popfit-dark text-white text-[9px] font-black uppercase rounded-sm"><?= $alat['kategori'] ?></span>
            </div>
        </div>

        <form method="POST" class="space-y-6">
            <div class="space-y-2">
                <label class="text-[10px] font-black text-popfit-dark uppercase tracking-widest">Jumlah Stok Tersedia</label>
                <input type="number" name="stok" required value="<?= $alat['stok'] ?>" class="w-full bg-popfit-bg border border-popfit-border rounded-sm px-4 py-3 font-black text-sm text-popfit-dark focus:border-popfit-dark outline-none">
            </div>

            <div class="space-y-2">
                <label class="text-[10px] font-black text-popfit-dark uppercase tracking-widest">Deskripsi / Catatan</label>
                <textarea name="deskripsi" rows="3" class="w-full bg-popfit-bg border border-popfit-border rounded-sm px-4 py-3 font-bold text-xs text-popfit-dark focus:border-popfit-dark outline-none uppercase"><?= htmlspecialchars($alat['deskripsi']) ?></textarea>
            </div>

            <div class="pt-4 grid grid-cols-2 gap-4">
                <button type="submit" name="update" class="bg-popfit-dark text-white py-4 text-[10px] font-black uppercase tracking-widest rounded-sm hover:bg-popfit-light transition-all">Update Data</button>
                <a href="daftarAlat.php" class="bg-white border border-popfit-border text-popfit-dark py-4 text-center text-[10px] font-black uppercase tracking-widest rounded-sm hover:bg-popfit-bg transition-all">Batal</a>
            </div>
        </form>
    </div>
</body>
</html>
