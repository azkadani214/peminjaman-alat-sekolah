<?php
require '../../config/config.php';
session_start();

if (!isset($_SESSION["login"]) || ($_SESSION["role"] != "admin utama" && $_SESSION["role"] != "admin")) {
    header("Location: ../../login.php");
    exit;
}

$adminName = htmlspecialchars($_SESSION['nama'] ?? 'Admin');
$adminUsername = htmlspecialchars($_SESSION['username'] ?? 'username');

/* DELETE KATEGORI */
if(isset($_GET['hapus'])){
    $kategori = mysqli_real_escape_string($connect, $_GET['hapus']);
    
    // CEK apakah kategori masih dipakai
    $cek = mysqli_query($connect, "SELECT * FROM alat_olahraga WHERE kategori = '$kategori'");
    if(mysqli_num_rows($cek) > 0) {
        header("Location: kategori.php?err=used");
        exit;
    }
    
    $_SESSION['swal'] = ['title' => 'BERHASIL!', 'text' => "Kategori $kategori berhasil dihapus.", 'icon' => 'success', 'redirect' => 'kategori.php'];
    header("Location: kategori.php");
    exit;
}

/* TAMBAH KATEGORI */
if(isset($_POST['tambah'])){
    $kategori = trim(mysqli_real_escape_string($connect, $_POST['kategori']));
    
    if(empty($kategori)){
        header("Location: kategori.php?err=empty");
        exit;
    }
    
    $cek = mysqli_query($connect, "SELECT * FROM kategori_alat_olahraga WHERE kategori = '$kategori'");
    if(mysqli_num_rows($cek) > 0){
        header("Location: kategori.php?err=exists");
    } else {
        mysqli_query($connect, "INSERT INTO kategori_alat_olahraga (kategori) VALUES ('$kategori')");
        tambahLog($_SESSION['id_user'], "Menambah kategori alat: $kategori");
        $_SESSION['swal'] = ['title' => 'BERHASIL!', 'text' => "Kategori $kategori berhasil ditambahkan.", 'icon' => 'success', 'redirect' => 'kategori.php'];
        header("Location: kategori.php");
    }
    exit;
}

/* LOAD DATA */
$keyword = $_GET['keyword'] ?? '';
$query = "SELECT * FROM kategori_alat_olahraga";
if($keyword !== '') {
    $query .= " WHERE kategori LIKE '%$keyword%'";
}
$query .= " ORDER BY kategori ASC";
$dataKategori = mysqli_query($connect, $query);

