<?php
require '../../config/config.php';
session_start();

if (!isset($_SESSION["login"]) || $_SESSION["role"] != "petugas") {
    header("Location: ../../login.php");
    exit;
}

$id = $_GET['id'] ?? '';
$idPetugas = $_SESSION["id_user"];
$petugasName = htmlspecialchars($_SESSION['nama'] ?? 'Staff');

$query = "SELECT t.*, u.nama, u.nis, u.kelas FROM transaksi t JOIN users u ON t.id_user = u.id_user WHERE t.id_transaksi = '$id'";
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
    <title>Petugas: Detail Transaksi - PopFit</title>
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

    <aside class="hidden md:flex flex-col w-64 bg-popfit-dark text-white border-r border-popfit-dark h-full flex-shrink-0">
        <div class="h-16 flex items-center px-6 border-b border-popfit-light bg-popfit-dark">
            <i class="ph-fill ph-paw-print text-popfit-accent text-2xl mr-3"></i>
            <span class="text-xl font-black tracking-wide uppercase">PopFit Staff</span>
        </div>
        <nav class="flex-1 overflow-y-auto py-4">
            <ul class="space-y-1">
                <li><a href="../dashboardPetugas.php" class="flex items-center px-6 py-3 text-gray-200 hover:bg-popfit-light transition-colors border-l-4 border-transparent"><i class="ph ph-squares-four text-xl w-6"></i><span class="ml-3 font-bold">Dashboard</span></a></li>
                <li><a href="transaksi.php" class="nav-active flex items-center px-6 py-3 text-gray-200 hover:bg-popfit-light transition-colors border-l-4 border-transparent"><i class="ph ph-arrows-left-right text-xl w-6"></i><span class="ml-3 font-bold">Transaksi</span></a></li>
            </ul>
        </nav>
    </aside>

    <div class="flex-1 flex flex-col h-screen overflow-hidden">
        <header class="h-16 bg-popfit-surface border-b border-popfit-border flex items-center px-6 flex-shrink-0">
            <a href="transaksi.php" class="mr-4 text-popfit-dark hover:scale-110 transition-transform"><i class="ph ph-arrow-left text-2xl"></i></a>
            <h2 class="text-lg font-black text-popfit-dark uppercase tracking-tight">Rincian #<?= $id ?> • <?= htmlspecialchars($trans['nama']) ?></h2>
        </header>

        <main class="flex-1 overflow-y-auto p-6">
            <div class="max-w-4xl mx-auto space-y-6">
                <!-- Peminjam Info -->
                <div class="bg-white border border-popfit-border p-6 rounded-sm flex items-center justify-between">
                    <div>
                        <p class="text-[9px] font-black text-popfit-textMuted uppercase tracking-widest mb-1">Peminjam</p>
                        <h3 class="text-lg font-black text-popfit-dark uppercase"><?= htmlspecialchars($trans['nama']) ?></h3>
                        <p class="text-[10px] font-bold text-popfit-textMuted uppercase"><?= $trans['nis'] ?> • <?= strtoupper($trans['kelas']) ?></p>
                    </div>
                    <div class="text-right">
                        <p class="text-[9px] font-black text-popfit-textMuted uppercase tracking-widest mb-1">Status Sirkulasi</p>
                        <span class="px-2 py-1 rounded-sm text-[10px] font-black uppercase bg-popfit-dark text-white"><?= $trans['status'] ?></span>
                    </div>
                </div>

                <!-- Items -->
                <div class="bg-white border border-popfit-border rounded-sm overflow-hidden">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="bg-popfit-bg border-b border-popfit-border">
                                <th class="px-6 py-4 font-black text-popfit-textMuted uppercase text-[10px]">Alat Olahraga</th>
                                <th class="px-6 py-4 font-black text-popfit-textMuted uppercase text-[10px] text-center">Qty</th>
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

                <!-- Action Button -->
                <?php if($trans['status'] == 'menunggu'): ?>
                <div class="flex space-x-4">
                    <a href="transaksi.php?approve=<?= $id ?>" class="flex-1 bg-popfit-dark text-white py-4 text-center text-[10px] font-black uppercase tracking-widest rounded-sm hover:bg-popfit-light transition-all">Setujui Peminjaman</a>
                    <a href="transaksi.php?reject=<?= $id ?>" class="flex-1 bg-white border border-red-200 text-red-600 py-4 text-center text-[10px] font-black uppercase tracking-widest rounded-sm hover:bg-red-50 transition-all">Tolak</a>
                </div>
                <?php elseif($trans['status'] == 'dipinjam'): ?>
                <a href="kembalikan.php?id=<?= $id ?>" class="block w-full bg-popfit-accent text-popfit-dark py-4 text-center text-[10px] font-black uppercase tracking-widest rounded-sm hover:bg-popfit-dark hover:text-white transition-all">Proses Pengembalian</a>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>
</html>