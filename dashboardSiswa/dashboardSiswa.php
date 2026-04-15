<?php
require '../config/config.php';
session_start();

if (!isset($_SESSION["login"]) || $_SESSION["role"] != "siswa") {
    header("Location: ../login.php");
    exit;
}

$username = $_SESSION['username'];
$siswaName = htmlspecialchars($_SESSION['nama'] ?? 'Siswa');
$siswaUsername = htmlspecialchars($_SESSION['username'] ?? 'siswa');

$userData = mysqli_fetch_assoc(mysqli_query($connect, "SELECT id_user, nis FROM users WHERE username = '$username' AND role = 'siswa'"));
if (!$userData) { session_destroy(); header("Location: ../login.php"); exit; }

$id_user = $userData['id_user'];
$countKeranjang = mysqli_fetch_assoc(mysqli_query($connect, "SELECT COUNT(*) as total FROM keranjang WHERE id_user = '$id_user'"))['total'] ?? 0;
$peminjamanAktif = mysqli_fetch_assoc(mysqli_query($connect, "SELECT COUNT(*) AS total FROM transaksi WHERE id_user = '$id_user' AND status = 'dipinjam'"))['total'] ?? 0;
$pengajuanPinjam = mysqli_fetch_assoc(mysqli_query($connect, "SELECT COUNT(*) AS total FROM transaksi WHERE id_user = '$id_user' AND status = 'menunggu'"))['total'] ?? 0;
$totalTerlambat = mysqli_fetch_assoc(mysqli_query($connect, "SELECT COUNT(*) AS total FROM transaksi WHERE id_user = '$id_user' AND status = 'dipinjam' AND batas_kembali < NOW()"))['total'] ?? 0;
$dendaBelumBayar = mysqli_fetch_assoc(mysqli_query($connect, "SELECT SUM(denda) AS total FROM transaksi WHERE id_user = '$id_user' AND denda > 0"))['total'] ?? 0;
$totalPeminjaman = mysqli_fetch_assoc(mysqli_query($connect, "SELECT COUNT(*) AS total FROM transaksi WHERE id_user = '$id_user'"))['total'] ?? 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Siswa - PopFit</title>
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
            <button id="closeSidebar" class="md:hidden text-gray-400 hover:text-white"><i class="ph ph-x text-2xl"></i></button>
        </div>

        <nav class="flex-1 overflow-y-auto py-4">
            <ul class="space-y-1">
                <li><a href="dashboardSiswa.php" class="nav-active flex items-center px-6 py-3 text-gray-200 hover:bg-popfit-light transition-colors border-l-4 border-transparent">
                    <i class="ph ph-squares-four text-xl w-6"></i><span class="ml-3 font-bold">Beranda</span>
                </a></li>
                <li class="px-6 py-2 mt-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Peminjaman</li>
                <li><a href="alat/daftarAlat.php" class="flex items-center px-6 py-3 text-gray-200 hover:bg-popfit-light transition-colors border-l-4 border-transparent">
                    <i class="ph ph-basketball text-xl w-6"></i><span class="ml-3 font-bold">Cari Alat</span>
                </a></li>
                <li><a href="transaksi/transaksi.php" class="flex items-center px-6 py-3 text-gray-200 hover:bg-popfit-light transition-colors border-l-4 border-transparent">
                    <i class="ph ph-arrows-left-right text-xl w-6"></i><span class="ml-3 font-bold">Transaksi</span>
                </a></li>
                <li><a href="denda/denda.php" class="flex items-center px-6 py-3 text-gray-200 hover:bg-popfit-light transition-colors border-l-4 border-transparent">
                    <i class="ph ph-wallet text-xl w-6"></i><span class="ml-3 font-bold">Denda</span>
                </a></li>
                <li><a href="riwayat/riwayat.php" class="flex items-center px-6 py-3 text-gray-200 hover:bg-popfit-light transition-colors border-l-4 border-transparent">
                    <i class="ph ph-clock-rotate-left text-xl w-6"></i><span class="ml-3 font-bold">Riwayat</span>
                </a></li>
            </ul>
        </nav>

        <div class="border-t border-popfit-light p-4">
            <div class="flex items-center w-full">
                <div class="w-8 h-8 rounded-sm bg-popfit-accent flex items-center justify-center text-popfit-dark font-black"><?= substr($siswaName, 0, 1) ?></div>
                <div class="ml-3 flex-1 overflow-hidden">
                    <p class="text-[12px] font-black text-white truncate uppercase"><?= $siswaName ?></p>
                    <p class="text-[10px] text-gray-400 truncate uppercase"><?= $siswaUsername ?></p>
                </div>
                <a href="../logout.php" class="text-gray-400 hover:text-white transition-colors"><i class="ph ph-sign-out text-xl"></i></a>
            </div>
        </div>
    </aside>

    <div class="flex-1 flex flex-col h-screen w-full relative">
        <header class="h-16 bg-popfit-surface border-b border-popfit-border flex items-center justify-between px-6 flex-shrink-0">
            <div class="flex items-center">
                <button id="openSidebar" class="md:hidden mr-4 text-popfit-dark"><i class="ph ph-list text-2xl"></i></button>
                <h2 class="text-lg font-black text-popfit-dark uppercase tracking-tight">Beranda Siswa</h2>
            </div>
            <div class="flex items-center space-x-6">
                <a href="keranjang/keranjang.php" class="relative text-popfit-textMuted hover:text-popfit-dark">
                    <i class="ph ph-shopping-cart text-2xl"></i>
                    <?php if($countKeranjang > 0): ?><span class="absolute -top-1.5 -right-1.5 bg-popfit-accent text-popfit-dark text-[9px] font-black w-5 h-5 flex items-center justify-center rounded-full border-2 border-white"><?= $countKeranjang ?></span><?php endif; ?>
                </a>
                <a href="notif.php" class="text-popfit-textMuted hover:text-popfit-dark"><i class="ph ph-bell text-2xl"></i></a>
            </div>
        </header>

        <main class="flex-1 overflow-y-auto p-6">
            <div class="bg-popfit-dark p-8 rounded-sm text-white mb-8 border-l-4 border-popfit-accent">
                <h1 class="text-3xl font-black tracking-tighter uppercase mb-2">HALO, <?= $siswaName ?>!</h1>
                <p class="text-popfit-accent/80 text-[10px] font-black uppercase tracking-[0.2em]">Selamat datang di dashboard peminjaman alat PopFit</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
                <div class="bg-white border border-popfit-border p-5 rounded-sm flex items-center justify-between">
                    <div><p class="text-[10px] font-black text-popfit-textMuted uppercase mb-1">AKTIF</p><h3 class="text-2xl font-black text-popfit-dark"><?= $peminjamanAktif ?></h3></div>
                    <div class="w-12 h-12 bg-popfit-bg border border-popfit-border flex items-center justify-center rounded-sm text-popfit-dark"><i class="ph ph-arrows-left-right text-2xl"></i></div>
                </div>
                <div class="bg-white border border-red-200 border-l-4 p-5 rounded-sm flex items-center justify-between">
                    <div><p class="text-[10px] font-black text-red-500 uppercase mb-1 tracking-widest">TELAT</p><h3 class="text-2xl font-black text-red-600"><?= $totalTerlambat ?></h3></div>
                    <div class="w-12 h-12 bg-red-50 border border-red-100 flex items-center justify-center rounded-sm text-red-600"><i class="ph ph-warning-circle text-2xl"></i></div>
                </div>
                <div class="bg-white border border-popfit-border p-5 rounded-sm flex items-center justify-between">
                    <div><p class="text-[10px] font-black text-popfit-textMuted uppercase mb-1">PROSES</p><h3 class="text-2xl font-black text-popfit-dark"><?= $pengajuanPinjam ?></h3></div>
                    <div class="w-12 h-12 bg-popfit-bg border border-popfit-border flex items-center justify-center rounded-sm text-popfit-dark"><i class="ph ph-clock text-2xl"></i></div>
                </div>
                <div class="bg-white border border-popfit-border p-5 rounded-sm flex items-center justify-between">
                    <div><p class="text-[10px] font-black text-popfit-textMuted uppercase mb-1">DENDA</p><h3 class="text-2xl font-black text-red-500">Rp <?= number_format($dendaBelumBayar, 0, ",", ".") ?></h3></div>
                    <div class="w-12 h-12 bg-popfit-bg border border-popfit-border flex items-center justify-center rounded-sm text-popfit-dark"><i class="ph ph-wallet text-2xl"></i></div>
                </div>
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