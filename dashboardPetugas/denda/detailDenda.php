<?php
require '../../config/config.php';
session_start();

if (!isset($_SESSION["login"]) || $_SESSION["role"] != "petugas") {
    header("Location: ../../login.php");
    exit;
}

$idTransaksi = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$petugasUsername = htmlspecialchars($_SESSION['username'] ?? 'Petugas');

// Detail Transaksi
$query = "SELECT t.*, u.nama, u.nis, u.kelas, u.no_telp 
          FROM transaksi t 
          JOIN users u ON t.id_user = u.id_user 
          WHERE t.id_transaksi = $idTransaksi";
$trx = mysqli_fetch_assoc(mysqli_query($connect, $query));

if (!$trx) {
    echo "<script>alert('Data tidak ditemukan!'); window.location='denda.php';</script>";
    exit;
}

// Alat Detail
$alat = queryReadData("SELECT dt.*, a.nama_alat_olahraga 
                       FROM detail_transaksi dt 
                       JOIN alat_olahraga a ON dt.id_alat_olahraga = a.id_alat_olahraga 
                       WHERE dt.id_transaksi = $idTransaksi");

/* BAYAR */
if (isset($_POST['bayar'])) {
    bayarDenda($idTransaksi, $_SESSION['id_user']);
    echo "<script>alert('Pembayaran denda diterima!'); window.location='detailDenda.php?id=$idTransaksi';</script>";
    exit;
}

if (isset($_POST['approve_pembayaran'])) {
    mysqli_query($connect, "UPDATE transaksi SET pembayaran = 'lunas', alasan_penolakan = NULL WHERE id_transaksi = $idTransaksi");
    tambahLog($_SESSION['id_user'], "Petugas Menyetujui bukti pembayaran denda ID: $idTransaksi");
    echo "<script>alert('Pembayaran Disetujui!'); window.location='detailDenda.php?id=$idTransaksi';</script>";
    exit;
}

if (isset($_POST['reject_pembayaran'])) {
    $alasan = mysqli_real_escape_string($connect, $_POST['alasan_penolakan']);
    mysqli_query($connect, "UPDATE transaksi SET pembayaran = 'ditolak', alasan_penolakan = '$alasan' WHERE id_transaksi = $idTransaksi");
    tambahLog($_SESSION['id_user'], "Petugas Menolak bukti pembayaran denda ID: $idTransaksi. Alasan: $alasan");
    echo "<script>alert('Pembayaran Ditolak!'); window.location='detailDenda.php?id=$idTransaksi';</script>";
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Denda - PopFit Petugas</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        popfit: {
                            dark: '#2A4736',
                            light: '#3E614C',
                            accent: '#F5C460',
                            accentHover: '#E3B24F',
                            bg: '#F4F4F5',
                            surface: '#FFFFFF',
                            border: '#E4E4E7',
                            text: '#1F2937',
                            textMuted: '#6B7280'
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
        .sidebar { transition: transform 0.3s ease-in-out; }
    </style>
</head>
<body class="bg-popfit-bg text-popfit-text font-sans h-screen overflow-hidden flex text-[13px]">

    <div id="sidebarOverlay" class="fixed inset-0 bg-black/50 z-40 hidden transition-opacity"></div>

    <!-- DESKTOP SIDEBAR -->
    <aside id="sidebar" class="fixed inset-y-0 left-0 w-64 bg-popfit-dark text-white border-r border-popfit-dark h-full flex-shrink-0 z-50 sidebar -translate-x-full md:translate-x-0 md:static flex flex-col">
        <div class="h-16 flex items-center px-6 border-b border-popfit-light bg-popfit-dark justify-between">
            <div class="flex items-center">
                <i class="ph-fill ph-paw-print text-popfit-accent text-2xl mr-3"></i>
                <span class="text-xl font-bold tracking-wide">PopFit Petugas</span>
            </div>
            <button id="closeSidebar" class="md:hidden text-gray-400 hover:text-white"><i class="ph ph-x text-2xl"></i></button>
        </div>

        <nav class="flex-1 overflow-y-auto py-4">
            <ul class="space-y-1">
                <li><a href="../dashboardPetugas.php" class="flex items-center px-6 py-3 text-gray-200 hover:bg-popfit-light transition-colors border-l-4 border-transparent">
                    <i class="ph ph-squares-four text-xl w-6"></i><span class="ml-3 text-sm font-medium">Beranda</span>
                </a></li>
                <li class="px-6 py-2 mt-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Operasional</li>
                <li><a href="../alat/daftarAlat.php" class="flex items-center px-6 py-3 text-gray-200 hover:bg-popfit-light transition-colors border-l-4 border-transparent">
                    <i class="ph ph-basketball text-xl w-6"></i><span class="ml-3 text-sm font-medium">Stok Alat</span>
                </a></li>
                <li><a href="../transaksi/transaksi.php" class="flex items-center px-6 py-3 text-gray-200 hover:bg-popfit-light transition-colors border-l-4 border-transparent">
                    <i class="ph ph-arrows-left-right text-xl w-6"></i><span class="ml-3 text-sm font-medium">Transaksi</span>
                </a></li>
                <li><a href="denda.php" class="nav-active flex items-center px-6 py-3 text-gray-200 hover:bg-popfit-light transition-colors border-l-4 border-transparent">
                    <i class="ph ph-wallet text-xl w-6"></i><span class="ml-3 text-sm font-medium">Kelola Denda</span>
                </a></li>
            </ul>
        </nav>

        <div class="border-t border-popfit-light p-4">
            <div class="flex items-center w-full">
                <div class="w-8 h-8 rounded-sm bg-popfit-accent flex items-center justify-center text-popfit-dark font-bold"><?= substr($petugasUsername, 0, 1) ?></div>
                <div class="ml-3 flex-1 overflow-hidden">
                    <p class="text-sm font-medium text-white truncate"><?= $petugasUsername ?></p>
                    <p class="text-xs text-gray-400 truncate">Petugas</p>
                </div>
                <a href="../../logout.php" class="text-gray-400 hover:text-white transition-colors"><i class="ph ph-sign-out text-xl"></i></a>
            </div>
        </div>
    </aside>

    <!-- MAIN CONTENT -->
    <div class="flex-1 flex flex-col h-screen w-full relative">
        <header class="h-16 bg-popfit-surface border-b border-popfit-border flex items-center justify-between px-6 flex-shrink-0 text-[13px]">
            <div class="flex items-center space-x-4">
                <button id="openSidebar" class="md:hidden text-popfit-dark"><i class="ph ph-list text-2xl"></i></button>
                <a href="denda.php" class="text-popfit-textMuted hover:text-popfit-dark"><i class="ph ph-arrow-left text-xl"></i></a>
                <h2 class="text-lg font-bold text-popfit-dark">Detail Denda #<?= $idTransaksi ?></h2>
            </div>
        </header>

        <main class="flex-1 overflow-y-auto p-6">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Info Peminjam & Alat -->
                <div class="lg:col-span-2 space-y-6">
                    <div class="bg-white border border-popfit-border rounded-sm p-8">
                        <div class="flex items-start justify-between mb-8">
                            <div>
                                <h3 class="text-[10px] font-black uppercase text-popfit-textMuted tracking-widest mb-1">DATA PEMINJAM</h3>
                                <h4 class="text-xl font-black text-popfit-dark leading-tight uppercase tracking-tighter"><?= htmlspecialchars($trx['nama']) ?></h4>
                                <p class="text-xs font-bold text-popfit-textMuted uppercase mt-1 tracking-widest"><?= $trx['nis'] ?> • Kelas <?= $trx['kelas'] ?></p>
                            </div>
                            <div class="text-right">
                                <h3 class="text-[10px] font-black uppercase text-popfit-textMuted tracking-widest mb-1">WAktu PINJAM</h3>
                                <p class="text-sm font-bold text-popfit-dark uppercase tracking-tighter"><?= date('d M Y, H:i', strtotime($trx['waktu_pinjam'])) ?></p>
                            </div>
                        </div>

                        <div class="h-px bg-gray-100 mb-8"></div>

                        <h3 class="text-[10px] font-black uppercase text-popfit-textMuted tracking-widest mb-4 flex items-center">
                            <i class="ph ph-package text-lg mr-2"></i> Alat yang Dipinjam
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8">
                            <?php foreach($alat as $a): ?>
                            <div class="flex items-center justify-between p-4 bg-popfit-bg border border-popfit-border rounded-sm">
                                <span class="text-sm font-bold text-popfit-dark uppercase tracking-tighter"><?= htmlspecialchars($a['nama_alat_olahraga']) ?></span>
                                <span class="text-[10px] font-black bg-popfit-dark text-white px-2 py-1 rounded-sm uppercase tracking-widest"><?= $a['jumlah'] ?> PCS</span>
                            </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-y-6">
                            <div>
                                <p class="text-[10px] uppercase font-black text-red-500 tracking-tighter mb-1 italic">Batas Kembali</p>
                                <p class="text-sm font-black text-red-600"><?= date('d M Y, H:i', strtotime($trx['batas_kembali'])) ?></p>
                            </div>
                            <div>
                                <p class="text-[10px] uppercase font-black text-popfit-textMuted tracking-tighter mb-1">Waktu Kembali</p>
                                <p class="text-sm font-bold text-popfit-dark"><?= $trx['waktu_kembali'] ? date('d M Y, H:i', strtotime($trx['waktu_kembali'])) : 'BELUM KEMBALI' ?></p>
                            </div>
                            <?php if($trx['keterlambatan'] == 'ya'): 
                                $det = cekDetailKeterlambatan($trx['batas_kembali'], $trx['waktu_kembali']);    
                            ?>
                            <div class="col-span-1 md:col-span-2 p-4 bg-red-50 border border-red-100 rounded-sm">
                                <p class="text-[10px] uppercase font-black text-red-600 tracking-widest mb-1 flex items-center">
                                    <i class="ph ph-clock-countdown text-lg mr-2"></i> Keterangan Keterlambatan
                                </p>
                                <p class="text-sm font-black text-red-700 uppercase leading-none">Terlambat: <?= $det['teks'] ?></p>
                                <p class="text-[10px] font-bold text-red-500 uppercase mt-1">Denda Keterlambatan: Rp <?= number_format($det['denda'], 0, ',', '.') ?></p>
                            </div>
                            <?php endif; ?>
                            <?php if($trx['denda_kerusakan'] > 0): ?>
                            <div class="col-span-1 md:col-span-2 p-4 bg-orange-50 border border-orange-100 rounded-sm">
                                <p class="text-[10px] uppercase font-black text-orange-600 tracking-widest mb-1 flex items-center">
                                    <i class="ph ph-wrench text-lg mr-2"></i> Denda Kerusakan
                                </p>
                                <p class="text-sm font-black text-orange-700 uppercase leading-none">Jumlah: Rp <?= number_format($trx['denda_kerusakan'], 0, ',', '.') ?></p>
                                <p class="text-[10px] font-bold text-orange-500 uppercase mt-1">Catatan Kondisi: <?= htmlspecialchars($trx['kondisi'] ?: '-') ?></p>
                            </div>
                            <?php endif; ?>
                            <div>
                                <p class="text-[10px] uppercase font-black text-popfit-textMuted tracking-tighter mb-1">Kontak Siswa</p>
                                <p class="text-sm font-bold text-popfit-dark"><?= $trx['no_telp'] ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Konfirmasi Pembayaran -->
                <div class="space-y-6">
                    <div class="bg-white border border-popfit-border rounded-sm p-8 text-center ring-2 ring-red-500 ring-offset-2">
                        <p class="text-[10px] font-black uppercase text-popfit-textMuted tracking-widest mb-4">Total Denda Harus Dibayar</p>
                        <h4 class="text-4xl font-black text-red-600 mb-2 leading-none uppercase tracking-tighter">Rp <?= number_format($trx['denda'], 0, ',', '.') ?></h4>
                        
                        <?php
                            $st = ($trx['pembayaran'] == 'lunas') ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700 border border-red-200';
                        ?>
                        <span class="inline-block px-3 py-1 rounded-sm text-[10px] font-black uppercase tracking-widest <?= $st ?> mb-8">
                            <?= strtoupper($trx['pembayaran'] ?: 'BELUM BAYAR') ?>
                        </span>

                        <div class="h-px bg-gray-100 mb-8"></div>

                        <?php if($trx['pembayaran'] == 'pending'): ?>
                            <div class="p-4 bg-popfit-accent/10 border border-popfit-accent/20 rounded-sm text-left">
                                <h5 class="text-[10px] font-black text-popfit-dark uppercase mb-4 flex items-center">
                                    <i class="ph-fill ph-warning-circle text-popfit-accent mr-2"></i> Verifikasi Siswa
                                </h5>
                                
                                <div class="grid grid-cols-2 gap-4 mb-6">
                                    <div>
                                        <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1">Metode:</p>
                                        <p class="text-xs font-black text-popfit-dark uppercase"><?= $trx['metode_pembayaran_denda'] ?></p>
                                    </div>
                                    <div>
                                        <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1">Pengirim:</p>
                                        <p class="text-xs font-black text-popfit-dark uppercase"><?= $trx['nama_pengirim_pembayaran'] ?: '-' ?></p>
                                    </div>
                                </div>

                                <?php if($trx['catatan_pembayaran']): ?>
                                <div class="mb-6">
                                    <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1">Catatan Siswa:</p>
                                    <p class="text-xs font-bold text-popfit-dark italic">"<?= htmlspecialchars($trx['catatan_pembayaran']) ?>"</p>
                                </div>
                                <?php endif; ?>

                                <div class="mb-6">
                                    <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-2">Bukti:</p>
                                    <a href="../../uploads/<?= $trx['bukti_pembayaran'] ?>" target="_blank" class="block group relative overflow-hidden rounded-sm border border-popfit-border">
                                        <img src="../../uploads/<?= $trx['bukti_pembayaran'] ?>" class="w-full h-48 object-cover group-hover:scale-110 transition-transform">
                                        <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center text-white text-[10px] font-black uppercase">Lihat Full</div>
                                    </a>
                                </div>

                                <div id="verifyActions">
                                    <div class="flex gap-2 mb-4">
                                        <form method="POST" class="flex-1">
                                            <button type="submit" name="approve_pembayaran" class="w-full bg-popfit-dark text-white py-3 rounded-sm text-[10px] font-black uppercase tracking-widest hover:bg-popfit-light transition-all flex items-center justify-center" onclick="return confirm('Terima pembayaran denda ini?')">
                                                <i class="ph-bold ph-check mr-2"></i> Terima
                                            </button>
                                        </form>
                                        <button type="button" onclick="showRejectForm()" class="flex-1 bg-white border border-red-500 text-red-500 py-3 rounded-sm text-[10px] font-black uppercase tracking-widest hover:bg-red-50 transition-all flex items-center justify-center">
                                            <i class="ph-bold ph-x mr-2"></i> Tolak
                                        </button>
                                    </div>

                                    <form method="POST" id="rejectForm" class="hidden animate-in fade-in slide-in-from-top-2 duration-300 space-y-3 pt-4 border-t border-popfit-accent/20">
                                        <p class="text-[10px] font-black text-red-600 uppercase">Alasan Penolakan:</p>
                                        <textarea name="alasan_penolakan" required placeholder="MISAL: BUKTI TIDAK JELAS" class="w-full bg-white border border-red-200 rounded-sm px-3 py-2 text-[11px] font-bold text-popfit-dark focus:border-red-500 outline-none transition-all uppercase"></textarea>
                                        <div class="flex gap-2">
                                            <button type="submit" name="reject_pembayaran" class="flex-1 bg-red-600 text-white py-2 rounded-sm text-[9px] font-black uppercase tracking-widest hover:bg-red-700 transition-all">Konfirmasi</button>
                                            <button type="button" onclick="hideRejectForm()" class="px-4 py-2 text-[9px] font-black uppercase text-gray-400">Batal</button>
                                        </div>
                                    </form>
                                </div>

                                <script>
                                    function showRejectForm() { document.getElementById('rejectForm').classList.remove('hidden'); }
                                    function hideRejectForm() { document.getElementById('rejectForm').classList.add('hidden'); }
                                </script>
                            </div>
                        <?php elseif($trx['pembayaran'] == 'belum bayar'): ?>
                            <form method="POST">
                                <button type="submit" name="bayar" class="w-full bg-popfit-dark text-white rounded-sm py-4 text-xs font-black uppercase tracking-widest hover:bg-popfit-light transition-all mb-4" onclick="return confirm('Sudah menerima pembayaran sebesar Rp <?= number_format($trx['denda'], 0, ',', '.') ?>?')">
                                    Konfirmasi Pembayaran
                                </button>
                                <p class="text-[10px] text-popfit-textMuted italic leading-relaxed">Pastikan Anda telah menerima uang tunai dari siswa sebelum menekan tombol di atas.</p>
                            </form>
                        <?php else: ?>
                            <div class="py-4">
                                <i class="ph ph-check-circle text-5xl text-green-500 mb-4"></i>
                                <p class="text-xs font-black text-green-600 uppercase tracking-widest leading-loose">Pembayaran Selesai</p>
                                <p class="text-[10px] text-popfit-textMuted mt-1">Diterima oleh petugas pada waktu pengembalian alat.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
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
