<?php
require '../../config/config.php';
session_start();

if (!isset($_SESSION["login"]) || $_SESSION["role"] != "siswa") {
    header("Location: ../../login.php");
    exit;
}

$id_user = $_SESSION['id_user'];
$siswaName = htmlspecialchars($_SESSION['nama'] ?? 'Siswa');
$siswaUsername = htmlspecialchars($_SESSION['username'] ?? 'username');

$countKeranjang = mysqli_fetch_assoc(mysqli_query($connect, "SELECT COUNT(*) as total FROM keranjang WHERE id_user = '$id_user'"))['total'] ?? 0;

$query = "SELECT t.* 
          FROM transaksi t 
          WHERE t.id_user = '$id_user' 
          ORDER BY t.waktu_pinjam DESC";
$result = mysqli_query($connect, $query);

function getStatusInfo($st) {
    return match($st) {
        'menunggu' => ['label' => 'Menunggu', 'color' => 'bg-popfit-accent text-popfit-dark'],
        'dipinjam' => ['label' => 'Dipinjam', 'color' => 'bg-popfit-dark text-white'],
        'disetujui' => ['label' => 'Disetujui', 'color' => 'bg-green-500 text-white'],
        'dikembalikan' => ['label' => 'Selesai', 'color' => 'bg-popfit-bg text-popfit-textMuted'],
        'ditolak' => ['label' => 'Ditolak', 'color' => 'bg-red-50 text-red-500'],
        default => ['label' => $st, 'color' => 'bg-gray-100']
    };
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaksi Saya - PopFit Siswa</title>
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
                <li><a href="../dashboardSiswa.php" class="flex items-center px-6 py-3 text-gray-200 hover:bg-popfit-light transition-colors border-l-4 border-transparent">
                    <i class="ph ph-squares-four text-xl w-6"></i><span class="ml-3 font-bold">Beranda</span>
                </a></li>
                <li class="px-6 py-2 mt-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Peminjaman</li>
                <li><a href="../alat/daftarAlat.php" class="flex items-center px-6 py-3 text-gray-200 hover:bg-popfit-light transition-colors border-l-4 border-transparent">
                    <i class="ph ph-basketball text-xl w-6"></i><span class="ml-3 font-bold">Cari Alat</span>
                </a></li>
                <li><a href="transaksi.php" class="nav-active flex items-center px-6 py-3 text-gray-200 hover:bg-popfit-light transition-colors border-l-4 border-transparent">
                    <i class="ph ph-arrows-left-right text-xl w-6"></i><span class="ml-3 font-bold">Transaksi</span>
                </a></li>
                <li><a href="../denda/denda.php" class="flex items-center px-6 py-3 text-gray-200 hover:bg-popfit-light transition-colors border-l-4 border-transparent">
                    <i class="ph ph-wallet text-xl w-6"></i><span class="ml-3 font-bold">Denda</span>
                </a></li>
                <li><a href="../riwayat/riwayat.php" class="flex items-center px-6 py-3 text-gray-200 hover:bg-popfit-light transition-colors border-l-4 border-transparent">
                    <i class="ph ph-clock-rotate-left text-xl w-6"></i><span class="ml-3 font-bold">Riwayat</span>
                </a></li>
            </ul>
        </nav>

        <div class="border-t border-popfit-light p-4">
            <div class="flex items-center w-full">
                <div class="w-8 h-8 rounded-sm bg-popfit-accent flex items-center justify-center text-popfit-dark font-black"><?= substr($siswaName, 0, 1) ?></div>
                <div class="ml-3 flex-1 overflow-hidden">
                    <p class="text-[12px] font-black text-white truncate uppercase"><?= $siswaName ?></p>
                    <p class="text-[10px] text-gray-400 truncate uppercase">Siswa</p>
                </div>
                <a href="../../logout.php" class="text-gray-400 hover:text-white transition-colors"><i class="ph ph-sign-out text-xl"></i></a>
            </div>
        </div>
    </aside>

    <div class="flex-1 flex flex-col h-screen w-full relative">
        <header class="h-16 bg-popfit-surface border-b border-popfit-border flex items-center justify-between px-6 flex-shrink-0">
            <div class="flex items-center">
                <button id="openSidebar" class="md:hidden mr-4 text-popfit-dark"><i class="ph ph-list text-2xl"></i></button>
                <h2 class="text-lg font-black text-popfit-dark uppercase tracking-tight">Transaksi Saya</h2>
            </div>
            <a href="../keranjang/keranjang.php" class="relative text-popfit-textMuted hover:text-popfit-dark transition-all">
                <i class="ph ph-shopping-cart text-2xl"></i>
                <?php if($countKeranjang > 0): ?><span class="absolute -top-1.5 -right-1.5 bg-popfit-accent text-popfit-dark text-[10px] font-black w-5 h-5 flex items-center justify-center rounded-full border-2 border-white"><?= $countKeranjang ?></span><?php endif; ?>
            </a>
        </header>

        <main class="flex-1 overflow-y-auto p-6">
            <div class="space-y-4">
                <?php while($row = mysqli_fetch_assoc($result)): 
                    $stat = getStatusInfo($row['status']);
                ?>
                <div class="bg-white border border-popfit-border rounded-sm p-5 flex flex-col md:flex-row items-start md:items-center justify-between group hover:border-popfit-dark transition-all">
                    <div class="flex items-center space-x-4">
                        <div class="w-12 h-12 bg-popfit-bg border border-popfit-border rounded-sm flex items-center justify-center text-popfit-dark"><i class="ph ph-package text-2xl"></i></div>
                        <div>
                            <h4 class="text-[12px] font-black text-popfit-dark uppercase tracking-tight">PINJAMAN #<?= $row['id_transaksi'] ?></h4>
                            <p class="text-[10px] font-bold text-popfit-textMuted uppercase mt-1"><?= date('d M Y • H:i', strtotime($row['waktu_pinjam'])) ?></p>
                        </div>
                    </div>
                    <div class="mt-4 md:mt-0 flex items-center space-x-4 w-full md:w-auto border-t md:border-t-0 pt-4 md:pt-0">
                        <div class="text-right hidden sm:block">
                            <p class="text-[9px] font-black text-popfit-textMuted uppercase tracking-widest border-b border-gray-100 mb-1">BATAS KEMBALI</p>
                            <p class="text-[11px] font-black text-popfit-dark uppercase"><?= date('d M Y', strtotime($row['batas_kembali'])) ?></p>
                        </div>
                        <span class="px-2 py-1 rounded-sm text-[9px] font-black uppercase tracking-tighter <?= $stat['color'] ?>"><?= $stat['label'] ?></span>
                        <a href="detailTransaksi.php?id=<?= $row['id_transaksi'] ?>" class="p-2 border border-popfit-border text-popfit-dark rounded-sm hover:bg-popfit-dark hover:text-white transition-all"><i class="ph-bold ph-caret-right"></i></a>
                    </div>
                </div>
                <?php endwhile; ?>
                <?php if(mysqli_num_rows($result) == 0): ?>
                <div class="py-20 text-center border-2 border-dashed border-popfit-border rounded-sm">
                    <i class="ph ph-receipt-x text-5xl text-gray-200 mb-4 block mx-auto"></i>
                    <p class="text-[11px] font-black uppercase tracking-widest text-popfit-textMuted">Belum ada aktivitas transaksi</p>
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
