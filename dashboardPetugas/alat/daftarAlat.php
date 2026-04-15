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

/* AJAX HANDLER FOR SEARCH */
if(isset($_GET['ajax'])){
    $keyword = mysqli_real_escape_string($connect, $_GET['keyword'] ?? '');
    $where = $keyword != '' ? "WHERE nama_alat_olahraga LIKE '%$keyword%' OR kategori LIKE '%$keyword%'" : "";
    
    $data = mysqli_query($connect, "SELECT * FROM alat_olahraga $where ORDER BY id_alat_olahraga DESC");
    
    if(mysqli_num_rows($data) > 0){
        while($row = mysqli_fetch_assoc($data)){
            $foto = (!empty($row['foto_alat_olahraga'])) ? $row['foto_alat_olahraga'] : 'default.png';
            echo "
            <tr class='hover:bg-gray-50 transition-colors'>
                <td class='px-6 py-4'>
                    <div class='flex items-center space-x-3'>
                        <div class='w-10 h-10 bg-white border border-popfit-border rounded-sm p-1'><img src='../../asset/$foto' class='w-full h-full object-contain'></div>
                        <div>
                            <p class='text-[11px] font-black text-popfit-dark uppercase tracking-tight'>".htmlspecialchars($row['nama_alat_olahraga'])."</p>
                            <p class='text-[9px] font-bold text-popfit-textMuted uppercase'>".htmlspecialchars($row['kategori'])."</p>
                        </div>
                    </div>
                </td>
                <td class='px-6 py-4 text-[10px] font-black uppercase text-popfit-dark'>#".htmlspecialchars($row['id_alat_olahraga'])."</td>
                <td class='px-6 py-4'><span class='text-[10px] font-black uppercase text-popfit-light bg-popfit-bg px-2 py-0.5 rounded-sm border border-popfit-border'>".$row['stok']." UNIT</span></td>
                <td class='px-6 py-4 text-right'>
                    <a href='editAlat.php?id=".$row['id_alat_olahraga']."' class='w-8 h-8 inline-flex items-center justify-center bg-white border border-popfit-border text-popfit-dark rounded-sm hover:bg-popfit-bg transition-all'><i class='ph ph-pencil-bold'></i></a>
                </td>
            </tr>";
        }
    } else {
        echo "<tr><td colspan='4' class='p-12 text-center text-popfit-textMuted uppercase font-black text-xs tracking-widest'>Alat tidak ditemukan</td></tr>";
    }
    exit;
}

