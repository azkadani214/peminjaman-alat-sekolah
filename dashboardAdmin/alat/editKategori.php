<?php
require '../../config/config.php';
session_start();

if (!isset($_SESSION["login"]) || $_SESSION["role"] != "admin") {
    header("Location: ../../login.php");
    exit;
}

$adminName = htmlspecialchars($_SESSION['nama'] ?? 'Admin');
$adminUsername = htmlspecialchars($_SESSION['username'] ?? 'Admin');

// CEK PARAMETER
if (!isset($_GET['kategori'])) {
    header("Location: kategori.php");
    exit;
}

$kategori_lama = urldecode($_GET['kategori']);
$kategori_lama = mysqli_real_escape_string($connect, $kategori_lama);

// AMBIL DATA KATEGORI
$result = mysqli_query($connect, "SELECT * FROM kategori_alat_olahraga WHERE kategori = '$kategori_lama'");

if (mysqli_num_rows($result) == 0) {
    echo "<script>alert('Kategori tidak ditemukan'); window.location.href='kategori.php';</script>";
    exit;
}

$data = mysqli_fetch_assoc($result);

// PROSES UPDATE
if (isset($_POST['simpan'])) {
    $kategori_baru = trim(mysqli_real_escape_string($connect, $_POST['kategori']));
    
    if (empty($kategori_baru)) {
        echo "<script>alert('Nama kategori tidak boleh kosong!'); window.history.back();</script>";
        exit;
    }
    
    // CEK APAKAH KATEGORI BARU SUDAH ADA (kecuali kategori itu sendiri)
    $cek = mysqli_query($connect, "SELECT * FROM kategori_alat_olahraga WHERE kategori = '$kategori_baru' AND kategori != '$kategori_lama'");
    if (mysqli_num_rows($cek) > 0) {
        echo "<script>alert('Kategori sudah ada! Silakan gunakan nama lain.'); window.history.back();</script>";
        exit;
    }
    
    // HANYA UPDATE JIKA NAMANYA BERBEDA
    if ($kategori_lama !== $kategori_baru) {
        // UPDATE KATEGORI DI TABEL kategori_alat_olahraga
        $update1 = mysqli_query($connect, "
            UPDATE kategori_alat_olahraga 
            SET kategori = '$kategori_baru' 
            WHERE kategori = '$kategori_lama'
        ");

        // UPDATE KATEGORI DI TABEL alat_olahraga
        $update2 = mysqli_query($connect, "
            UPDATE alat_olahraga 
            SET kategori = '$kategori_baru' 
            WHERE kategori = '$kategori_lama'
        ");
        
        if ($update1 && $update2) {
            echo "<script>
                alert('Kategori berhasil diperbarui');
                window.location.href='kategori.php';
            </script>";
            exit;
        } else {
            echo "<script>alert('Gagal memperbarui kategori'); window.history.back();</script>";
        }
    } else {
        // NAMA SAMA, TIDAK PERLU UPDATE
        echo "<script>
            alert('Tidak ada perubahan pada kategori');
            window.location.href='kategori.php';
        </script>";
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Edit Kategori - PopFit</title>

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
    max-width: 800px;
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
    width: 180px;
}

.data-table input {
    width: 100%;
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 8px;
    font-family: 'Poppins', sans-serif;
    font-size: 14px;
    transition: all 0.3s;
}

.data-table input:focus {
    outline: none;
    border-color: #2F4A39;
    box-shadow: 0 0 0 3px rgba(47, 74, 57, 0.1);
}

/* ACTION BUTTONS */
.action-buttons {
    display: flex;
    gap: 12px;
    width: 100%;
}

.btn-simpan {
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
}

.btn-simpan:hover {
    background: #1f3727;
    transform: translateY(-2px);
}

.btn-batal {
    flex: 1;
    background: #5a8f6e;
    color: white;
    border: none;
    padding: 12px 0;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 600;
    font-family: 'Poppins', sans-serif;
    text-decoration: none;
    text-align: center;
    transition: 0.3s;
    font-size: 14px;
    display: block;
}

.btn-batal:hover {
    background: #3d6b4e;
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
                <div class="admin-name"><?php echo $adminUsername; ?></div>
                <hr>
                <p>Akun Terverifikasi <i class="fa-solid fa-circle-check" style="color:#2F4A39"></i></p>
                <button onclick="window.location.href='../logout.php'">Keluar</button>
            </div>
        </div>
    </div>
</nav>

<!-- SIDEBAR -->
<div class="sidebar">
    <a href="../dashboardAdmin.php"><i class="fa-solid fa-gauge-high"></i>Beranda</a>
    <a href="../petugas/petugas.php"><i class="fa-solid fa-user-tie"></i>Petugas</a>
    <a href="../siswa/siswa.php"><i class="fa-solid fa-users"></i>Siswa</a>
    <a href="../alat/daftarAlat.php" class="active"><i class="fa-solid fa-volleyball"></i>Alat Olahraga</a>
    <a href="../transaksi/transaksi.php"><i class="fa-solid fa-right-left"></i>Transaksi</a>
    <a href="../denda/denda.php"><i class="fa-solid fa-wallet"></i>Denda</a>
    <a href="../log/log.php"><i class="fa-solid fa-clock-rotate-left"></i>Log Aktivitas</a>
    <a href="../laporan/laporan.php"><i class="fa-solid fa-chart-column"></i>Laporan</a>
</div>

<!-- CONTENT -->
<div class="content">
    <div class="page-title">
        <h2>Edit Kategori</h2>
    </div>

    <div class="table-wrapper">
        <form method="POST">
            <table class="data-table">
                <thead>
                    <tr>
                        <th colspan="2">Form Edit Kategori</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><i class="fa-solid fa-tags"></i> Nama Kategori</th>
                        <td>
                            <input type="text" name="kategori" value="<?= htmlspecialchars($data['kategori']); ?>" required>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <div class="action-buttons">
                                <button type="submit" name="simpan" class="btn-simpan">
                                    Simpan
                                </button>
                                <a href="kategori.php" class="btn-batal">
                                    Batal
                                </a>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </form>
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

// NOTIFIKASI
const badge = document.getElementById("notifBadge");

function loadNotif(){
    fetch("../notif.php")
    .then(res => res.json())
    .then(data => {
        if(data.length > 0){
            badge.style.display = "block";
            badge.textContent = data.length;
        } else {
            badge.style.display = "none";
        }
    });
}

loadNotif();
setInterval(loadNotif, 5000);
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>