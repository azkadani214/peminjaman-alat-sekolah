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
    $query = "SELECT * FROM users WHERE role = 'siswa'";
    if($keyword !== '') {
        $query .= " AND (nis LIKE '%$keyword%' OR nama LIKE '%$keyword%' OR username LIKE '%$keyword%')";
    }
    $query .= " ORDER BY id_user DESC";
    $data = mysqli_query($connect, $query);

    if(mysqli_num_rows($data) > 0){
        while($row = mysqli_fetch_assoc($data)){
            echo "
            <tr class='hover:bg-gray-50 transition-colors'>
                <td class='px-6 py-4 text-[10px] font-black text-popfit-dark uppercase tracking-widest'>#" . htmlspecialchars($row['nis']) . "</td>
                <td class='px-6 py-4'>
                    <p class='text-[12px] font-black text-popfit-dark uppercase tracking-tight'>" . htmlspecialchars($row['nama']) . "</p>
                    <p class='text-[10px] font-bold text-popfit-textMuted lowercase tracking-widest'>@" . htmlspecialchars($row['username']) . "</p>
                </td>
                <td class='px-6 py-4 hidden md:table-cell'><span class='text-[10px] font-black uppercase text-popfit-textMuted bg-popfit-bg px-2 py-1 rounded-sm border border-popfit-border'>" . htmlspecialchars($row['kelas'] ?? '-') . "</span></td>
                <td class='px-6 py-4 hidden md:table-cell text-[11px] font-bold text-popfit-dark'>" . htmlspecialchars($row['no_telp'] ?? '-') . "</td>
                <td class='px-6 py-4 text-right'>
                    <div class='flex items-center justify-end space-x-2'>
                        <a href='editSiswa.php?id=" . $row['nis'] . "' class='w-8 h-8 flex items-center justify-center bg-white border border-popfit-border text-popfit-dark rounded-sm hover:bg-popfit-bg transition-colors' title='Edit'><i class='ph ph-pencil-simple-bold'></i></a>
                        <a href='?hapus=" . $row['nis'] . "' class='btn-hapus w-8 h-8 flex items-center justify-center bg-white border border-popfit-border text-red-500 rounded-sm hover:bg-red-50 transition-colors' title='Hapus' data-name='" . htmlspecialchars($row['nama']) . "'><i class='ph ph-trash-bold'></i></a>
                    </div>
                </td>
            </tr>";
        }
    } else {
        echo "<tr><td colspan='5' class='p-12 text-center text-popfit-textMuted uppercase font-black text-xs tracking-widest'>Siswa tidak ditemukan</td></tr>";
    }
    exit;
}

if(isset($_GET['hapus'])){
    $nis = mysqli_real_escape_string($connect, $_GET['hapus']);
    mysqli_query($connect, "DELETE FROM users WHERE nis = '$nis' AND role = 'siswa'");
    header("Location: siswa.php?msg=hapus_success");
    exit;
}

