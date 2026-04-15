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
          AND (t.denda > 0 OR t.status = 'dipinjam')
          ORDER BY t.pembayaran ASC, t.waktu_pinjam DESC";
$result = mysqli_query($connect, $query);

$listDenda = [];
while($row = mysqli_fetch_assoc($result)){
    $idT = $row['id_transaksi'];
    // Ambil nama-nama alat
    $qItems = mysqli_query($connect, "SELECT a.nama_alat_olahraga FROM detail_transaksi dt JOIN alat_olahraga a ON dt.id_alat_olahraga = a.id_alat_olahraga WHERE dt.id_transaksi = '$idT'");
    $itemNames = [];
    while($i = mysqli_fetch_assoc($qItems)) $itemNames[] = $i['nama_alat_olahraga'];
    $row['nama_barang'] = implode(", ", $itemNames);

    if($row['status'] == 'dipinjam'){
        $det = cekDetailKeterlambatan($row['batas_kembali']);
        if($det['is_telat']){
            $row['denda_berjalan'] = $det['denda'];
            $row['keterangan_telat'] = $det['teks'];
            $listDenda[] = $row;
        }
    } else if($row['denda'] > 0){
        $listDenda[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Denda Saya - PopFit Siswa</title>
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
        $activeIndex = "denda"; 
        include '../../layout/sidebar_siswa.php'; 
    ?>

    <div class="flex-1 flex flex-col h-screen w-full relative">
        <?php 
            $pageTitle = "Denda Saya"; 
            include '../../layout/header_siswa.php'; 
        ?>

        <main class="flex-1 overflow-y-auto p-6">
            <div class="space-y-4">
                <?php foreach($listDenda as $row): 
                    $isLunas = ($row['pembayaran'] == 'lunas');
                    $isAktif = ($row['status'] == 'dipinjam');
                    $bg = $isLunas ? 'border-green-100 bg-white' : 'border-red-100 bg-white';
                    
                    $totalDenda = $isAktif ? $row['denda_berjalan'] : $row['denda'];
                    $labelStatus = $isAktif ? 'AKTIF (BELUM KEMBALI)' : ($row['pembayaran'] ?: 'BELUM BAYAR');
                ?>
                <div class="border border-popfit-border rounded-sm p-6 flex flex-col md:flex-row items-center justify-between group hover:border-popfit-dark transition-all <?= $bg ?>">
                    <div class="flex items-center space-x-4">
                        <div class="w-12 h-12 <?= $isLunas ? 'bg-green-50 text-green-600' : 'bg-red-50 text-red-600' ?> rounded-sm flex items-center justify-center border border-current opacity-20"><i class="ph ph-warning-circle text-2xl"></i></div>
                        <div>
                            <h4 class="text-[12px] font-black text-popfit-dark uppercase tracking-tight">TRANSAKSI #<?= $row['id_transaksi'] ?></h4>
                            <p class="text-[10px] font-black text-popfit-light uppercase mt-0.5 tracking-tighter">ALAT: <?= htmlspecialchars($row['nama_barang']) ?></p>
                            <p class="text-[10px] font-bold text-popfit-textMuted uppercase mt-1">Rp <?= number_format($totalDenda, 0, ',', '.') ?> <?= $isAktif ? '<span class="text-red-500 ml-1 italic">(ESTIMASI: '.$row['keterangan_telat'].')</span>' : '' ?></p>
                        </div>
                    </div>
                    <div class="mt-4 md:mt-0 flex items-center space-x-6">
                        <span class="px-3 py-1.5 rounded-sm text-[10px] font-black uppercase tracking-widest <?= $isLunas ? 'bg-green-50 text-green-700' : ($isAktif ? 'bg-popfit-accent text-popfit-dark' : 'bg-red-50 text-red-700') ?>">
                            <?= strtoupper($labelStatus) ?>
                        </span>
                        <?php if(!$isAktif): ?>
                        <a href="detailDenda.php?id=<?= $row['id_transaksi'] ?>" class="text-[10px] font-black uppercase tracking-widest bg-popfit-dark text-white px-4 py-2 rounded-sm hover:bg-popfit-light transition-all">Bayar / Detail</a>
                        <?php else: ?>
                        <a href="../transaksi/detailTransaksi.php?id=<?= $row['id_transaksi'] ?>" class="text-[10px] font-black uppercase tracking-widest border border-popfit-dark text-popfit-dark px-4 py-2 rounded-sm hover:bg-popfit-dark hover:text-white transition-all">Lihat Pinjaman</a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php if(empty($listDenda)): ?>
                <div class="py-20 text-center bg-white border-2 border-dashed border-popfit-border rounded-sm">
                    <i class="ph ph-smiley-check text-5xl text-popfit-dark mb-4 block mx-auto opacity-20"></i>
                    <p class="text-[11px] font-black uppercase tracking-widest text-popfit-textMuted lowercase">Tidak ada tanggungan denda. Kamu hebat!</p>
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
