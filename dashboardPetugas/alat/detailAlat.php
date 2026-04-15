<?php
require '../../config/config.php';
session_start();

if (!isset($_SESSION["login"]) || $_SESSION["role"] != "petugas") {
    header("Location: ../../login.php");
    exit;
}

$petugasName = htmlspecialchars($_SESSION['nama'] ?? 'Petugas');
$petugasUsername = htmlspecialchars($_SESSION['username'] ?? 'petugas');

if (!isset($_GET['id'])) {
    header("Location: daftarAlat.php");
    exit;
}

$id = mysqli_real_escape_string($connect, $_GET['id']);
$result = mysqli_query($connect, "SELECT * FROM alat_olahraga WHERE id_alat_olahraga = '$id'");
if (mysqli_num_rows($result) == 0) {
    echo "<script>alert('Data tidak ditemukan'); window.location.href='daftarAlat.php';</script>";
    exit;
}
$data = mysqli_fetch_assoc($result);

/* HITUNG JUMLAH SEDANG DIPINJAM */
$sedangDipinjam = 0;
$queryPinjam = mysqli_query($connect, "
    SELECT COUNT(*) as total 
    FROM transaksi t
    JOIN detail_transaksi d ON t.id_transaksi = d.id_transaksi
    WHERE d.id_alat_olahraga = '$id' 
    AND t.status = 'dipinjam'
");
if ($queryPinjam) {
    $row = mysqli_fetch_assoc($queryPinjam);
    $sedangDipinjam = $row['total'] ?? 0;
}

/* RIWAYAT PEMINJAMAN TERAKHIR */
$riwayatPinjam = mysqli_query($connect, "
    SELECT u.nama AS nama_siswa, t.waktu_pinjam, t.status
    FROM transaksi t
    JOIN detail_transaksi d ON t.id_transaksi = d.id_transaksi
    JOIN users u ON t.id_user = u.id_user
    WHERE d.id_alat_olahraga = '$id'
    ORDER BY t.waktu_pinjam DESC
    LIMIT 5
");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Alat - PopFit Staff</title>
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
                <span class="text-xl font-black tracking-wide uppercase">PopFit Staff</span>
            </div>
            <button id="closeSidebar" class="md:hidden text-gray-400 hover:text-white"><i class="ph ph-x text-2xl"></i></button>
        </div>

        <nav class="flex-1 overflow-y-auto py-4">
            <ul class="space-y-1">
                <li><a href="../dashboardPetugas.php" class="flex items-center px-6 py-3 text-gray-200 hover:bg-popfit-light transition-colors border-l-4 border-transparent">
                    <i class="ph ph-squares-four text-xl w-6"></i><span class="ml-3 font-bold">Dashboard</span>
                </a></li>
                <li class="px-6 py-2 mt-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Sirkulasi</li>
                <li><a href="daftarAlat.php" class="nav-active flex items-center px-6 py-3 text-gray-200 hover:bg-popfit-light transition-colors border-l-4 border-transparent">
                    <i class="ph ph-basketball text-xl w-6"></i><span class="ml-3 font-bold">Katalog Alat</span>
                </a></li>
                <li><a href="../transaksi/transaksi.php" class="flex items-center px-6 py-3 text-gray-200 hover:bg-popfit-light transition-colors border-l-4 border-transparent">
                    <i class="ph ph-arrows-left-right text-xl w-6"></i><span class="ml-3 font-bold">Transaksi</span>
                </a></li>
                <li><a href="../denda/denda.php" class="flex items-center px-6 py-3 text-gray-200 hover:bg-popfit-light transition-colors border-l-4 border-transparent">
                    <i class="ph ph-wallet text-xl w-6"></i><span class="ml-3 font-bold">Denda</span>
                </a></li>
                <li><a href="../laporan/laporan.php" class="flex items-center px-6 py-3 text-gray-200 hover:bg-popfit-light transition-colors border-l-4 border-transparent">
                    <i class="ph ph-file-text text-xl w-6"></i><span class="ml-3 font-bold">Laporan</span>
                </a></li>
            </ul>
        </nav>

        <div class="border-t border-popfit-light p-4">
            <div class="flex items-center w-full">
                <div class="w-8 h-8 rounded-sm bg-popfit-accent flex items-center justify-center text-popfit-dark font-black"><?= substr($petugasName, 0, 1) ?></div>
                <div class="ml-3 flex-1 overflow-hidden">
                    <p class="text-[12px] font-black text-white truncate uppercase"><?= $petugasName ?></p>
                    <p class="text-[10px] text-gray-400 truncate uppercase"><?= $petugasUsername ?></p>
                </div>
                <a href="../../logout.php" class="text-gray-400 hover:text-white transition-colors"><i class="ph ph-sign-out text-xl"></i></a>
            </div>
        </div>
    </aside>

    <div class="flex-1 flex flex-col h-screen w-full relative">
        <header class="h-16 bg-popfit-surface border-b border-popfit-border flex items-center justify-between px-6 flex-shrink-0">
            <div class="flex items-center">
                <button id="openSidebar" class="md:hidden mr-4 text-popfit-dark"><i class="ph ph-list text-2xl"></i></button>
                <h2 class="text-lg font-black text-popfit-dark uppercase tracking-tight">Detail Alat</h2>
            </div>
            <a href="daftarAlat.php" class="flex items-center text-popfit-textMuted hover:text-popfit-dark transition-all text-[11px] font-black uppercase tracking-widest">
                <i class="ph ph-arrow-left mr-2"></i> Kembali
            </a>
        </header>

        <main class="flex-1 overflow-y-auto p-6">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Info Utama -->
                <div class="lg:col-span-2 space-y-6">
                    <div class="bg-white border border-popfit-border rounded-sm p-8 flex flex-col md:flex-row items-start md:items-center space-y-6 md:space-y-0 md:space-x-8 text-[13px]">
                        <div class="w-full md:w-64 h-64 bg-popfit-bg border border-popfit-border rounded-sm flex items-center justify-center overflow-hidden group relative cursor-pointer" onclick="openModal()">
                            <?php if (!empty($data['foto_alat_olahraga']) && file_exists("../../asset/" . $data['foto_alat_olahraga'])): ?>
                                <img src="../../asset/<?= htmlspecialchars($data['foto_alat_olahraga']) ?>" alt="<?= htmlspecialchars($data['nama_alat_olahraga']) ?>" class="w-full h-full object-contain group-hover:scale-110 transition-transform duration-500">
                                <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center text-white text-[10px] font-black uppercase tracking-widest">Klik Zoom</div>
                            <?php else: ?>
                                <i class="ph ph-camera-slash text-5xl text-gray-300"></i>
                            <?php endif; ?>
                        </div>
                        <div class="flex-1">
                            <span class="px-2 py-1 bg-popfit-accent text-popfit-dark text-[10px] font-black uppercase tracking-widest rounded-sm mb-3 inline-block"><?= htmlspecialchars($data['kategori']) ?></span>
                            <h1 class="text-3xl font-black text-popfit-dark tracking-tighter uppercase leading-none mb-2"><?= htmlspecialchars($data['nama_alat_olahraga']) ?></h1>
                            <p class="text-[11px] font-bold text-popfit-textMuted uppercase tracking-widest mb-6">ID ALAT: #<?= htmlspecialchars($data['id_alat_olahraga']) ?></p>
                            
                            <div class="grid grid-cols-2 gap-4">
                                <div class="p-4 bg-popfit-bg border border-popfit-border rounded-sm">
                                    <p class="text-[9px] font-black text-popfit-textMuted uppercase tracking-widest mb-1">Stok Total</p>
                                    <p class="text-xl font-black text-popfit-dark"><?= $data['stok'] ?> <span class="text-[10px] text-gray-400">UNIT</span></p>
                                </div>
                                <div class="p-4 bg-popfit-bg border border-popfit-border rounded-sm">
                                    <p class="text-[9px] font-black text-popfit-textMuted uppercase tracking-widest mb-1">Sedang Dipinjam</p>
                                    <p class="text-xl font-black text-popfit-dark"><?= $sedangDipinjam ?> <span class="text-[10px] text-gray-400">UNIT</span></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white border border-popfit-border rounded-sm p-8 text-[13px]">
                        <h3 class="text-[10px] font-black text-popfit-textMuted uppercase tracking-[0.3em] mb-4 border-b border-gray-100 pb-2">Deskripsi Alat</h3>
                        <p class="text-[13px] leading-relaxed text-popfit-text font-medium opacity-80">
                            <?= !empty($data['deskripsi']) ? nl2br(htmlspecialchars($data['deskripsi'])) : '<em>Tidak ada deskripsi untuk alat ini.</em>' ?>
                        </p>
                    </div>
                </div>

                <!-- Info Samping / Riwayat -->
                <div class="space-y-6">
                    <div class="bg-white border border-popfit-border rounded-sm p-8 text-[13px]">
                        <h3 class="text-[10px] font-black text-popfit-textMuted uppercase tracking-[0.3em] mb-6 flex items-center">
                            <i class="ph ph-clock-counter-clockwise text-lg mr-2"></i> Riwayat Terakhir
                        </h3>
                        <div class="space-y-4">
                            <?php if(mysqli_num_rows($riwayatPinjam) > 0): ?>
                                <?php while($row = mysqli_fetch_assoc($riwayatPinjam)): ?>
                                <div class="flex items-center space-x-3 pb-4 border-b border-gray-100 last:border-0 last:pb-0">
                                    <div class="w-8 h-8 rounded-sm bg-gray-50 flex items-center justify-center text-popfit-dark font-black text-[10px] uppercase"><?= substr($row['nama_siswa'], 0, 1) ?></div>
                                    <div class="flex-1 overflow-hidden">
                                        <p class="text-[11px] font-black text-popfit-dark truncate uppercase"><?= htmlspecialchars($row['nama_siswa']) ?></p>
                                        <p class="text-[9px] font-bold text-gray-400 uppercase"><?= date('d.m.Y • H:i', strtotime($row['waktu_pinjam'])) ?></p>
                                    </div>
                                    <span class="text-[8px] font-black uppercase text-popfit-textMuted"><?= $row['status'] ?></span>
                                </div>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <div class="text-center py-8 opacity-40">
                                    <i class="ph ph-note-blank text-3xl mb-2"></i>
                                    <p class="text-[10px] font-black uppercase tracking-widest">Belum ada peminjaman</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="p-6 bg-popfit-dark rounded-sm text-center">
                        <p class="text-white text-[11px] font-black uppercase tracking-[0.2em] mb-4 leading-relaxed">Kelola data alat lebih lanjut di halaman Admin utama</p>
                        <i class="ph ph-lock-key text-popfit-accent text-3xl opacity-50"></i>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Image Modal -->
    <div id="imageModal" class="fixed inset-0 bg-black/90 z-[100] hidden items-center justify-center p-4 backdrop-blur-sm transition-all" onclick="closeModal()">
        <img id="modalImg" src="" class="max-w-full max-h-[90vh] object-contain rounded-sm shadow-2xl scale-95 transition-transform duration-300">
        <button class="absolute top-6 right-6 text-white text-3xl hover:text-popfit-accent transition-colors"><i class="ph ph-x-circle"></i></button>
    </div>

    <script>
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebarOverlay');
        const openBtn = document.getElementById('openSidebar');
        const closeBtn = document.getElementById('closeSidebar');
        const modal = document.getElementById('imageModal');
        const modalImg = document.getElementById('modalImg');

        function toggleSidebar() { sidebar.classList.toggle('-translate-x-full'); overlay.classList.toggle('hidden'); }
        openBtn.addEventListener('click', toggleSidebar);
        closeBtn.addEventListener('click', toggleSidebar);
        overlay.addEventListener('click', toggleSidebar);

        function openModal() {
            const currentImg = document.querySelector('img[alt="<?= htmlspecialchars($data['nama_alat_olahraga']) ?>"]');
            if (currentImg) {
                modalImg.src = currentImg.src;
                modal.classList.remove('hidden');
                modal.classList.add('flex');
                setTimeout(() => modalImg.classList.remove('scale-95'), 10);
            }
        }
        function closeModal() {
            modalImg.classList.add('scale-95');
            setTimeout(() => {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }, 300);
        }
    </script>
</body>
</html>