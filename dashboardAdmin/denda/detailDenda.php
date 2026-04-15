<?php
require '../../config/config.php';
session_start();

if (!isset($_SESSION["login"]) || !in_array($_SESSION["role"], ["admin utama", "admin", "petugas"])) {
    header("Location: ../../login.php");
    exit;
}

$idTransaksi = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$adminUsername = htmlspecialchars($_SESSION['username'] ?? 'Admin');

// Ambil Detail Transaksi
$query = "SELECT t.*, u.nama, u.nis, u.kelas, u.no_telp 
          FROM transaksi t 
          JOIN users u ON t.id_user = u.id_user 
          WHERE t.id_transaksi = $idTransaksi";
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

/* HANDLE PEMBAYARAN */
if (isset($_POST['bayar'])) {
    bayarDenda($idTransaksi, $_SESSION['id_user']);
    echo "<script>alert('Pembayaran berhasil dikonfirmasi!'); window.location='detailDenda.php?id=$idTransaksi';</script>";
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Denda - PopFit Admin</title>
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
            <span class="text-xl font-bold tracking-wide">PopFit Admin</span>
        </div>

        <nav class="flex-1 overflow-y-auto py-4">
            <ul class="space-y-1">
                <li><a href="../dashboardAdmin.php" class="flex items-center px-6 py-3 text-gray-200 hover:bg-popfit-light transition-colors border-l-4 border-transparent">
                    <i class="ph ph-squares-four text-xl w-6"></i><span class="ml-3 text-sm font-medium">Dashboard</span>
                </a></li>
                <li class="px-6 py-2 mt-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Manajemen</li>
                <li><a href="../alat/daftarAlat.php" class="flex items-center px-6 py-3 text-gray-200 hover:bg-popfit-light transition-colors border-l-4 border-transparent">
                    <i class="ph ph-basketball text-xl w-6"></i><span class="ml-3 text-sm font-medium">Katalog Alat</span>
                </a></li>
                <li><a href="../petugas/petugas.php" class="flex items-center px-6 py-3 text-gray-200 hover:bg-popfit-light transition-colors border-l-4 border-transparent">
                    <i class="ph ph-user-tie text-xl w-6"></i><span class="ml-3 text-sm font-medium">Petugas</span>
                </a></li>
                <li><a href="../siswa/siswa.php" class="flex items-center px-6 py-3 text-gray-200 hover:bg-popfit-light transition-colors border-l-4 border-transparent">
                    <i class="ph ph-users text-xl w-6"></i><span class="ml-3 text-sm font-medium">Siswa</span>
                </a></li>
                <li class="px-6 py-2 mt-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Sirkulasi</li>
                <li><a href="../transaksi/transaksi.php" class="flex items-center px-6 py-3 text-gray-200 hover:bg-popfit-light transition-colors border-l-4 border-transparent">
                    <i class="ph ph-arrows-left-right text-xl w-6"></i><span class="ml-3 text-sm font-medium">Transaksi</span>
                </a></li>
                <li><a href="denda.php" class="nav-active flex items-center px-6 py-3 text-gray-200 hover:bg-popfit-light transition-colors border-l-4 border-transparent">
                    <i class="ph ph-wallet text-xl w-6"></i><span class="ml-3 text-sm font-medium">Denda</span>
                </a></li>
                <li><a href="../log/log.php" class="flex items-center px-6 py-3 text-gray-200 hover:bg-popfit-light transition-colors border-l-4 border-transparent">
                    <i class="ph ph-clock-rotate-left text-xl w-6"></i><span class="ml-3 text-sm font-medium">Log Aktivitas</span>
                </a></li>
                <li><a href="../laporan/laporan.php" class="flex items-center px-6 py-3 text-gray-200 hover:bg-popfit-light transition-colors border-l-4 border-transparent">
                    <i class="ph ph-file-text text-xl w-6"></i><span class="ml-3 text-sm font-medium">Laporan</span>
                </a></li>
            </ul>
        </nav>

        <div class="border-t border-popfit-light p-4">
            <div class="flex items-center w-full">
                <div class="w-8 h-8 rounded-sm bg-popfit-accent flex items-center justify-center text-popfit-dark font-bold">A</div>
                <div class="ml-3 flex-1 overflow-hidden">
                    <p class="text-sm font-medium text-white truncate"><?= $adminUsername ?></p>
                    <p class="text-xs text-gray-400 truncate">Admin</p>
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
        </header>

        <main class="flex-1 overflow-y-auto p-6">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Info Peminjam -->
                <div class="lg:col-span-2 space-y-6">
                    <div class="bg-white border border-popfit-border rounded-sm p-8">
                        <h3 class="text-[10px] font-black uppercase text-popfit-textMuted tracking-widest mb-6 flex items-center">
                            <i class="ph ph-user-circle text-lg mr-2"></i> Informasi Peminjam
                        </h3>
                        <div class="grid grid-cols-2 gap-y-6">
                            <div>
                                <p class="text-[10px] uppercase font-black text-gray-400 tracking-tighter mb-1">Nama Lengkap</p>
                                <p class="text-sm font-bold text-popfit-dark"><?= htmlspecialchars($trx['nama']) ?></p>
                            </div>
                            <div>
                                <p class="text-[10px] uppercase font-black text-gray-400 tracking-tighter mb-1">NIS / ID</p>
                                <p class="text-sm font-bold text-popfit-dark uppercase tracking-widest"><?= $trx['nis'] ?></p>
                            </div>
                            <div>
                                <p class="text-[10px] uppercase font-black text-gray-400 tracking-tighter mb-1">Kelas</p>
                                <p class="text-sm font-bold text-popfit-dark uppercase tracking-widest"><?= $trx['kelas'] ?></p>
                            </div>
                            <div>
                                <p class="text-[10px] uppercase font-black text-gray-400 tracking-tighter mb-1">No. Telepon</p>
                                <p class="text-sm font-bold text-popfit-dark lowercase tracking-widest"><?= $trx['no_telp'] ?></p>
                            </div>
                        </div>

                        <div class="h-px bg-gray-100 my-8"></div>

                        <h3 class="text-[10px] font-black uppercase text-popfit-textMuted tracking-widest mb-6 flex items-center">
                            <i class="ph ph-basketball text-lg mr-2"></i> Rincian Alat
                        </h3>
                        <div class="space-y-3">
                            <?php if(empty($alat)): ?>
                                <p class="text-sm text-gray-400 italic">Tidak ada rincian alat.</p>
                            <?php else: ?>
                                <?php foreach($alat as $a): ?>
                                <div class="flex items-center justify-between p-3 bg-popfit-bg border border-popfit-border rounded-sm">
                                    <span class="text-xs font-bold text-popfit-dark"><?= htmlspecialchars($a['nama_alat_olahraga']) ?></span>
                                    <span class="text-[10px] font-black bg-popfit-dark text-white px-2 py-0.5 rounded-sm uppercase tracking-tighter"><?= $a['jumlah'] ?> PCS</span>
                                </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="bg-white border border-popfit-border rounded-sm p-8">
                        <h3 class="text-[10px] font-black uppercase text-popfit-textMuted tracking-widest mb-6 flex items-center">
                            <i class="ph ph-calendar text-lg mr-2"></i> Timeline Peminjaman
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 text-center md:text-left">
                            <div class="relative pl-6 md:pl-0">
                                <p class="text-[10px] uppercase font-black text-gray-400 tracking-tighter mb-1">Waktu Pinjam</p>
                                <p class="text-xs font-bold text-popfit-dark"><?= date('d M Y, H:i', strtotime($trx['waktu_pinjam'])) ?></p>
                            </div>
                            <div>
                                <p class="text-[10px] uppercase font-black text-red-400 tracking-tighter mb-1 italic">Batas Kembali</p>
                                <p class="text-xs font-black text-red-600"><?= date('d M Y, H:i', strtotime($trx['batas_kembali'])) ?></p>
                            </div>
                            <div>
                                <p class="text-[10px] uppercase font-black text-gray-400 tracking-tighter mb-1">Waktu Kembali</p>
                                <p class="text-xs font-bold text-popfit-dark"><?= $trx['waktu_kembali'] ? date('d M Y, H:i', strtotime($trx['waktu_kembali'])) : 'BELUM KEMBALI' ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tagihan -->
                <div class="space-y-6">
                    <div class="bg-white border border-popfit-border rounded-sm p-8 text-center ring-2 ring-red-500 ring-offset-2">
                        <p class="text-[10px] font-black uppercase text-popfit-textMuted tracking-widest mb-4">Tagihan Denda</p>
                        <h4 class="text-3xl font-black text-red-600 mb-2 leading-none uppercase tracking-tighter">Rp <?= number_format($trx['denda'], 0, ',', '.') ?></h4>
                        
                        <?php
                            $st = ($trx['pembayaran'] == 'lunas') ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700 border border-red-200';
                        ?>
                        <span class="inline-block px-3 py-1 rounded-sm text-[10px] font-black uppercase tracking-widest <?= $st ?> mb-8">
                            <?= strtoupper($trx['pembayaran'] ?: 'BELUM BAYAR') ?>
                        </span>

                        <div class="h-px bg-gray-100 mb-8"></div>

                        <?php if($trx['pembayaran'] != 'lunas' && $trx['denda'] > 0): ?>
                            <p class="text-[11px] text-popfit-textMuted leading-relaxed mb-6 uppercase font-bold px-4">Pastikan Anda telah menerima uang tunai dari siswa.</p>
                            <form method="POST">
                                <button type="submit" name="bayar" class="w-full py-4 bg-popfit-dark text-white text-xs font-black uppercase tracking-[0.2em] rounded-sm hover:bg-popfit-light transition-all shadow-lg active:scale-95" 
                                        onclick="return confirm('Konfirmasi pembayaran denda secara tunai?')">
                                    Lunaskan Denda
                                </button>
                            </form>
                        <?php else: ?>
                            <div class="py-4">
                                <i class="ph ph-check-circle text-5xl text-green-500 mb-4"></i>
                                <p class="text-xs font-black text-green-600 uppercase tracking-widest leading-loose">Tagihan Sudah Lunas<br>Tgl: <?= date('d M Y') ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </main>
    </div>

</body>
</html>
