<?php
require '../config/config.php';
session_start();

if (!isset($_SESSION["login"])) {
    header('Content-Type: application/json');
    echo json_encode([]);
    exit;
}

$id_user = $_SESSION["id_user"];

// Jika ada param read, tandai semua sudah dibaca
if(isset($_GET['read_all'])){
    mysqli_query($connect, "UPDATE notifikasi SET is_read = 1 WHERE id_user = $id_user");
    header("Location: notif.php");
    exit;
}

// Ambil notifikasi dari database
$query = "SELECT * FROM notifikasi WHERE id_user = $id_user ORDER BY waktu_notif DESC LIMIT 50";
$result = mysqli_query($connect, $query);
$notif = [];
while($row = mysqli_fetch_assoc($result)){
    $notif[] = $row;
}

// AJAX RESPONSE UNTUK BADGE (Hanya yang belum dibaca)
if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') {
    $unread_query = "SELECT COUNT(*) as total FROM notifikasi WHERE id_user = $id_user AND is_read = 0";
    $unread_count = mysqli_fetch_assoc(mysqli_query($connect, $unread_query))['total'];
    
    header('Content-Type: application/json');
    echo json_encode([
        'total' => count($notif),
        'unread' => (int)$unread_count,
        'data' => $notif
    ]);
    exit;
}

function tglIndo($tanggal){
    $bulan = [1=>"Jan","Feb","Mar","Apr","Mei","Jun","Jul","Agu","Sep","Okt","Nov","Des"];
    $t = strtotime($tanggal);
    return date('d', $t) . ' ' . $bulan[(int)date('m', $t)] . ' ' . date('Y H:i', $t);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifikasi Saya - PopFit</title>
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
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-thumb { background: #E4E4E7; }
    </style>
</head>
<body class="bg-popfit-bg text-popfit-text font-sans min-h-screen flex items-center justify-center p-4">

    <div class="w-full max-w-2xl bg-white border border-popfit-border rounded-sm overflow-hidden flex flex-col h-[85vh]">
        <!-- Header -->
        <header class="p-6 border-b border-popfit-border flex items-center justify-between flex-shrink-0">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-sm bg-popfit-bg flex items-center justify-center text-popfit-dark">
                    <i class="ph ph-bell text-2xl"></i>
                </div>
                <h2 class="text-lg font-bold text-popfit-dark">Notifikasi Saya</h2>
            </div>
            <?php if(count($notif) > 0) : ?>
            <a href="?read_all=1" class="text-[10px] font-black uppercase tracking-widest text-popfit-textMuted hover:text-popfit-dark transition-colors">
                Tandai Semua Dibaca
            </a>
            <?php endif; ?>
        </header>

        <!-- Content -->
        <main class="flex-1 overflow-y-auto p-6 space-y-4 bg-gray-50/50">
            <?php if(count($notif) > 0) : ?>
                <?php foreach($notif as $n) : ?>
                <div class="p-5 border border-popfit-border rounded-sm transition-all relative <?= ($n['is_read'] == 0) ? 'bg-white border-l-4 border-l-popfit-accent' : 'bg-gray-50' ?>">
                    <div class="flex items-start justify-between mb-2">
                        <span class="text-[11px] font-bold text-popfit-dark leading-relaxed pr-8">
                            <?= htmlspecialchars($n['pesan']); ?>
                        </span>
                        <?php if($n['is_read'] == 0): ?>
                        <div class="w-2 h-2 rounded-full bg-popfit-accent flex-shrink-0 mt-1"></div>
                        <?php endif; ?>
                    </div>
                    <div class="flex items-center text-[10px] font-medium text-popfit-textMuted uppercase tracking-wider">
                        <i class="ph ph-clock mr-1.5 text-xs"></i>
                        <?= tglIndo($n['waktu_notif']); ?>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else : ?>
                <div class="h-full flex flex-col items-center justify-center py-20 opacity-30 text-center">
                    <i class="ph ph-bell-slash text-6xl mb-4"></i>
                    <p class="text-sm font-medium uppercase tracking-widest">Belum ada notifikasi</p>
                </div>
            <?php endif; ?>
        </main>

        <!-- Footer -->
        <footer class="p-6 border-t border-popfit-border flex justify-center flex-shrink-0">
            <button onclick="history.back()" class="bg-popfit-dark text-white text-[10px] font-black uppercase tracking-widest px-8 py-3 rounded-sm hover:bg-popfit-light transition-colors">
                Kembali
            </button>
        </footer>
    </div>

</body>
</html>
