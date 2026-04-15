<?php
require '../config/config.php';
session_start();

if (!isset($_SESSION["login"]) || ($_SESSION["role"] != "admin" && $_SESSION["role"] != "admin utama")) {
    header("Location: ../login.php");
    exit;
}
$_SESSION["role"] = "admin utama"; 

$adminName = htmlspecialchars($_SESSION['nama'] ?? 'Admin');
$adminUsername = htmlspecialchars($_SESSION['username'] ?? 'username');

$jumlahPetugas = mysqli_fetch_assoc(mysqli_query($connect, "SELECT COUNT(*) AS total FROM users WHERE role = 'petugas'"))['total'];
$jumlahSiswa = mysqli_fetch_assoc(mysqli_query($connect, "SELECT COUNT(*) AS total FROM users WHERE role = 'siswa'"))['total'];
$jumlahAlat = mysqli_fetch_assoc(mysqli_query($connect, "SELECT COUNT(*) AS total FROM alat_olahraga"))['total'];
$peminjamanAktif = mysqli_fetch_assoc(mysqli_query($connect, "SELECT COUNT(*) AS total FROM transaksi WHERE status = 'dipinjam'"))['total'];
$totalTerlambat = mysqli_fetch_assoc(mysqli_query($connect, "SELECT COUNT(*) AS total FROM transaksi WHERE status = 'dipinjam' AND batas_kembali < NOW()"))['total'];
$pengajuanPinjam = mysqli_fetch_assoc(mysqli_query($connect, "SELECT COUNT(*) AS total FROM transaksi WHERE status = 'menunggu'"))['total'];
$totalDenda = mysqli_fetch_assoc(mysqli_query($connect, "SELECT SUM(denda) AS total FROM transaksi WHERE status = 'dikembalikan' AND denda > 0"))['total'] ?? 0;
$dendaBelumBayar = mysqli_fetch_assoc(mysqli_query($connect, "SELECT SUM(denda) AS total FROM transaksi WHERE denda > 0 AND status != 'dikembalikan'"))['total'] ?? 0;

