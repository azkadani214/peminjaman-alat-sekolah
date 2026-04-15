<?php
require '../../config/config.php';
session_start();

if (!isset($_SESSION["login"]) || !in_array($_SESSION["role"], ["admin utama", "admin", "petugas"])) {
    header("Location: ../../login.php");
    exit;
}

$idPetugas = $_SESSION["id_user"];
$role = $_SESSION["role"];
$adminName = htmlspecialchars($_SESSION['nama'] ?? 'Admin');
$adminUsername = htmlspecialchars($_SESSION['username'] ?? 'username');

/* HANDLE ACTIONS */
if(isset($_GET['approve'])){
    if(!isOperationalHour()){
        header("Location: transaksi.php?msg=bukan_jam_kerja");
        exit;
    }
    mysqli_query($connect, "UPDATE transaksi SET status = 'dipinjam', id_petugas = '$idPetugas' WHERE id_transaksi = '".$_GET['approve']."'");
    tambahLog($idPetugas, "Menyetujui peminjaman #" . $_GET['approve']);
    header("Location: transaksi.php?msg=disetujui");
    exit;
}
if(isset($_GET['reject'])){
    mysqli_query($connect, "UPDATE transaksi SET status = 'ditolak', id_petugas = '$idPetugas' WHERE id_transaksi = '".$_GET['reject']."'");
    tambahLog($idPetugas, "Menolak peminjaman #" . $_GET['reject']);
    header("Location: transaksi.php?msg=ditolak");
    exit;
}

/* FETCH TRANSACTIONS */
$filterStatus = $_GET['status'] ?? '';
$query = "SELECT t.*, u.nama, u.nis, u.kelas 
          FROM transaksi t 
          JOIN users u ON t.id_user = u.id_user";
if($filterStatus) $query .= " WHERE t.status = '$filterStatus'";
$query .= " ORDER BY t.waktu_pinjam DESC";

