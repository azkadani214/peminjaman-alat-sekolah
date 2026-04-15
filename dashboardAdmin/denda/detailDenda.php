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

if (isset($_POST['approve_pembayaran'])) {
    mysqli_query($connect, "UPDATE transaksi SET pembayaran = 'lunas', alasan_penolakan = NULL WHERE id_transaksi = $idTransaksi");
    tambahLog($_SESSION['id_user'], "Menyetujui bukti pembayaran denda ID: $idTransaksi");
    echo "<script>alert('Pembayaran Disetujui!'); window.location='detailDenda.php?id=$idTransaksi';</script>";
    exit;
}

if (isset($_POST['reject_pembayaran'])) {
    $alasan = mysqli_real_escape_string($connect, $_POST['alasan_penolakan']);
    mysqli_query($connect, "UPDATE transaksi SET pembayaran = 'ditolak', alasan_penolakan = '$alasan' WHERE id_transaksi = $idTransaksi");
    tambahLog($_SESSION['id_user'], "Menolak bukti pembayaran denda ID: $idTransaksi. Alasan: $alasan");
    echo "<script>alert('Pembayaran Ditolak!'); window.location='detailDenda.php?id=$idTransaksi';</script>";
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <!-- ... header content ... -->
</head>
<!-- ... body and sidebar ... -->
                        <div class="h-px bg-gray-100 mb-8"></div>

                        <?php if($trx['pembayaran'] == 'pending'): ?>
                            <div class="p-4 bg-popfit-accent/10 border border-popfit-accent/20 rounded-sm text-left">
                                <h5 class="text-[10px] font-black text-popfit-dark uppercase mb-4 flex items-center">
                                    <i class="ph-fill ph-warning-circle text-popfit-accent mr-2"></i> Verifikasi Pembayaran
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
                                    <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-2">Bukti Pembayaran:</p>
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
                                        <textarea name="alasan_penolakan" required placeholder="MISAL: BUKTI TIDAK JELAS / NOMINAL SALAH" class="w-full bg-white border border-red-200 rounded-sm px-3 py-2 text-[11px] font-bold text-popfit-dark focus:border-red-500 outline-none transition-all uppercase"></textarea>
                                        <div class="flex gap-2">
                                            <button type="submit" name="reject_pembayaran" class="flex-1 bg-red-600 text-white py-2 rounded-sm text-[9px] font-black uppercase tracking-widest hover:bg-red-700 transition-all">Konfirmasi Tolak</button>
                                            <button type="button" onclick="hideRejectForm()" class="px-4 py-2 text-[9px] font-black uppercase text-gray-400">Batal</button>
                                        </div>
                                    </form>
                                </div>

                                <script>
                                    function showRejectForm() { document.getElementById('rejectForm').classList.remove('hidden'); }
                                    function hideRejectForm() { document.getElementById('rejectForm').classList.add('hidden'); }
                                </script>
                            </div>
                        <?php elseif($trx['pembayaran'] == 'belum bayar' && $trx['denda'] > 0): ?>
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
                                <p class="text-xs font-black text-green-600 uppercase tracking-widest leading-loose">Tagihan Sudah Lunas<br>Tgl: <?= $trx['waktu_kembali'] ? date('d M Y', strtotime($trx['waktu_kembali'])) : date('d M Y') ?></p>
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
