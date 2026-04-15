<?php
require '../../config/config.php';
session_start();

if (!isset($_SESSION["login"]) || $_SESSION["role"] != "siswa") {
    header("Location: ../../login.php");
    exit;
}

$id = $_GET['id'] ?? '';
$id_user = $_SESSION['id_user'];
$siswaName = htmlspecialchars($_SESSION['nama'] ?? 'Siswa');

$query = "SELECT t.*, u.nama, u.nis FROM transaksi t JOIN users u ON t.id_user = u.id_user WHERE t.id_transaksi = '$id' AND t.id_user = '$id_user'";
$res = mysqli_query($connect, $query);
$trans = mysqli_fetch_assoc($res);

if(!$trans){
    header("Location: transaksi.php");
    exit;
}

$items = mysqli_query($connect, "SELECT dt.*, a.nama_alat_olahraga, a.foto_alat_olahraga FROM detail_transaksi dt JOIN alat_olahraga a ON dt.id_alat_olahraga = a.id_alat_olahraga WHERE dt.id_transaksi = '$id'");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Transaksi - PopFit Siswa</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
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
    </style>
</head>
<body class="bg-popfit-bg text-popfit-text font-sans h-screen overflow-hidden flex text-[13px]">

    <?php 
        $rel = "../"; 
        $activeIndex = "transaksi"; 
        include '../../layout/sidebar_siswa.php'; 
    ?>

    <div class="flex-1 flex flex-col h-screen overflow-hidden">
        <?php 
            $pageTitle = "Rincian Peminjaman #$id"; 
            include '../../layout/header_siswa.php'; 
        ?>

        <main class="flex-1 overflow-y-auto p-6">
            <div class="max-w-4xl mx-auto space-y-6">
                <!-- Info Section -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-white border border-popfit-border p-5 rounded-sm">
                        <p class="text-[9px] font-black text-popfit-textMuted uppercase tracking-widest mb-1">Status Saat Ini</p>
                        <span class="px-2 py-1 rounded-sm text-[9px] font-black uppercase bg-popfit-dark text-white"><?= $trans['status'] ?></span>
                    </div>
                    <div class="bg-white border border-popfit-border p-5 rounded-sm">
                        <p class="text-[9px] font-black text-popfit-textMuted uppercase tracking-widest mb-1">Batas Pengembalian</p>
                        <p class="text-[11px] font-black text-popfit-dark lowercase"><?= date('d F Y', strtotime($trans['batas_kembali'])) ?></p>
                    </div>
                    <div class="bg-white border border-popfit-border p-5 rounded-sm">
                        <p class="text-[9px] font-black text-popfit-textMuted uppercase tracking-widest mb-1">Total Denda</p>
                        <p class="text-[11px] font-black text-red-600 uppercase">Rp <?= number_format($trans['denda'],0,',','.') ?></p>
                    </div>
                </div>

                <!-- Items Table -->
                <div class="bg-white border border-popfit-border rounded-sm overflow-hidden">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="bg-popfit-bg border-b border-popfit-border">
                                <th class="px-6 py-4 font-black text-popfit-textMuted uppercase text-[10px]">Alat Olahraga</th>
                                <th class="px-6 py-4 font-black text-popfit-textMuted uppercase text-[10px] text-center">Jumlah</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            <?php while($item = mysqli_fetch_assoc($items)): ?>
                            <tr>
                                <td class="px-6 py-4">
                                    <div class="flex items-center space-x-3">
                                        <img src="../../asset/<?= $item['foto_alat_olahraga'] ?: 'default.png' ?>" class="w-10 h-10 object-contain">
                                        <p class="font-black text-popfit-dark uppercase"><?= htmlspecialchars($item['nama_alat_olahraga']) ?></p>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center font-black text-popfit-dark"><?= $item['jumlah'] ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>

                <?php if($trans['denda'] > 0 && $trans['pembayaran'] != 'lunas'): ?>
                <div class="bg-red-50 border-2 border-red-500 p-8 rounded-sm text-center shadow-lg relative overflow-hidden">
                    <div class="absolute top-0 right-0 p-2 opacity-10">
                        <i class="ph ph-warning-circle text-8xl"></i>
                    </div>
                    <h5 class="text-[12px] font-black text-red-600 uppercase tracking-[0.2em] mb-2">Tagihan Denda Terdeteksi</h5>
                    <p class="text-[14px] font-black text-popfit-dark mb-6 leading-tight uppercase">Anda memiliki denda sebesar Rp <?= number_format($trans['denda'], 0, ',', '.') ?> yang belum terlunasi.</p>
                    
                    <?php if($trans['pembayaran'] == 'pending'): ?>
                        <div class="py-3 px-6 bg-popfit-accent text-popfit-dark rounded-sm text-[10px] font-black uppercase tracking-widest inline-flex items-center">
                            <i class="ph ph-clock-countdown mr-2 text-lg"></i> Menunggu Verifikasi Admin
                        </div>
                    <?php elseif($trans['pembayaran'] == 'ditolak'): ?>
                        <div class="mb-4">
                            <p class="text-[10px] font-black text-red-600 uppercase mb-2 italic">Ditolak: <?= htmlspecialchars($trans['alasan_penolakan']) ?></p>
                            <a href="../denda/detailDenda.php?id=<?= $id ?>" class="w-full md:w-auto inline-flex items-center justify-center bg-red-600 text-white px-8 py-4 rounded-sm text-[11px] font-black uppercase tracking-[0.2em] hover:bg-red-700 transition-all shadow-md active:scale-95">
                                Re-upload Bukti Bayar
                            </a>
                        </div>
                    <?php else: ?>
                        <a href="../denda/detailDenda.php?id=<?= $id ?>" class="w-full md:w-auto inline-flex items-center justify-center bg-popfit-dark text-white px-8 py-4 rounded-sm text-[11px] font-black uppercase tracking-[0.2em] hover:bg-popfit-light transition-all shadow-md active:scale-95">
                            Bayar Denda Sekarang
                        </a>
                    <?php endif; ?>
                </div>
                <?php elseif($trans['status'] == 'dipinjam'): ?>
                <div class="bg-popfit-accent/10 border border-popfit-accent p-6 rounded-sm text-center">
                    <p class="text-[10px] font-black text-popfit-dark uppercase tracking-widest leading-loose">Harap kembalikan alat tepat waktu. Keterlambatan akan dikenakan denda keterlambatan secara otomatis.</p>
                </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>
</html>
