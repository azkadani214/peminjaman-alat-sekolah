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
    $_SESSION['swal'] = ['title' => 'BERHASIL!', 'text' => 'Pembayaran tunai telah dikonfirmasi.', 'icon' => 'success', 'redirect' => "detailDenda.php?id=$idTransaksi"];
    header("Location: detailDenda.php?id=$idTransaksi");
    exit;
}

if (isset($_POST['approve_pembayaran'])) {
    mysqli_query($connect, "UPDATE transaksi SET pembayaran = 'lunas', alasan_penolakan = NULL WHERE id_transaksi = $idTransaksi");
    tambahLog($_SESSION['id_user'], "Petugas Menyetujui bukti pembayaran denda ID: $idTransaksi");
    $_SESSION['swal'] = ['title' => 'BERHASIL!', 'text' => 'Pembayaran digital telah disetujui.', 'icon' => 'success', 'redirect' => "detailDenda.php?id=$idTransaksi"];
    header("Location: detailDenda.php?id=$idTransaksi");
    exit;
}

if (isset($_POST['reject_pembayaran'])) {
    $alasan = mysqli_real_escape_string($connect, $_POST['alasan_penolakan']);
    mysqli_query($connect, "UPDATE transaksi SET pembayaran = 'ditolak', alasan_penolakan = '$alasan' WHERE id_transaksi = $idTransaksi");
    tambahLog($_SESSION['id_user'], "Petugas Menolak bukti pembayaran denda ID: $idTransaksi. Alasan: $alasan");
    $_SESSION['swal'] = ['title' => 'DITOLAK', 'text' => 'Bukti pembayaran denda telah ditolak.', 'icon' => 'info', 'redirect' => "detailDenda.php?id=$idTransaksi"];
    header("Location: detailDenda.php?id=$idTransaksi");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Konfirmasi Denda - PopFit Staff</title>
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
        $activeIndex = "denda"; 
        include '../../layout/sidebar_petugas.php'; 
    ?>

    <div class="flex-1 flex flex-col h-screen w-full relative">
        <?php 
            $pageTitle = "Verifikasi Denda #$idTransaksi"; 
            include '../../layout/header_petugas.php'; 
        ?>

        <main class="flex-1 overflow-y-auto p-6">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Info Peminjaman -->
                <div class="lg:col-span-2 space-y-6">
                    <div class="bg-white border border-popfit-border rounded-sm p-8">
                        <div class="flex items-center justify-between mb-8 pb-4 border-b border-gray-100">
                             <h3 class="text-[11px] font-black uppercase text-popfit-dark tracking-widest flex items-center">
                                <i class="ph ph-user-circle text-xl mr-2 text-popfit-accent"></i> Data Siswa
                            </h3>
                            <span class="text-[10px] font-black bg-popfit-bg text-popfit-textMuted px-2 py-1 rounded-sm uppercase tracking-tight">KELAS: <?= htmlspecialchars($trx['kelas']) ?></span>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-8 mb-10">
                            <div>
                                <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1">Nama Lengkap:</p>
                                <p class="text-sm font-black text-popfit-dark uppercase"><?= htmlspecialchars($trx['nama']) ?></p>
                            </div>
                            <div>
                                <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1">NIS Siswa:</p>
                                <p class="text-sm font-black text-popfit-dark uppercase">#<?= htmlspecialchars($trx['nis']) ?></p>
                            </div>
                        </div>

                        <h3 class="text-[11px] font-black uppercase text-popfit-dark tracking-widest mb-4 flex items-center">
                            <i class="ph ph-package text-xl mr-2 text-popfit-accent"></i> Alat yang Dipinjam
                        </h3>
                        <div class="space-y-2 mb-10">
                            <?php foreach($alat as $a): ?>
                            <div class="flex items-center justify-between p-3 bg-popfit-bg border border-popfit-border rounded-sm">
                                <span class="text-xs font-bold text-popfit-dark uppercase"><?= htmlspecialchars($a['nama_alat_olahraga']) ?></span>
                                <span class="text-[10px] font-black text-popfit-textMuted uppercase"><?= $a['jumlah'] ?> PCS</span>
                            </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="grid grid-cols-2 gap-y-6 pt-6 border-t border-gray-100">
                             <div class="col-span-1">
                                <p class="text-[9px] font-black text-red-400 uppercase mb-1 italic">Batas Kembali</p>
                                <p class="text-xs font-black text-red-600"><?= date('d.m.Y, H:i', strtotime($trx['batas_kembali'])) ?></p>
                            </div>
                            <div class="col-span-1">
                                <p class="text-[9px] font-black text-gray-400 uppercase mb-1">Waktu Kembali</p>
                                <p class="text-xs font-bold text-popfit-dark"><?= $trx['waktu_kembali'] ? date('d.m.Y, H:i', strtotime($trx['waktu_kembali'])) : '-' ?></p>
                            </div>
                            <?php if($trx['keterlambatan'] == 'ya'): 
                                $det = cekDetailKeterlambatan($trx['batas_kembali'], $trx['waktu_kembali']);    
                            ?>
                            <div class="col-span-2 p-4 bg-red-50 border border-red-100 rounded-sm">
                                <p class="text-[10px] uppercase font-black text-red-600 tracking-widest mb-1 flex items-center">
                                    <i class="ph ph-clock-countdown text-lg mr-2"></i> Keterangan Keterlambatan
                                </p>
                                <p class="text-sm font-black text-red-700 uppercase leading-none">Terlambat: <?= $det['teks'] ?></p>
                            </div>
                            <?php endif; ?>
                            <?php if($trx['denda_kerusakan'] > 0): ?>
                            <div class="col-span-2 p-4 bg-orange-50 border border-orange-100 rounded-sm">
                                <p class="text-[10px] uppercase font-black text-orange-600 tracking-widest mb-1 flex items-center">
                                    <i class="ph ph-wrench text-lg mr-2"></i> Denda Kerusakan
                                </p>
                                <p class="text-sm font-black text-orange-700 uppercase leading-none">Jumlah: Rp <?= number_format($trx['denda_kerusakan'], 0, ',', '.') ?></p>
                                <p class="text-[10px] font-bold text-orange-500 uppercase mt-1">Catatan: <?= htmlspecialchars($trx['kondisi'] ?: '-') ?></p>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Info Denda & Pembayaran -->
                <div class="space-y-6">
                    <div class="bg-white border border-popfit-border rounded-sm p-8 text-center shadow-sm h-fit">
                        <p class="text-[10px] font-black uppercase text-popfit-textMuted tracking-widest mb-4">Total Tagihan Denda</p>
                        <h4 class="text-3xl font-black text-red-600 mb-6 leading-none uppercase tracking-tighter">Rp <?= number_format($trx['denda'], 0, ',', '.') ?></h4>
                        
                        <div class="h-px bg-gray-100 mb-8"></div>

                        <?php if($trx['pembayaran'] == 'pending'): ?>
                            <div class="p-6 bg-popfit-accent/10 border border-popfit-accent/20 rounded-sm text-left">
                                <h5 class="text-[11px] font-black text-popfit-dark uppercase mb-6 flex items-center justify-center border-b border-popfit-accent/20 pb-4">
                                    <i class="ph-fill ph-shield-check text-popfit-accent text-xl mr-2"></i> Bukti Digital
                                </h5>
                                
                                <div class="space-y-4 mb-8">
                                    <div class="bg-white/50 p-3 rounded-sm border border-white">
                                        <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1">Metode & Pengirim:</p>
                                        <p class="text-[11px] font-black text-popfit-dark uppercase"><?= $trx['metode_pembayaran_denda'] ?> • <?= $trx['nama_pengirim_pembayaran'] ?: '-' ?></p>
                                    </div>
                                    <?php if($trx['catatan_pembayaran']): ?>
                                    <div class="bg-white/50 p-3 rounded-sm border border-white">
                                        <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1">Catatan Siswa:</p>
                                        <p class="text-[11px] font-bold text-popfit-dark italic">"<?= htmlspecialchars($trx['catatan_pembayaran']) ?>"</p>
                                    </div>
                                    <?php endif; ?>
                                </div>

                                <div class="mb-8">
                                    <a href="../../uploads/<?= $trx['bukti_pembayaran'] ?>" target="_blank" class="block group relative overflow-hidden rounded-sm border border-popfit-border">
                                        <img src="../../uploads/<?= $trx['bukti_pembayaran'] ?>" class="w-full h-44 object-contain group-hover:scale-105 transition-transform">
                                        <div class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 transition-opacity flex flex-col items-center justify-center text-white p-4 text-center">
                                            <i class="ph ph-magnifying-glass-plus text-3xl mb-2"></i>
                                            <span class="text-[9px] font-black uppercase tracking-widest">Klik Perbesar</span>
                                        </div>
                                    </a>
                                </div>

                                <div class="space-y-3">
                                    <button type="button" class="w-full bg-popfit-dark text-white py-4 rounded-sm text-[11px] font-black uppercase tracking-[0.2em] hover:bg-popfit-light transition-all shadow-md active:scale-95 flex items-center justify-center" onclick="confirmApprove()">
                                        <i class="ph-bold ph-check-circle text-lg mr-2"></i> Terima
                                    </button>
                                    <button type="button" onclick="showRejectForm()" class="w-full bg-white border border-red-500 text-red-500 py-3 rounded-sm text-[10px] font-black uppercase tracking-widest hover:bg-red-50 transition-all flex items-center justify-center">
                                        <i class="ph-bold ph-x-circle text-lg mr-2"></i> Tolak
                                    </button>
                                </div>

                                <form method="POST" id="rejectForm" class="hidden animate-in fade-in slide-in-from-top-2 duration-300 space-y-4 pt-6 mt-6 border-t border-popfit-accent/20">
                                    <div class="space-y-2">
                                        <label class="text-[10px] font-black text-red-600 uppercase tracking-widest">Alasan Penolakan</label>
                                        <textarea name="alasan_penolakan" required placeholder="MISAL: BUKTI TIDAK VALID" class="w-full bg-white border border-red-200 rounded-sm px-4 py-3 text-[11px] font-bold text-popfit-dark focus:border-red-500 outline-none transition-all uppercase h-24"></textarea>
                                    </div>
                                    <div class="flex gap-2">
                                        <button type="submit" name="reject_pembayaran" class="flex-1 bg-red-600 text-white py-3 rounded-sm text-[10px] font-black uppercase tracking-widest hover:bg-red-700 transition-all">Kirim</button>
                                        <button type="button" onclick="hideRejectForm()" class="px-6 py-3 text-[10px] font-black uppercase text-popfit-textMuted hover:text-popfit-dark transition-all">Batal</button>
                                    </div>
                                </form>
                            </div>
                        <?php elseif($trx['pembayaran'] == 'belum bayar'): ?>
                            <div class="p-6 bg-popfit-bg border border-popfit-border rounded-sm">
                                <i class="ph ph-hand-coins text-4xl text-popfit-dark mb-4 opacity-30"></i>
                                <p class="text-[11px] text-popfit-textMuted leading-relaxed mb-6 uppercase font-bold">Terima uang tunai di koperasi sekolah?</p>
                                <button type="button" onclick="confirmBayar()" class="w-full py-4 bg-popfit-dark text-white text-[11px] font-black uppercase tracking-[0.2em] rounded-sm hover:bg-popfit-light transition-all shadow-lg active:scale-95">
                                    Konfirmasi Tunai
                                </button>
                            </div>
                        <?php else: ?>
                            <div class="py-12 bg-green-50 rounded-sm border border-green-100">
                                <i class="ph-fill ph-check-circle text-6xl text-green-500 mb-6 drop-shadow-sm"></i>
                                <p class="text-xs font-black text-green-600 uppercase tracking-[0.2em] mb-2 leading-none">TAGIHAN LUNAS</p>
                                <p class="text-[10px] text-green-700 font-bold opacity-70 italic uppercase">Pembayaran Selesai</p>
                            </div>
                        <?php endif; ?>
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

        function showRejectForm() { document.getElementById('rejectForm').classList.remove('hidden'); }
        function hideRejectForm() { document.getElementById('rejectForm').classList.add('hidden'); }

        function confirmApprove() {
            Swal.fire({
                title: 'TERIMA PEMBAYARAN?',
                text: "Pastikan nominal digital sudah sesuai.",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#2A4736',
                cancelButtonColor: '#E4E4E7',
                confirmButtonText: 'YA, TERIMA!',
                cancelButtonText: 'BATAL'
            }).then((result) => {
                if (result.isConfirmed) {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    const input = document.createElement('input');
                    input.type = 'hidden'; input.name = 'approve_pembayaran'; input.value = '1';
                    form.appendChild(input);
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        }

        function confirmBayar() {
            Swal.fire({
                title: 'TERIMA TUNAI?',
                text: "Apakah Anda sudah menerima uang tunai dari siswa?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#2A4736',
                cancelButtonColor: '#E4E4E7',
                confirmButtonText: 'YA, SUDAH!',
                cancelButtonText: 'BELUM'
            }).then((result) => {
                if (result.isConfirmed) {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    const input = document.createElement('input');
                    input.type = 'hidden'; input.name = 'bayar'; input.value = '1';
                    form.appendChild(input);
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        }

        <?php if(isset($_SESSION['swal'])): ?>
            Swal.fire({
                title: '<?= $_SESSION['swal']['title'] ?>',
                text: '<?= $_SESSION['swal']['text'] ?>',
                icon: '<?= $_SESSION['swal']['icon'] ?>',
                confirmButtonColor: '#2A4736',
            }).then(() => {
                <?php if(isset($_SESSION['swal']['redirect'])): ?>
                    window.location.href = '<?= $_SESSION['swal']['redirect'] ?>';
                <?php endif; ?>
            });
            <?php unset($_SESSION['swal']); ?>
        <?php endif; ?>
    </script>
</body>
</html>
