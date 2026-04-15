<?php
require '../../config/config.php';
session_start();

if (!isset($_SESSION["login"]) || $_SESSION["role"] != "siswa") {
    header("Location: ../../login.php");
    exit;
}

$id_user = $_SESSION['id_user'];
$siswaName = htmlspecialchars($_SESSION['nama'] ?? 'Siswa');
$siswaUsername = htmlspecialchars($_SESSION['username'] ?? 'username');

/* AJAX HANDLER FOR SEARCH */
if(isset($_GET['ajax'])){
    $keyword = mysqli_real_escape_string($connect, $_GET['keyword'] ?? '');
    $kategori = mysqli_real_escape_string($connect, $_GET['kategori'] ?? '');
    
    $where = "WHERE 1=1";
    if($keyword != '') $where .= " AND (nama_alat_olahraga LIKE '%$keyword%' OR kategori LIKE '%$keyword%')";
    if($kategori != '') $where .= " AND kategori = '$kategori'";
    
    $data = mysqli_query($connect, "SELECT * FROM alat_olahraga $where ORDER BY id_alat_olahraga DESC");
    
    if(mysqli_num_rows($data) > 0){
        while($row = mysqli_fetch_assoc($data)){
            $foto = (!empty($row['foto_alat_olahraga'])) ? $row['foto_alat_olahraga'] : 'default.png';
            $imgSrc = (strpos($foto, 'http') === 0) ? $foto : "../../asset/$foto";
            echo "
            <div class='bg-white border border-popfit-border rounded-sm overflow-hidden flex flex-col group hover:border-popfit-dark transition-all'>
                <div class='h-40 bg-popfit-bg p-4 flex items-center justify-center relative border-b border-popfit-border overflow-hidden'>
                    <img src='$imgSrc' class='h-full object-contain filter drop-shadow-sm group-hover:scale-110 transition-transform duration-300'>
                </div>
                <div class='p-4 flex-1 flex flex-col'>
                    <h4 class='text-[12px] font-black text-popfit-dark uppercase tracking-tight line-clamp-1'>".htmlspecialchars($row['nama_alat_olahraga'])."</h4>
                    <p class='text-[10px] font-bold text-popfit-textMuted uppercase tracking-widest mt-1'>".htmlspecialchars($row['kategori'])."</p>
                    <div class='mt-4 pt-4 border-t border-gray-50 flex items-center justify-between'>
                        <span class='text-[10px] font-black uppercase text-popfit-light'>Stok: ".$row['stok']."</span>
                        <div class='flex space-x-1'>
                            <button onclick='openModal(\"".$row['id_alat_olahraga']."\", \"".addslashes($row['nama_alat_olahraga'])."\", ".$row['stok'].", \"$foto\")' 
                                    class='w-8 h-8 flex items-center justify-center bg-popfit-accent text-popfit-dark rounded-sm hover:bg-popfit-dark hover:text-white transition-all'><i class='ph-bold ph-shopping-cart'></i></button>
                            <a href='detailAlat.php?id=".$row['id_alat_olahraga']."' class='w-8 h-8 flex items-center justify-center bg-white border border-popfit-border text-popfit-dark rounded-sm hover:bg-popfit-bg transition-all'><i class='ph-bold ph-eye'></i></a>
                        </div>
                    </div>
                </div>
            </div>";
        }
    } else {
        echo "<div class='col-span-full py-20 text-center text-popfit-textMuted font-black uppercase tracking-widest text-xs'>Alat tidak ditemukan</div>";
    }
    exit;
}