$dataAlat = mysqli_query($connect, "SELECT * FROM alat_olahraga ORDER BY id_alat_olahraga DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stok Alat - PopFit Staff</title>
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
                <span class="text-xl font-black tracking-wide uppercase">PopFit Staff</span>
            </div>
            <button id="closeSidebar" class="md:hidden text-gray-400 hover:text-white"><i class="ph ph-x text-2xl"></i></button>
        </div>

        <nav class="flex-1 overflow-y-auto py-4">
            <ul class="space-y-1">
                <li><a href="../dashboardPetugas.php" class="flex items-center px-6 py-3 text-gray-200 hover:bg-popfit-light transition-colors border-l-4 border-transparent">
                    <i class="ph ph-squares-four text-xl w-6"></i><span class="ml-3 font-bold">Dashboard</span>
                </a></li>
                <li class="px-6 py-2 mt-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Operasional</li>
                <li><a href="daftarAlat.php" class="nav-active flex items-center px-6 py-3 text-gray-200 hover:bg-popfit-light transition-colors border-l-4 border-transparent">
                    <i class="ph ph-basketball text-xl w-6"></i><span class="ml-3 font-bold">Katalog Alat</span>
                </a></li>
                <li><a href="../transaksi/transaksi.php" class="flex items-center px-6 py-3 text-gray-200 hover:bg-popfit-light transition-colors border-l-4 border-transparent">
                    <i class="ph ph-arrows-left-right text-xl w-6"></i><span class="ml-3 font-bold">Transaksi</span>
                </a></li>
                <li><a href="../denda/denda.php" class="flex items-center px-6 py-3 text-gray-200 hover:bg-popfit-light transition-colors border-l-4 border-transparent">
                    <i class="ph ph-wallet text-xl w-6"></i><span class="ml-3 font-bold">Denda</span>
                </a></li>
                <li><a href="../notif.php" class="flex items-center px-6 py-3 text-gray-200 hover:bg-popfit-light transition-colors border-l-4 border-transparent">
                    <i class="ph ph-bell text-xl w-6"></i><span class="ml-3 font-bold">Notifikasi</span>
                </a></li>
            </ul>
        </nav>

        <div class="border-t border-popfit-light p-4">
            <div class="flex items-center w-full">
                <div class="w-8 h-8 rounded-sm bg-popfit-accent flex items-center justify-center text-popfit-dark font-black">P</div>
                <div class="ml-3 flex-1 overflow-hidden">
                    <p class="text-[12px] font-black text-white truncate uppercase"><?= $petugasName ?></p>
                    <p class="text-[10px] text-gray-400 truncate uppercase">Petugas</p>
                </div>
                <a href="../../logout.php" class="text-gray-400 hover:text-white transition-colors"><i class="ph ph-sign-out text-xl"></i></a>
            </div>
        </div>
    </aside>

    <div class="flex-1 flex flex-col h-screen w-full relative">
        <header class="h-16 bg-popfit-surface border-b border-popfit-border flex items-center justify-between px-6 flex-shrink-0">
            <div class="flex items-center space-x-4">
                <button id="openSidebar" class="md:hidden text-popfit-dark"><i class="ph ph-list text-2xl"></i></button>
                <h2 class="text-lg font-black text-popfit-dark uppercase tracking-tight">Stok Alat</h2>
            </div>
        </header>

        <main class="flex-1 overflow-y-auto p-6">
            <div class="bg-white border border-popfit-border rounded-sm p-4 mb-6 flex items-center">
                <i class="ph ph-magnifying-glass text-popfit-textMuted mr-3 text-xl"></i>
                <input type="text" id="searchInput" placeholder="Cari Kode atau Nama Alat..." class="flex-1 bg-transparent border-none outline-none font-bold text-xs text-popfit-dark uppercase">
            </div>

            <div class="bg-white border border-popfit-border rounded-sm overflow-hidden text-[11px]">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-popfit-bg border-b border-popfit-border">
                            <th class="px-6 py-4 font-black text-popfit-textMuted uppercase tracking-widest">Informasi Alat</th>
                            <th class="px-6 py-4 font-black text-popfit-textMuted uppercase tracking-widest">KODE</th>
                            <th class="px-6 py-4 font-black text-popfit-textMuted uppercase tracking-widest">Stok</th>
                            <th class="px-6 py-4 font-black text-popfit-textMuted uppercase tracking-widest text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="alatTable" class="divide-y divide-gray-50">
                        <?php while($row = mysqli_fetch_assoc($dataAlat)): 
                            $foto = (!empty($row['foto_alat_olahraga'])) ? $row['foto_alat_olahraga'] : 'default.png';
                        ?>
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center space-x-3">
                                    <div class="w-10 h-10 bg-white border border-popfit-border rounded-sm p-1"><img src="../../asset/<?= $foto ?>" class="w-full h-full object-contain"></div>
                                    <div>
                                        <p class="text-[11px] font-black text-popfit-dark uppercase tracking-tight"><?= htmlspecialchars($row['nama_alat_olahraga']) ?></p>
                                        <p class="text-[9px] font-bold text-popfit-textMuted uppercase"><?= htmlspecialchars($row['kategori']) ?></p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-[10px] font-black uppercase text-popfit-dark">#<?= htmlspecialchars($row['id_alat_olahraga']) ?></td>
                            <td class="px-6 py-4"><span class="text-[10px] font-black uppercase text-popfit-light bg-popfit-bg px-2 py-0.5 rounded-sm border border-popfit-border"><?= $row['stok'] ?> UNIT</span></td>
                            <td class="px-6 py-4 text-right">
                                <a href="editAlat.php?id=<?= $row['id_alat_olahraga'] ?>" class="w-8 h-8 inline-flex items-center justify-center bg-white border border-popfit-border text-popfit-dark rounded-sm hover:bg-popfit-bg transition-all"><i class="ph ph-pencil-bold"></i></a>
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
        function toggleSidebar() { sidebar.classList.toggle('-translate-x-full'); overlay.classList.toggle('hidden'); }
        openBtn.addEventListener('click', toggleSidebar);
        overlay.addEventListener('click', toggleSidebar);

        document.getElementById('searchInput').addEventListener('input', function() {
            fetch(`daftarAlat.php?ajax=1&keyword=${this.value}`)
            .then(res => res.text()).then(data => document.getElementById('alatTable').innerHTML = data);
        });
    </script>
</body>
</html>
