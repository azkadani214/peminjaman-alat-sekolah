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
    switch($st) {
        case 'menunggu': return ['label' => 'Menunggu', 'color' => 'bg-popfit-accent text-popfit-dark'];
        case 'dipinjam': return ['label' => 'Dipinjam', 'color' => 'bg-popfit-dark text-white'];
        case 'disetujui': return ['label' => 'Disetujui', 'color' => 'bg-green-500 text-white'];
        case 'dikembalikan': return ['label' => 'Selesai', 'color' => 'bg-popfit-bg text-popfit-textMuted'];
        case 'ditolak': return ['label' => 'Ditolak', 'color' => 'bg-red-50 text-red-500'];
        default: return ['label' => $st, 'color' => 'bg-gray-100'];
    }
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

    <?php 
        $rel = "../"; 
        $activeIndex = "transaksi"; 
        include '../../layout/sidebar_siswa.php'; 
    ?>

    <div class="flex-1 flex flex-col h-screen w-full relative">
        <?php 
            $pageTitle = "Transaksi Saya"; 
            include '../../layout/header_siswa.php'; 
        ?>

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
                            <?php 
                            if($row['status'] == 'dipinjam') {
                                $det = cekDetailKeterlambatan($row['batas_kembali']);
                                if($det['is_telat']) {
                                    echo '<p class="text-[9px] font-black text-red-600 uppercase mt-1">Telat: '.$det['teks'].'</p>';
                                    echo '<p class="text-[10px] font-black text-red-600">± Rp '.number_format($det['denda'], 0, ',', '.').'</p>';
                                }
                            } elseif($row['status'] == 'dikembalikan' && ($row['denda'] > 0)) {
                                echo '<p class="text-[10px] font-black text-popfit-dark mt-1">Denda: Rp '.number_format($row['denda'], 0, ',', '.').'</p>';
                                echo '<p class="text-[8px] font-black uppercase tracking-widest '.($row['pembayaran'] == 'lunas' ? 'text-green-500' : 'text-red-400').' italic text-right mt-0.5">'.$row['pembayaran'].'</p>';
                            }
                            ?>
                        </div>
                            <div class="flex flex-col items-end gap-1">
                                <span class="px-2 py-1 rounded-sm text-[9px] font-black uppercase tracking-tighter <?= $stat['color'] ?>"><?= $stat['label'] ?></span>
                                
                                <?php if($row['denda'] > 0): ?>
                                    <?php if($row['pembayaran'] == 'belum bayar'): ?>
                                        <span class="px-2 py-1 rounded-sm text-[8px] font-black uppercase tracking-widest bg-red-600 text-white animate-pulse">DENDA BELUM BAYAR</span>
                                    <?php elseif($row['pembayaran'] == 'pending'): ?>
                                        <span class="px-2 py-1 rounded-sm text-[8px] font-black uppercase tracking-widest bg-popfit-accent text-popfit-dark">VERIFIKASI PEMBAYARAN</span>
                                    <?php elseif($row['pembayaran'] == 'ditolak'): ?>
                                        <span class="px-2 py-1 rounded-sm text-[8px] font-black uppercase tracking-widest bg-red-100 text-red-600 border border-red-200">PEMBAYARAN DITOLAK</span>
                                    <?php elseif($row['pembayaran'] == 'lunas'): ?>
                                        <span class="px-2 py-1 rounded-sm text-[8px] font-black uppercase tracking-widest bg-green-100 text-green-700">DENDA LUNAS</span>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
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
        const closeBtn = document.getElementById('closeSidebar');
        function toggleSidebar() { sidebar.classList.toggle('-translate-x-full'); overlay.classList.toggle('hidden'); }
        openBtn.addEventListener('click', toggleSidebar);
        closeBtn.addEventListener('click', toggleSidebar);
        overlay.addEventListener('click', toggleSidebar);
    </script>
</body>
</html>
