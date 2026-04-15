<?php
require '../../config/config.php';
session_start();

if (!isset($_SESSION["login"]) || $_SESSION["role"] != "petugas") {
    header("Location: ../../login.php");
    exit;
}

$idPetugas = $_SESSION["id_user"];
$petugasName = htmlspecialchars($_SESSION['nama'] ?? 'Staff');
$petugasUsername = htmlspecialchars($_SESSION['username'] ?? 'username');

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

$filterStatus = $_GET['status'] ?? '';
$keyword = $_GET['keyword'] ?? '';

$query = "SELECT t.*, u.nama, u.nis, u.kelas 
          FROM transaksi t 
          JOIN users u ON t.id_user = u.id_user";

$conditions = [];
if($filterStatus) $conditions[] = "t.status = '$filterStatus'";
if($keyword) $conditions[] = "(u.nama LIKE '%$keyword%' OR u.nis LIKE '%$keyword%')";

if(!empty($conditions)) {
    $query .= " WHERE " . implode(" AND ", $conditions);
}

$query .= " ORDER BY t.waktu_pinjam DESC";
$result = mysqli_query($connect, $query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaksi Staff - PopFit</title>
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
        $activeIndex = "transaksi"; 
        include '../../layout/sidebar_petugas.php'; 
    ?>

    <div class="flex-1 flex flex-col h-screen w-full relative">
        <?php 
            $pageTitle = "Daftar Transaksi"; 
            include '../../layout/header_petugas.php'; 
        ?>

        <!-- Filter Sub-header -->
        <div class="px-6 py-4 bg-white border-b border-popfit-border flex items-center justify-between">
            <div class="flex items-center space-x-2">
                <a href="transaksi.php<?= $keyword ? '?keyword='.$keyword : '' ?>" class="px-4 py-2 text-[10px] font-black uppercase <?= !$filterStatus ? 'bg-popfit-dark text-white' : 'bg-popfit-bg text-popfit-textMuted' ?> rounded-sm transition-all shadow-sm">SEMUA</a>
                <a href="?status=menunggu<?= $keyword ? '&keyword='.$keyword : '' ?>" class="px-4 py-2 text-[10px] font-black uppercase <?= $filterStatus=='menunggu' ? 'bg-popfit-dark text-white' : 'bg-popfit-bg text-popfit-textMuted' ?> rounded-sm transition-all shadow-sm">MENUNGGU</a>
            </div>
            <div class="flex-1 max-w-xs ml-auto">
                <form action="" method="GET" class="relative">
                    <?php if($filterStatus): ?><input type="hidden" name="status" value="<?= $filterStatus ?>"><?php endif; ?>
                    <i class="ph ph-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    <input type="text" name="keyword" value="<?= htmlspecialchars($keyword) ?>" placeholder="CARI SISWA/NIS..." class="w-full bg-popfit-bg border border-popfit-border rounded-sm pl-10 pr-4 py-2 text-[10px] font-black text-popfit-dark outline-none focus:border-popfit-dark uppercase">
                </form>
            </div>
        </div>

        <main class="flex-1 overflow-y-auto p-6">
            <div class="bg-white border border-popfit-border rounded-sm overflow-hidden text-[11px]">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-popfit-bg border-b border-popfit-border">
                            <th class="px-6 py-4 font-black text-popfit-textMuted uppercase tracking-widest">Waktu</th>
                            <th class="px-6 py-4 font-black text-popfit-textMuted uppercase tracking-widest">Siswa</th>
                            <th class="px-6 py-4 font-black text-popfit-textMuted uppercase tracking-widest">Status & Keterlambatan</th>
                            <th class="px-6 py-4 font-black text-popfit-textMuted uppercase tracking-widest text-right">Denda</th>
                            <th class="px-6 py-4 font-black text-popfit-textMuted uppercase tracking-widest text-right">Aksi</th>
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
                                default: $stColor = 'bg-gray-100'; break;
                            }
                        ?>
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 font-bold text-popfit-textMuted"><?= date('d.m.Y H:i', strtotime($row['waktu_pinjam'])) ?></td>
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <div>
                                        <p class="font-black text-popfit-dark uppercase"><?= htmlspecialchars($row['nama']) ?></p>
                                        <p class="text-[9px] font-bold text-popfit-textMuted uppercase">#<?= $row['nis'] ?></p>
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
                                        <span class="px-2 py-0.5 rounded-sm text-[8px] font-black uppercase tracking-widest w-fit <?= $stColor ?>"><?= $st ?></span>
                                        <?php if($row['denda'] > 0): ?>
                                            <?php if($row['pembayaran'] == 'belum bayar'): ?>
                                                <span class="px-2 py-0.5 rounded-sm text-[8px] font-black uppercase tracking-widest w-fit bg-red-600 text-white animate-pulse">DENDA UNPAID</span>
                                            <?php elseif($row['pembayaran'] == 'pending'): ?>
                                                <span class="px-2 py-0.5 rounded-sm text-[8px] font-black uppercase tracking-widest w-fit bg-popfit-accent text-popfit-dark animate-bounce">PENDING VERIFIKASI</span>
                                            <?php elseif($row['pembayaran'] == 'ditolak'): ?>
                                                <span class="px-2 py-0.5 rounded-sm text-[8px] font-black uppercase tracking-widest w-fit bg-red-100 text-red-600 border border-red-200">PAYMENT REJECTED</span>
                                            <?php elseif($row['pembayaran'] == 'lunas'): ?>
                                                <span class="px-2 py-0.5 rounded-sm text-[8px] font-black uppercase tracking-widest w-fit bg-green-100 text-green-700">LUNAS</span>
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
                                        echo '<p class="text-[10px] font-black text-red-600">± Rp '.number_format($det['denda'], 0, ',', '.').'</p>';
                                        echo '<p class="text-[8px] font-bold text-popfit-textMuted uppercase">Akumulasi</p>';
                                    } else {
                                        echo '<span class="text-gray-200">—</span>';
                                    }
                                } elseif($st == 'dikembalikan' || $row['denda'] > 0) {
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
                                        <button onclick="confirmAction('?approve=<?= $row['id_transaksi'] ?>', 'SETUJUI?', 'Terima peminjaman ini?')" class="w-8 h-8 flex items-center justify-center bg-popfit-dark text-white rounded-sm hover:bg-popfit-light transition-all shadow-sm"><i class="ph-bold ph-check"></i></button>
                                        <button onclick="confirmAction('?reject=<?= $row['id_transaksi'] ?>', 'TOLAK?', 'Tolak peminjaman?', '#EF4444')" class="w-8 h-8 flex items-center justify-center bg-white border border-popfit-border text-red-500 rounded-sm hover:bg-red-50 transition-all shadow-sm"><i class="ph-bold ph-x"></i></button>
                                    <?php elseif($st == 'dipinjam'): ?>
                                        <a href="kembalikan.php?id=<?= $row['id_transaksi'] ?>" class="text-[9px] font-black uppercase tracking-[0.2em] bg-popfit-dark text-white px-3 py-1.5 rounded-sm hover:bg-popfit-light transition-all shadow-sm">Kembalikan</a>
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
                confirmButtonText: 'YA!', cancelButtonText: 'BATAL',
                customClass: { popup: 'rounded-sm' }
            }).then((result) => { if (result.isConfirmed) window.location.href = url; });
        }

        <?php 
        $msgs = [
            'bukan_jam_kerja' => ['icon'=>'warning', 'title'=>'DILUAR JAM KERJA', 'text'=>'Persetujuan hanya bisa dilakukan pukul 06.45 - 17.00.'],
            'disetujui' => ['icon'=>'success', 'title'=>'BERHASIL', 'text'=>'Peminjaman telah disetujui.'],
            'ditolak' => ['icon'=>'error', 'title'=>'DITOLAK', 'text'=>'Peminjaman telah dibatalkan.'],
        ];
        $msgKey = $_GET['msg'] ?? '';
        if(isset($msgs[$msgKey])): 
            $m = $msgs[$msgKey];
        ?>
        Swal.fire({
            icon: '<?= $m['icon'] ?>',
            title: '<?= $m['title'] ?>',
            text: '<?= $m['text'] ?>',
            confirmButtonColor: '#2A4736',
            customClass: { popup: 'rounded-sm' }
        }).then(() => {
            window.history.replaceState({}, document.title, window.location.pathname);
        });
        <?php endif; ?>
    </script>
</body>
</html>
