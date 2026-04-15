<?php
require '../../config/config.php';
session_start();

if (!isset($_SESSION["login"]) || ($_SESSION["role"] != "admin utama" && $_SESSION["role"] != "admin")) {
    header("Location: ../../login.php");
    exit;
}

$adminName = htmlspecialchars($_SESSION['nama'] ?? 'Admin');
$adminUsername = htmlspecialchars($_SESSION['username'] ?? 'username');

/* AJAX HANDLER */
if(isset($_GET['ajax'])){
    $keyword = mysqli_real_escape_string($connect, $_GET['keyword'] ?? '');
    $query = "SELECT * FROM users WHERE role = 'petugas'";
    if($keyword !== '') {
        $query .= " AND (nama LIKE '%$keyword%' OR username LIKE '%$keyword%')";
    }
    $query .= " ORDER BY id_user DESC";
    $data = mysqli_query($connect, $query);

    if(mysqli_num_rows($data) > 0){
        while($row = mysqli_fetch_assoc($data)){
            echo "
            <tr class='hover:bg-gray-50 transition-colors'>
                <td class='px-6 py-4'>
                    <p class='text-[12px] font-black text-popfit-dark uppercase tracking-tight'>" . htmlspecialchars($row['nama']) . "</p>
                    <p class='text-[10px] font-bold text-popfit-textMuted lowercase tracking-widest'>@" . htmlspecialchars($row['username']) . "</p>
                </td>
                <td class='px-6 py-4 hidden md:table-cell text-[11px] font-bold text-popfit-dark'>" . htmlspecialchars($row['no_telp'] ?? '-') . "</td>
                <td class='px-6 py-4 text-right'>
                    <div class='flex items-center justify-end space-x-2'>
                        <a href='editPetugas.php?id=" . $row['id_user'] . "' class='w-8 h-8 flex items-center justify-center bg-white border border-popfit-border text-popfit-dark rounded-sm hover:bg-popfit-bg transition-colors' title='Edit'><i class='ph ph-pencil-simple-bold'></i></a>
                        <a href='?hapus=" . $row['id_user'] . "' class='btn-hapus w-8 h-8 flex items-center justify-center bg-white border border-popfit-border text-red-500 rounded-sm hover:bg-red-50 transition-colors' title='Hapus'><i class='ph ph-trash-bold'></i></a>
                    </div>
                </td>
            </tr>";
        }
    } else {
        echo "<tr><td colspan='3' class='p-12 text-center text-popfit-textMuted uppercase font-black text-xs tracking-widest'>Petugas tidak ditemukan</td></tr>";
    }
    exit;
}

if(isset($_GET['hapus'])){
    $id = mysqli_real_escape_string($connect, $_GET['hapus']);
    mysqli_query($connect, "DELETE FROM users WHERE id_user = '$id' AND role = 'petugas'");
    header("Location: petugas.php?msg=hapus_success");
    exit;
}