$kategoriList = mysqli_query($connect, "SELECT * FROM kategori_alat_olahraga ORDER BY kategori ASC");
$countKeranjang = mysqli_fetch_assoc(mysqli_query($connect, "SELECT COUNT(*) as total FROM keranjang WHERE id_user = '$id_user'"))['total'] ?? 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cari Alat - PopFit Siswa</title>
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
        $activeIndex = "alat"; 
        include '../../layout/sidebar_siswa.php'; 
    ?>

    <div class="flex-1 flex flex-col h-screen w-full relative">
        <?php 
            $pageTitle = "Cari Alat Olahraga"; 
            include '../../layout/header_siswa.php'; 
        ?>

        <main class="flex-1 overflow-y-auto p-6">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
                <div class="flex-1 bg-white border border-popfit-border rounded-sm p-4 flex items-center">
                    <i class="ph ph-magnifying-glass text-popfit-textMuted mr-3 text-xl"></i>
                    <input type="text" id="searchInput" placeholder="Cari nama alat..." class="flex-1 bg-transparent border-none outline-none font-bold text-xs text-popfit-dark uppercase">
                </div>
                <div class="flex items-center space-x-2 overflow-x-auto pb-2 scrollbar-none" id="catFilter">
                    <button data-cat="" class="cat-btn px-4 py-2 bg-popfit-dark text-white text-[10px] font-black uppercase rounded-sm whitespace-nowrap">Semua</button>
                    <?php while($k = mysqli_fetch_assoc($kategoriList)): ?>
                    <button data-cat="<?= htmlspecialchars($k['kategori']) ?>" class="cat-btn px-4 py-2 bg-white border border-popfit-border text-popfit-textMuted text-[10px] font-black uppercase rounded-sm whitespace-nowrap hover:border-popfit-dark transition-all"><?= htmlspecialchars($k['kategori']) ?></button>
                    <?php endwhile; ?>
                </div>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4" id="alatGrid">
                <!-- AJAX -->
            </div>
        </main>
    </div>

    <!-- MODAL -->
    <div id="modalQty" class="fixed inset-0 bg-black/50 z-[100] hidden items-center justify-center p-4">
        <div class="bg-white rounded-sm w-full max-w-sm overflow-hidden border border-popfit-border p-6 shadow-2xl">
            <h3 id="modalNama" class="font-black text-popfit-dark text-sm uppercase mb-1">Nama Alat</h3>
            <p id="modalStok" class="text-[10px] font-bold text-popfit-textMuted uppercase mb-6">Tersedia: 0</p>
            <form id="formCart">
                <input type="hidden" id="modalId">
                <div class="flex items-center justify-center space-x-4 mb-8">
                    <button type="button" onclick="changeQty(-1)" class="w-10 h-10 rounded-sm bg-popfit-bg border border-popfit-border flex items-center justify-center text-xl font-black">-</button>
                    <input type="number" id="qtyInput" value="1" readonly class="w-16 text-center font-black text-lg bg-transparent border-none outline-none">
                    <button type="button" onclick="changeQty(1)" class="w-10 h-10 rounded-sm bg-popfit-bg border border-popfit-border flex items-center justify-center text-xl font-black">+</button>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <button type="button" onclick="closeModal()" class="py-3 text-[10px] font-black uppercase bg-popfit-bg text-popfit-textMuted rounded-sm hover:bg-gray-200 transition-all">Batal</button>
                    <button type="submit" class="py-3 text-[10px] font-black uppercase bg-popfit-dark text-white rounded-sm hover:bg-popfit-light transition-all">Tambah</button>
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
        function toggleSidebar() { sidebar.classList.toggle('-translate-x-full'); overlay.classList.toggle('hidden'); }
        openBtn.addEventListener('click', toggleSidebar);
        closeBtn.addEventListener('click', toggleSidebar);
        overlay.addEventListener('click', toggleSidebar);

        const grid = document.getElementById('alatGrid');
        const search = document.getElementById('searchInput');
        const catBtns = document.querySelectorAll('.cat-btn');
        let currentCat = '';

        function loadAlat() {
            fetch(`daftarAlat.php?ajax=1&keyword=${search.value}&kategori=${currentCat}`)
            .then(res => res.text()).then(data => grid.innerHTML = data);
        }

        search.addEventListener('input', loadAlat);
        catBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                catBtns.forEach(b => { b.classList.replace('bg-popfit-dark', 'bg-white'); b.classList.replace('text-white', 'text-popfit-textMuted'); });
                this.classList.replace('bg-white', 'bg-popfit-dark'); this.classList.replace('text-popfit-textMuted', 'text-white');
                currentCat = this.dataset.cat;
                loadAlat();
            });
        });

        let maxStok = 0;
        function openModal(id, nama, stok, img) {
            document.getElementById('modalId').value = id;
            document.getElementById('modalNama').textContent = nama;
            document.getElementById('modalStok').textContent = `TERSEDIA: ${stok} UNIT`;
            document.getElementById('qtyInput').value = 1;
            maxStok = stok;
            document.getElementById('modalQty').classList.replace('hidden', 'flex');
        }
        function closeModal() { document.getElementById('modalQty').classList.replace('flex', 'hidden'); }
        function changeQty(d) {
            const i = document.getElementById('qtyInput');
            let v = parseInt(i.value) + d;
            if(v >= 1 && v <= maxStok) i.value = v;
        }

        document.getElementById('formCart').onsubmit = function(e){
            e.preventDefault();
            const id = document.getElementById('modalId').value;
            const qty = document.getElementById('qtyInput').value;
            fetch(`../keranjang/tambahKeranjang.php`, {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: `id=${id}&jumlah=${qty}`
            })
            .then(res => res.json())
            .then(data => {
                closeModal();
                if (data.status === 'success') {
                    Swal.fire({
                        title: 'BERHASIL BERHASIL HORE!',
                        text: data.message,
                        icon: 'success',
                        showConfirmButton: true,
                        confirmButtonColor: '#2A4736',
                        customClass: { popup: 'rounded-sm' }
                    }).then(() => {
                        window.location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error', 
                        title: 'GAGAL', 
                        text: data.message, 
                        confirmButtonColor: '#2A4736'
                    });
                }
            })
            .catch(err => {
                closeModal();
                Swal.fire({
                    icon: 'error', 
                    title: 'ERROR', 
                    text: 'Terjadi kesalahan sistem.', 
                    confirmButtonColor: '#2A4736'
                });
            });
        }
        loadAlat();
    </script>
</body>
</html>