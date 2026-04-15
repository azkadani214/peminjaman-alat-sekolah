<?php
require 'config/config.php';

// Fungsi helper untuk eksekusi query
function runQuery($connect, $sql, $desc) {
    if (mysqli_query($connect, $sql)) {
        echo "[SUCCESS] $desc\n";
    } else {
        echo "[ERROR] $desc: " . mysqli_error($connect) . "\n";
    }
}

echo "--- POPFIT COMPREHENSIVE SEEDER ---\n";

// 1. DATA MASTER: KATEGORI
$kategori = ['Bola', 'Raket', 'Atletik', 'Gym', 'Lainnya'];
foreach ($kategori as $k) {
    runQuery($connect, "INSERT IGNORE INTO kategori_alat_olahraga (kategori) VALUES ('$k')", "Kategori: $k");
}

// 2. DATA MASTER: ALAT
$alat = [
    ['BOLA-01', 'Bola Basket Spalding', 'Bola', 'Bola basket kualitas internasional untuk pertandingan resmi.', 10, 'basket.png'],
    ['BOLA-02', 'Bola Sepak Adidas', 'Bola', 'Bola sepak standar FIFA Prospero.', 15, 'sepak.png'],
    ['RK-01', 'Raket Yonex Nanoflare', 'Raket', 'Raket badminton ultra-lightweight.', 8, 'raket.png'],
    ['RK-02', 'Raket Tenis Wilson', 'Raket', 'Raket tenis untuk pemula dan menengah.', 5, 'tenis.png'],
    ['ATL-01', 'Lembing Alumunium', 'Atletik', 'Lembing untuk latihan ringan atletik sekolah.', 5, 'lembing.png'],
    ['GYM-01', 'Matras Yoga Premium', 'Gym', 'Matras yoga anti-slip, tebal 10mm.', 20, 'matras.png']
];
foreach ($alat as $a) {
    runQuery($connect, "INSERT IGNORE INTO alat_olahraga (id_alat_olahraga, nama_alat_olahraga, kategori, deskripsi, stok, foto_alat_olahraga) 
          VALUES ('$a[0]', '$a[1]', '$a[2]', '$a[3]', $a[4], '$a[5]')", "Alat: $a[1]");
}

// 3. DATA MASTER: USERS
$pass = password_hash('password', PASSWORD_DEFAULT);
$users = [
    ['Admin Utama', 'admin', $pass, 'admin utama', NULL, NULL],
    ['Staff Operasional', 'petugas', $pass, 'petugas', NULL, NULL],
    ['Siswa Teladan', 'siswa', $pass, 'siswa', '10001', 'XII RPL 1'],
    ['Ahmad Dani', 'dani', $pass, 'siswa', '10002', 'XII RPL 2'],
    ['Siti Nurhaliza', 'siti', $pass, 'siswa', '10003', 'XI TKJ 1']
];
foreach ($users as $u) {
    $nis = $u[4] ? "'$u[4]'" : "NULL";
    $kelas = $u[5] ? "'$u[5]'" : "NULL";
    runQuery($connect, "INSERT IGNORE INTO users (nama, username, password, role, nis, kelas, tgl_daftar) 
          VALUES ('$u[0]', '$u[1]', '$u[2]', '$u[3]', $nis, $kelas, NOW())", "User: $u[0]");
}

// 4. TRANSAKSI SAMPEL
// Ambil ID User Siswa (biasanya id 3, 4, 5 setelah master data masuk)
// Kita gunakan subquery untuk keamanan ID
$transaksi = [
    ['TRX-001', 'siswa', '2026-04-10 08:00:00', '2026-04-12', 'dikembalikan', 0, 'lunas', 'petugas'],
    ['TRX-002', 'dani', '2026-04-12 09:00:00', '2026-04-14', 'dipinjam', 0, '', 'petugas'],
    ['TRX-003', 'siti', '2026-04-15 10:00:00', '2026-04-17', 'menunggu', 0, '', NULL],
    ['TRX-004', 'siswa', '2026-04-01 08:00:00', '2026-04-03', 'dikembalikan', 10000, 'lunas', 'petugas']
];

foreach ($transaksi as $t) {
    $user_id_q = mysqli_fetch_assoc(mysqli_query($connect, "SELECT id_user FROM users WHERE username = '$t[1]'"))['id_user'] ?? 'NULL';
    $petugas_id_q = $t[7] ? (mysqli_fetch_assoc(mysqli_query($connect, "SELECT id_user FROM users WHERE username = '$t[7]'"))['id_user'] ?? 'NULL') : 'NULL';
    $pembayaran = $t[6] ? "'$t[6]'" : "NULL";
    
    runQuery($connect, "INSERT IGNORE INTO transaksi (id_transaksi, id_user, waktu_pinjam, batas_kembali, status, denda, pembayaran, id_petugas) 
          VALUES ('$t[0]', $user_id_q, '$t[2]', '$t[3]', '$t[4]', $t[5], $pembayaran, $petugas_id_q)", "Transaksi: $t[0]");
}

// 5. DETAIL TRANSAKSI
$details = [
    ['TRX-001', 'BOLA-01', 1],
    ['TRX-002', 'RK-01', 2],
    ['TRX-003', 'BOLA-02', 1],
    ['TRX-004', 'GYM-01', 1]
];
foreach ($details as $d) {
    runQuery($connect, "INSERT IGNORE INTO detail_transaksi (id_transaksi, id_alat_olahraga, jumlah) 
          VALUES ('$d[0]', '$d[1]', $d[2])", "Detail: $d[0] - $d[1]");
}

// 6. NOTIFIKASI
$siswa_id = mysqli_fetch_assoc(mysqli_query($connect, "SELECT id_user FROM users WHERE username = 'siswa'"))['id_user'] ?? 3;
$admin_id = mysqli_fetch_assoc(mysqli_query($connect, "SELECT id_user FROM users WHERE username = 'admin'"))['id_user'] ?? 1;

$notifs = [
    [$siswa_id, 'Peminjaman #TRX-001 Anda telah selesai.', '#', 1],
    [$siswa_id, 'Peminjaman #TRX-002 disetujui, silakan ambil alat di ruang olahraga.', '../transaksi/transaksi.php', 0],
    [$admin_id, 'Ada permintaan peminjaman baru dari Siti Nurhaliza.', 'transaksi/transaksi.php', 0]
];
foreach ($notifs as $n) {
    runQuery($connect, "INSERT INTO notifikasi (id_user, pesan, link, is_read, waktu_notif) 
          VALUES ($n[0], '$n[1]', '$n[2]', $n[3], NOW())", "Notif untuk User ID $n[0]");
}

echo "--- SEEDER SELESAI ---\n";
?>
