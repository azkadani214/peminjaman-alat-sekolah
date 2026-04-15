<?php
require 'config/config.php';

function runQuery($connect, $sql, $desc) {
    if (mysqli_query($connect, $sql)) {
        echo "[SUCCESS] $desc\n";
    } else {
        echo "[ERROR] $desc: " . mysqli_error($connect) . "\n";
    }
}

echo "--- POPFIT MASSIVE SEEDER (Min 10 per table) ---\n";

// 1. KATEGORI (10)
$kategoris = ['Bola Besar', 'Bola Kecil', 'Atletik', 'Gym & Fitness', 'Bela Diri', 'Renang', 'Panahan', 'Tenis Meja', 'Bulutangkis', 'E-Sport Accessories'];
foreach ($kategoris as $k) {
    runQuery($connect, "INSERT IGNORE INTO kategori_alat_olahraga (kategori) VALUES ('$k')", "Kategori: $k");
}

// 2. USERS (15)
$pass = password_hash('password', PASSWORD_DEFAULT);
$users = [
    ['Administrator Utama', 'admin', $pass, 'admin utama', NULL, NULL],
    ['Admin Cadangan', 'admin2', $pass, 'admin', NULL, NULL],
    ['Staff Budi', 'petugas1', $pass, 'petugas', NULL, NULL],
    ['Staff Siti', 'petugas2', $pass, 'petugas', NULL, NULL],
    ['Staff Agus', 'petugas3', $pass, 'petugas', NULL, NULL],
    ['Rizky Ramadhan', 'rizky', $pass, 'siswa', '10001', 'XII RPL 1'],
    ['Dewi Sartika', 'dewi', $pass, 'siswa', '10002', 'XII RPL 2'],
    ['Bambang Pamungkas', 'bambang', $pass, 'siswa', '10003', 'XI TKJ 1'],
    ['Susi Susanti', 'susi', $pass, 'siswa', '10004', 'XI TKJ 2'],
    ['Taufik Hidayat', 'taufik', $pass, 'siswa', '10005', 'X MM 1'],
    ['Jonathan Christie', 'jojo', $pass, 'siswa', '10006', 'X MM 2'],
    ['Kevin Sanjaya', 'kevin', $pass, 'siswa', '10007', 'XII TKJ 1'],
    ['Marcus Gideon', 'marcus', $pass, 'siswa', '10008', 'XII TKJ 2'],
    ['Anthony Ginting', 'ginting', $pass, 'siswa', '10009', 'XI RPL 1'],
    ['Greysia Polii', 'greys', $pass, 'siswa', '10010', 'XI RPL 2']
];
foreach ($users as $u) {
    $nis = $u[4] ? "'$u[4]'" : "NULL";
    $kelas = $u[5] ? "'$u[5]'" : "NULL";
    runQuery($connect, "INSERT IGNORE INTO users (nama, username, password, role, nis, kelas, tgl_daftar) 
          VALUES ('$u[0]', '$u[1]', '$u[2]', '$u[3]', $nis, $kelas, NOW())", "User: $u[0]");
}

// 3. ALAT OLAHRAGA (12)
$alats = [
    ['BOLA-01', 'Bola Basket Molten BG5000', 'Bola Besar', 'Bola basket premium dari kulit asli.', 10, 'basket1.png'],
    ['BOLA-02', 'Bola Sepak Nike Flight', 'Bola Besar', 'Bola sepak teknologi Aerowsculpt.', 15, 'sepak1.png'],
    ['RK-01', 'Yonex Astrox 88D Pro', 'Bulutangkis', 'Raket badminton power-heavy.', 8, 'raket1.png'],
    ['RK-02', 'Victor Thruster K', 'Bulutangkis', 'Raket badminton stabil dan kuat.', 12, 'raket2.png'],
    ['TM-01', 'Bat Butterfly Timo Boll', 'Tenis Meja', 'Bat tenis meja profesional.', 10, 'bat1.png'],
    ['ATL-01', 'Lembing Nordic Carbon', 'Atletik', 'Lembing kompetisi atletik.', 5, 'lembing1.png'],
    ['GYM-01', 'Dumbbell Rubber 5kg', 'Gym & Fitness', 'Beban tangan karet antislip.', 20, 'dumb1.png'],
    ['GYM-02', 'Kettlebell 10kg', 'Gym & Fitness', 'Beban ayun untuk latihan core.', 10, 'kettle1.png'],
    ['PAN-01', 'Busur Recurve Cartel', 'Panahan', 'Busur panah standar kompetisi.', 6, 'busur1.png'],
    ['BD-01', 'Samsak Tinju Fairtex', 'Bela Diri', 'Samsak gantung kulit sintetis.', 4, 'samsak1.png'],
    ['RN-01', 'Kacamata Renang Speedo', 'Renang', 'Kacamata renang anti-fog.', 25, 'kacamata1.png'],
    ['ES-01', 'Logitech G Pro Mouse', 'E-Sport Accessories', 'Mouse gaming ultra-lightweight.', 10, 'mouse1.png']
];
foreach ($alats as $a) {
    runQuery($connect, "INSERT IGNORE INTO alat_olahraga (id_alat_olahraga, nama_alat_olahraga, kategori, deskripsi, stok, foto_alat_olahraga) 
          VALUES ('$a[0]', '$a[1]', '$a[2]', '$a[3]', $a[4], '$a[5]')", "Alat: $a[1]");
}

// Map Users
$res = mysqli_query($connect, "SELECT id_user FROM users WHERE role = 'siswa'");
while($row = mysqli_fetch_assoc($res)) $siswaIDs[] = $row['id_user'];
$res = mysqli_query($connect, "SELECT id_user FROM users WHERE role = 'petugas'");
while($row = mysqli_fetch_assoc($res)) $petugasIDs[] = $row['id_user'];
$res = mysqli_query($connect, "SELECT id_user FROM users WHERE username = 'admin'");
$adminID = mysqli_fetch_assoc($res)['id_user'] ?? 1;

// 4. TRANSAKSI (15)
$statuses = ['menunggu', 'disetujui', 'dipinjam', 'dikembalikan', 'ditolak'];
for ($i=1; $i <= 15; $i++) { 
    $id_trx = "TRX-MASS" . str_pad($i, 3, "0", STR_PAD_LEFT);
    $user_id = $siswaIDs[array_rand($siswaIDs)];
    $petugas_id = ($i % 3 == 0) ? 'NULL' : $petugasIDs[array_rand($petugasIDs)];
    $status = $statuses[array_rand($statuses)];
    $denda = ($status == 'dikembalikan' && $i % 4 == 0) ? 20000 : 0;
    $pembayaran = ($denda > 0) ? "'lunas'" : "''";
    $tgl = date('Y-m-d H:i:s', strtotime("-$i days"));
    $batas = date('Y-m-d', strtotime("-$i days + 3 days"));
    
    runQuery($connect, "INSERT IGNORE INTO transaksi (id_transaksi, id_user, waktu_pinjam, batas_kembali, status, denda, pembayaran, id_petugas) 
          VALUES ('$id_trx', $user_id, '$tgl', '$batas', '$status', $denda, $pembayaran, $petugas_id)", "Trx: $id_trx");
}

// 5. DETAIL TRANSAKSI
$allAlatIDs = [];
foreach($alats as $a) $allAlatIDs[] = $a[0];
for ($i=1; $i <= 15; $i++) { 
    $id_trx = "TRX-MASS" . str_pad($i, 3, "0", STR_PAD_LEFT);
    $id_alat = $allAlatIDs[array_rand($allAlatIDs)];
    runQuery($connect, "INSERT IGNORE INTO detail_transaksi (id_transaksi, id_alat_olahraga, jumlah) 
          VALUES ('$id_trx', '$id_alat', 1)", "Detail Trx: $id_trx");
}

// 6. NOTIFIKASI
for ($i=1; $i <= 15; $i++) { 
    $user_id = $siswaIDs[array_rand($siswaIDs)];
    runQuery($connect, "INSERT INTO notifikasi (id_user, pesan, link, is_read, waktu_notif) 
          VALUES ($user_id, 'Pesan notifikasi sistem ke-$i', '#', 0, NOW())", "Notif $i");
}

// 7. LOG AKTIVITAS (10)
runQuery($connect, "CREATE TABLE IF NOT EXISTS log_aktivitas (
    id_log INT AUTO_INCREMENT PRIMARY KEY,
    id_user INT,
    aktivitas TEXT,
    waktu_aktivitas DATETIME
)", "Ensure Log Table Exists");

$logs = ['Sistem Start', 'Massive Seeding', 'Data Update', 'Audit Log', 'Admin Login', 'Staff Setup', 'Inventory Check', 'Monthly Report', 'User Management', 'Fines Calculation'];
foreach ($logs as $l) {
    runQuery($connect, "INSERT INTO log_aktivitas (id_user, aktivitas, waktu_aktivitas) VALUES ($adminID, '$l', NOW())", "Log: $l");
}

echo "--- MASSIVE SEEDER COMPLETED ---\n";
?>
