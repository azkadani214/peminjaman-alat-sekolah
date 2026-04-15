<?php
require '../../config/config.php';
session_start();

if (!isset($_SESSION["login"]) || !in_array($_SESSION["role"], ["admin utama", "admin", "petugas"])) {
    header("Location: ../../login.php");
    exit;
}

$id_transaksi = $_GET['id'] ?? null;
if (!$id_transaksi) {
    header("Location: transaksi.php");
    exit;
}

// Ambil data transaksi
$query = "SELECT t.*, u.nama as nama_siswa 
          FROM transaksi t
          JOIN users u ON t.id_user = u.id_user
          WHERE t.id_transaksi = $id_transaksi";
$result = mysqli_query($connect, $query);
$data = mysqli_fetch_assoc($result);

if (!$data) {
    header("Location: transaksi.php");
    exit;
}

$error = '';
if (isset($_POST['submit_kembali'])) {
    $tgl_kembali = $_POST['tgl_kembali'];
    $jam_kembali = $_POST['jam_kembali'];
    $waktu_kembali_input = $tgl_kembali . ' ' . $jam_kembali;
    $kondisi = $_POST['kondisi'];

    $denda_kerusakan = (int)($_POST['denda_kerusakan'] ?? 0);
    $denda_keterlambatan = hitungDenda($data['batas_kembali'], $waktu_kembali_input);
    $total_denda = $denda_keterlambatan + $denda_kerusakan;
    $keterlambatan = ($denda_keterlambatan > 0) ? 'ya' : 'tidak';

    $id_petugas = $_SESSION['id_user'];
    $update = "UPDATE transaksi SET 
                status = 'dikembalikan',
                waktu_kembali = '$waktu_kembali_input',
                kondisi = '$kondisi',
                denda = $total_denda,
                keterlambatan = '$keterlambatan',
                id_petugas = $id_petugas,
                pembayaran = 'belum bayar'
               WHERE id_transaksi = $id_transaksi";

    if (mysqli_query($connect, $update)) {
        // Balikin stok
        $items = mysqli_query($connect, "SELECT * FROM detail_transaksi WHERE id_transaksi = $id_transaksi");
        while($item = mysqli_fetch_assoc($items)){
            $id_alat = $item['id_alat_olahraga'];
            $qty = $item['jumlah'];
            mysqli_query($connect, "UPDATE alat_olahraga SET stok = stok + $qty WHERE id_alat_olahraga = '$id_alat'");
        }
        tambahLog($id_petugas, "Memproses pengembalian transaksi #$id_transaksi. Kondisi: $kondisi. Denda: Rp$total_denda");
        $_SESSION['swal'] = [
            'title' => 'BERHASIL!',
            'text' => 'Pengembalian alat telah dikonfirmasi.',
            'icon' => 'success',
            'redirect' => 'transaksi.php'
        ];
        header("Location: kembalikan.php?id=$id_transaksi");
        exit;
    } else {
        $error = "Terjadi kesalahan saat memproses data.";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proses Pengembalian - PopFit Admin</title>
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
                            dark: '#2A4736', light: '#3E614C', accent: '#F5C460', border: '#E4E4E7', textMuted: '#6B7280'
                        }
                    }
                }
            }
        }
    </script>
    <style> * { box-shadow: none !important; } </style>