$dataPetugas = mysqli_query($connect, "SELECT * FROM users WHERE role = 'petugas' ORDER BY id_user DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Petugas - PopFit Admin</title>
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
                <li><a href="../dashboardAdmin.php" class="flex items-center px-6 py-3 text-gray-200 hover:bg-popfit-light transition-colors border-l-4 border-transparent">
                    <i class="ph ph-squares-four text-xl w-6"></i><span class="ml-3 font-bold">Dashboard</span>
                </a></li>
                <li class="px-6 py-2 mt-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Manajemen</li>
                <li><a href="../alat/daftarAlat.php" class="flex items-center px-6 py-3 text-gray-200 hover:bg-popfit-light transition-colors border-l-4 border-transparent">
                    <i class="ph ph-basketball text-xl w-6"></i><span class="ml-3 font-bold">Katalog Alat</span>
                </a></li>
                <li><a href="petugas.php" class="nav-active flex items-center px-6 py-3 text-gray-200 hover:bg-popfit-light transition-colors border-l-4 border-transparent">
                    <i class="ph ph-user-tie text-xl w-6"></i><span class="ml-3 font-bold">Petugas</span>
                </a></li>
                <li><a href="../siswa/siswa.php" class="flex items-center px-6 py-3 text-gray-200 hover:bg-popfit-light transition-colors border-l-4 border-transparent">
                    <i class="ph ph-users text-xl w-6"></i><span class="ml-3 font-bold">Siswa</span>
                </a></li>
                <li class="px-6 py-2 mt-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Sirkulasi</li>
                <li><a href="../transaksi/transaksi.php" class="flex items-center px-6 py-3 text-gray-200 hover:bg-popfit-light transition-colors border-l-4 border-transparent">
                    <i class="ph ph-arrows-left-right text-xl w-6"></i><span class="ml-3 font-bold">Transaksi</span>
                </a></li>
                <li><a href="../denda/denda.php" class="flex items-center px-6 py-3 text-gray-200 hover:bg-popfit-light transition-colors border-l-4 border-transparent">
                    <i class="ph ph-wallet text-xl w-6"></i><span class="ml-3 font-bold">Denda</span>
                </a></li>
                <li><a href="../laporan/laporan.php" class="flex items-center px-6 py-3 text-gray-200 hover:bg-popfit-light transition-colors border-l-4 border-transparent">
                    <i class="ph ph-file-text text-xl w-6"></i><span class="ml-3 font-bold">Laporan</span>
                </a></li>
                <li><a href="../log/log.php" class="flex items-center px-6 py-3 text-gray-200 hover:bg-popfit-light transition-colors border-l-4 border-transparent">
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
                <a href="../../logout.php" class="text-gray-400 hover:text-white transition-colors"><i class="ph ph-sign-out text-xl"></i></a>
            </div>
        </div>
    </aside>

    <div class="flex-1 flex flex-col h-screen w-full relative">
        <header class="h-16 bg-popfit-surface border-b border-popfit-border flex items-center justify-between px-6 flex-shrink-0">
            <div class="flex items-center">
                <button id="openSidebar" class="md:hidden mr-4 text-popfit-dark"><i class="ph ph-list text-2xl"></i></button>
                <h2 class="text-lg font-black text-popfit-dark uppercase tracking-tight">Kelola Petugas</h2>
            </div>
            <a href="tambahPetugas.php" class="bg-popfit-dark text-white px-4 py-2 text-[10px] font-black uppercase tracking-[0.1em] rounded-sm hover:bg-popfit-light transition-all flex items-center">
                <i class="ph-bold ph-user-plus mr-2"></i> Tambah Petugas
            </a>
        </header>

        <main class="flex-1 overflow-y-auto p-6">
            <div class="mb-6 bg-white border border-popfit-border rounded-sm p-4 flex items-center">
                <i class="ph ph-magnifying-glass text-popfit-textMuted mr-3 text-xl"></i>
                <input type="text" id="searchInput" placeholder="Cari Nama atau Username Petugas..." class="flex-1 bg-transparent border-none outline-none text-xs font-bold text-popfit-dark">
            </div>

            <div class="bg-white border border-popfit-border rounded-sm overflow-hidden">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-popfit-bg border-b border-popfit-border">
                            <th class="px-6 py-4 text-[10px] font-black text-popfit-textMuted uppercase tracking-widest">Informasi Petugas</th>
                            <th class="px-6 py-4 text-[10px] font-black text-popfit-textMuted uppercase tracking-widest hidden md:table-cell">No Telp</th>
                            <th class="px-6 py-4 text-[10px] font-black text-popfit-textMuted uppercase tracking-widest text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="petugasTable" class="divide-y divide-gray-50">
                        <?php while($row = mysqli_fetch_assoc($dataPetugas)): ?>
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4">
                                <p class="text-[12px] font-black text-popfit-dark uppercase tracking-tight"><?= htmlspecialchars($row['nama']) ?></p>
                                <p class="text-[10px] font-bold text-popfit-textMuted lowercase tracking-widest">@<?= htmlspecialchars($row['username']) ?></p>
                            </td>
                            <td class="px-6 py-4 hidden md:table-cell text-[11px] font-bold text-popfit-dark"><?= htmlspecialchars($row['no_telp'] ?? '-') ?></td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end space-x-2">
                                    <a href="editPetugas.php?id=<?= $row['id_user'] ?>" class="w-8 h-8 flex items-center justify-center bg-white border border-popfit-border text-popfit-dark rounded-sm hover:bg-popfit-bg transition-colors" title="Edit"><i class="ph-bold ph-pencil-simple"></i></a>
                                    <a href="?hapus=<?= $row['id_user'] ?>" class="btn-hapus w-8 h-8 flex items-center justify-center bg-white border border-popfit-border text-red-500 rounded-sm hover:bg-red-50 transition-colors" title="Hapus"><i class="ph-bold ph-trash"></i></a>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebarOverlay');
        const openBtn = document.getElementById('openSidebar');
        function toggleSidebar() { sidebar.classList.toggle('-translate-x-full'); overlay.classList.toggle('hidden'); }
        openBtn.addEventListener('click', toggleSidebar);
        overlay.addEventListener('click', toggleSidebar);

        document.getElementById('searchInput').addEventListener('input', function() {
            fetch(`petugas.php?ajax=1&keyword=${this.value}`)
            .then(res => res.text()).then(data => document.getElementById('petugasTable').innerHTML = data);
        });

        document.querySelectorAll('.btn-hapus').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const link = this.href;
                Swal.fire({
                    title: 'HAPUS PETUGAS?',
                    text: "Aksi ini tidak dapat dibatalkan!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#2A4736',
                    cancelButtonColor: '#E4E4E7',
                    confirmButtonText: 'YA, HAPUS!',
                    cancelButtonText: 'BATAL'
                }).then((result) => { if (result.isConfirmed) window.location.href = link; });
            });
        });
    </script>
</body>
</html>