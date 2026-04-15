<?php
require '../../config/config.php';
session_start();

if (!isset($_SESSION["login"]) || $_SESSION["role"] != "siswa") {
    header("Location: ../../login.php");
    exit;
}

$id_user = $_SESSION["id_user"];
$siswaName = htmlspecialchars($_SESSION['nama'] ?? 'Siswa');
$siswaUsername = htmlspecialchars($_SESSION['username'] ?? 'username');

if(isset($_POST['checkout'])){
    $waktu_pinjam = $_POST['waktu_pinjam'];
    $batas_kembali = $_POST['batas_kembali'];
    
    // VALIDASI STATUS SISWA
    $cekStatus = canUserBorrow($id_user);
    if(isset($cekStatus['error'])){
        $error = $cekStatus['error'];
    } else {
        // VALIDASI DURASI
        $cekDurasi = validateDuration($waktu_pinjam, $batas_kembali);
        if(isset($cekDurasi['error'])){
            $error = $cekDurasi['error'];
        } else {
            // HANDLE UPLOAD KARTU
            $bukti_kartu = "";
            if($_FILES['bukti_kartu']['error'] === 0){
                $namaFile = $_FILES['bukti_kartu']['name'];
                $tmpName = $_FILES['bukti_kartu']['tmp_name'];
                $ext = pathinfo($namaFile, PATHINFO_EXTENSION);
                $newName = "kartu_" . rand(1000, 9999) . "_" . rand(1000, 9999) . "." . $ext;
                move_uploaded_file($tmpName, "../../uploads/" . $newName);
                $bukti_kartu = $newName;
            }

            $result = checkoutKeranjang($id_user, $waktu_pinjam, $batas_kembali, $bukti_kartu);
            if(isset($result['success'])){
                echo "<script>alert('Peminjaman berhasil diajukan! Silakan tunggu konfirmasi petugas.'); window.location='../transaksi/transaksi.php';</script>";
                exit;
            } else {
                $error = $result['error'];
            }
        }
    }
}

