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
    $_SESSION['swal'] = ['title' => 'ERROR', 'text' => 'Data denda tidak ditemukan!', 'icon' => 'error', 'redirect' => 'denda.php'];
    header("Location: denda.php");
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

// HANDLE SIMULASI PEMBAYARAN
if (isset($_POST['bayar_simulasi'])) {
    $metode = mysqli_real_escape_string($connect, $_POST['metode_pembayaran_denda']);
    $nama_pengirim = mysqli_real_escape_string($connect, $_POST['nama_pengirim_pembayaran']);
    $catatan = mysqli_real_escape_string($connect, $_POST['catatan_pembayaran']);
    
    $bukti_pembayaran = null;
    if(isset($_FILES['bukti_pembayaran']) && $_FILES['bukti_pembayaran']['error'] === 0){
        $ext = pathinfo($_FILES['bukti_pembayaran']['name'], PATHINFO_EXTENSION);
        $nama_file = "bayar_" . time() . "_" . rand(1000, 9999) . "." . $ext;
        if(move_uploaded_file($_FILES['bukti_pembayaran']['tmp_name'], "../../uploads/" . $nama_file)){
            $bukti_pembayaran = $nama_file;
        }
    }

    if ($bukti_pembayaran) {
        $update = "UPDATE transaksi SET 
                    pembayaran = 'pending', 
                    bukti_pembayaran = '$bukti_pembayaran', 
                    metode_pembayaran_denda = '$metode',
                    nama_pengirim_pembayaran = '$nama_pengirim',
                    catatan_pembayaran = '$catatan',
                    alasan_penolakan = NULL 
                   WHERE id_transaksi = $idTransaksi";
        if (mysqli_query($connect, $update)) {
            tambahLog($id_user, "Mengunggah bukti pembayaran denda ID: $idTransaksi ($metode)");
            $_SESSION['swal'] = [
                'title' => 'BERHASIL!',
                'text' => 'Bukti pembayaran berhasil diunggah! Tunggu verifikasi admin.',
                'icon' => 'success',
                'redirect' => "detailDenda.php?id=$idTransaksi"
            ];
            header("Location: detailDenda.php?id=$idTransaksi");
            exit;
        }
    } else {
        $error_upload = "Gagal mengunggah bukti. Pastikan file terpilih.";
    }
}
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
        .sidebar { transition: transform 0.3s ease-in-out; }
    </style>