$namaBulan = [1=>"Jan",2=>"Feb",3=>"Mar",4=>"Apr",5=>"Mei",6=>"Jun",7=>"Jul",8=>"Agu",9=>"Sep",10=>"Okt",11=>"Nov",12=>"Des"];
$dataPerBulan = array_fill(1,12,0);
$dataChart = mysqli_query($connect,"SELECT MONTH(waktu_pinjam) as bulan, COUNT(*) as total FROM transaksi WHERE YEAR(waktu_pinjam) = YEAR(NOW()) GROUP BY MONTH(waktu_pinjam)");
while($row=mysqli_fetch_assoc($dataChart)){ $dataPerBulan[(int)$row['bulan']] = (int)$row['total']; }
$bulan = array_values($namaBulan);
$total = array_values($dataPerBulan);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - PopFit</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" type="text/css" href="https://unpkg.com/@phosphor-icons/web@2.1.1/src/duotone/style.css" />
    <script src="https://unpkg.com/@phosphor-icons/web@2.1.1"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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

    <!-- SIDEBAR -->
    <aside id="sidebar" class="fixed inset-y-0 left-0 w-64 bg-popfit-dark text-white border-r border-popfit-dark h-full flex-shrink-0 z-50 sidebar -translate-x-full md:translate-x-0 md:static flex flex-col">
        <div class="h-16 flex items-center px-6 border-b border-popfit-light bg-popfit-dark justify-between">
            <div class="flex items-center">
                <i class="ph-fill ph-paw-print text-popfit-accent text-2xl mr-3"></i>
                <span class="text-xl font-black tracking-wide uppercase">PopFit Admin</span>
            </div>
            <button id="closeSidebar" class="md:hidden text-gray-400 hover:text-white"><i class="ph ph-x text-2xl"></i></button>
        </div>

        <nav class="flex-1 overflow-y-auto py-4">
            <ul class="space-y-1">
                <li><a href="dashboardAdmin.php" class="nav-active flex items-center px-6 py-3 text-gray-200 hover:bg-popfit-light transition-colors border-l-4 border-transparent">
                    <i class="ph ph-squares-four text-xl w-6"></i><span class="ml-3 font-bold">Dashboard</span>
                </a></li>
                <li class="px-6 py-2 mt-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Manajemen</li>
                <li><a href="alat/daftarAlat.php" class="flex items-center px-6 py-3 text-gray-200 hover:bg-popfit-light transition-colors border-l-4 border-transparent">
                    <i class="ph ph-basketball text-xl w-6"></i><span class="ml-3 font-bold">Katalog Alat</span>
                </a></li>
                <li><a href="petugas/petugas.php" class="flex items-center px-6 py-3 text-gray-200 hover:bg-popfit-light transition-colors border-l-4 border-transparent">
                    <i class="ph ph-user-tie text-xl w-6"></i><span class="ml-3 font-bold">Petugas</span>
                </a></li>
                <li><a href="siswa/siswa.php" class="flex items-center px-6 py-3 text-gray-200 hover:bg-popfit-light transition-colors border-l-4 border-transparent">
                    <i class="ph ph-users text-xl w-6"></i><span class="ml-3 font-bold">Siswa</span>
                </a></li>
                <li class="px-6 py-2 mt-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Sirkulasi</li>
                <li><a href="transaksi/transaksi.php" class="flex items-center px-6 py-3 text-gray-200 hover:bg-popfit-light transition-colors border-l-4 border-transparent">
                    <i class="ph ph-arrows-left-right text-xl w-6"></i><span class="ml-3 font-bold">Transaksi</span>
                </a></li>
                <li><a href="denda/denda.php" class="flex items-center px-6 py-3 text-gray-200 hover:bg-popfit-light transition-colors border-l-4 border-transparent">
                    <i class="ph ph-wallet text-xl w-6"></i><span class="ml-3 font-bold">Denda</span>
                </a></li>
                <li><a href="laporan/laporan.php" class="flex items-center px-6 py-3 text-gray-200 hover:bg-popfit-light transition-colors border-l-4 border-transparent">
                    <i class="ph ph-file-text text-xl w-6"></i><span class="ml-3 font-bold">Laporan</span>
                </a></li>
                <li><a href="log/log.php" class="flex items-center px-6 py-3 text-gray-200 hover:bg-popfit-light transition-colors border-l-4 border-transparent">
                    <i class="ph ph-scroll text-xl w-6"></i><span class="ml-3 font-bold">Log Aktivitas</span>
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
                <a href="../logout.php" class="text-gray-400 hover:text-white transition-colors"><i class="ph ph-sign-out text-xl"></i></a>
            </div>
        </div>
    </aside>

    <!-- MAIN CONTENT -->
    <div class="flex-1 flex flex-col h-screen w-full relative">
        <header class="h-16 bg-popfit-surface border-b border-popfit-border flex items-center justify-between px-6 flex-shrink-0">
            <div class="flex items-center">
                <button id="openSidebar" class="md:hidden mr-4 text-popfit-dark"><i class="ph ph-list text-2xl"></i></button>
                <h2 class="text-lg font-black text-popfit-dark uppercase tracking-tight">Ringkasan Sistem</h2>
            </div>
            <div class="relative">
                <a href="notif.php" class="text-popfit-textMuted hover:text-popfit-dark transition-colors">
                    <i class="ph ph-bell text-2xl"></i>
                    <span id="notifBadge" class="absolute -top-1 -right-1 w-4 h-4 bg-red-500 text-white text-[9px] flex items-center justify-center rounded-full hidden font-black"></span>
                </a>
            </div>
        </header>

        <main class="flex-1 overflow-y-auto p-6">
            <div class="bg-popfit-dark p-8 rounded-sm text-white mb-8 border-l-4 border-popfit-accent">
                <h1 class="text-3xl font-black tracking-tighter uppercase leading-none mb-2 text-white">HALO, <?= $adminName ?>!</h1>
                <p class="text-popfit-accent/80 text-[10px] font-black uppercase tracking-[0.2em]">Panel Kendali Utama • PopFit Management System</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
                <div class="bg-white border border-popfit-border p-5 rounded-sm">
                    <p class="text-[10px] font-black text-popfit-textMuted uppercase mb-1 tracking-widest">KATALOG ALAT</p>
                    <h3 class="text-2xl font-black text-popfit-dark"><?= $jumlahAlat ?> UNIT</h3>
                </div>
                <div class="bg-white border border-popfit-border p-5 rounded-sm">
                    <p class="text-[10px] font-black text-popfit-textMuted uppercase mb-1 tracking-widest">DATABASE SISWA</p>
                    <h3 class="text-2xl font-black text-popfit-dark"><?= $jumlahSiswa ?> ORANG</h3>
                </div>
                <div class="bg-white border border-popfit-border p-5 rounded-sm">
                    <p class="text-[10px] font-black text-popfit-textMuted uppercase mb-1 tracking-widest">PETUGAS AKTIF</p>
                    <h3 class="text-2xl font-black text-popfit-dark"><?= $jumlahPetugas ?> ORANG</h3>
                </div>
                <div class="bg-white border border-red-100 border-l-4 p-5 rounded-sm">
                    <p class="text-[10px] font-black text-red-500 uppercase mb-1 tracking-widest">TRANSAKSI TELAT</p>
                    <h3 class="text-2xl font-black text-red-600"><?= $totalTerlambat ?> KASUS</h3>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
                <div class="bg-white border border-popfit-border p-5 rounded-sm col-span-1">
                    <p class="text-[10px] font-black text-popfit-textMuted uppercase mb-1">TOTAL DENDA MASUK</p>
                    <h3 class="text-xl font-black text-green-600">Rp <?= number_format($totalDenda, 0, ",", ".") ?></h3>
                </div>
                <div class="bg-white border border-popfit-border p-5 rounded-sm col-span-1">
                    <p class="text-[10px] font-black text-popfit-textMuted uppercase mb-1">PIUTANG DENDA</p>
                    <h3 class="text-xl font-black text-red-500">Rp <?= number_format($dendaBelumBayar, 0, ",", ".") ?></h3>
                </div>
                <div class="bg-white border border-popfit-border p-5 rounded-sm col-span-1">
                    <p class="text-[10px] font-black text-popfit-textMuted uppercase mb-1">PENGAJUAN MENUNGGU</p>
                    <h3 class="text-xl font-black text-popfit-dark"><?= $pengajuanPinjam ?> BERKAS</h3>
                </div>
            </div>

            <!-- Chart -->
            <div class="bg-white border border-popfit-border p-8 rounded-sm">
                <h3 class="text-[12px] font-black text-popfit-dark uppercase mb-8 tracking-[0.2em] border-b border-popfit-border pb-4">GRAFIK AKTIVITAS TAHUN <?= date('Y') ?></h3>
                <div class="h-80">
                    <canvas id="peminjamanChart"></canvas>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Sidebar Toggle
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebarOverlay');
        const openBtn = document.getElementById('openSidebar');
        const closeBtn = document.getElementById('closeSidebar');

        function toggleSidebar() {
            sidebar.classList.toggle('-translate-x-full');
            overlay.classList.toggle('hidden');
        }

        openBtn.addEventListener('click', toggleSidebar);
        closeBtn.addEventListener('click', toggleSidebar);
        overlay.addEventListener('click', toggleSidebar);

        new Chart(document.getElementById('peminjamanChart'),{
            type:'bar',
            data:{
                labels:<?= json_encode($bulan) ?>,
                datasets:[{
                    label: 'Peminjaman',
                    data:<?= json_encode($total) ?>,
                    backgroundColor:'#2A4736',
                    hoverBackgroundColor: '#F5C460',
                    borderRadius: 0
                }]
            },
            options:{
                responsive:true,
                maintainAspectRatio: false,
                plugins:{legend:{display:false}},
                scales:{
                    y:{beginAtZero:true, grid:{display:true, color:'#F4F4F5'}},
                    x:{grid:{display:false}}
                }
            }
        });
        const badge = document.getElementById("notifBadge");
        function loadNotif(){
            fetch("notif.php", { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(res => res.json())
            .then(data => {
                if(data && data.unread > 0){
                    badge.classList.remove('hidden');
                    badge.textContent = data.unread;
                } else {
                    badge.classList.add('hidden');
                }
            });
        }
        loadNotif();
        setInterval(loadNotif, 15000);
    </script>
</body>
</html>