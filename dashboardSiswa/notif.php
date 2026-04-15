<?php
require '../config/config.php';
session_start();

if (!isset($_SESSION["login"]) || $_SESSION["role"] != "siswa") {
    header("Location: ../login.php");
    exit;
}

$id_user = $_SESSION["id_user"];
$siswaName = htmlspecialchars($_SESSION['nama'] ?? 'Siswa');
$siswaUsername = htmlspecialchars($_SESSION['username'] ?? 'siswa');

if(isset($_GET['read_all'])){
    mysqli_query($connect, "UPDATE notifikasi SET is_read = 1 WHERE id_user = $id_user");
    header("Location: notif.php");
    exit;
}

$query = "SELECT * FROM notifikasi WHERE id_user = $id_user ORDER BY waktu_notif DESC LIMIT 50";
$result = mysqli_query($connect, $query);

function tglIndo($tanggal){
    $bulan = [1=>"JAN","FEB","MAR","APR","MEI","JUN","JUL","AGU","SEP","OKT","NOV","DES"];
    $t = strtotime($tanggal);
    return date('d', $t) . ' ' . $bulan[(int)date('m', $t)] . ' ' . date('Y H:i', $t);
}

$countKeranjang = mysqli_fetch_assoc(mysqli_query($connect, "SELECT COUNT(*) as total FROM keranjang WHERE id_user = '$id_user'"))['total'] ?? 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifikasi Saya - PopFit</title>
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
                <li><a href="dashboardSiswa.php" class="flex items-center px-6 py-3 text-gray-200 hover:bg-popfit-light transition-colors border-l-4 border-transparent"><i class="ph ph-squares-four text-xl w-6"></i><span class="ml-3 font-bold">Beranda</span></a></li>
                <li class="px-6 py-2 mt-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Peminjaman</li>
                <li><a href="alat/daftarAlat.php" class="flex items-center px-6 py-3 text-gray-200 hover:bg-popfit-light transition-colors border-l-4 border-transparent"><i class="ph ph-basketball text-xl w-6"></i><span class="ml-3 font-bold">Cari Alat</span></a></li>
                <li><a href="transaksi/transaksi.php" class="flex items-center px-6 py-3 text-gray-200 hover:bg-popfit-light transition-colors border-l-4 border-transparent"><i class="ph ph-arrows-left-right text-xl w-6"></i><span class="ml-3 font-bold">Transaksi</span></a></li>
                <li><a href="notif.php" class="nav-active flex items-center px-6 py-3 text-gray-200 hover:bg-popfit-light transition-colors border-l-4 border-transparent"><i class="ph ph-bell text-xl w-6"></i><span class="ml-3 font-bold">Notifikasi</span></a></li>
            </ul>
        </nav>
    </aside>

    <div class="flex-1 flex flex-col h-screen w-full relative">
        <header class="h-16 bg-popfit-surface border-b border-popfit-border flex items-center justify-between px-6 flex-shrink-0">
            <div class="flex items-center space-x-4">
                <button id="openSidebar" class="md:hidden text-popfit-dark"><i class="ph ph-list text-2xl"></i></button>
                <h2 class="text-lg font-black text-popfit-dark uppercase tracking-tight">Kotak Masuk</h2>
            </div>
            <div class="flex items-center space-x-4">
                <a href="?read_all=1" class="text-[10px] font-black uppercase text-popfit-textMuted hover:text-popfit-dark transition-all">Tandai Dibaca</a>
                <a href="keranjang/keranjang.php" class="relative text-popfit-textMuted hover:text-popfit-dark">
                    <i class="ph ph-shopping-cart text-2xl"></i>
                    <?php if($countKeranjang > 0): ?><span class="absolute -top-1.5 -right-1.5 bg-popfit-accent text-popfit-dark text-[10px] font-black w-5 h-5 flex items-center justify-center rounded-full border-2 border-white"><?= $countKeranjang ?></span><?php endif; ?>
                </a>
            </div>
        </header>

        <main class="flex-1 overflow-y-auto p-6">
            <div class="max-w-2xl mx-auto space-y-3">
                <?php while($n = mysqli_fetch_assoc($result)): ?>
                <div class="bg-white border <?= $n['is_read'] ? 'border-popfit-border' : 'border-popfit-accent border-l-4' ?> p-5 rounded-sm relative group">
                    <p class="text-[11px] font-bold text-popfit-dark leading-relaxed"><?= htmlspecialchars($n['pesan']) ?></p>
                    <p class="text-[9px] font-black text-popfit-textMuted uppercase mt-3 tracking-widest flex items-center">
                        <i class="ph ph-clock mr-1.5"></i> <?= tglIndo($n['waktu_notif']) ?>
                    </p>
                    <?php if(!$n['is_read']): ?>
                    <span class="absolute top-5 right-5 w-2 h-2 bg-popfit-accent rounded-full"></span>
                    <?php endif; ?>
                </div>
                <?php endwhile; ?>
                <?php if(mysqli_num_rows($result) == 0): ?>
                <div class="py-20 text-center border-2 border-dashed border-popfit-border rounded-sm">
                    <i class="ph ph-bell-slash text-5xl text-gray-200 mb-4 block mx-auto"></i>
                    <p class="text-[11px] font-black uppercase tracking-widest text-popfit-textMuted">TIDAK ADA NOTIFIKASI</p>
                </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <script>
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebarOverlay');
        const openBtn = document.getElementById('openSidebar');
        function toggleSidebar() { sidebar.classList.toggle('-translate-x-full'); overlay.classList.toggle('hidden'); }
        openBtn.addEventListener('click', toggleSidebar);
        overlay.addEventListener('click', toggleSidebar);
    </script>
</body>
</html>
