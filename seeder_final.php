<?php
require 'config/config.php';

echo "Memulai Seeder...\n";

// 1. CLEAR TABLES (Optional, but good for fresh state)
// mysqli_query($connect, "SET FOREIGN_KEY_CHECKS = 0");
// mysqli_query($connect, "TRUNCATE TABLE detail_transaksi");
// mysqli_query($connect, "TRUNCATE TABLE transaksi");
// mysqli_query($connect, "TRUNCATE TABLE users");
// mysqli_query($connect, "TRUNCATE TABLE alat_olahraga");
// mysqli_query($connect, "TRUNCATE TABLE kategori_alat_olahraga");
// mysqli_query($connect, "TRUNCATE TABLE notifikasi");
// mysqli_query($connect, "SET FOREIGN_KEY_CHECKS = 1");

// 2. SEED USERS
$pass_admin = password_hash('admin123', PASSWORD_DEFAULT);
$pass_petugas = password_hash('petugas123', PASSWORD_DEFAULT);
$pass_siswa = password_hash('siswa123', PASSWORD_DEFAULT);

$users = [
    ['Administrator', 'admin', $pass_admin, 'admin utama', NULL, NULL],
    ['Budi Petugas', 'petugas1', $pass_petugas, 'petugas', NULL, NULL],
    ['Siti Petugas', 'petugas2', $pass_petugas, 'petugas', NULL, NULL],
    ['Aditya Pratama', 'aditya', $pass_siswa, 'siswa', '12345', 'XII RPL 1'],
    ['Bambang Wijaya', 'bambang', $pass_siswa, 'siswa', '12346', 'XII RPL 2'],
    ['Citra Lestari', 'citra', $pass_siswa, 'siswa', '12347', 'XI TKJ 1'],
    ['Dedi Kurniawan', 'dedi', $pass_siswa, 'siswa', '12348', 'XI TKJ 2'],
    ['Eka Putri', 'ekaputri', $pass_siswa, 'siswa', '12349', 'X MM 1'],
    ['Farhan Hakim', 'farhan', $pass_siswa, 'siswa', '12350', 'X MM 2'],
    ['Gita Permata', 'gita', $pass_siswa, 'siswa', '12351', 'XII RPL 1'],
    ['Hadi Syahputra', 'hadi', $pass_siswa, 'siswa', '12352', 'XI TKJ 1'],
    ['Indra Bakti', 'indra', $pass_siswa, 'siswa', '12353', 'X MM 1'],
    ['Joko Susilo', 'joko', $pass_siswa, 'siswa', '12354', 'XII RPL 2']
];

foreach ($users as $u) {
    $q = "INSERT IGNORE INTO users (nama, username, password, role, nis, kelas, tgl_daftar) 
          VALUES ('$u[0]', '$u[1]', '$u[2]', '$u[3]', " . ($u[4] ? "'$u[4]'" : "NULL") . ", " . ($u[5] ? "'$u[5]'" : "NULL") . ", NOW())";
    mysqli_query($connect, $q);
}
echo "Seed Users: Berhasil\n";

// 3. SEED KATEGORI
$kategori = ['Bola', 'Raket', 'Atletik', 'Gym', 'Lainnya'];
foreach ($kategori as $k) {
    mysqli_query($connect, "INSERT IGNORE INTO kategori_alat_olahraga (kategori) VALUES ('$k')");
}
echo "Seed Kategori: Berhasil\n";

// 4. SEED ALAT
$alat = [
    ['BOLA-01', 'Bola Basket Spalding', 'Bola', 'Bola basket kualitas internasional', 10, 'basket.png'],
    ['BOLA-02', 'Bola Sepak Adidas', 'Bola', 'Bola sepak standar FIFA', 15, 'sepak.png'],
    ['RK-01', 'Raket Yonex Nanoflare', 'Raket', 'Raket badminton ringan', 8, 'raket.png'],
    ['RK-02', 'Raket Tenis Wilson', 'Raket', 'Raket tenis profesional', 5, 'tenis.png'],
    ['ATL-01', 'Lembing Alumunium', 'Atletik', 'Lembing untuk latihan', 5, 'lembing.png'],
    ['ATL-02', 'Peluru Logam 4kg', 'Atletik', 'Tolak peluru standar kompetisi', 12, 'peluru.png'],
    ['GYM-01', 'Matras Yoga Premium', 'Gym', 'Matras anti slip', 20, 'matras.png'],
    ['GYM-02', 'Dumbbell 5kg', 'Gym', 'Beban angkat tangan', 10, 'dumbbell.png']
];

foreach ($alat as $a) {
    mysqli_query($connect, "INSERT IGNORE INTO alat_olahraga (id_alat_olahraga, nama_alat_olahraga, kategori, deskripsi, stok, foto_alat_olahraga) 
          VALUES ('$a[0]', '$a[1]', '$a[2]', '$a[3]', $a[4], '$a[5]')");
}
echo "Seed Alat: Berhasil\n";

// 5. SEED NOTIFIKASI SAMPLE
$notifs = [
    [1, 'Selamat datang di PopFit Admin!', '#', 0],
    [4, 'Peminjaman Anda telah disetujui.', '../transaksi/transaksi.php', 0],
    [4, 'Jangan lupa mengembalikan alat esok hari.', '../transaksi/transaksi.php', 0]
];

foreach ($notifs as $n) {
    mysqli_query($connect, "INSERT INTO notifikasi (id_user, pesan, link, is_read, waktu_notif) 
          VALUES ($n[0], '$n[1]', '$n[2]', $n[3], NOW())");
}
echo "Seed Notifikasi: Berhasil\n";

echo "Seeder Selesai!\n";
?>