$keranjang = queryReadData("SELECT k.*, a.nama_alat_olahraga, a.foto_alat_olahraga, a.stok, a.kategori 
                            FROM keranjang k 
                            JOIN alat_olahraga a ON k.id_alat_olahraga = a.id_alat_olahraga 
                            WHERE k.id_user = $id_user");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keranjang Saya - PopFit</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" type="text/css" href="https://unpkg.com/@phosphor-icons/web@2.1.1/src/duotone/style.css" />
    <script src="https://unpkg.com/@phosphor-icons/web@2.1.1"></script>
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
        .sidebar { transition: transform 0.3s ease-in-out; }
    </style>
</head>
<body class="bg-popfit-bg text-popfit-text font-sans h-screen overflow-hidden flex text-[13px]">

    <div id="sidebarOverlay" class="fixed inset-0 bg-black/50 z-40 hidden transition-opacity"></div>

    <?php 
        $rel = "../"; 
        $activeIndex = "keranjang"; 
        include '../../layout/sidebar_siswa.php'; 
    ?>

    <div class="flex-1 flex flex-col h-screen w-full relative">
        <?php 
            $pageTitle = "Keranjang Anda"; 
            include '../../layout/header_siswa.php'; 
        ?>

        <main class="flex-1 overflow-y-auto p-6">
            <?php if(isset($error)): ?>
                <div class="max-w-4xl mx-auto mb-6 p-4 bg-red-50 border border-red-100 text-red-600 text-[11px] font-bold uppercase tracking-tight flex items-center rounded-sm">
                    <i class="ph-fill ph-warning-circle text-xl mr-3"></i> <?= $error ?>
                </div>
            <?php endif; ?>
            <div class="max-w-4xl mx-auto flex flex-col lg:flex-row gap-6">
                
                <!-- ITEMS LIST -->
                <div class="flex-1 flex flex-col gap-4">
                    <?php if(!empty($keranjang)): ?>
                        <?php foreach($keranjang as $item): ?>
                        <div class="bg-white border border-popfit-border rounded-sm p-4 flex items-center group hover:border-popfit-dark transition-all">
                            <div class="w-20 h-20 bg-popfit-bg rounded-sm flex-shrink-0 p-2 flex items-center justify-center">
                                <img src="../../asset/<?= $item['foto_alat_olahraga'] ?: 'default.png' ?>" class="h-full object-contain">
                            </div>
                            <div class="ml-4 flex-1">
                                <h4 class="text-[13px] font-black text-popfit-dark uppercase tracking-tight"><?= htmlspecialchars($item['nama_alat_olahraga']) ?></h4>
                                <p class="text-[10px] font-bold text-popfit-textMuted uppercase mt-1 tracking-widest"><?= htmlspecialchars($item['kategori']) ?></p>
                                <p class="text-[11px] font-black text-popfit-light mt-2 uppercase">Jumlah: <?= $item['jumlah'] ?> Unit</p>
                            </div>
                            <div class="flex flex-col gap-2">
                                <a href="hapusKeranjang.php?id=<?= $item['id_keranjang'] ?>" class="w-8 h-8 flex items-center justify-center bg-red-50 text-red-500 rounded-sm hover:bg-red-500 hover:text-white transition-all"><i class="ph-bold ph-trash"></i></a>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="bg-white border border-popfit-border border-dashed rounded-sm py-20 text-center">
                            <i class="ph-duotone ph-shopping-cart text-6xl text-gray-200 mb-4"></i>
                            <p class="text-[11px] font-black text-popfit-textMuted uppercase tracking-widest">Keranjang Kosong</p>
                            <a href="../alat/daftarAlat.php" class="mt-6 inline-block bg-popfit-dark text-white px-6 py-3 text-[10px] font-black uppercase tracking-[0.2em] rounded-sm hover:bg-popfit-light transition-all">Mulai Cari Alat</a>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- CHECKOUT FORM -->
                <?php if(!empty($keranjang)): ?>
                <div class="w-full lg:w-80 flex-shrink-0">
                    <div class="bg-white border border-popfit-border rounded-sm p-6 sticky top-0">
                        <h3 class="text-xs font-black text-popfit-dark uppercase tracking-widest mb-6 pb-4 border-b border-gray-100 flex items-center">
                            <i class="ph-fill ph-receipt text-popfit-accent mr-2 text-lg"></i> Checkout
                        </h3>

                        <form method="POST" enctype="multipart/form-data" class="space-y-4">
                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-popfit-textMuted uppercase tracking-[0.2em]">Tanggal Pinjam</label>
                                <input type="datetime-local" name="waktu_pinjam" required value="<?= date('Y-m-d\TH:i') ?>" 
                                       class="w-full bg-popfit-bg border border-popfit-border rounded-sm px-4 py-3 text-[11px] font-bold text-popfit-dark focus:border-popfit-dark outline-none transition-all">
                            </div>
                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-popfit-textMuted uppercase tracking-[0.2em]">Batas Kembali (Max 5 Jam)</label>
                                <input type="datetime-local" name="batas_kembali" required value="<?= date('Y-m-d\TH:i', strtotime('+5 hours')) ?>" 
                                       class="w-full bg-popfit-bg border border-popfit-border rounded-sm px-4 py-3 text-[11px] font-bold text-popfit-dark focus:border-popfit-dark outline-none transition-all">
                            </div>
                            <div class="space-y-2 pt-2 pb-2">
                                <label class="text-[10px] font-black text-popfit-textMuted uppercase tracking-[0.2em]">Upload Kartu Pelajar</label>
                                <div class="relative group">
                                    <input type="file" name="bukti_kartu" id="buktiInput" accept="image/*" required class="hidden">
                                    <label for="buktiInput" class="w-full border-2 border-dashed border-popfit-border rounded-sm p-4 flex flex-col items-center justify-center cursor-pointer group-hover:bg-popfit-bg group-hover:border-popfit-dark transition-all">
                                        <i class="ph-duotone ph-identification-card text-3xl text-popfit-textMuted mb-2"></i>
                                        <span id="fileName" class="text-[9px] font-black text-popfit-textMuted uppercase tracking-tighter text-center">Pilih Gambar Kartu</span>
                                    </label>
                                </div>
                                <p class="text-[8px] font-bold text-gray-400 italic">Format: JPG/PNG, Maks 2MB</p>
                            </div>

                            <button type="submit" name="checkout" class="w-full bg-popfit-dark text-white py-4 rounded-sm text-[11px] font-black uppercase tracking-widest hover:bg-popfit-light transition-all flex items-center justify-center">
                                AJUKAN PINJAMAN
                            </button>
                        </form>
                    </div>
                </div>
                <?php endif; ?>

            </div>
        </main>
    </div>

    <script>
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebarOverlay');
        const openBtn = document.getElementById('openSidebar');
        const closeBtn = document.getElementById('closeSidebar');
        
        function toggleSidebar() { sidebar.classList.toggle('-translate-x-full'); overlay.classList.toggle('hidden'); }
        openBtn.addEventListener('click', toggleSidebar);
        closeBtn.addEventListener('click', toggleSidebar);
        overlay.addEventListener('click', toggleSidebar);

        const buktiInput = document.getElementById('buktiInput');
        const fileName = document.getElementById('fileName');
        if(buktiInput){
            buktiInput.onchange = function() {
                if(this.files.length > 0) fileName.textContent = this.files[0].name;
            };
        }
    </script>
</body>
</html>