</head>
<body class="bg-popfit-bg text-popfit-text font-sans h-screen overflow-hidden flex text-[13px]">

    <div id="sidebarOverlay" class="fixed inset-0 bg-black/50 z-40 hidden transition-opacity"></div>

    <?php 
        $rel = "../"; 
        $activeIndex = "denda"; 
        include '../../layout/sidebar_siswa.php'; 
    ?>

    <!-- MAIN CONTENT -->
    <div class="flex-1 flex flex-col h-screen w-full relative">
        <?php 
            $pageTitle = "Detail Denda #$idTransaksi"; 
            include '../../layout/header_siswa.php'; 
        ?>

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

                            <?php if($trx['keterlambatan'] == 'ya' && $trx['waktu_kembali']): 
                                $det = cekDetailKeterlambatan($trx['batas_kembali'], $trx['waktu_kembali']);
                            ?>
                            <div class="col-span-1 md:col-span-2 p-4 bg-red-50 border border-red-100 rounded-sm">
                                <div class="flex items-center justify-between mb-2">
                                    <p class="text-[10px] uppercase font-black text-red-600 tracking-widest flex items-center">
                                        <i class="ph ph-clock-countdown text-lg mr-2"></i> Keterangan Keterlambatan
                                    </p>
                                    <span class="text-[10px] font-black bg-red-600 text-white px-2 py-0.5 rounded-sm uppercase tracking-tighter">TELAT <?= $det['teks'] ?></span>
                                </div>
                                <p class="text-xs font-bold text-red-700 leading-relaxed italic">Kamu mengembalikan alat melewati batas waktu (Maksimal 5 Jam).</p>
                                <p class="text-[11px] font-black text-red-600 mt-2">Denda Keterlambatan: Rp <?= number_format($det['denda'], 0, ',', '.') ?></p>
                            </div>
                            <?php endif; ?>

                            <?php if($trx['denda_kerusakan'] > 0): ?>
                            <div class="col-span-1 md:col-span-2 p-4 bg-orange-50 border border-orange-100 rounded-sm">
                                <div class="flex items-center justify-between mb-2">
                                    <p class="text-[10px] uppercase font-black text-orange-600 tracking-widest flex items-center">
                                        <i class="ph ph-wrench text-lg mr-2"></i> Keterangan Kerusakan
                                    </p>
                                    <span class="text-[10px] font-black bg-orange-600 text-white px-2 py-0.5 rounded-sm uppercase tracking-tighter">Kondisi: <?= htmlspecialchars($trx['kondisi'] ?: 'Rusak') ?></span>
                                </div>
                                <p class="text-xs font-bold text-orange-700 leading-relaxed italic">Biaya tambahan karena kondisi alat saat dikembalikan memerlukan perbaikan.</p>
                                <p class="text-[11px] font-black text-orange-600 mt-2">Denda Kerusakan: Rp <?= number_format($trx['denda_kerusakan'], 0, ',', '.') ?></p>
                            </div>
                            <?php endif; ?>
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

                        <?php if(!$trx['pembayaran'] || $trx['pembayaran'] == 'belum bayar' || $trx['pembayaran'] == 'ditolak'): ?>
                            <?php if($trx['alasan_penolakan']): ?>
                                <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-sm text-left">
                                    <p class="text-[10px] font-black text-red-600 uppercase mb-1">Ditolak Sebelumnya:</p>
                                    <p class="text-xs font-bold text-red-700 italic">"<?= htmlspecialchars($trx['alasan_penolakan']) ?>"</p>
                                </div>
                            <?php endif; ?>

                            <div class="space-y-6 text-left" id="paymentFlow">
                                <h5 class="text-[10px] font-black text-popfit-dark uppercase tracking-widest border-b border-gray-100 pb-2">1. Pilih Metode Pembayaran</h5>
                                <div class="grid grid-cols-2 gap-3">
                                    <button type="button" onclick="selectMethod('Online')" class="method-btn p-4 border-2 border-popfit-border rounded-sm hover:border-popfit-dark transition-all flex flex-col items-center gap-2 group">
                                        <i class="ph-duotone ph-credit-card text-3xl text-popfit-textMuted group-hover:text-popfit-dark"></i>
                                        <div class="text-center">
                                            <span class="text-[11px] font-black uppercase block">Transfer Online</span>
                                            <span class="text-[8px] font-bold text-popfit-textMuted uppercase">Bank / E-Wallet</span>
                                        </div>
                                    </button>
                                    <button type="button" onclick="selectMethod('Cash')" class="method-btn p-4 border-2 border-popfit-border rounded-sm hover:border-popfit-dark transition-all flex flex-col items-center gap-2 group">
                                        <i class="ph-duotone ph-hand-coins text-3xl text-popfit-textMuted group-hover:text-popfit-dark"></i>
                                        <div class="text-center">
                                            <span class="text-[11px] font-black uppercase block">Bayar Tunai</span>
                                            <span class="text-[8px] font-bold text-popfit-textMuted uppercase">Ke Petugas (Gudang)</span>
                                        </div>
                                    </button>
                                </div>

                                <div id="methodDetail" class="hidden border-2 border-popfit-dark p-5 bg-popfit-bg rounded-sm animate-in fade-in slide-in-from-top-4 duration-500">
                                    <!-- Online Detail -->
                                    <div id="onlineDetail" class="hidden space-y-4">
                                        <div class="bg-white p-3 border border-popfit-border rounded-sm">
                                            <div class="flex justify-between items-center mb-2">
                                                <span class="text-[9px] font-black text-popfit-textMuted uppercase">Siap Transfer Ke:</span>
                                                <span class="text-[9px] font-black bg-popfit-dark text-white px-2 py-0.5 rounded-sm">QRIS / BANK</span>
                                            </div>
                                            <div class="flex items-center gap-4">
                                                <div class="w-16 h-16 bg-gray-100 flex items-center justify-center border border-popfit-border p-1">
                                                    <i class="ph ph-qr-code text-4xl opacity-20"></i>
                                                </div>
                                                <div>
                                                    <p class="text-[11px] font-black text-popfit-dark uppercase">BANK JATIM: 1234567890</p>
                                                    <p class="text-[10px] font-medium text-popfit-textMuted uppercase">a.n PopFit Official</p>
                                                </div>
                                            </div>
                                        </div>
                                        <p class="text-[9px] font-bold text-popfit-textMuted uppercase italic leading-loose">* Silakan lakukan pembayaran sesuai total denda (Rp <?= number_format($trx['denda'], 0, ',', '.') ?>), lalu unggah bukti di bawah.</p>
                                    </div>

                                    <!-- Cash Detail -->
                                    <div id="cashDetail" class="hidden">
                                        <div class="bg-popfit-dark text-white p-4 rounded-sm flex items-start gap-3">
                                            <i class="ph-fill ph-info text-2xl text-popfit-accent"></i>
                                            <div>
                                                <p class="text-[11px] font-black uppercase mb-1">Panduan Bayar Tunai</p>
                                                <p class="text-[10px] font-medium leading-relaxed opacity-80 uppercase">Silakan temui petugas inventaris di gudang sekolah dengan membawa uang pas. Petugas akan memotret uang/tanda terima sebagai bukti unggah.</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <form method="POST" enctype="multipart/form-data" class="space-y-4 hidden" id="paymentForm">
                                    <h5 class="text-[10px] font-black text-popfit-dark uppercase tracking-widest border-b border-gray-100 pb-2">2. Data & Bukti Unggah</h5>
                                    <input type="hidden" name="metode_pembayaran_denda" id="selectedMethodInput" required>
                                    
                                    <div class="space-y-4">
                                        <div>
                                            <label class="block text-[10px] font-black text-popfit-textMuted uppercase mb-1.5 tracking-tight">Nama Pengirim / Pembayar</label>
                                            <input type="text" name="nama_pengirim_pembayaran" required placeholder="NAMA SESUAI REKENING/SISWA" class="w-full bg-white border-2 border-popfit-border rounded-sm px-4 py-3 text-[11px] font-black text-popfit-dark focus:border-popfit-dark outline-none transition-all uppercase placeholder:text-gray-300">
                                        </div>
                                        <div>
                                            <label class="block text-[10px] font-black text-popfit-textMuted uppercase mb-1.5 tracking-tight">Foto Bukti (Screenshot/Kwitansi)</label>
                                            <div class="relative group">
                                                <input type="file" name="bukti_pembayaran" required accept="image/*" id="buktiImg" class="hidden">
                                                <label for="buktiImg" class="w-full border-2 border-dashed border-popfit-border px-4 py-8 rounded-sm flex flex-col items-center justify-center cursor-pointer group-hover:bg-popfit-bg transition-all">
                                                    <i class="ph-duotone ph-image-square text-4xl text-popfit-textMuted mb-2"></i>
                                                    <span id="fileName" class="text-[9px] font-black text-popfit-textMuted uppercase">Klik untuk Pilih Gambar</span>
                                                </label>
                                            </div>
                                        </div>
                                        <div>
                                            <label class="block text-[10px] font-black text-popfit-textMuted uppercase mb-1.5 tracking-tight">Catatan (Opsional)</label>
                                            <textarea name="catatan_pembayaran" rows="2" placeholder="CONTOH: SUDAH TRANSFER VIA OVO" class="w-full bg-white border-2 border-popfit-border rounded-sm px-4 py-3 text-[11px] font-bold text-popfit-dark focus:border-popfit-dark outline-none transition-all uppercase placeholder:text-gray-300"></textarea>
                                        </div>
                                    </div>

                                    <button type="submit" name="bayar_simulasi" onclick="confirmPayment(event)" class="w-full bg-popfit-dark text-white py-5 rounded-sm text-[11px] font-black uppercase tracking-[0.2em] hover:bg-popfit-light transition-all flex items-center justify-center gap-2 group uppercase">
                                        KONFIRMASI & KIRIM BUKTI <i class="ph ph-arrow-right group-hover:translate-x-1 transition-transform"></i>
                                    </button>
                                </form>
                            </div>

                            <script>
                                function selectMethod(method) {
                                    // Reset UI
                                    document.querySelectorAll('.method-btn').forEach(b => {
                                        b.classList.remove('bg-popfit-dark', 'text-white', 'border-popfit-dark');
                                        b.classList.add('border-popfit-border');
                                    });
                                    
                                    // Highlight selected
                                    const btn = event.currentTarget;
                                    btn.classList.add('bg-popfit-dark', 'text-white', 'border-popfit-dark');
                                    btn.classList.remove('border-popfit-border');
                                    
                                    // Set value
                                    document.getElementById('selectedMethodInput').value = method;
                                    
                                    // Show sections
                                    document.getElementById('methodDetail').classList.remove('hidden');
                                    document.getElementById('paymentForm').classList.remove('hidden');
                                    
                                    // Toggle content
                                    if(method === 'Online') {
                                        document.getElementById('onlineDetail').classList.remove('hidden');
                                        document.getElementById('cashDetail').classList.add('hidden');
                                    } else {
                                        document.getElementById('onlineDetail').classList.add('hidden');
                                        document.getElementById('cashDetail').classList.remove('hidden');
                                    }
                                }

                                document.getElementById('buktiImg').onchange = function() {
                                    if(this.files.length > 0) document.getElementById('fileName').textContent = this.files[0].name;
                                };

                                function confirmPayment(e) {
                                    e.preventDefault();
                                    const form = document.getElementById('uploadForm'); // Wait, ID is uploadForm or paymentForm? Let's check.
                                    // The ID in my snippet is paymentForm. I should fix it.
                                    
                                    Swal.fire({
                                        title: 'KIRIM BUKTI?',
                                        text: 'Pastikan data yang Anda masukkan sudah benar.',
                                        icon: 'question',
                                        showCancelButton: true,
                                        confirmButtonColor: '#2A4736',
                                        cancelButtonColor: '#d33',
                                        confirmButtonText: 'YA, KIRIM!',
                                        cancelButtonText: 'BATAL'
                                    }).then((result) => {
                                        if (result.isConfirmed) {
                                            document.getElementById('paymentForm').submit();
                                        }
                                    });
                                }
                            </script>
                        <?php elseif($trx['pembayaran'] == 'pending'): ?>
                            <div class="py-4 bg-popfit-accent/10 border border-popfit-accent/20 rounded-sm">
                                <i class="ph ph-hourglass text-5xl text-popfit-accent mb-4 block mx-auto"></i>
                                <p class="text-xs font-black text-popfit-dark uppercase tracking-widest">Menunggu Verifikasi</p>
                                <p class="text-[10px] text-popfit-textMuted mt-1 italic">Bukti pembayaran sedang diperiksa oleh admin.</p>
                                <?php if($trx['bukti_pembayaran']): ?>
                                    <a href="../../uploads/<?= $trx['bukti_pembayaran'] ?>" target="_blank" class="mt-4 inline-block text-[9px] font-black text-popfit-dark underline uppercase">Lihat Bukti Anda</a>
                                <?php endif; ?>
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

        <?php if(isset($error_upload)): ?>
            Swal.fire({
                title: 'GAGAL!',
                text: '<?= $error_upload ?>',
                icon: 'error',
                confirmButtonColor: '#2A4736',
            });
        <?php endif; ?>
    </script>
</body>
</html>
