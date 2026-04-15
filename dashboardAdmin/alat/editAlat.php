<?php
require '../../config/config.php';
session_start();

if (!isset($_SESSION["login"]) || ($_SESSION["role"] != "admin utama" && $_SESSION["role"] != "admin")) {
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

$kategori_res = mysqli_query($connect, "SELECT * FROM kategori_alat_olahraga ORDER BY kategori ASC");

if(isset($_POST['update'])){
    $nama = mysqli_real_escape_string($connect, $_POST['nama']);
    $kategori = mysqli_real_escape_string($connect, $_POST['kategori']);
    $stok = (int)$_POST['stok'];
    $deskripsi = mysqli_real_escape_string($connect, $_POST['deskripsi']);
    $foto = $alat['foto_alat_olahraga'];

    if($_FILES['foto']['error'] === 0){
        $namaFile = $_FILES['foto']['name'];
        $tmpName = $_FILES['foto']['tmp_name'];
        $ext = pathinfo($namaFile, PATHINFO_EXTENSION);
        $newName = uniqid() . "." . $ext;
        if(move_uploaded_file($tmpName, '../../asset/' . $newName)){
            if($foto != 'default.png' && file_exists('../../asset/'.$foto)) unlink('../../asset/'.$foto);
            $foto = $newName;
        }
    }

    $query = "UPDATE alat_olahraga SET 
              nama_alat_olahraga = '$nama', 
              kategori = '$kategori', 
              deskripsi = '$deskripsi', 
              stok = $stok, 
              foto_alat_olahraga = '$foto'
              WHERE id_alat_olahraga = '$id_alat'";
    
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
    <title>Edit Alat - PopFit Admin</title>
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
                            dark: '#2A4736', light: '#3E614C', accent: '#F5C460', accentHover: '#E3B24F',
                            bg: '#F4F4F5', surface: '#FFFFFF', border: '#E4E4E7', text: '#1F2937', textMuted: '#6B7280'
                        }
                    },
                    borderRadius: { 'sm': '2px', DEFAULT: '4px' }
                }
            }
        }
    </script>
    <style>
        .nav-active { background-color: #3E614C; border-left: 4px solid #F5C460; }
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-thumb { background: #E4E4E7; }
        * { box-shadow: none !important; }
    </style>
</head>
<body class="bg-popfit-bg text-popfit-text font-sans h-screen overflow-hidden flex text-[13px]">

    <aside class="hidden md:flex flex-col w-64 bg-popfit-dark text-white border-r border-popfit-dark h-full flex-shrink-0">
        <div class="h-16 flex items-center px-6 border-b border-popfit-light bg-popfit-dark">
            <i class="ph-fill ph-paw-print text-popfit-accent text-2xl mr-3"></i>
            <span class="text-xl font-black tracking-wide uppercase">PopFit</span>
        </div>
        <nav class="flex-1 overflow-y-auto py-4">
            <ul class="space-y-1">
                <li><a href="../dashboardAdmin.php" class="flex items-center px-6 py-3 text-gray-200 hover:bg-popfit-light transition-colors border-l-4 border-transparent"><i class="ph ph-squares-four text-xl w-6"></i><span class="ml-3 font-bold">Dashboard</span></a></li>
                <li class="px-6 py-2 mt-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Manajemen</li>
                <li><a href="daftarAlat.php" class="nav-active flex items-center px-6 py-3 text-gray-200 hover:bg-popfit-light transition-colors border-l-4 border-transparent"><i class="ph ph-basketball text-xl w-6"></i><span class="ml-3 font-bold">Katalog Alat</span></a></li>
            </ul>
        </nav>
    </aside>

    <div class="flex-1 flex flex-col h-screen overflow-hidden">
        <header class="h-16 bg-popfit-surface border-b border-popfit-border flex items-center justify-between px-6 flex-shrink-0">
            <div class="flex items-center">
                <a href="daftarAlat.php" class="mr-4 text-popfit-dark hover:scale-110 transition-transform"><i class="ph ph-arrow-left text-2xl"></i></a>
                <h2 class="text-lg font-black text-popfit-dark uppercase tracking-tight">Perbarui Informasi Alat</h2>
            </div>
        </header>

        <main class="flex-1 overflow-y-auto p-6 flex items-center justify-center">
            <div class="w-full max-w-2xl bg-white border border-popfit-border rounded-sm p-8">
                <?php if(isset($error)): ?>
                <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 text-[10px] font-black uppercase text-red-700 tracking-widest"><?= $error ?></div>
                <?php endif; ?>

                <form method="POST" enctype="multipart/form-data" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-popfit-textMuted uppercase tracking-widest">Kode Alat (TETAP)</label>
                            <input type="text" value="<?= $alat['id_alat_olahraga'] ?>" disabled class="w-full bg-gray-50 border border-popfit-border rounded-sm px-4 py-3 text-xs font-bold text-gray-400 outline-none uppercase cursor-not-allowed">
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-popfit-textMuted uppercase tracking-widest">Nama Alat</label>
                            <input type="text" name="nama" required value="<?= htmlspecialchars($alat['nama_alat_olahraga']) ?>" class="w-full bg-popfit-bg border border-popfit-border rounded-sm px-4 py-3 text-xs font-bold text-popfit-dark focus:border-popfit-dark outline-none transition-all uppercase">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-popfit-textMuted uppercase tracking-widest">Kategori</label>
                            <select name="kategori" required class="w-full bg-popfit-bg border border-popfit-border rounded-sm px-4 py-3 text-xs font-bold text-popfit-dark focus:border-popfit-dark outline-none transition-all">
                                <?php while($k = mysqli_fetch_assoc($kategori_res)): ?>
                                <option value="<?= $k['kategori'] ?>" <?= ($alat['kategori'] == $k['kategori'] ? 'selected' : '') ?>><?= strtoupper($k['kategori']) ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-popfit-textMuted uppercase tracking-widest">Stok Sekarang</label>
                            <input type="number" name="stok" required value="<?= $alat['stok'] ?>" class="w-full bg-popfit-bg border border-popfit-border rounded-sm px-4 py-3 text-xs font-bold text-popfit-dark focus:border-popfit-dark outline-none transition-all">
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-popfit-textMuted uppercase tracking-widest">Deskripsi Singkat</label>
                        <textarea name="deskripsi" rows="3" class="w-full bg-popfit-bg border border-popfit-border rounded-sm px-4 py-3 text-xs font-bold text-popfit-dark focus:border-popfit-dark outline-none transition-all uppercase"><?= htmlspecialchars($alat['deskripsi']) ?></textarea>
                    </div>

                    <div class="flex items-center space-x-6">
                        <div class="w-24 h-24 bg-gray-50 border border-popfit-border rounded-sm overflow-hidden flex-shrink-0 flex items-center justify-center">
                            <img src="../../asset/<?= $alat['foto_alat_olahraga'] ?>" class="w-full h-full object-cover">
                        </div>
                        <div class="flex-1 space-y-2">
                            <label class="text-[10px] font-black text-popfit-textMuted uppercase tracking-widest">Ganti Foto Alat</label>
                            <input type="file" name="foto" class="w-full bg-gray-50 border border-popfit-border rounded-sm px-4 py-2 text-xs font-bold text-popfit-dark focus:border-popfit-dark outline-none transition-all">
                        </div>
                    </div>

                    <div class="pt-6 flex space-x-4">
                        <button type="submit" name="update" class="flex-1 bg-popfit-dark text-white py-4 text-[10px] font-black uppercase tracking-widest rounded-sm hover:bg-popfit-light transition-all">Simpan Perubahan</button>
                        <a href="daftarAlat.php" class="flex-1 bg-white border border-popfit-border text-popfit-dark py-4 text-center text-[10px] font-black uppercase tracking-widest rounded-sm hover:bg-popfit-bg transition-all">Batal</a>
                    </div>
                </form>
            </div>
        </main>
    </div>
</body>
</html>