<?php
require 'config/config.php';

// Empty tables first for fresh seed (Optional, but user asked for 20 data)
// mysqli_query($connect, "SET FOREIGN_KEY_CHECKS = 0");
// mysqli_query($connect, "TRUNCATE detail_transaksi");
// mysqli_query($connect, "TRUNCATE transaksi");
// mysqli_query($connect, "TRUNCATE log_aktivitas");
// mysqli_query($connect, "TRUNCATE notifikasi");
// mysqli_query($connect, "SET FOREIGN_KEY_CHECKS = 1");

echo "Seeding Categories...\n";
$kategori = ['Bola', 'Raket', 'Matras', 'Gym', 'Renang', 'Kesehatan'];
foreach($kategori as $k){
    mysqli_query($connect, "INSERT IGNORE INTO kategori_alat_olahraga (kategori) VALUES ('$k')");
}

echo "Seeding Students...\n";
$firstNames = ['Budi', 'Siti', 'Agus', 'Dewi', 'Rian', 'Lia', 'Fajar', 'Anita', 'Dedi', 'Maya'];
$lastNames = ['Santoso', 'Rahmawati', 'Hidayat', 'Putri', 'Kurniawan', 'Sari', 'Pratama', 'Utami', 'Saputra', 'Lestari'];

for($i=1; $i<=15; $i++){
    $nama = $firstNames[array_rand($firstNames)] . " " . $lastNames[array_rand($lastNames)];
    $user = strtolower(str_replace(' ', '', $nama)) . $i;
    $pass = password_hash('password123', PASSWORD_DEFAULT);
    $nis = "100" . str_pad($i, 3, "0", STR_PAD_LEFT);
    $kelas = (rand(10, 12)) . " IPA " . (rand(1, 5));
    mysqli_query($connect, "INSERT IGNORE INTO users (nama, username, password, role, nis, kelas, no_telp) VALUES ('$nama', '$user', '$pass', 'siswa', '$nis', '$kelas', '08123456789')");
}

echo "Seeding Tools...\n";
$tools = [
    ['B001', 'Bola Basket Spalding', 'Bola', 10],
    ['B002', 'Bola Voli Mikasa', 'Bola', 12],
    ['B003', 'Bola Sepak Adidas', 'Bola', 15],
    ['R001', 'Raket Badminton Yonex', 'Raket', 20],
    ['R002', 'Raket Tenis Wilson', 'Raket', 8],
    ['M001', 'Matras Yoga Premium', 'Matras', 10],
    ['M002', 'Matras Senam Lantai', 'Matras', 5],
    ['G001', 'Dumbbell 5KG', 'Gym', 6],
    ['G002', 'Skipping Rope', 'Gym', 25],
    ['S001', 'Kacamata Renang Speedo', 'Renang', 15]
];
foreach($tools as $t){
    mysqli_query($connect, "INSERT IGNORE INTO alat_olahraga (id_alat_olahraga, nama_alat_olahraga, kategori, stok, deskripsi) VALUES ('{$t[0]}', '{$t[1]}', '{$t[2]}', {$t[3]}, 'Alat olahraga kualitas sekolah.')");
}

echo "Seeding Transactions (The 20 data user requested)...\n";
$users = queryReadData("SELECT id_user FROM users WHERE role = 'siswa'");
$alats = queryReadData("SELECT id_alat_olahraga FROM alat_olahraga");

for($i=1; $i<=20; $i++){
    $idU = $users[array_rand($users)]['id_user'];
    $idA = $alats[array_rand($alats)]['id_alat_olahraga'];
    
    // Shuffle conditions
    if($i <= 5) {
        $status = 'menunggu';
        $pembayaran = 'belum bayar';
        $denda = 0;
        $wktPinjam = date('Y-m-d H:i:s', strtotime('-1 hour'));
        $batasKembali = date('Y-m-d H:i:s', strtotime('+4 hours'));
    } elseif($i <= 10) {
        $status = 'dipinjam';
        $pembayaran = 'belum bayar';
        $denda = 0;
        $wktPinjam = date('Y-m-d H:i:s', strtotime('-2 hours'));
        $batasKembali = date('Y-m-d H:i:s', strtotime('+3 hours'));
    } elseif($i <= 15) {
        // Late Return
        $status = 'dikembalikan';
        $pembayaran = 'pending'; // Proof uploaded
        $denda = 15000;
        $wktPinjam = date('Y-m-d H:i:s', strtotime('-8 hours'));
        $batasKembali = date('Y-m-d H:i:s', strtotime('-4 hours'));
        $wktKembali = date('Y-m-d H:i:s', strtotime('-3 hours')); // 1 hour late
        mysqli_query($connect, "INSERT INTO transaksi (id_user, status, waktu_pinjam, batas_kembali, waktu_kembali, denda, pembayaran, metode_pembayaran_denda, bukti_pembayaran) 
                                VALUES ($idU, '$status', '$wktPinjam', '$batasKembali', '$wktKembali', $denda, '$pembayaran', 'OVO', 'dummy_bukti.png')");
        $idT = mysqli_insert_id($connect);
        mysqli_query($connect, "INSERT INTO detail_transaksi (id_transaksi, id_alat_olahraga, jumlah) VALUES ($idT, '$idA', 1)");
        continue;
    } else {
        // Clean return
        $status = 'dikembalikan';
        $pembayaran = 'lunas';
        $denda = 0;
        $wktPinjam = date('Y-m-d H:i:s', strtotime('-1 day'));
        $batasKembali = date('Y-m-d H:i:s', strtotime('-20 hours'));
        $wktKembali = date('Y-m-d H:i:s', strtotime('-21 hours'));
    }
    
    mysqli_query($connect, "INSERT INTO transaksi (id_user, status, waktu_pinjam, batas_kembali, waktu_kembali, denda, pembayaran) 
                            VALUES ($idU, '$status', '$wktPinjam', '$batasKembali', ".($status=='dikembalikan' ? "'$wktKembali'" : "NULL").", $denda, '$pembayaran')");
    $idT = mysqli_insert_id($connect);
    mysqli_query($connect, "INSERT INTO detail_transaksi (id_transaksi, id_alat_olahraga, jumlah) VALUES ($idT, '$idA', 1)");
}

echo "Seeding LOGS...\n";
tambahLog(1, "Sistem melakukan seeder data otomatis.");

echo "SEEDING COMPLETED SUCCESSFULLY!\n";
?>