$dataSiswa = mysqli_query($connect, "SELECT * FROM users WHERE role = 'siswa' ORDER BY id_user DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Siswa - PopFit Admin</title>
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
        $activeIndex = "siswa"; 
        include '../../layout/sidebar_admin.php'; 
    ?>

    <div class="flex-1 flex flex-col h-screen w-full relative">
        <?php 
            $pageTitle = "Manajemen Siswa"; 
            include '../../layout/header_admin.php'; 
        ?>

        <!-- Sub Header with Action -->
        <div class="px-6 py-4 bg-white border-b border-popfit-border flex items-center justify-between">
            <div class="flex-1 max-w-md bg-popfit-bg border border-popfit-border rounded-sm px-4 py-2 flex items-center">
                <i class="ph ph-magnifying-glass text-popfit-textMuted mr-3"></i>
                <input type="text" id="searchInput" placeholder="Cari NIS, Nama, atau Username..." class="flex-1 bg-transparent border-none outline-none text-[11px] font-bold text-popfit-dark uppercase">
            </div>
            <a href="tambahSiswa.php" class="bg-popfit-dark text-white px-4 py-2.5 text-[10px] font-black uppercase tracking-[0.1em] rounded-sm hover:bg-popfit-light transition-all flex items-center ml-4">
                <i class="ph-bold ph-user-plus mr-2"></i> Tambah Siswa
            </a>
        </div>

        <main class="flex-1 overflow-y-auto p-6">
            <div class="mb-6 bg-white border border-popfit-border rounded-sm p-4 flex items-center">
                <i class="ph ph-magnifying-glass text-popfit-textMuted mr-3 text-xl"></i>
                <input type="text" id="searchInput" placeholder="Cari NIS, Nama, atau Username..." 
                       class="flex-1 bg-transparent border-none outline-none text-xs font-bold text-popfit-dark placeholder-gray-400">
            </div>

            <div class="bg-white border border-popfit-border rounded-sm overflow-hidden">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-popfit-bg border-b border-popfit-border">
                            <th class="px-6 py-4 text-[10px] font-black text-popfit-textMuted uppercase tracking-widest">NIS</th>
                            <th class="px-6 py-4 text-[10px] font-black text-popfit-textMuted uppercase tracking-widest">Siswa</th>
                            <th class="px-6 py-4 text-[10px] font-black text-popfit-textMuted uppercase tracking-widest hidden md:table-cell">Kelas</th>
                            <th class="px-6 py-4 text-[10px] font-black text-popfit-textMuted uppercase tracking-widest hidden md:table-cell">No Telp</th>
                            <th class="px-6 py-4 text-[10px] font-black text-popfit-textMuted uppercase tracking-widest text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="siswaTable" class="divide-y divide-gray-50">
                        <?php while($row = mysqli_fetch_assoc($dataSiswa)): ?>
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 text-[10px] font-black text-popfit-dark uppercase tracking-widest">#<?= htmlspecialchars($row['nis']) ?></td>
                            <td class="px-6 py-4">
                                <p class="text-[12px] font-black text-popfit-dark uppercase tracking-tight"><?= htmlspecialchars($row['nama']) ?></p>
                                <p class="text-[10px] font-bold text-popfit-textMuted lowercase tracking-widest">@<?= htmlspecialchars($row['username']) ?></p>
                            </td>
                            <td class="px-6 py-4 hidden md:table-cell"><span class="text-[10px] font-black uppercase text-popfit-textMuted bg-popfit-bg px-2 py-1 rounded-sm border border-popfit-border"><?= htmlspecialchars($row['kelas'] ?? '-') ?></span></td>
                            <td class="px-6 py-4 hidden md:table-cell text-[11px] font-bold text-popfit-dark"><?= htmlspecialchars($row['no_telp'] ?? '-') ?></td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end space-x-2">
                                    <a href="editSiswa.php?id=<?= $row['nis'] ?>" class="w-8 h-8 flex items-center justify-center bg-white border border-popfit-border text-popfit-dark rounded-sm hover:bg-popfit-bg transition-colors" title="Edit"><i class="ph-bold ph-pencil-simple"></i></a>
                                    <a href="?hapus=<?= $row['nis'] ?>" class="btn-hapus w-8 h-8 flex items-center justify-center bg-white border border-popfit-border text-red-500 rounded-sm hover:bg-red-50 transition-colors" title="Hapus"><i class="ph-bold ph-trash"></i></a>
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
        const closeBtn = document.getElementById('closeSidebar');
        function toggleSidebar() { sidebar.classList.toggle('-translate-x-full'); overlay.classList.toggle('hidden'); }
        openBtn.addEventListener('click', toggleSidebar);
        closeBtn.addEventListener('click', toggleSidebar);
        overlay.addEventListener('click', toggleSidebar);

        document.getElementById('searchInput').addEventListener('input', function() {
            fetch(`siswa.php?ajax=1&keyword=${this.value}`)
            .then(res => res.text()).then(data => document.getElementById('siswaTable').innerHTML = data);
        });

        document.querySelectorAll('.btn-hapus').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const link = this.href;
                Swal.fire({
                    title: 'HAPUS SISWA?',
                    text: "Seluruh data transaksi siswa ini juga akan hilang!",
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