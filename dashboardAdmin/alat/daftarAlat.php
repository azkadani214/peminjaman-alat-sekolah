<?php
require '../../config/config.php';
session_start();

if (!isset($_SESSION["login"]) || ($_SESSION["role"] != "admin utama" && $_SESSION["role"] != "admin")) {
    header("Location: ../../login.php");
    exit;
}

$adminName = htmlspecialchars($_SESSION['nama'] ?? 'Admin');
$adminUsername = htmlspecialchars($_SESSION['username'] ?? 'username');

// Filter & Search
$keyword = $_GET['keyword'] ?? '';
$kategori = $_GET['kategori'] ?? 'semua';

$query = "SELECT * FROM alat_olahraga WHERE 1=1";
if($kategori !== 'semua') $query .= " AND kategori = '$kategori'";
if($keyword !== '') $query .= " AND (nama_alat_olahraga LIKE '%$keyword%' OR id_alat_olahraga LIKE '%$keyword%')";
$query .= " ORDER BY id_alat_olahraga DESC";

$data = mysqli_query($connect, $query);
$kategori_res = mysqli_query($connect, "SELECT * FROM kategori_alat_olahraga");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Katalog Alat - PopFit Admin</title>
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

    <!-- SIDEBAR -->
    <?php 
        $rel = "../"; 
        $activeIndex = "alat"; 
        include '../../layout/sidebar_admin.php'; 
    ?>

    <!-- MAIN CONTENT -->
    <div class="flex-1 flex flex-col h-screen w-full relative">
        <header class="h-16 bg-popfit-surface border-b border-popfit-border flex items-center justify-between px-6 flex-shrink-0">
            <div class="flex items-center">
                <button id="openSidebar" class="md:hidden mr-4 text-popfit-dark"><i class="ph ph-list text-2xl"></i></button>
                <h2 class="text-lg font-black text-popfit-dark uppercase tracking-tight">Katalog Alat</h2>
            </div>
            <a href="tambahAlat.php" class="bg-popfit-dark text-white px-4 py-2 text-[10px] font-black uppercase tracking-[0.1em] rounded-sm hover:bg-popfit-light transition-all flex items-center">
                <i class="ph ph-plus-bold mr-2"></i> Tambah Alat
            </a>
        </header>

        <main class="flex-1 overflow-y-auto p-6">
            <!-- Filter Bar -->
            <div class="bg-white border border-popfit-border p-6 rounded-sm mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
                <form class="flex-1 flex flex-col md:flex-row items-center gap-4">
                    <div class="relative w-full md:w-64">
                        <i class="ph ph-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <input type="text" name="keyword" value="<?= htmlspecialchars($keyword) ?>" placeholder="Cari nama atau ID alat..." 
                               class="w-full bg-popfit-bg border border-popfit-border rounded-sm pl-10 pr-4 py-2 text-[11px] font-bold text-popfit-dark focus:border-popfit-dark outline-none transition-colors">
                    </div>
                    <select name="kategori" class="w-full md:w-48 bg-popfit-bg border border-popfit-border rounded-sm px-4 py-2 text-[11px] font-bold text-popfit-dark focus:border-popfit-dark outline-none transition-colors appearance-none">
                        <option value="semua">Semua Kategori</option>
                        <?php while($k = mysqli_fetch_assoc($kategori_res)): ?>
                            <option value="<?= $k['kategori'] ?>" <?= ($kategori == $k['kategori'] ? 'selected' : '') ?>><?= $k['kategori'] ?></option>
                        <?php endwhile; ?>
                    </select>
                    <button type="submit" class="w-full md:w-auto px-6 py-2 bg-popfit-bg text-popfit-dark border border-popfit-border rounded-sm text-[10px] font-black uppercase tracking-widest hover:bg-popfit-dark hover:text-white transition-all">Filter</button>
                </form>
            </div>

            <!-- Table View -->
            <div class="bg-white border border-popfit-border rounded-sm overflow-hidden">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-popfit-bg border-b border-popfit-border">
                            <th class="px-6 py-4 text-[10px] font-black text-popfit-textMuted uppercase tracking-widest">Alat</th>
                            <th class="px-6 py-4 text-[10px] font-black text-popfit-textMuted uppercase tracking-widest hidden md:table-cell">Kategori</th>
                            <th class="px-6 py-4 text-[10px] font-black text-popfit-textMuted uppercase tracking-widest text-center">Stok</th>
                            <th class="px-6 py-4 text-[10px] font-black text-popfit-textMuted uppercase tracking-widest text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <?php if(mysqli_num_rows($data) > 0): while($row = mysqli_fetch_assoc($data)): 
                            $foto = (!empty($row['foto_alat_olahraga'])) ? $row['foto_alat_olahraga'] : 'default.png';
                        ?>
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <div class="w-12 h-12 bg-popfit-bg border border-popfit-border rounded-sm overflow-hidden flex-shrink-0 flex items-center justify-center mr-4">
                                        <img src="../../asset/<?= htmlspecialchars($foto) ?>" class="w-full h-full object-cover">
                                    </div>
                                    <div>
                                        <p class="text-[12px] font-black text-popfit-dark uppercase tracking-tight"><?= htmlspecialchars($row['nama_alat_olahraga']) ?></p>
                                        <p class="text-[10px] font-bold text-popfit-textMuted uppercase mt-0.5 tracking-tighter">ID: #<?= htmlspecialchars($row['id_alat_olahraga']) ?></p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 hidden md:table-cell">
                                <span class="text-[10px] font-black uppercase text-popfit-textMuted bg-popfit-bg px-2 py-1 rounded-sm border border-popfit-border"><?= htmlspecialchars($row['kategori']) ?></span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="text-[12px] font-black <?= ($row['stok'] > 0 ? 'text-popfit-dark' : 'text-red-500') ?>"><?= $row['stok'] ?></span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end space-x-2">
                                    <a href="editAlat.php?id=<?= $row['id_alat_olahraga'] ?>" class="w-8 h-8 flex items-center justify-center bg-white border border-popfit-border text-popfit-dark rounded-sm hover:bg-popfit-bg transition-colors" title="Edit"><i class="ph-bold ph-pencil-simple"></i></a>
                                    <a href="hapusAlat.php?id=<?= $row['id_alat_olahraga'] ?>" class="btn-hapus w-8 h-8 flex items-center justify-center bg-white border border-popfit-border text-red-500 rounded-sm hover:bg-red-50 transition-colors" title="Hapus"><i class="ph-bold ph-trash"></i></a>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; else: ?>
                        <tr><td colspan="4" class="p-12 text-center text-popfit-textMuted uppercase font-black text-xs tracking-widest">Data tidak ditemukan</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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

        // SweetAlert Delete
        document.querySelectorAll('.btn-hapus').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const link = this.href;
                Swal.fire({
                    title: 'HAPUS ALAT?',
                    text: "Data yang dihapus tidak dapat dikembalikan!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#2A4736',
                    cancelButtonColor: '#E4E4E7',
                    confirmButtonText: 'YA, HAPUS!',
                    cancelButtonText: 'BATAL',
                    customClass: { popup: 'rounded-sm' }
                }).then((result) => {
                    if (result.isConfirmed) window.location.href = link;
                });
            });
        });
    </script>
</body>
</html>