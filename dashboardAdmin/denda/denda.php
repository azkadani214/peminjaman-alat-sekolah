<?php
require '../../config/config.php';
session_start();

if (!isset($_SESSION["login"]) || !in_array($_SESSION["role"], ["admin utama", "admin", "petugas"])) {
    header("Location: ../../login.php");
    exit;
}

$adminName = htmlspecialchars($_SESSION['nama'] ?? 'Admin');
$adminUsername = htmlspecialchars($_SESSION['username'] ?? 'username');

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
    <title>Denda Peminjaman - PopFit</title>
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
        $activeIndex = "denda"; 
        include '../../layout/sidebar_admin.php'; 
    ?>

    <div class="flex-1 flex flex-col h-screen w-full relative">
        <?php 
            $pageTitle = "Denda Peminjaman"; 
            include '../../layout/header_admin.php'; 
        ?>

        <main class="flex-1 overflow-y-auto p-6">
            <div class="bg-white border border-popfit-border rounded-sm overflow-hidden">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-popfit-bg border-b border-popfit-border">
                            <th class="px-6 py-4 text-[10px] font-black text-popfit-textMuted uppercase tracking-widest">Waktu</th>
                            <th class="px-6 py-4 text-[10px] font-black text-popfit-textMuted uppercase tracking-widest">Siswa</th>
                            <th class="px-6 py-4 text-[10px] font-black text-popfit-textMuted uppercase tracking-widest">Denda</th>
                            <th class="px-6 py-4 text-[10px] font-black text-popfit-textMuted uppercase tracking-widest">Status</th>
                            <th class="px-6 py-4 text-[10px] font-black text-popfit-textMuted uppercase tracking-widest text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <?php while($row = mysqli_fetch_assoc($result)): 
                             $stColor = '';
                             if ($row['pembayaran'] == 'lunas') {
                                 $stColor = 'bg-green-50 text-green-600';
                             } elseif ($row['pembayaran'] == 'pending') {
                                 $stColor = 'bg-popfit-accent/20 text-popfit-dark border border-popfit-accent/30';
                             } else {
                                 $stColor = 'bg-red-50 text-red-600';
                             }
                        ?>
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4">
                                <p class="text-[11px] font-black text-popfit-dark"><?= date('d.m.Y', strtotime($row['waktu_pinjam'])) ?></p>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-[12px] font-black text-popfit-dark uppercase tracking-tight"><?= htmlspecialchars($row['nama']) ?></p>
                                <p class="text-[10px] font-bold text-popfit-textMuted tracking-tighter uppercase">#<?= $row['nis'] ?> • <?= $row['kelas'] ?></p>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm font-black text-red-600">Rp <?= number_format($row['denda'], 0, ',', '.') ?></p>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 rounded-sm text-[9px] font-black uppercase tracking-tighter <?= $stColor ?>"><?= $row['pembayaran'] ?: 'BELUM BAYAR' ?></span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <a href="detailDenda.php?id=<?= $row['id_transaksi'] ?>" class="text-[10px] font-black uppercase tracking-widest bg-popfit-dark text-white px-3 py-1.5 rounded-sm hover:bg-popfit-light transition-all flex items-center justify-end">
                                    Detail <i class="ph-bold ph-caret-right ml-2"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                        <?php if(mysqli_num_rows($result) == 0): ?>
                        <tr><td colspan="5" class="p-12 text-center text-popfit-textMuted uppercase font-black text-xs tracking-widest">Tidak ada data denda</td></tr>
                        <?php endif; ?>
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