$result = mysqli_query($connect, $query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Transaksi - PopFit</title>
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
                <span class="text-xl font-black tracking-wide uppercase">PopFit Admin</span>
            </div>
            <button id="closeSidebar" class="md:hidden text-gray-400 hover:text-white"><i class="ph ph-x text-2xl"></i></button>
        </div>

        <nav class="flex-1 overflow-y-auto py-4">
            <ul class="space-y-1">
                <li><a href="../dashboardAdmin.php" class="flex items-center px-6 py-3 text-gray-200 hover:bg-popfit-light transition-colors border-l-4 border-transparent">
                    <i class="ph ph-squares-four text-xl w-6"></i><span class="ml-3 font-bold">Dashboard</span>
                </a></li>
                <li class="px-6 py-2 mt-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Manajemen</li>
                <li><a href="../alat/daftarAlat.php" class="flex items-center px-6 py-3 text-gray-200 hover:bg-popfit-light transition-colors border-l-4 border-transparent">
                    <i class="ph ph-basketball text-xl w-6"></i><span class="ml-3 font-bold">Katalog Alat</span>
                </a></li>
                <li><a href="../petugas/petugas.php" class="flex items-center px-6 py-3 text-gray-200 hover:bg-popfit-light transition-colors border-l-4 border-transparent">
                    <i class="ph ph-user-tie text-xl w-6"></i><span class="ml-3 font-bold">Petugas</span>
                </a></li>
                <li><a href="../siswa/siswa.php" class="flex items-center px-6 py-3 text-gray-200 hover:bg-popfit-light transition-colors border-l-4 border-transparent">
                    <i class="ph ph-users text-xl w-6"></i><span class="ml-3 font-bold">Siswa</span>
                </a></li>
                <li class="px-6 py-2 mt-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Sirkulasi</li>
                <li><a href="transaksi.php" class="nav-active flex items-center px-6 py-3 text-gray-200 hover:bg-popfit-light transition-colors border-l-4 border-transparent">
                    <i class="ph ph-arrows-left-right text-xl w-6"></i><span class="ml-3 font-bold">Transaksi</span>
                </a></li>
                <li><a href="../denda/denda.php" class="flex items-center px-6 py-3 text-gray-200 hover:bg-popfit-light transition-colors border-l-4 border-transparent">
                    <i class="ph ph-wallet text-xl w-6"></i><span class="ml-3 font-bold">Denda</span>
                </a></li>
                <li><a href="../laporan/laporan.php" class="flex items-center px-6 py-3 text-gray-200 hover:bg-popfit-light transition-colors border-l-4 border-transparent">
                    <i class="ph ph-file-text text-xl w-6"></i><span class="ml-3 font-bold">Laporan</span>
                </a></li>
                <li><a href="../log/log.php" class="flex items-center px-6 py-3 text-gray-200 hover:bg-popfit-light transition-colors border-l-4 border-transparent">
                    <i class="ph ph-scroll text-xl w-6"></i><span class="ml-3 font-bold">Log Aktivitas</span>
                </a></li>
            </ul>
        </nav>

        <div class="border-t border-popfit-light p-4">
            <div class="flex items-center w-full">
                <div class="w-8 h-8 rounded-sm bg-popfit-accent flex items-center justify-center text-popfit-dark font-black">A</div>
                <div class="ml-3 flex-1 overflow-hidden">
                    <p class="text-[12px] font-black text-white truncate uppercase"><?= $adminName ?></p>
                    <p class="text-[10px] text-gray-400 truncate uppercase"><?= $adminUsername ?></p>
                </div>
                <a href="../../logout.php" class="text-gray-400 hover:text-white transition-colors"><i class="ph ph-sign-out text-xl"></i></a>
            </div>
        </div>
    </aside>

    <div class="flex-1 flex flex-col h-screen w-full relative">
        <header class="h-16 bg-popfit-surface border-b border-popfit-border flex items-center justify-between px-6 flex-shrink-0">
            <div class="flex items-center">
                <button id="openSidebar" class="md:hidden mr-4 text-popfit-dark"><i class="ph ph-list text-2xl"></i></button>
                <h2 class="text-lg font-black text-popfit-dark uppercase tracking-tight">Kelola Transaksi</h2>
            </div>
            <div class="flex items-center space-x-2">
                <a href="transaksi.php" class="px-3 py-1.5 text-[10px] font-black uppercase <?= !$filterStatus ? 'bg-popfit-dark text-white' : 'bg-white text-popfit-textMuted border border-popfit-border' ?> rounded-sm transition-all">Semua</a>
                <a href="?status=menunggu" class="px-3 py-1.5 text-[10px] font-black uppercase <?= $filterStatus=='menunggu' ? 'bg-popfit-dark text-white' : 'bg-white text-popfit-textMuted border border-popfit-border' ?> rounded-sm transition-all">Menunggu</a>
                <a href="?status=dipinjam" class="px-3 py-1.5 text-[10px] font-black uppercase <?= $filterStatus=='dipinjam' ? 'bg-popfit-dark text-white' : 'bg-white text-popfit-textMuted border border-popfit-border' ?> rounded-sm transition-all">Aktif</a>
            </div>
        </header>

        <main class="flex-1 overflow-y-auto p-6">
            <div class="bg-white border border-popfit-border rounded-sm overflow-hidden">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-popfit-bg border-b border-popfit-border">
                            <th class="px-6 py-4 text-[10px] font-black text-popfit-textMuted uppercase tracking-widest">Waktu</th>
                            <th class="px-6 py-4 text-[10px] font-black text-popfit-textMuted uppercase tracking-widest">Siswa</th>
                            <th class="px-6 py-4 text-[10px] font-black text-popfit-textMuted uppercase tracking-widest">Status & Keterlambatan</th>
                            <th class="px-6 py-4 text-[10px] font-black text-popfit-textMuted uppercase tracking-widest text-right">Denda</th>
                            <th class="px-6 py-4 text-[10px] font-black text-popfit-textMuted uppercase tracking-widest text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <?php while($row = mysqli_fetch_assoc($result)): 
                            $st = $row['status'];
                            $stColor = '';
                            switch($st){
                                case 'menunggu': $stColor = 'bg-popfit-accent text-popfit-dark'; break;
                                case 'dipinjam': $stColor = 'bg-popfit-dark text-white'; break;
                                case 'dikembalikan': $stColor = 'bg-popfit-bg text-popfit-textMuted'; break;
                                case 'ditolak': $stColor = 'bg-red-50 text-red-500'; break;
                                default: $stColor = 'bg-gray-100 text-gray-500'; break;
                            }
                        ?>
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4">
                                <p class="text-[11px] font-black text-popfit-dark"><?= date('d.m.Y', strtotime($row['waktu_pinjam'])) ?></p>
                                <p class="text-[10px] font-bold text-popfit-textMuted mt-0.5"><?= date('H:i', strtotime($row['waktu_pinjam'])) ?></p>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <div>
                                        <p class="text-[12px] font-black text-popfit-dark uppercase tracking-tight"><?= htmlspecialchars($row['nama']) ?></p>
                                        <p class="text-[10px] font-bold text-popfit-textMuted tracking-widest uppercase">#<?= $row['nis'] ?> • <?= $row['kelas'] ?></p>
                                    </div>
                                    <?php if($row['bukti_kartu']): ?>
                                        <a href="../../uploads/<?= $row['bukti_kartu'] ?>" target="_blank" class="ml-3 w-7 h-7 flex items-center justify-center bg-blue-50 text-blue-500 rounded-sm hover:bg-blue-500 hover:text-white transition-all shadow-sm" title="Lihat Kartu Pelajar">
                                            <i class="ph-bold ph-identification-card text-sm"></i>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-col space-y-1">
                                    <div class="flex items-center gap-1.5 flex-wrap">
                                        <span class="px-2 py-0.5 rounded-sm text-[9px] font-black uppercase tracking-tighter w-fit <?= $stColor ?>"><?= $st ?></span>
                                        <?php if($row['denda'] > 0): ?>
                                            <?php if($row['pembayaran'] == 'belum bayar'): ?>
                                                <span class="px-2 py-0.5 rounded-sm text-[9px] font-black uppercase tracking-tighter w-fit bg-red-600 text-white animate-pulse">DENDA UNPAID</span>
                                            <?php elseif($row['pembayaran'] == 'pending'): ?>
                                                <span class="px-2 py-0.5 rounded-sm text-[9px] font-black uppercase tracking-tighter w-fit bg-popfit-accent text-popfit-dark animate-bounce">PENDING VERIFIKASI</span>
                                            <?php elseif($row['pembayaran'] == 'ditolak'): ?>
                                                <span class="px-2 py-0.5 rounded-sm text-[9px] font-black uppercase tracking-tighter w-fit bg-red-100 text-red-600 border border-red-200">PAYMENT REJECTED</span>
                                            <?php elseif($row['pembayaran'] == 'lunas'): ?>
                                                <span class="px-2 py-0.5 rounded-sm text-[9px] font-black uppercase tracking-tighter w-fit bg-green-100 text-green-700">LUNAS</span>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </div>
                                    <?php 
                                    if($st == 'dipinjam') {
                                        $det = cekDetailKeterlambatan($row['batas_kembali']);
                                        if($det['is_telat']) {
                                            echo '<span class="text-[9px] font-black text-red-600 uppercase italic">Telat: '.$det['teks'].'</span>';
                                        }
                                    } elseif($st == 'dikembalikan' && $row['keterlambatan'] == 'ya') {
                                        $det = cekDetailKeterlambatan($row['batas_kembali'], $row['waktu_kembali']);
                                        echo '<span class="text-[9px] font-black text-popfit-textMuted uppercase italic">Selesai Telat: '.$det['teks'].'</span>';
                                    }
                                    ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <?php 
                                if($st == 'dipinjam') {
                                    $det = cekDetailKeterlambatan($row['batas_kembali']);
                                    if($det['denda'] > 0) {
                                        echo '<p class="text-[11px] font-black text-red-600">± Rp '.number_format($det['denda'], 0, ',', '.').'</p>';
                                        echo '<p class="text-[8px] font-bold text-popfit-textMuted uppercase">Estimasi</p>';
                                    } else {
                                        echo '<span class="text-gray-200">—</span>';
                                    }
                                } elseif($row['denda'] > 0 || $st == 'dikembalikan') {
                                    echo '<p class="text-[11px] font-black text-popfit-dark">Rp '.number_format($row['denda'], 0, ',', '.').'</p>';
                                    echo '<p class="text-[8px] font-black uppercase tracking-widest '.($row['pembayaran'] == 'lunas' ? 'text-green-500' : 'text-red-400').' italic">'.$row['pembayaran'].'</p>';
                                } else {
                                    echo '<span class="text-gray-200">—</span>';
                                }
                                ?>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end space-x-2">
                                    <?php if($st == 'menunggu'): ?>
                                        <button onclick="confirmAction('?approve=<?= $row['id_transaksi'] ?>', 'SETUJUI PINJAM?', 'Ingin mengizinkan peminjaman ini?')" 
                                                class="w-8 h-8 flex items-center justify-center bg-popfit-dark text-white rounded-sm hover:bg-popfit-light transition-all shadow-sm"><i class="ph-bold ph-check"></i></button>
                                        <button onclick="confirmAction('?reject=<?= $row['id_transaksi'] ?>', 'TOLAK PINJAM?', 'Tolak permintaan peminjaman alat?', '#EF4444')" 
                                                class="w-8 h-8 flex items-center justify-center bg-white border border-popfit-border text-red-500 rounded-sm hover:bg-red-50 transition-all shadow-sm"><i class="ph-bold ph-x"></i></button>
                                    <?php elseif($st == 'dipinjam'): ?>
                                        <a href="kembalikan.php?id=<?= $row['id_transaksi'] ?>" class="text-[10px] font-black uppercase tracking-[0.2em] bg-popfit-dark text-white px-3 py-1.5 rounded-sm hover:bg-popfit-light transition-all shadow-sm">Selesai</a>
                                    <?php endif; ?>
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

        function confirmAction(url, title, text, confirmColor = '#2A4736') {
            Swal.fire({
                title: title, text: text, icon: 'question', showCancelButton: true,
                confirmButtonColor: confirmColor, cancelButtonColor: '#E4E4E7',
                confirmButtonText: 'YA!', cancelButtonText: 'BATAL'
            }).then((result) => { if (result.isConfirmed) window.location.href = url; });
        }
    </script>
</body>
</html>
