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

/* HANDLE ACTION */
if(isset($_POST['checkout'])){
    $waktu_pinjam = $_POST['waktu_pinjam'];
    $batas_kembali = $_POST['batas_kembali'];
    
    $bukti_kartu = null;
    if(isset($_FILES['bukti_kartu']) && $_FILES['bukti_kartu']['error'] === 0){
        $ext = pathinfo($_FILES['bukti_kartu']['name'], PATHINFO_EXTENSION);
        $nama_file = "kartu_" . time() . "_" . rand(1000, 9999) . "." . $ext;
        if(move_uploaded_file($_FILES['bukti_kartu']['tmp_name'], "../../uploads/" . $nama_file)){
            $bukti_kartu = $nama_file;
        }
    }

    $result = checkoutKeranjang($id_user, $waktu_pinjam, $batas_kembali, $bukti_kartu);
    if(isset($result['success'])){
        echo "<script>alert('Peminjaman berhasil diajukan! Silakan tunggu konfirmasi petugas.'); window.location='../transaksi/transaksi.php';</script>";
        exit;
    } else {
        $error = $result['error'];
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

    <aside id="sidebar" class="fixed inset-y-0 left-0 w-64 bg-popfit-dark text-white border-r border-popfit-dark h-full flex-shrink-0 z-50 sidebar -translate-x-full md:translate-x-0 md:static flex flex-col">
        <div class="h-16 flex items-center px-6 border-b border-popfit-light bg-popfit-dark justify-between">
            <div class="flex items-center">
                <i class="ph-fill ph-paw-print text-popfit-accent text-2xl mr-3"></i>
                <span class="text-xl font-black tracking-wide uppercase">PopFit</span>
            </div>
            <button id="closeSidebar" class="md:hidden text-2xl"><i class="ph ph-x"></i></button>
        </div>

        <nav class="flex-1 overflow-y-auto pt-6">
            <ul class="space-y-1">
                <li class="px-6 py-2 text-[10px] font-black text-gray-400 uppercase tracking-widest">Main Menu</li>
                <li><a href="../dashboardSiswa.php" class="flex items-center px-6 py-3 text-gray-200 hover:bg-popfit-light transition-colors border-l-4 border-transparent">
                    <i class="ph ph-squares-four text-xl w-6"></i><span class="ml-3 font-bold">Beranda</span>
                </a></li>
                <li class="px-6 py-2 mt-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Peminjaman</li>
                <li><a href="../alat/daftarAlat.php" class="flex items-center px-6 py-3 text-gray-200 hover:bg-popfit-light transition-colors border-l-4 border-transparent">
                    <i class="ph ph-magnifying-glass text-xl w-6"></i><span class="ml-3 font-bold">Cari Alat</span>
                </a></li>
                <li><a href="keranjang.php" class="flex items-center px-6 py-3 text-white transition-colors border-l-4 border-popfit-accent nav-active">
                    <i class="ph-fill ph-shopping-cart text-xl w-6"></i><span class="ml-3 font-bold">Keranjang</span>
                </a></li>
                <li><a href="../transaksi/transaksi.php" class="flex items-center px-6 py-3 text-gray-200 hover:bg-popfit-light transition-colors border-l-4 border-transparent">
                    <i class="ph ph-arrows-left-right text-xl w-6"></i><span class="ml-3 font-bold">Transaksi</span>
                </a></li>
                <li><a href="../denda/denda.php" class="flex items-center px-6 py-3 text-gray-200 hover:bg-popfit-light transition-colors border-l-4 border-transparent">
                    <i class="ph ph-wallet text-xl w-6"></i><span class="ml-3 font-bold">Denda</span>
                </a></li>
                <li><a href="../riwayat/riwayat.php" class="flex items-center px-6 py-3 text-gray-200 hover:bg-popfit-light transition-colors border-l-4 border-transparent">
                    <i class="ph ph-clock-counter-clockwise text-xl w-6"></i><span class="ml-3 font-bold">Riwayat</span>
                </a></li>
            </ul>
        </nav>

        <div class="p-6 border-t border-popfit-light">
            <div class="flex items-center bg-popfit-light/30 p-3 rounded-sm">
                <div class="w-8 h-8 rounded-sm bg-popfit-accent flex items-center justify-center text-popfit-dark font-black text-xs"><?= substr($siswaName, 0, 1) ?></div>
                <div class="ml-3 flex-1 overflow-hidden">
                    <p class="text-[12px] font-black text-white truncate uppercase"><?= $siswaName ?></p>
                    <p class="text-[10px] text-gray-400 truncate uppercase">Siswa</p>
                </div>
                <a href="../logout.php" class="text-gray-400 hover:text-white"><i class="ph-bold ph-sign-out text-xl"></i></a>
            </div>
        </div>
    </aside>

    <div class="flex-1 flex flex-col h-screen w-full relative">
        <header class="h-16 bg-popfit-surface border-b border-popfit-border flex items-center justify-between px-6 flex-shrink-0">
            <div class="flex items-center">
                <button id="openSidebar" class="md:hidden mr-4 text-2xl"><i class="ph ph-list"></i></button>
                <h2 class="text-lg font-black text-popfit-dark uppercase tracking-tight">Keranjang Anda</h2>
            </div>
            <div class="flex items-center space-x-4">
                <a href="../notif.php" class="p-2 bg-popfit-bg text-popfit-dark rounded-sm hover:bg-popfit-border transition-colors"><i class="ph-bold ph-bell text-xl"></i></a>
            </div>
        </header>

        <main class="flex-1 overflow-y-auto p-6">
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
                                <label class="text-[10px] font-black text-popfit-textMuted uppercase tracking-[0.2em]">Batas Kembali</label>
                                <input type="datetime-local" name="batas_kembali" required value="<?= date('Y-m-d\TH:i', strtotime('+3 days')) ?>" 
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
