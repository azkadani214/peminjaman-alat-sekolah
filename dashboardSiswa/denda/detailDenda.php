<?php
require '../../config/config.php';
session_start();

// CEK LOGIN SISWA
if (!isset($_SESSION["login"]) || $_SESSION["role"] != "siswa") {
    header("Location: ../../login.php");
    exit;
}

$idTransaksi = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$id_user = $_SESSION['id_user'];
$siswaUsername = htmlspecialchars($_SESSION['username'] ?? 'siswa');

// Ambil Detail Transaksi
$query = "SELECT t.* 
          FROM transaksi t 
          WHERE t.id_transaksi = $idTransaksi AND t.id_user = $id_user";
$trx = mysqli_fetch_assoc(mysqli_query($connect, $query));

if (!$trx) {
    echo "<script>alert('Data tidak ditemukan!'); window.location='denda.php';</script>";
    exit;
}

// Ambil Alat yang dipinjam
$alat = queryReadData("SELECT dt.*, a.nama_alat_olahraga 
                       FROM detail_transaksi dt 
                       JOIN alat_olahraga a ON dt.id_alat_olahraga = a.id_alat_olahraga 
                       WHERE dt.id_transaksi = $idTransaksi");

// HITUNG KERANJANG UNTUK BADGE
$countKeranjangQuery = mysqli_query($connect, "SELECT COUNT(*) as total FROM keranjang WHERE id_user = '$id_user'");
$countKeranjang = ($countKeranjangQuery) ? mysqli_fetch_assoc($countKeranjangQuery)['total'] : 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Denda Saya - PopFit Siswa</title>
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
    </style>
</head>
<body class="bg-popfit-bg text-popfit-text font-sans h-screen overflow-hidden flex">

    <!-- DESKTOP SIDEBAR -->
    <aside class="hidden md:flex flex-col w-64 bg-popfit-dark text-white border-r border-popfit-dark h-full flex-shrink-0">
        <div class="h-16 flex items-center px-6 border-b border-popfit-light bg-popfit-dark">
            <i class="ph-fill ph-paw-print text-popfit-accent text-2xl mr-3"></i>
            <span class="text-xl font-bold tracking-wide">PopFit</span>
        </div>

        <nav class="flex-1 overflow-y-auto py-4">
            <ul class="space-y-1">
                <li><a href="../dashboardSiswa.php" class="flex items-center px-6 py-3 text-gray-200 hover:bg-popfit-light transition-colors border-l-4 border-transparent">
                    <i class="ph ph-squares-four text-xl w-6"></i><span class="ml-3 text-sm font-medium">Beranda</span>
                </a></li>
                <li class="px-6 py-2 mt-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Peminjaman</li>
                <li><a href="../alat/daftarAlat.php" class="flex items-center px-6 py-3 text-gray-200 hover:bg-popfit-light transition-colors border-l-4 border-transparent">
                    <i class="ph ph-basketball text-xl w-6"></i><span class="ml-3 text-sm font-medium">Cari Alat</span>
                </a></li>
                <li><a href="../transaksi/transaksi.php" class="flex items-center px-6 py-3 text-gray-200 hover:bg-popfit-light transition-colors border-l-4 border-transparent">
                    <i class="ph ph-arrows-left-right text-xl w-6"></i><span class="ml-3 text-sm font-medium">Transaksi Saya</span>
                </a></li>
                <li><a href="denda.php" class="nav-active flex items-center px-6 py-3 text-gray-200 hover:bg-popfit-light transition-colors border-l-4 border-transparent">
                    <i class="ph ph-wallet text-xl w-6"></i><span class="ml-3 text-sm font-medium">Denda</span>
                </a></li>
                <li><a href="../riwayat/riwayat.php" class="flex items-center px-6 py-3 text-gray-200 hover:bg-popfit-light transition-colors border-l-4 border-transparent">
                    <i class="ph ph-clock-rotate-left text-xl w-6"></i><span class="ml-3 text-sm font-medium">Riwayat</span>
                </a></li>
            </ul>
        </nav>

        <div class="border-t border-popfit-light p-4">
            <div class="flex items-center w-full">
                <div class="w-8 h-8 rounded-sm bg-popfit-accent flex items-center justify-center text-popfit-dark font-bold"><?= substr($siswaUsername, 0, 1) ?></div>
                <div class="ml-3 flex-1 overflow-hidden">
                    <p class="text-sm font-medium text-white truncate"><?= $siswaUsername ?></p>
                    <p class="text-xs text-gray-400 truncate">Siswa</p>
                </div>
                <a href="../../logout.php" class="text-gray-400 hover:text-white transition-colors"><i class="ph ph-sign-out text-xl"></i></a>
            </div>
        </div>
    </aside>

    <!-- MAIN CONTENT -->
    <div class="flex-1 flex flex-col h-screen w-full relative">
        <header class="h-16 bg-popfit-surface border-b border-popfit-border flex items-center justify-between px-6 flex-shrink-0">
            <div class="flex items-center space-x-4">
                <a href="denda.php" class="text-popfit-textMuted hover:text-popfit-dark"><i class="ph ph-arrow-left text-xl"></i></a>
                <h2 class="text-lg font-bold text-popfit-dark">Detail Denda #<?= $idTransaksi ?></h2>
            </div>
            <div class="flex items-center">
                <a href="../keranjang/keranjang.php" class="relative text-popfit-textMuted hover:text-popfit-dark transition-colors">
                    <i class="ph ph-shopping-cart text-2xl"></i>
                    <?php if($countKeranjang > 0): ?>
                    <span class="absolute -top-1.5 -right-1.5 bg-popfit-accent text-popfit-dark text-[10px] font-bold w-5 h-5 flex items-center justify-center rounded-full border-2 border-white"><?= $countKeranjang ?></span>
                    <?php endif; ?>
                </a>
            </div>
        </header>

        <main class="flex-1 overflow-y-auto p-6">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Info Peminjaman -->
                <div class="lg:col-span-2 space-y-6">
                    <div class="bg-white border border-popfit-border rounded-sm p-8">
                        <h3 class="text-[10px] font-black uppercase text-popfit-textMuted tracking-widest mb-6 flex items-center">
                            <i class="ph ph-info text-lg mr-2"></i> Rincian Peminjaman
                        </h3>
                        
                        <div class="space-y-4">
                            <?php foreach($alat as $a): ?>
                            <div class="flex items-center justify-between p-4 bg-popfit-bg border border-popfit-border rounded-sm">
                                <div class="flex items-center">
                                    <i class="ph ph-package text-xl text-popfit-dark mr-3"></i>
                                    <span class="text-sm font-bold text-popfit-dark leading-tight"><?= htmlspecialchars($a['nama_alat_olahraga']) ?></span>
                                </div>
                                <span class="text-[10px] font-black bg-popfit-dark text-white px-2 py-1 rounded-sm uppercase tracking-tighter"><?= $a['jumlah'] ?> PCS</span>
                            </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="h-px bg-gray-100 my-8"></div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-y-6">
                            <div>
                                <p class="text-[10px] uppercase font-black text-gray-400 tracking-tighter mb-1">Waktu Pinjam</p>
                                <p class="text-sm font-bold text-popfit-dark"><?= date('d M Y, H:i', strtotime($trx['waktu_pinjam'])) ?></p>
                            </div>
                            <div>
                                <p class="text-[10px] uppercase font-black text-red-400 tracking-tighter mb-1 italic">Batas Kembali</p>
                                <p class="text-sm font-black text-red-600"><?= date('d M Y, H:i', strtotime($trx['batas_kembali'])) ?></p>
                            </div>
                            <div>
                                <p class="text-[10px] uppercase font-black text-gray-400 tracking-tighter mb-1">Waktu Kembali</p>
                                <p class="text-sm font-bold text-popfit-dark"><?= $trx['waktu_kembali'] ? date('d M Y, H:i', strtotime($trx['waktu_kembali'])) : 'BELUM DIKEMBALIKAN' ?></p>
                            </div>
                            <div>
                                <p class="text-[10px] uppercase font-black text-gray-400 tracking-tighter mb-1">Status Transaksi</p>
                                <p class="text-sm font-bold text-popfit-dark uppercase tracking-widest"><?= $trx['status'] ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Status Denda -->
                <div class="space-y-6">
                    <div class="bg-white border border-popfit-border rounded-sm p-8 text-center ring-2 ring-red-500 ring-offset-2">
                        <p class="text-[10px] font-black uppercase text-popfit-textMuted tracking-widest mb-4">Total Denda Anda</p>
                        <h4 class="text-3xl font-black text-red-600 mb-2 leading-none uppercase tracking-tighter">Rp <?= number_format($trx['denda'], 0, ',', '.') ?></h4>
                        
                        <?php
                            $st = ($trx['pembayaran'] == 'lunas') ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700 border border-red-200';
                        ?>
                        <span class="inline-block px-3 py-1 rounded-sm text-[10px] font-black uppercase tracking-widest <?= $st ?> mb-8">
                            <?= strtoupper($trx['pembayaran'] ?: 'BELUM BAYAR') ?>
                        </span>

                        <div class="h-px bg-gray-100 mb-8"></div>

                        <?php if($trx['pembayaran'] != 'lunas'): ?>
                            <div class="p-4 bg-popfit-bg border border-popfit-border rounded-sm text-left">
                                <h5 class="text-[10px] font-black text-popfit-dark uppercase mb-2">Instruksi Pembayaran:</h5>
                                <ol class="text-[11px] text-popfit-textMuted space-y-2 list-decimal ml-4">
                                    <li>Kunjungi meja petugas/admin di ruang Olahraga.</li>
                                    <li>Sebutkan ID Transaksi <span class="font-bold text-popfit-dark">#<?= $idTransaksi ?></span>.</li>
                                    <li>Bayar tunai sebesar denda di atas.</li>
                                    <li>Pastikan status berubah menjadi <span class="font-bold text-green-600">LUNAS</span> setelah bayar.</li>
                                </ol>
                            </div>
                        <?php else: ?>
                            <div class="py-4">
                                <i class="ph ph-check-circle text-5xl text-green-500 mb-4"></i>
                                <p class="text-xs font-black text-green-600 uppercase tracking-widest leading-loose">Denda Sudah Lunas</p>
                                <p class="text-[10px] text-popfit-textMuted mt-1 italic">Terima kasih atas kerjasamanya!</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </main>
    </div>

</body>
</html>
