<?php
require '../../config/config.php';
session_start();

if (!isset($_SESSION["login"]) || ($_SESSION["role"] != "admin utama" && $_SESSION["role"] != "admin")) {
    header("Location: ../../login.php");
    exit;
}

$adminName = htmlspecialchars($_SESSION['nama'] ?? 'Admin');
$adminUsername = htmlspecialchars($_SESSION['username'] ?? 'username');

if (!isset($_GET['kategori'])) {
    header("Location: kategori.php");
    exit;
}

$kategori_lama = urldecode($_GET['kategori']);
$kategori_lama = mysqli_real_escape_string($connect, $kategori_lama);

$result = mysqli_query($connect, "SELECT * FROM kategori_alat_olahraga WHERE kategori = '$kategori_lama'");
if (mysqli_num_rows($result) == 0) {
    echo "<script>alert('Kategori tidak ditemukan'); window.location.href='kategori.php';</script>";
    exit;
}
$data = mysqli_fetch_assoc($result);

if (isset($_POST['simpan'])) {
    $kategori_baru = trim(mysqli_real_escape_string($connect, $_POST['kategori']));
    
    if (empty($kategori_baru)) {
        echo "<script>alert('Nama kategori tidak boleh kosong!'); window.history.back();</script>";
        exit;
    }
    
    $cek = mysqli_query($connect, "SELECT * FROM kategori_alat_olahraga WHERE kategori = '$kategori_baru' AND kategori != '$kategori_lama'");
    if (mysqli_num_rows($cek) > 0) {
        echo "<script>alert('Kategori sudah ada! Silakan gunakan nama lain.'); window.history.back();</script>";
        exit;
    }
    
    if ($kategori_lama !== $kategori_baru) {
        $update1 = mysqli_query($connect, "UPDATE kategori_alat_olahraga SET kategori = '$kategori_baru' WHERE kategori = '$kategori_lama'");
        $update2 = mysqli_query($connect, "UPDATE alat_olahraga SET kategori = '$kategori_baru' WHERE kategori = '$kategori_lama'");
        
        if ($update1 && $update2) {
            tambahLog($_SESSION['id_user'], "Mengubah kategori: $kategori_lama -> $kategori_baru");
            echo "<script>alert('Kategori berhasil diperbarui'); window.location.href='kategori.php';</script>";
            exit;
        } else {
            echo "<script>alert('Gagal memperbarui kategori'); window.history.back();</script>";
        }
    } else {
        header("Location: kategori.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Kategori - PopFit Admin</title>
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

    <aside id="sidebar" class="fixed inset-y-0 left-0 w-64 bg-popfit-dark text-white border-r border-popfit-dark h-full flex-shrink-0 z-50 sidebar -translate-x-full md:translate-x-0 md:static flex flex-col tracking-tight">
        <div class="h-16 flex items-center px-6 border-b border-popfit-light bg-popfit-dark justify-between">
            <div class="flex items-center">
                <i class="ph-fill ph-paw-print text-popfit-accent text-2xl mr-3"></i>
                <span class="text-xl font-black tracking-wide uppercase">PopFit Admin</span>
            </div>
            <button id="closeSidebar" class="md:hidden text-gray-400 hover:text-white"><i class="ph ph-x text-2xl"></i></button>
        </div>

        <nav class="flex-1 overflow-y-auto py-4">
            <ul class="space-y-1">
                <li><a href="../dashboardAdmin.php" class="flex items-center px-6 py-3 text-gray-200 hover:bg-popfit-light transition-colors border-l-4 border-transparent">
                    <i class="ph ph-squares-four text-xl w-6"></i><span class="ml-3 font-bold">Dashboard</span>
                </a></li>
                <li class="px-6 py-2 mt-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Manajemen</li>
                <li><a href="daftarAlat.php" class="nav-active flex items-center px-6 py-3 text-gray-200 hover:bg-popfit-light transition-colors border-l-4 border-transparent">
                    <i class="ph ph-basketball text-xl w-6"></i><span class="ml-3 font-bold">Katalog Alat</span>
                </a></li>
                <li><a href="../petugas/petugas.php" class="flex items-center px-6 py-3 text-gray-200 hover:bg-popfit-light transition-colors border-l-4 border-transparent">
                    <i class="ph ph-user-tie text-xl w-6"></i><span class="ml-3 font-bold">Petugas</span>
                </a></li>
                <li><a href="../siswa/siswa.php" class="flex items-center px-6 py-3 text-gray-200 hover:bg-popfit-light transition-colors border-l-4 border-transparent">
                    <i class="ph ph-users text-xl w-6"></i><span class="ml-3 font-bold">Siswa</span>
                </a></li>
                <li class="px-6 py-2 mt-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Sirkulasi</li>
                <li><a href="../transaksi/transaksi.php" class="flex items-center px-6 py-3 text-gray-200 hover:bg-popfit-light transition-colors border-l-4 border-transparent">
                    <i class="ph ph-arrows-left-right text-xl w-6"></i><span class="ml-3 font-bold">Transaksi</span>
                </a></li>
                <li><a href="../denda/denda.php" class="flex items-center px-6 py-3 text-gray-200 hover:bg-popfit-light transition-colors border-l-4 border-transparent">
                    <i class="ph ph-wallet text-xl w-6"></i><span class="ml-3 font-bold">Denda</span>
                </a></li>
                <li><a href="../laporan/laporan.php" class="flex items-center px-6 py-3 text-gray-200 hover:bg-popfit-light transition-colors border-l-4 border-transparent">
                    <i class="ph ph-file-text text-xl w-6"></i><span class="ml-3 font-bold">Laporan</span>
                </a></li>
            </ul>
        </nav>

        <div class="border-t border-popfit-light p-4">
            <div class="flex items-center w-full">
                <div class="w-8 h-8 rounded-sm bg-popfit-accent flex items-center justify-center text-popfit-dark font-black">A</div>
                <div class="ml-3 flex-1 overflow-hidden">
                    <p class="text-[12px] font-black text-white truncate uppercase"><?= $adminName ?></p>
                    <p class="text-[10px] text-gray-400 truncate uppercase"><?= $adminUsername ?></p>
                </div>
                <a href="../../logout.php" class="text-gray-400 hover:text-white transition-colors"><i class="ph ph-sign-out text-xl"></i></a>
            </div>
        </div>
    </aside>

    <div class="flex-1 flex flex-col h-screen w-full relative">
        <header class="h-16 bg-popfit-surface border-b border-popfit-border flex items-center justify-between px-6 flex-shrink-0">
            <div class="flex items-center">
                <button id="openSidebar" class="md:hidden mr-4 text-popfit-dark"><i class="ph ph-list text-2xl"></i></button>
                <h2 class="text-lg font-black text-popfit-dark uppercase tracking-tight">Perbarui Kategori</h2>
            </div>
            <a href="kategori.php" class="flex items-center text-popfit-textMuted hover:text-popfit-dark transition-all text-[11px] font-black uppercase tracking-widest">
                <i class="ph ph-arrow-left mr-2"></i> Batal
            </a>
        </header>

        <main class="flex-1 overflow-y-auto p-6 flex items-center justify-center">
            <div class="w-full max-w-md bg-white border border-popfit-border rounded-sm p-8 shadow-sm">
                <div class="text-center mb-8">
                    <div class="w-16 h-16 bg-popfit-bg border border-popfit-border rounded-sm flex items-center justify-center mx-auto mb-4">
                        <i class="ph ph-tag-bold text-3xl text-popfit-dark"></i>
                    </div>
                </div>

                <form method="POST" class="space-y-6 text-[13px]">
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-popfit-textMuted uppercase tracking-widest">Nama Kategori</label>
                        <input type="text" name="kategori" required value="<?= htmlspecialchars($data['kategori']) ?>" class="w-full bg-popfit-bg border border-popfit-border rounded-sm px-4 py-3 text-xs font-bold text-popfit-dark focus:border-popfit-dark outline-none transition-all uppercase">
                    </div>

                    <div class="pt-4">
                        <button type="submit" name="simpan" class="w-full bg-popfit-dark text-white py-4 text-[11px] font-black uppercase tracking-[0.2em] rounded-sm hover:bg-popfit-light transition-all shadow-md active:scale-95 flex items-center justify-center">
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
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
    </script>
</body>
</html>
