<?php
require '../../config/config.php';
session_start();

// CEK LOGIN PETUGAS
if (!isset($_SESSION["login"]) || $_SESSION["role"] != "petugas") {
    header("Location: ../../login.php");
    exit;
}

$petugasName = htmlspecialchars($_SESSION['nama'] ?? 'Petugas');
$petugasUsername = htmlspecialchars($_SESSION['username'] ?? 'petugas');

// CEK ID
if (!isset($_GET['id'])) {
    header("Location: daftarAlat.php");
    exit;
}

$id = mysqli_real_escape_string($connect, $_GET['id']);

// AMBIL DATA ALAT
$result = mysqli_query($connect, "SELECT * FROM alat_olahraga WHERE id_alat_olahraga = '$id'");
if (mysqli_num_rows($result) == 0) {
    echo "<script>alert('Data tidak ditemukan'); window.location.href='daftarAlat.php';</script>";
    exit;
}
$data = mysqli_fetch_assoc($result);

/* HITUNG JUMLAH SEDANG DIPINJAM (DARI transaksi + detail_transaksi) */
$sedangDipinjam = 0;
$queryPinjam = mysqli_query($connect, "
    SELECT COUNT(*) as total 
    FROM transaksi t
    JOIN detail_transaksi d ON t.id_transaksi = d.id_transaksi
    WHERE d.id_alat_olahraga = '$id' 
    AND t.status = 'dipinjam'
");
if ($queryPinjam) {
    $row = mysqli_fetch_assoc($queryPinjam);
    $sedangDipinjam = $row['total'] ?? 0;
}

/* AMBIL RIWAYAT PEMINJAMAN TERAKHIR (DARI transaksi + users) */
$riwayatPinjam = mysqli_query($connect, "
    SELECT u.nama AS nama_siswa, t.waktu_pinjam, t.status
    FROM transaksi t
    JOIN detail_transaksi d ON t.id_transaksi = d.id_transaksi
    JOIN users u ON t.id_user = u.id_user
    WHERE d.id_alat_olahraga = '$id'
    ORDER BY t.waktu_pinjam DESC
    LIMIT 3
");

/* NOTIFIKASI */
function tableExists($connect, $table){
    $result = mysqli_query($connect, "SHOW TABLES LIKE '$table'");
    return mysqli_num_rows($result) > 0;
}

$file = '../../notifikasi_' . $_SESSION['username'] . '.txt';
if (!file_exists($file)) {
    file_put_contents($file, '2000-01-01 00:00:00');
}
$last_read = file_get_contents($file);
$last_read_ts = strtotime($last_read);
$jumlahNotifikasi = 0;

if(tableExists($connect, 'transaksi')){
    $q1 = mysqli_query($connect, "SELECT batas_kembali AS waktu FROM transaksi WHERE status='dipinjam' AND batas_kembali < NOW()");
    if($q1){
        while($d = mysqli_fetch_assoc($q1)){ 
            if(strtotime($d['waktu']) > $last_read_ts) $jumlahNotifikasi++; 
        }
    }
    
    $q2 = mysqli_query($connect, "SELECT waktu_pinjam AS waktu FROM transaksi WHERE status='menunggu'");
    if($q2){
        while($d = mysqli_fetch_assoc($q2)){ 
            if(strtotime($d['waktu']) > $last_read_ts) $jumlahNotifikasi++; 
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Detail Alat Olahraga - Petugas</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Poppins', sans-serif;
    background: #AFC2B2;
    margin: 0;
}

/* NAVBAR */
.navbar {
    background: #2F4A39;
    padding: 12px 30px;
    position: fixed;
    width: 100%;
    z-index: 1000;
    top: 0;
    left: 0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.navbar img {
    height: 45px;
}

.navbar .icons {
    display: flex;
    align-items: center;
    gap: 15px;
}

.navbar .icons i {
    color: white;
    font-size: 1.2rem;
    cursor: pointer;
}

.navbar .icons i:hover {
    color: #C7DBC9;
    transition: 0.2s;
}

/* PROFILE DROPDOWN */
.profile-dropdown {
    position: relative;
}

.profile-menu {
    display: none;
    position: absolute;
    top: 40px;
    right: 0;
    background: #DFE8E2;
    border-radius: 15px;
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
    text-align: center;
    padding: 20px;
    width: 220px;
    z-index: 1001;
}

.profile-menu hr {
    margin: 10px 0;
}

.profile-menu button {
    background: #2F4A39;
    border: none;
    color: white;
    padding: 8px 15px;
    border-radius: 10px;
    cursor: pointer;
    font-weight: 600;
    width: 100%;
    transition: 0.3s;
}

.profile-menu button:hover {
    background: #1f3727;
}

.profile-menu .admin-icon {
    font-size: 2.5rem;
    color: #2F4A39;
    margin-bottom: 10px;
}

.profile-menu .admin-name {
    font-weight: 600;
    margin-bottom: 5px;
    color: #2F4A39;
}

.profile-menu p {
    font-size: 0.9rem;
    color: #555;
    margin-bottom: 10px;
}

/* SIDEBAR */
.sidebar {
    background: #2F4A39;
    width: 230px;
    min-height: 100vh;
    padding-top: 80px;
    position: fixed;
    top: 0;
    left: 0;
}

.sidebar a {
    display: flex;
    align-items: center;
    color: white;
    padding: 12px 20px;
    margin: 6px 10px;
    border-radius: 14px;
    transition: 0.3s;
    text-decoration: none;
    font-weight: 500;
}

.sidebar a i {
    margin-right: 12px;
    width: 22px;
}

.sidebar a:hover {
    background: #3f5a49;
    transform: translateX(4px);
}

.sidebar a.active {
    background: #3f5a49;
}

/* CONTENT */
.content {
    margin-left: 230px;
    padding: 100px 30px 40px;
}

/* PAGE TITLE */
.page-title {
    text-align: center;
    margin-bottom: 30px;
}

.page-title h2 {
    color: #2F4A39;
    font-weight: 700;
    font-size: 28px;
    margin-bottom: 0;
}

/* TABLE WRAPPER */
.table-wrapper {
    background: #DFE8E2;
    border-radius: 20px;
    padding: 20px;
    box-shadow: 0 8px 25px rgba(0,0,0,0.08);
    overflow-x: auto;
    max-width: 850px;
    margin: 0 auto;
}

.data-table {
    width: 100%;
    border-collapse: collapse;
    border-radius: 16px;
    overflow: hidden;
}

.data-table thead tr {
    background: #2F4A39;
}

.data-table thead th {
    padding: 16px;
    color: white;
    font-weight: 600;
    font-size: 14px;
    text-align: left;
}

.data-table tbody tr {
    background: white;
}

.data-table tbody tr:hover {
    background: #f0f5f2;
}

.data-table tbody td {
    padding: 14px;
    font-size: 14px;
    color: #333;
    border-bottom: 1px solid #e0ece4;
}

.data-table tbody td:first-child {
    font-weight: 600;
    color: #2F4A39;
    width: 160px;
    white-space: nowrap;
}

/* FOTO ROW */
.foto-row {
    display: flex;
    gap: 15px;
    align-items: flex-start;
}

.foto-preview {
    width: 100px;
    height: 100px;
    text-align: center;
    background: #f5f8f6;
    border-radius: 12px;
    border: 1px solid #ddd;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    cursor: pointer;
    transition: 0.2s;
}

.foto-preview:hover {
    opacity: 0.8;
    transform: scale(1.02);
}

.foto-preview img {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
}

/* STOK BADGE */
.stok-tersedia {
    color: #4CAF50;
    font-weight: 700;
}

.stok-habis {
    color: #F44336;
    font-weight: 700;
}

/* RIWAYAT LIST */
.riwayat-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.riwayat-list li {
    padding: 6px 0;
    border-bottom: 1px solid #e0ece4;
    font-size: 13px;
}

.riwayat-list li:last-child {
    border-bottom: none;
}

.riwayat-nama {
    font-weight: 600;
    color: #2F4A39;
}

.riwayat-tanggal {
    color: #888;
    font-size: 11px;
    margin-left: 10px;
}

.empty-riwayat {
    color: #999;
    font-style: italic;
    font-size: 13px;
}

/* ACTION BUTTONS */
.action-buttons {
    display: flex;
    gap: 12px;
    width: 100%;
}

.btn-kembali {
    flex: 1;
    background: #2F4A39;
    color: white;
    border: none;
    padding: 12px 0;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 600;
    font-family: 'Poppins', sans-serif;
    text-align: center;
    transition: 0.3s;
    font-size: 14px;
    text-decoration: none;
    display: block;
}

.btn-kembali:hover {
    background: #1f3727;
    transform: translateY(-2px);
    color: white;
}

@media (max-width: 992px) {
    .sidebar {
        position: relative;
        width: 100%;
        min-height: auto;
        padding-top: 70px;
    }
    .content {
        margin-left: 0;
        padding-top: 20px;
    }
    .foto-row {
        flex-direction: column;
    }
    .data-table tbody td:first-child {
        white-space: normal;
    }
}

@media (max-width: 768px) {
    .action-buttons {
        flex-direction: column;
        gap: 10px;
    }
}
</style>
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar d-flex justify-content-between align-items-center">
    <img src="../../asset/logonav.png" alt="Logo PopFit">
    <div class="icons">
        <div style="position:relative;">
            <a href="../notif.php" style="color:white;">
                <i class="fa-solid fa-bell"></i>
            </a>
            <span id="notifBadge"
                  style="position:absolute;top:-5px;right:-5px;
                  background:red;color:white;
                  font-size:11px;padding:3px 6px;
                  border-radius:50%;display:none;">
            </span>
        </div>

        <div class="profile-dropdown">
            <i class="fa-solid fa-user" id="profileIcon"></i>
            <div class="profile-menu" id="profileMenu">
                <div class="admin-icon">
                    <i class="fa-solid fa-user" style="font-size:2.5rem;color:#2F4A39;"></i>
                </div>
                <div class="admin-name"><?php echo $petugasUsername; ?></div>
                <hr>
                <p>Akun Terverifikasi <i class="fa-solid fa-circle-check" style="color:#2F4A39"></i></p>
                <button onclick="window.location.href='../logout.php'">Keluar</button>
            </div>
        </div>
    </div>
</nav>

<!-- SIDEBAR -->
<div class="sidebar">
    <a href="../dashboardPetugas.php"><i class="fa-solid fa-gauge-high"></i>Beranda</a>
    <a href="daftarAlat.php" class="active"><i class="fa-solid fa-volleyball"></i>Alat Olahraga</a>
    <a href="../transaksi/transaksi.php"><i class="fa-solid fa-right-left"></i>Transaksi</a>
    <a href="../denda/denda.php"><i class="fa-solid fa-wallet"></i>Denda</a>
    <a href="../laporan/laporan.php"><i class="fa-solid fa-chart-column"></i>Laporan</a>
</div>

<!-- CONTENT -->
<div class="content">
    <div class="page-title">
        <h2>Detail Alat Olahraga</h2>
    </div>

    <div class="table-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th colspan="2">Informasi Alat Olahraga</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><i class="fa-solid fa-hashtag"></i> ID Alat Olahraga</th>
                    <td><?= htmlspecialchars($data['id_alat_olahraga']) ?></td>
                </tr>
                
                <tr>
                    <td><i class="fa-solid fa-image"></i> Foto Alat Olahraga</th>
                    <td>
                        <div class="foto-row">
                            <div class="foto-preview" id="fotoPreview">
                                <?php if (!empty($data['foto_alat_olahraga']) && file_exists("../../asset/" . $data['foto_alat_olahraga'])): ?>
                                <img src="../../asset/<?= htmlspecialchars($data['foto_alat_olahraga']) ?>" alt="Foto Alat Olahraga" id="fotoAlat">
                                <?php else: ?>
                                <div style="text-align:center; color:#999;">
                                    <i class="fa-solid fa-camera" style="font-size:30px;"></i>
                                    <p style="font-size:10px; margin:0;">Tidak ada foto</p>
                                </div>
                                <?php endif; ?>
                            </div>
                            <small style="color:#888; font-size:11px;">Klik gambar untuk memperbesar</small>
                        </div>
                      </th>
                </tr>
                
                <tr>
                    <td><i class="fa-solid fa-volleyball"></i> Nama Alat Olahraga</th>
                    <td><?= htmlspecialchars($data['nama_alat_olahraga']) ?></th>
                </tr>
                
                <tr>
                    <td><i class="fa-solid fa-tags"></i> Kategori</th>
                    <td><?= htmlspecialchars($data['kategori']) ?></td>
                </tr>
                
                <tr>
                    <td><i class="fa-solid fa-boxes"></i> Stok</th>
                    <td>
                        <?php if($data['stok'] > 0): ?>
                            <span class="stok-tersedia"><?= $data['stok'] ?> (Tersedia)</span>
                        <?php else: ?>
                            <span class="stok-habis">0 (Habis)</span>
                        <?php endif; ?>
                      </th>
                </tr>
                
                <tr>
                    <td><i class="fa-solid fa-right-left"></i> Sedang Dipinjam</th>
                    <td><?= $sedangDipinjam ?> unit saat ini</th>
                </tr>
                
                <tr>
                    <td><i class="fa-solid fa-align-left"></i> Deskripsi</th>
                    <td><?= !empty($data['deskripsi']) ? nl2br(htmlspecialchars($data['deskripsi'])) : '<em>Tidak ada deskripsi</em>' ?></th>
                </tr>
                
                <tr>
                    <td><i class="fa-solid fa-clock-rotate-left"></i> Riwayat Peminjaman</th>
                    <td>
                        <?php if(mysqli_num_rows($riwayatPinjam) > 0): ?>
                            <ul class="riwayat-list">
                                <?php while($row = mysqli_fetch_assoc($riwayatPinjam)): ?>
                                <li>
                                    <span class="riwayat-nama"><?= htmlspecialchars($row['nama_siswa']) ?></span>
                                    <span class="riwayat-tanggal"><?= date('d/m/Y', strtotime($row['waktu_pinjam'])) ?></span>
                                </li>
                                <?php endwhile; ?>
                            </ul>
                        <?php else: ?>
                            <span class="empty-riwayat">Belum ada riwayat peminjaman</span>
                        <?php endif; ?>
                       </th>
                </tr>
                
                <tr>
                    <td colspan="2">
                        <div class="action-buttons">
                            <a href="daftarAlat.php" class="btn-kembali">
                                <i class="fa-solid fa-arrow-left"></i> Kembali ke Daftar Alat Olahraga
                            </a>
                        </div>
                       </th>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- MODAL ZOOM GAMBAR -->
<div class="modal fade" id="imageModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content" style="background: transparent; border: none;">
            <div class="modal-body" style="text-align: center;">
                <img id="modalImage" src="" alt="Zoom" style="max-width: 100%; max-height: 80vh; border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.3);">
            </div>
            <div style="text-align: center; margin-top: 15px;">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal" style="border-radius: 30px; padding: 8px 25px; font-weight: 600;">
                    <i class="fa-solid fa-times"></i> Tutup
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Profile dropdown
const profileIcon = document.getElementById('profileIcon');
const profileMenu = document.getElementById('profileMenu');

profileIcon.addEventListener('click', () => {
    profileMenu.style.display = profileMenu.style.display === 'block' ? 'none' : 'block';
});

window.addEventListener('click', function(e){
    if(!profileIcon.contains(e.target) && !profileMenu.contains(e.target)){
        profileMenu.style.display = 'none';
    }
});

// ZOOM GAMBAR SAAT DIKLIK
const fotoPreview = document.getElementById('fotoPreview');
const modalImage = document.getElementById('modalImage');

if (fotoPreview) {
    fotoPreview.addEventListener('click', function() {
        const img = this.querySelector('img');
        if (img && img.src) {
            modalImage.src = img.src;
            const modal = new bootstrap.Modal(document.getElementById('imageModal'));
            modal.show();
        }
    });
}

// NOTIFIKASI
const badge = document.getElementById("notifBadge");

function loadNotif(){
    fetch("../notif.php")
    .then(res => res.json())
    .then(data => {
        if(data && data.length > 0){
            badge.style.display = "block";
            badge.textContent = data.length;
        } else {
            badge.style.display = "none";
        }
    })
    .catch(err => console.log('Notif error:', err));
}

loadNotif();
setInterval(loadNotif, 10000);
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>