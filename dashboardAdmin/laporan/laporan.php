<?php
require '../../config/config.php';
session_start();

if (!isset($_SESSION["login"]) || $_SESSION["role"] != "admin utama") {
    header("Location: ../../login.php");
    exit;
}

$adminName = htmlspecialchars($_SESSION['nama'] ?? 'Admin');
$adminUsername = htmlspecialchars($_SESSION['username'] ?? 'username');

$tgl_mulai = $_GET['tgl_mulai'] ?? date('Y-m-01');
$tgl_selesai = $_GET['tgl_selesai'] ?? date('Y-m-t');

$query = "SELECT t.*, u.nama, u.nis, u.kelas 
          FROM transaksi t
          JOIN users u ON t.id_user = u.id_user
          WHERE DATE(t.waktu_pinjam) BETWEEN '$tgl_mulai' AND '$tgl_selesai'
          ORDER BY t.waktu_pinjam DESC";
$result = mysqli_query($connect, $query);

if(isset($_GET['export'])){
    header("Content-type: application/vnd-ms-excel");
    header("Content-Disposition: attachment; filename=Laporan_Peminjaman_$tgl_mulai.xls");
    echo "<table border='1'><tr><th>No</th><th>Waktu Pinjam</th><th>Nama</th><th>Status</th><th>Keterangan Telat</th><th>Total Denda</th></tr>";
    $no = 1;
    while($row = mysqli_fetch_assoc($result)){
        $ket_telat = "-";
        if($row['keterlambatan'] == 'ya' && $row['waktu_kembali']){
            $det = cekDetailKeterlambatan($row['batas_kembali'], $row['waktu_kembali']);
            $ket_telat = "TELAT " . $det['teks'];
        }
        echo "<tr><td>".$no++."</td><td>".$row['waktu_pinjam']."</td><td>".$row['nama']."</td><td>".$row['status']."</td><td>".$ket_telat."</td><td>".$row['denda']."</td></tr>";
    }
    echo "</table>";
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan - PopFit Admin</title>
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

    <!-- MOBILE OVERLAY -->
    <div id="sidebarOverlay" class="fixed inset-0 bg-black/50 z-40 hidden transition-opacity"></div>

    <?php 
        $rel = "../"; 
        $activeIndex = "laporan"; 
        include '../../layout/sidebar_admin.php'; 
    ?>

    <div class="flex-1 flex flex-col h-screen w-full relative">
        <?php 
            $pageTitle = "Laporan Sistem"; 
            include '../../layout/header_admin.php'; 
        ?>

        <!-- Sub Header with Export Actions -->
        <div class="px-6 py-4 bg-white border-b border-popfit-border flex items-center justify-end space-x-3">
             <a href="generatePDF.php?tgl_mulai=<?= $tgl_mulai ?>&tgl_selesai=<?= $tgl_selesai ?>" target="_blank" class="bg-popfit-dark text-white px-4 py-2 text-[10px] font-black uppercase tracking-widest rounded-sm hover:bg-popfit-light transition-all flex items-center shadow-sm">
                <i class="ph ph-file-pdf mr-2 text-base"></i> Export PDF
            </a>
            <a href="?tgl_mulai=<?= $tgl_mulai ?>&tgl_selesai=<?= $tgl_selesai ?>&export=1" class="bg-white border border-popfit-border text-popfit-dark px-4 py-2 text-[10px] font-black uppercase tracking-widest rounded-sm hover:bg-popfit-bg transition-all flex items-center shadow-sm">
                <i class="ph ph-file-xls mr-2 text-base"></i> Export Excel
            </a>
        </div>

        <main class="flex-1 overflow-y-auto p-6">
            <div class="bg-white border border-popfit-border rounded-sm p-6 mb-6">
                <form method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-6 items-end">
                    <div>
                        <label class="text-[10px] font-black uppercase text-popfit-textMuted tracking-[0.2em] mb-2 block">DARI TANGGAL</label>
                        <input type="date" name="tgl_mulai" value="<?= $tgl_mulai ?>" class="w-full bg-popfit-bg border border-popfit-border rounded-sm px-4 py-2 text-[11px] font-bold text-popfit-dark focus:border-popfit-dark outline-none">
                    </div>
                    <div>
                        <label class="text-[10px] font-black uppercase text-popfit-textMuted tracking-[0.2em] mb-2 block">SAMPAI TANGGAL</label>
                        <input type="date" name="tgl_selesai" value="<?= $tgl_selesai ?>" class="w-full bg-popfit-bg border border-popfit-border rounded-sm px-4 py-2 text-[11px] font-bold text-popfit-dark focus:border-popfit-dark outline-none">
                    </div>
                    <button type="submit" class="bg-popfit-dark text-white rounded-sm h-[38px] text-[10px] font-black uppercase tracking-widest hover:bg-popfit-light transition-all">Filter</button>
                </form>
            </div>

            <div class="bg-white border border-popfit-border rounded-sm overflow-hidden text-[11px]">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-popfit-bg border-b border-popfit-border">
                            <th class="px-6 py-4 font-black text-popfit-textMuted uppercase tracking-widest">Waktu</th>
                            <th class="px-6 py-4 font-black text-popfit-textMuted uppercase tracking-widest">Siswa</th>
                            <th class="px-6 py-4 font-black text-popfit-textMuted uppercase tracking-widest">Status</th>
                            <th class="px-6 py-4 font-black text-popfit-textMuted uppercase tracking-widest text-center">Detail Keterlambatan</th>
                            <th class="px-6 py-4 font-black text-popfit-textMuted uppercase tracking-widest text-right">Denda</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <?php while($row = mysqli_fetch_assoc($result)): ?>
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 font-bold text-popfit-textMuted"><?= date('d.m.Y H:i', strtotime($row['waktu_pinjam'])) ?></td>
                            <td class="px-6 py-4">
                                <p class="font-black text-popfit-dark uppercase"><?= htmlspecialchars($row['nama']) ?></p>
                                <p class="text-[9px] font-bold text-popfit-textMuted uppercase">#<?= $row['nis'] ?></p>
                            </td>
                             <td class="px-6 py-4">
                                <span class="px-2 py-0.5 rounded-sm text-[8px] font-black uppercase tracking-widest bg-gray-100"><?= $row['status'] ?></span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <?php if($row['keterlambatan'] == 'ya' && $row['waktu_kembali']): 
                                    $det = cekDetailKeterlambatan($row['batas_kembali'], $row['waktu_kembali']);    
                                ?>
                                    <span class="text-[9px] font-black text-red-600 bg-red-50 px-2 py-1 rounded-sm border border-red-100 uppercase tracking-tighter">TELAT <?= $det['teks'] ?></span>
                                <?php else: ?>
                                    <span class="text-[9px] font-bold text-gray-300">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 text-right font-black text-popfit-dark">Rp <?= number_format($row['denda'], 0, ',', '.') ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
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
