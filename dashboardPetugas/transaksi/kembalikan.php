<?php
require '../../config/config.php';
session_start();

if (!isset($_SESSION["login"]) || $_SESSION["role"] != "petugas") {
    header("Location: ../../login.php");
    exit;
}

$id_transaksi = $_GET['id'] ?? null;
if (!$id_transaksi) {
    header("Location: transaksi.php");
    exit;
}

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

if (isset($_POST['submit_kembali'])) {
    $tgl_kembali = $_POST['tgl_kembali'];
    $jam_kembali = $_POST['jam_kembali'];
    $waktu_kembali_input = $tgl_kembali . ' ' . $jam_kembali;
    $kondisi = $_POST['kondisi'];

    $waktu_batas = strtotime($data['batas_kembali']);
    $waktu_kembali = strtotime($waktu_kembali_input);

    $denda = 0; $keterlambatan = 'tidak';
    if ($waktu_kembali > $waktu_batas) {
        $selisih = $waktu_kembali - $waktu_batas;
        $hari_terlambat = ceil($selisih / (60 * 60 * 24));
        $denda = $hari_terlambat * 5000;
        $keterlambatan = 'ya';
    }

    $id_petugas = $_SESSION['id_user'];
    $update = "UPDATE transaksi SET 
                status = 'dikembalikan',
                waktu_kembali = '$waktu_kembali_input',
                kondisi = '$kondisi',
                denda = $denda,
                keterlambatan = '$keterlambatan',
                id_petugas = $id_petugas
               WHERE id_transaksi = $id_transaksi";

    if (mysqli_query($connect, $update)) {
        $items = mysqli_query($connect, "SELECT * FROM detail_transaksi WHERE id_transaksi = $id_transaksi");
        while($item = mysqli_fetch_assoc($items)){
            $id_alat = $item['id_alat_olahraga'];
            $qty = $item['jumlah'];
            mysqli_query($connect, "UPDATE alat_olahraga SET stok = stok + $qty WHERE id_alat_olahraga = '$id_alat'");
        }
        tambahLog($id_petugas, "Memproses pengembalian transaksi #$id_transaksi");
        header("Location: transaksi.php?msg=kembali_success");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proses Pengembalian - PopFit Staff</title>
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
<body class="bg-[#F4F4F5] font-sans min-h-screen flex items-center justify-center p-6">
    <div class="w-full max-w-lg bg-white border border-popfit-border rounded-sm overflow-hidden flex flex-col">
        <header class="p-8 border-b border-popfit-border">
            <div class="flex items-center justify-between mb-6">
                <span class="text-[10px] font-black uppercase tracking-[0.3em] text-popfit-textMuted">Proses Staff</span>
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

            <div class="pt-4 flex flex-col space-y-3">
                <button type="submit" name="submit_kembali" class="w-full bg-popfit-dark text-white py-4 text-[11px] font-black uppercase tracking-[0.3em] rounded-sm hover:bg-popfit-light transition-all">
                    Konfirmasi Kembali
                </button>
                <a href="transaksi.php" class="w-full text-center py-4 text-[10px] font-black uppercase tracking-[0.2em] text-popfit-textMuted border border-popfit-border rounded-sm">Batal</a>
            </div>
        </form>
    </div>
</body>
</html>