/* AJAX Handler for Live Search */
if(isset($_GET['ajax'])){
    if(mysqli_num_rows($dataKategori) > 0){
        $no = 1;
        while($row = mysqli_fetch_assoc($dataKategori)){
            echo "
            <tr class='hover:bg-gray-50 transition-colors'>
                <td class='px-6 py-4 text-[11px] font-bold text-gray-400'>".($no++)."</td>
                <td class='px-6 py-4'>
                    <span class='text-[12px] font-black text-popfit-dark uppercase tracking-tight'>".htmlspecialchars($row['kategori'])."</span>
                </td>
                <td class='px-6 py-4 text-right'>
                    <div class='flex items-center justify-end space-x-2'>
                        <a href='editKategori.php?kategori=".urlencode($row['kategori'])."' class='p-2 bg-white border border-popfit-border text-popfit-dark rounded-sm hover:bg-popfit-bg transition-colors' title='Edit'><i class='ph-bold ph-pencil-simple text-sm'></i></a>
                        <a href='?hapus=".urlencode($row['kategori'])."' class='btn-hapus p-2 bg-white border border-popfit-border text-red-500 rounded-sm hover:bg-red-50 transition-colors' title='Hapus'><i class='ph-bold ph-trash text-sm'></i></a>
                    </div>
                </td>
            </tr>
            ";
        }
    } else {
        echo "<tr><td colspan='3' class='p-12 text-center text-popfit-textMuted uppercase font-black text-xs tracking-widest'>Data tidak ditemukan</td></tr>";
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kategori Alat - PopFit Admin</title>
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
        $rel = "../"; 
        $activeIndex = "kategori"; 
        include '../../layout/sidebar_admin.php'; 
    ?>

    <div class="flex-1 flex flex-col h-screen w-full relative">
        <header class="h-16 bg-popfit-surface border-b border-popfit-border flex items-center justify-between px-6 flex-shrink-0">
            <div class="flex items-center">
                <button id="openSidebar" class="md:hidden mr-4 text-popfit-dark"><i class="ph ph-list text-2xl"></i></button>
                <h2 class="text-lg font-black text-popfit-dark uppercase tracking-tight">Kategori Alat</h2>
            </div>
            <div class="flex items-center space-x-4">
                <a href="daftarAlat.php" class="flex items-center text-popfit-textMuted hover:text-popfit-dark transition-all text-[10px] font-black uppercase tracking-widest border-r border-popfit-border pr-4 h-full">
                    <i class="ph ph-arrow-left mr-2"></i> Kembali
                </a>
                <button onclick="toggleModal()" class="bg-popfit-dark text-white px-4 py-2 text-[10px] font-black uppercase tracking-widest rounded-sm hover:bg-popfit-light transition-all flex items-center">
                    <i class="ph ph-plus-bold mr-2"></i> Tambah Baru
                </button>
            </div>
        </header>

        <main class="flex-1 overflow-y-auto p-6">
            <div class="max-w-4xl mx-auto">
                <!-- Search Bar -->
                <div class="bg-white border border-popfit-border p-4 rounded-sm mb-6 flex items-center">
                    <div class="relative flex-1">
                        <i class="ph ph-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <input type="text" id="searchInput" placeholder="Cari kategori..." 
                               class="w-full bg-popfit-bg border border-popfit-border rounded-sm pl-10 pr-4 py-2 text-[11px] font-bold text-popfit-dark focus:border-popfit-dark outline-none transition-colors">
                    </div>
                </div>

                <!-- Table View -->
                <div class="bg-white border border-popfit-border rounded-sm overflow-hidden shadow-sm">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-popfit-bg border-b border-popfit-border">
                                <th class="px-6 py-4 text-[10px] font-black text-popfit-textMuted uppercase tracking-widest w-16">No</th>
                                <th class="px-6 py-4 text-[10px] font-black text-popfit-textMuted uppercase tracking-widest">Nama Kategori</th>
                                <th class="px-6 py-4 text-[10px] font-black text-popfit-textMuted uppercase tracking-widest text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="tableBody" class="divide-y divide-gray-50">
                            <?php if(mysqli_num_rows($dataKategori) > 0): $no=1; while($row = mysqli_fetch_assoc($dataKategori)): ?>
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 text-[11px] font-bold text-gray-400"><?= $no++ ?></td>
                                <td class="px-6 py-4">
                                    <span class="text-[12px] font-black text-popfit-dark uppercase tracking-tight"><?= htmlspecialchars($row['kategori']) ?></span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end space-x-2">
                                        <a href="editKategori.php?kategori=<?= urlencode($row['kategori']) ?>" class="p-2 bg-white border border-popfit-border text-popfit-dark rounded-sm hover:bg-popfit-bg transition-colors" title="Edit"><i class="ph-bold ph-pencil-simple text-sm"></i></a>
                                        <a href="?hapus=<?= urlencode($row['kategori']) ?>" class="btn-hapus p-2 bg-white border border-popfit-border text-red-500 rounded-sm hover:bg-red-50 transition-colors" title="Hapus"><i class="ph-bold ph-trash text-sm"></i></a>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; else: ?>
                            <tr><td colspan="3" class="p-12 text-center text-popfit-textMuted uppercase font-black text-xs tracking-widest">Data tidak ditemukan</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <!-- Modal Tambah Overlay -->
    <div id="modalTambah" class="fixed inset-0 bg-black/60 z-[100] hidden items-center justify-center p-4 backdrop-blur-sm transition-all shadow-2xl">
        <div class="bg-white rounded-sm w-full max-w-md overflow-hidden animate-in fade-in zoom-in duration-300">
            <div class="bg-popfit-dark px-6 py-4 flex items-center justify-between">
                <h3 class="text-white text-[11px] font-black uppercase tracking-[0.2em] flex items-center">
                    <i class="ph ph-tag-bold mr-3 text-popfit-accent"></i> Tambah Kategori Baru
                </h3>
                <button onclick="toggleModal()" class="text-white hover:text-popfit-accent transition-colors"><i class="ph ph-x-bold"></i></button>
            </div>
            <form method="POST" class="p-8 space-y-6">
                <div class="space-y-2">
                    <label class="text-[10px] font-black text-popfit-textMuted uppercase tracking-widest">Nama Kategori</label>
                    <input type="text" name="kategori" required placeholder="CONTOH: BOLA, RAKET, DLL" class="w-full bg-popfit-bg border border-popfit-border rounded-sm px-4 py-3 text-xs font-bold text-popfit-dark focus:border-popfit-dark outline-none transition-all uppercase placeholder:text-gray-300">
                </div>
                <div class="flex space-x-3">
                    <button type="submit" name="tambah" class="flex-1 bg-popfit-dark text-white py-3 text-[10px] font-black uppercase tracking-widest rounded-sm hover:bg-popfit-light transition-all">Simpan</button>
                    <button type="button" onclick="toggleModal()" class="px-6 py-3 bg-white border border-popfit-border text-popfit-dark text-[10px] font-black uppercase tracking-widest rounded-sm hover:bg-popfit-bg transition-all">Batal</button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebarOverlay');
        const openBtn = document.getElementById('openSidebar');
        const closeBtn = document.getElementById('closeSidebar');
        const modal = document.getElementById('modalTambah');
        const searchInput = document.getElementById('searchInput');
        const tableBody = document.getElementById('tableBody');

        function toggleSidebar() { sidebar.classList.toggle('-translate-x-full'); overlay.classList.toggle('hidden'); }
        openBtn.addEventListener('click', toggleSidebar);
        closeBtn.addEventListener('click', toggleSidebar);
        overlay.addEventListener('click', toggleSidebar);

        function toggleModal() { modal.classList.toggle('hidden'); modal.classList.toggle('flex'); }

        // Live Search
        let timeout = null;
        searchInput.addEventListener('input', function() {
            clearTimeout(timeout);
            timeout = setTimeout(() => {
                fetch("kategori.php?ajax=1&keyword=" + encodeURIComponent(this.value))
                    .then(res => res.text())
                    .then(data => tableBody.innerHTML = data);
            }, 300);
        });

        // Handle URL Messages/Errors
        const urlParams = new URLSearchParams(window.location.search);
        if(urlParams.has('msg') || urlParams.has('err')) {
            const msg = urlParams.get('msg');
            const err = urlParams.get('err');
            
            let config = { icon: 'success', customClass: { popup: 'rounded-sm' } };
            if (msg === 'added') config.title = 'BERHASIL DITAMBAH';
            else if (msg === 'deleted') config.title = 'BERHASIL DIHAPUS';
            else if (err === 'used') { config.icon = 'error'; config.title = 'GAGAL DIHAPUS'; config.text = 'Kategori masih digunakan alat lain!'; }
            else if (err === 'exists') { config.icon = 'warning'; config.title = 'DUPLIKAT'; config.text = 'Kategori sudah ada!'; }
            else if (err === 'empty') { config.icon = 'warning'; config.title = 'KOSONG'; config.text = 'Nama tidak boleh kosong!'; }

            if(config.title) Swal.fire(config).then(() => {
                window.history.replaceState({}, document.title, window.location.pathname);
            });
        }

        // Delete Alert
        document.addEventListener('click', function(e) {
            if (e.target.closest('.btn-hapus')) {
                e.preventDefault();
                const link = e.target.closest('.btn-hapus').href;
                Swal.fire({
                    title: 'HAPUS KATEGORI?',
                    text: "Data yang dihapus tidak dapat dipulihkan!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#2A4736',
                    cancelButtonColor: '#E4E4E7',
                    confirmButtonText: 'YA, HAPUS!',
                    cancelButtonText: 'BATAL',
                    customClass: { popup: 'rounded-sm' }
                }).then((res) => {
                    if (res.isConfirmed) window.location.href = link;
                });
            }
        });
    </script>
</body>
</html>
