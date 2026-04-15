<?php
require '../../config/config.php';
session_start();

if (!isset($_SESSION["login"]) || $_SESSION["role"] != "petugas") {
    header("Location: ../../login.php");
    exit;
}

$idPetugas = $_SESSION["id_user"];
$petugasName = htmlspecialchars($_SESSION['nama'] ?? 'Staff');
$petugasUsername = htmlspecialchars($_SESSION['username'] ?? 'username');

$query = "SELECT t.*, u.nama, u.nis, u.kelas 
          FROM transaksi t
          JOIN users u ON t.id_user = u.id_user
          WHERE t.denda > 0
          ORDER BY t.pembayaran ASC, t.waktu_pinjam DESC";
$result = mysqli_query($connect, $query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Denda - PopFit Staff</title>
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
        include '../../layout/sidebar_petugas.php'; 
    ?>

    <div class="flex-1 flex flex-col h-screen w-full relative">
        <header class="h-16 bg-popfit-surface border-b border-popfit-border flex items-center justify-between px-6 flex-shrink-0">
            <div class="flex items-center space-x-4">
                <button id="openSidebar" class="md:hidden text-popfit-dark"><i class="ph ph-list text-2xl"></i></button>
                <h2 class="text-lg font-black text-popfit-dark uppercase tracking-tight">Kelola Denda</h2>
            </div>
        </header>

        <main class="flex-1 overflow-y-auto p-6">
            <div class="bg-white border border-popfit-border rounded-sm overflow-hidden text-[11px]">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-popfit-bg border-b border-popfit-border">
                            <th class="px-6 py-4 font-black text-popfit-textMuted uppercase tracking-widest">Waktu Pinjam</th>
                            <th class="px-6 py-4 font-black text-popfit-textMuted uppercase tracking-widest">Siswa</th>
                            <th class="px-6 py-4 font-black text-popfit-textMuted uppercase tracking-widest">Total Denda</th>
                            <th class="px-6 py-4 font-black text-popfit-textMuted uppercase tracking-widest text-center">Status</th>
                            <th class="px-6 py-4 font-black text-popfit-textMuted uppercase tracking-widest text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <?php while($row = mysqli_fetch_assoc($result)): 
                             $isLunas = ($row['pembayaran'] == 'lunas');
                        ?>
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 font-bold text-popfit-textMuted"><?= date('d M Y', strtotime($row['waktu_pinjam'])) ?></td>
                            <td class="px-6 py-4">
                                <p class="font-black text-popfit-dark uppercase tracking-tight"><?= htmlspecialchars($row['nama']) ?></p>
                                <p class="text-[9px] font-bold text-popfit-textMuted uppercase">#<?= $row['nis'] ?></p>
                            </td>
                            <td class="px-6 py-4 font-black text-red-600">Rp <?= number_format($row['denda'], 0, ',', '.') ?></td>
                            <td class="px-6 py-4 text-center">
                                <?php
                                    $stColor = '';
                                    if($row['pembayaran'] == 'lunas') {
                                        $stColor = 'bg-popfit-dark text-white';
                                    } elseif($row['pembayaran'] == 'pending') {
                                        $stColor = 'bg-popfit-accent text-popfit-dark border border-popfit-accent/30';
                                    } else {
                                        $stColor = 'bg-red-50 text-red-600 border border-red-100';
                                    }
                                ?>
                                <span class="px-2 py-0.5 rounded-sm text-[8px] font-black uppercase tracking-widest <?= $stColor ?>">
                                    <?= $row['pembayaran'] ?: 'BELUM BAYAR' ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <a href="detailDenda.php?id=<?= $row['id_transaksi'] ?>" class="text-[9px] font-black uppercase tracking-widest bg-popfit-bg text-popfit-dark px-3 py-1.5 rounded-sm hover:bg-popfit-dark hover:text-white transition-all border border-popfit-border">Rincian</a>
                            </td>
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
