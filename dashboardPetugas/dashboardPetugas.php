<?php
require '../config/config.php';
session_start();

if (!isset($_SESSION["login"]) || $_SESSION["role"] != "petugas") {
    header("Location: ../login.php");
    exit;
}

$petugasName = htmlspecialchars($_SESSION['nama'] ?? 'Petugas');
$petugasUsername = htmlspecialchars($_SESSION['username'] ?? 'username');

$jumlahSiswa = mysqli_fetch_assoc(mysqli_query($connect, "SELECT COUNT(*) AS total FROM users WHERE role = 'siswa'"))['total'];
$jumlahAlat = mysqli_fetch_assoc(mysqli_query($connect, "SELECT COUNT(*) AS total FROM alat_olahraga"))['total'];
$peminjamanAktif = mysqli_fetch_assoc(mysqli_query($connect, "SELECT COUNT(*) AS total FROM transaksi WHERE status = 'dipinjam'"))['total'];
$pengajuanPinjam = mysqli_fetch_assoc(mysqli_query($connect, "SELECT COUNT(*) AS total FROM transaksi WHERE status = 'menunggu'"))['total'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Dashboard - PopFit</title>
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
        $rel = "./"; 
        $activeIndex = "dashboard"; 
        include '../layout/sidebar_petugas.php'; 
    ?>

    <div class="flex-1 flex flex-col h-screen w-full relative">
        <?php 
            $pageTitle = "Staff Control"; 
            include '../layout/header_petugas.php'; 
        ?>

        <main class="flex-1 overflow-y-auto p-6">
            <div class="bg-popfit-dark p-8 rounded-sm text-white mb-8 border-l-4 border-popfit-accent">
                <h1 class="text-3xl font-black tracking-tighter uppercase leading-none mb-2">HALO, <?= $petugasName ?>!</h1>
                <p class="text-popfit-accent/80 text-[10px] font-black uppercase tracking-[0.2em]">Petugas Operasional • PopFit Dashboard</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
                <div class="bg-white border border-popfit-border p-5 rounded-sm">
                    <p class="text-[10px] font-black text-popfit-textMuted uppercase mb-1 tracking-widest">KATALOG ALAT</p>
                    <h3 class="text-2xl font-black text-popfit-dark"><?= $jumlahAlat ?> UNIT</h3>
                </div>
                <!-- ... other stats ... -->
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