</head>
<body class="bg-[#F4F4F5] font-sans min-h-screen flex items-center justify-center p-6 text-[13px]">
    <div class="w-full max-w-lg bg-white border border-popfit-border rounded-sm overflow-hidden flex flex-col">
        <header class="p-8 border-b border-popfit-border text-[13px]">
            <div class="flex items-center justify-between mb-6">
                <span class="text-[10px] font-black uppercase tracking-[0.3em] text-popfit-textMuted">Proses Admin</span>
                <span class="text-[10px] font-black uppercase tracking-widest bg-popfit-dark text-white px-3 py-1 rounded-sm">ID #<?= $id_transaksi ?></span>
            </div>
            <h1 class="text-3xl font-black text-popfit-dark tracking-tighter uppercase leading-none"><?= htmlspecialchars($data['nama_siswa']) ?></h1>
            <p class="text-[11px] font-bold text-popfit-textMuted uppercase mt-2">Batas: <span class="text-red-500"><?= date('d/m/Y H:i', strtotime($data['batas_kembali'])) ?></span></p>
        </header>

        <form method="post" class="p-8 space-y-6">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-[10px] font-black text-popfit-dark uppercase tracking-[0.2em] mb-2.5">Tgl Kembali</label>
                    <input type="date" name="tgl_kembali" required value="<?= date('Y-m-d') ?>"
                           class="w-full px-4 py-3 bg-gray-50 border border-popfit-border text-xs font-black focus:border-popfit-dark outline-none rounded-sm transition-all">
                </div>
                <div>
                    <label class="block text-[10px] font-black text-popfit-dark uppercase tracking-[0.2em] mb-2.5">Jam Kembali</label>
                    <input type="time" name="jam_kembali" required value="<?= date('H:i') ?>"
                           class="w-full px-4 py-3 bg-gray-50 border border-popfit-border text-xs font-black focus:border-popfit-dark outline-none rounded-sm transition-all">
                </div>
            </div>

            <div>
                <label class="block text-[10px] font-black text-popfit-dark uppercase tracking-[0.2em] mb-2.5">Kondisi Alat</label>
                <textarea name="kondisi" required rows="3"
                          class="w-full px-4 py-3 bg-gray-50 border border-popfit-border text-xs font-bold focus:border-popfit-dark outline-none rounded-sm transition-all"
                          placeholder="Catatan kondisi..."></textarea>
            </div>

            <div>
                <label class="block text-[10px] font-black text-popfit-dark uppercase tracking-[0.2em] mb-2.5">Denda Kerusakan (Manual)</label>
                <div class="relative">
                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-[10px] font-black text-popfit-dark">RP</span>
                    <input type="number" name="denda_kerusakan" value="0" min="0"
                           class="w-full pl-10 pr-4 py-3 bg-gray-50 border border-popfit-border text-xs font-black focus:border-popfit-dark outline-none rounded-sm transition-all">
                </div>
            </div>

            <div id="delayInfoBox" class="p-4 bg-popfit-dark rounded-sm transition-all">
                <p class="text-[10px] font-black text-popfit-accent uppercase tracking-widest mb-1 italic">* INFO KETERLAMBATAN</p>
                <div id="delayDetails" class="text-[11px] text-white font-medium leading-relaxed">
                    Sedang menghitung...
                </div>
            </div>

            <div class="pt-4 flex flex-col space-y-3">
                <button type="submit" name="submit_kembali" class="w-full bg-popfit-dark text-white py-4 text-[11px] font-black uppercase tracking-[0.3em] rounded-sm hover:bg-popfit-light transition-all">
                    Konfirmasi Kembali
                </button>
                <a href="transaksi.php" class="w-full text-center py-4 text-[10px] font-black uppercase tracking-[0.2em] text-popfit-textMuted border border-popfit-border rounded-sm">Batal</a>
            </div>
        </form>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        const tglInput = document.querySelector('input[name="tgl_kembali"]');
        const jamInput = document.querySelector('input[name="jam_kembali"]');
        const infoBox = document.getElementById('delayInfoBox');
        const infoDetails = document.getElementById('delayDetails');
        const batasKembali = "<?= $data['batas_kembali'] ?>";

        function updateDelayInfo() {
            const tgl = tglInput.value;
            const jam = jamInput.value;
            if(!tgl || !jam) return;

            const waktuKembali = new Date(tgl + 'T' + jam);
            const tsBatas = new Date(batasKembali.replace(' ', 'T'));
            
            const selisihMs = waktuKembali - tsBatas;
            const selisihMenit = Math.ceil(selisihMs / (1000 * 60));

            if(selisihMenit <= 0) {
                infoBox.className = "p-4 bg-green-600 rounded-sm transition-all text-[13px]";
                infoDetails.innerHTML = "Tepat Waktu / Lebih Awal. <b class='text-white'>Tidak ada denda keterlambatan.</b>";
            } else {
                infoBox.className = "p-4 bg-popfit-dark rounded-sm transition-all border-l-4 border-popfit-accent text-[13px]";
                const jamTelat = Math.floor(selisihMenit / 60);
                const menitTelat = selisihMenit % 60;
                const denda = Math.ceil(selisihMenit / 30) * 5000;
                
                let teks = "";
                if(jamTelat > 0) teks += jamTelat + " Jam ";
                if(menitTelat > 0) teks += menitTelat + " Menit";
                
                infoDetails.innerHTML = "Terlambat: <b class='text-popfit-accent'>" + teks + "</b><br>Estimasi Denda: <b class='text-popfit-accent'>Rp " + denda.toLocaleString('id-ID') + "</b>";
            }
        }

        tglInput.addEventListener('change', updateDelayInfo);
        jamInput.addEventListener('change', updateDelayInfo);
        updateDelayInfo();

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
