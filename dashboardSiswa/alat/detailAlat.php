<?php
require '../../config/config.php';
session_start();

if (!isset($_SESSION["login"]) || $_SESSION["role"] != "siswa") {
    header("Location: ../../login.php");
    exit;
}

$id = mysqli_real_escape_string($connect, $_GET['id'] ?? '');
if(!$id) header("Location: daftarAlat.php");

$query = "SELECT * FROM alat_olahraga WHERE id_alat_olahraga = '$id'";
$res = mysqli_query($connect, $query);
$alat = mysqli_fetch_assoc($res);

if(!$alat) header("Location: daftarAlat.php");

$siswaName = htmlspecialchars($_SESSION['nama'] ?? 'Siswa');
$foto = (!empty($alat['foto_alat_olahraga'])) ? $alat['foto_alat_olahraga'] : 'default.png';
$imgSrc = (strpos($foto, 'http') === 0) ? $foto : "../../asset/$foto";
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($alat['nama_alat_olahraga']) ?> - PopFit</title>
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
                <span class="text-xl font-black tracking-wide uppercase">PopFit</span>
            </div>
            <button id="closeSidebar" class="md:hidden text-2xl"><i class="ph ph-x"></i></button>
        </div>

        <nav class="flex-1 overflow-y-auto pt-6">
            <ul class="space-y-1">
                <li class="px-6 py-2 text-[10px] font-black text-gray-400 uppercase tracking-widest">Main Menu</li>
                <li><a href="../dashboardSiswa.php" class="flex items-center px-6 py-3 text-gray-200 hover:bg-popfit-light transition-colors border-l-4 border-transparent">
                    <i class="ph ph-squares-four text-xl w-6"></i><span class="ml-3 font-bold">Beranda</span>
                </a></li>
                <li class="px-6 py-2 mt-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Peminjaman</li>
                <li><a href="daftarAlat.php" class="flex items-center px-6 py-3 text-white transition-colors border-l-4 border-popfit-accent nav-active">
                    <i class="ph-fill ph-magnifying-glass text-xl w-6"></i><span class="ml-3 font-bold">Cari Alat</span>
                </a></li>
                <li><a href="../keranjang/keranjang.php" class="flex items-center px-6 py-3 text-gray-200 hover:bg-popfit-light transition-colors border-l-4 border-transparent">
                    <i class="ph ph-shopping-cart text-xl w-6"></i><span class="ml-3 font-bold">Keranjang</span>
                </a></li>
                <li><a href="../transaksi/transaksi.php" class="flex items-center px-6 py-3 text-gray-200 hover:bg-popfit-light transition-colors border-l-4 border-transparent">
                    <i class="ph ph-arrows-left-right text-xl w-6"></i><span class="ml-3 font-bold">Transaksi</span>
                </a></li>
                <li><a href="../denda/denda.php" class="flex items-center px-6 py-3 text-gray-200 hover:bg-popfit-light transition-colors border-l-4 border-transparent">
                    <i class="ph ph-wallet text-xl w-6"></i><span class="ml-3 font-bold">Denda</span>
                </a></li>
                <li><a href="../riwayat/riwayat.php" class="flex items-center px-6 py-3 text-gray-200 hover:bg-popfit-light transition-colors border-l-4 border-transparent">
                    <i class="ph ph-clock-counter-clockwise text-xl w-6"></i><span class="ml-3 font-bold">Riwayat</span>
                </a></li>
            </ul>
        </nav>

        <div class="p-6 border-t border-popfit-light">
            <div class="flex items-center bg-popfit-light/30 p-3 rounded-sm">
                <div class="w-8 h-8 rounded-sm bg-popfit-accent flex items-center justify-center text-popfit-dark font-black text-xs"><?= substr($siswaName, 0, 1) ?></div>
                <div class="ml-3 flex-1 overflow-hidden">
                    <p class="text-[12px] font-black text-white truncate uppercase"><?= $siswaName ?></p>
                    <p class="text-[10px] text-gray-400 truncate uppercase">Siswa</p>
                </div>
                <a href="../logout.php" class="text-gray-400 hover:text-white"><i class="ph-bold ph-sign-out text-xl"></i></a>
            </div>
        </div>
    </aside>

    <div class="flex-1 flex flex-col h-screen w-full relative">
        <header class="h-16 bg-popfit-surface border-b border-popfit-border flex items-center justify-between px-6 flex-shrink-0">
            <div class="flex items-center">
                <button id="openSidebar" class="md:hidden mr-4 text-2xl"><i class="ph ph-list"></i></button>
                <div class="flex items-center space-x-2">
                    <a href="daftarAlat.php" class="text-popfit-textMuted hover:text-popfit-dark"><i class="ph-bold ph-arrow-left"></i></a>
                    <h2 class="text-lg font-black text-popfit-dark uppercase tracking-tight">Detail Alat</h2>
                </div>
            </div>
        </header>

        <main class="flex-1 overflow-y-auto p-6 md:p-12">
            <div class="max-w-5xl mx-auto bg-white border border-popfit-border rounded-sm overflow-hidden flex flex-col md:flex-row shadow-sm">
                
                <!-- IMG SECTION -->
                <div class="md:w-1/2 bg-popfit-bg p-8 flex items-center justify-center min-h-[400px]">
                    <img src="<?= $imgSrc ?>" class="w-full max-h-[400px] object-contain drop-shadow-xl hover:scale-105 transition-transform duration-500">
                </div>

                <!-- INFO SECTION -->
                <div class="flex-1 p-8 md:p-12 border-l border-popfit-border flex flex-col">
                    <div class="mb-8">
                        <span class="px-3 py-1 bg-popfit-accent text-[9px] font-black uppercase text-popfit-dark rounded-sm tracking-widest"><?= htmlspecialchars($alat['kategori']) ?></span>
                        <h1 class="text-3xl font-black text-popfit-dark uppercase tracking-tighter mt-4 leading-none"><?= htmlspecialchars($alat['nama_alat_olahraga']) ?></h1>
                        <p class="text-[10px] font-bold text-popfit-textMuted uppercase tracking-[0.2em] mt-2">ID Produk: #<?= htmlspecialchars($alat['id_alat_olahraga']) ?></p>
                    </div>

                    <div class="space-y-6 flex-1">
                        <div>
                            <h3 class="text-[11px] font-black text-popfit-dark uppercase tracking-widest mb-2 flex items-center">
                                <i class="ph ph-info mr-2 text-lg"></i> Deskripsi Alat
                            </h3>
                            <p class="text-[12px] leading-relaxed text-popfit-textMuted font-medium">
                                <?= nl2br(htmlspecialchars($alat['deskripsi'])) ?>
                            </p>
                        </div>

                        <div class="grid grid-cols-2 gap-4 pt-4">
                            <div class="bg-popfit-bg p-4 rounded-sm border border-popfit-border">
                                <p class="text-[10px] font-black text-popfit-textMuted uppercase tracking-widest text-center">Status Stok</p>
                                <p class="text-2xl font-black text-popfit-dark text-center mt-1"><?= $alat['stok'] ?></p>
                            </div>
                            <div class="bg-popfit-bg p-4 rounded-sm border border-popfit-border">
                                <p class="text-[10px] font-black text-popfit-textMuted uppercase tracking-widest text-center">Tersedia</p>
                                <p class="text-2xl font-black text-popfit-light text-center mt-1">YA</p>
                            </div>
                        </div>
                    </div>

                    <div class="mt-12 pt-8 border-t border-gray-100 grid grid-cols-2 gap-4">
                        <button onclick="window.history.back()" class="py-4 text-[10px] font-black uppercase tracking-widest bg-popfit-bg text-popfit-textMuted rounded-sm hover:bg-gray-200 transition-all flex items-center justify-center">
                           KEMBALI
                        </button>
                        <button onclick="addToCart('<?= $alat['id_alat_olahraga'] ?>')" class="py-4 text-[10px] font-black uppercase tracking-widest bg-popfit-dark text-white rounded-sm hover:bg-popfit-light transition-all flex items-center justify-center shadow-md">
                            <i class="ph-bold ph-shopping-cart-simple mr-2 text-lg"></i> MASUK KERANJANG
                        </button>
                    </div>
                </div>
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

        function addToCart(id) {
            Swal.fire({
                title: 'JUMLAH PINJAM',
                input: 'number',
                inputValue: 1,
                inputAttributes: { min: 1, max: <?= $alat['stok'] ?>, step: 1 },
                showCancelButton: true,
                confirmButtonText: 'TAMBAH',
                confirmButtonColor: '#2A4736',
                cancelButtonText: 'BATAL'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch(`../keranjang/tambahKeranjang.php`, {
                        method: 'POST',
                        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                        body: `id=${id}&jumlah=${result.value}`
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.status === 'success') {
                            Swal.fire({
                                title: 'BERHASIL BERHASIL HORE!',
                                text: data.message,
                                icon: 'success',
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
                        Swal.fire({
                            icon: 'error',
                            title: 'ERROR',
                            text: 'Terjadi kesalahan sistem. Silakan coba lagi.',
                            confirmButtonColor: '#2A4736'
                        });
                    });
                }
            });
        }
    </script>
</body>
</html>
