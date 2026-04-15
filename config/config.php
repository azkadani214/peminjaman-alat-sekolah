<?php   
$host = "127.0.0.1";  
$username = "root";  
$password = "";  
$database = "peminjaman-alat-sekolah";
$connect = mysqli_connect($host, $username, $password, $database); 

if (!$connect) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

date_default_timezone_set('Asia/Jakarta');

/* READ DATA */
function queryReadData($query) {
    global $connect;
    $result = mysqli_query($connect, $query);
    $items = [];
    if($result){
        while($item = mysqli_fetch_assoc($result)) {
            $items[] = $item;
        }
    }
    return $items;
}

/* SIGN UP / REGISTER */
function signUp($data) {
    global $connect;
    $nis = trim($data["nis"] ?? "");
    $username = strtolower(trim($data["username"] ?? ""));
    $nama = trim($data["nama_siswa"] ?? "");
    $password = $data["password"] ?? "";
    $confirmPw = $data["confirmPw"] ?? "";
    $kelas = trim($data["kelas"] ?? "");
    $no_telp = trim($data["no_telp"] ?? "");
    $tgl_daftar = isset($data["tgl_daftar"]) ? $data["tgl_daftar"] . " " . date("H:i:s") : date("Y-m-d H:i:s");

    if(empty($username) || empty($password)) return ["error" => "Username dan Password wajib diisi!"];
    if(strlen($password) < 6) return ["error" => "Password minimal 6 karakter!"];
    if($password !== $confirmPw) return ["error" => "Konfirmasi password tidak cocok!"];

    $cekUser = mysqli_query($connect, "SELECT id_user FROM users WHERE username = '$username'");
    if(mysqli_num_rows($cekUser) > 0) return ["error" => "Username sudah digunakan!"];

    if(!empty($nis)){
        $cekNis = mysqli_query($connect, "SELECT id_user FROM users WHERE nis = '$nis'");
        if(mysqli_num_rows($cekNis) > 0) return ["error" => "NIS sudah terdaftar!"];
    }

    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = mysqli_prepare($connect, "INSERT INTO users (nama, username, password, role, nis, kelas, no_telp, tgl_daftar) VALUES (?, ?, ?, 'siswa', ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "sssssss", $nama, $username, $passwordHash, $nis, $kelas, $no_telp, $tgl_daftar);
    
    if(mysqli_stmt_execute($stmt)){
        return ["success" => true, "id" => mysqli_insert_id($connect)];
    } else {
        return ["error" => "Gagal mendaftar: " . mysqli_error($connect)];
    }
}

/* ALAT OLAHRAGA (CRUD) */
function tambahAlat($data, $file, $idUser) {
    global $connect;
    $id_alat = mysqli_real_escape_string($connect, $data['id_alat']);
    $nama = mysqli_real_escape_string($connect, $data['nama']);
    $kategori = mysqli_real_escape_string($connect, $data['kategori']);
    $deskripsi = mysqli_real_escape_string($connect, $data['deskripsi']);
    $stok = (int)$data['stok'];

    $foto = uploadFoto($file);
    if(!$foto) return ["error" => "Gagal upload foto atau format tidak sesuai!"];

    $query = "INSERT INTO alat_olahraga (id_alat_olahraga, foto_alat_olahraga, nama_alat_olahraga, kategori, deskripsi, stok) 
              VALUES ('$id_alat', '$foto', '$nama', '$kategori', '$deskripsi', $stok)";
    
    if(mysqli_query($connect, $query)){
        tambahLog($idUser, "Menambahkan alat: $nama (ID: $id_alat)");
        return ["success" => true];
    }
    return ["error" => mysqli_error($connect)];
}

function uploadFoto($file) {
    if($file['error'] === 4) return false;
    $namaFile = $file['name'];
    $tmpName = $file['tmp_name'];
    $ekstensi = strtolower(pathinfo($namaFile, PATHINFO_EXTENSION));
    $ekstensiValid = ['jpg','jpeg','png','webp'];
    if(!in_array($ekstensi, $ekstensiValid)) return false;
    $namaBaru = uniqid() . "." . $ekstensi;
    move_uploaded_file($tmpName, "../../asset/".$namaBaru);
    return $namaBaru;
}

/* HITUNG DENDA OTOMATIS */
function hitungDenda($batasKembali, $waktuKembali = null) {
    if (!$waktuKembali) $waktuKembali = date("Y-m-d H:i:s");
    $selisih = strtotime($waktuKembali) - strtotime($batasKembali);
    if ($selisih > 0) {
        $hari = ceil($selisih / (60 * 60 * 24));
        return $hari * 2000; // Biaya denda per hari
    }
    return 0;
}

/* TRANSAKSI */
function setujuiTransaksi($idTransaksi, $idPetugas) {
    global $connect;
    mysqli_query($connect, "UPDATE transaksi SET status='disetujui' WHERE id_transaksi = $idTransaksi");
    tambahLog($idPetugas, "Menyetujui transaksi ID: $idTransaksi");
    $trx = mysqli_fetch_assoc(mysqli_query($connect, "SELECT id_user FROM transaksi WHERE id_transaksi = $idTransaksi"));
    if($trx) tambahNotif($trx['id_user'], "Peminjaman Anda disetujui.", "transaksi/riwayat.php");
}

function tolakTransaksi($idTransaksi, $idPetugas) {
    global $connect;
    $detail = mysqli_fetch_assoc(mysqli_query($connect, "SELECT id_alat_olahraga, jumlah FROM detail_transaksi WHERE id_transaksi = $idTransaksi"));
    if($detail) mysqli_query($connect, "UPDATE alat_olahraga SET stok = stok + {$detail['jumlah']} WHERE id_alat_olahraga = '{$detail['id_alat_olahraga']}'");
    mysqli_query($connect, "UPDATE transaksi SET status='ditolak' WHERE id_transaksi = $idTransaksi");
    tambahLog($idPetugas, "Menolak transaksi ID: $idTransaksi");
    $trx = mysqli_fetch_assoc(mysqli_query($connect, "SELECT id_user FROM transaksi WHERE id_transaksi = $idTransaksi"));
    if($trx) tambahNotif($trx['id_user'], "Peminjaman Anda ditolak.", "transaksi/riwayat.php");
}

function checkoutKeranjang($idUser, $waktuPinjam, $batasKembali) {
    global $connect;
    $waktuPinjam = date("Y-m-d H:i:s", strtotime($waktuPinjam));
    $batasKembali = date("Y-m-d H:i:s", strtotime($batasKembali));
    $keranjang = queryReadData("SELECT k.*, a.stok FROM keranjang k JOIN alat_olahraga a ON k.id_alat_olahraga = a.id_alat_olahraga WHERE k.id_user = $idUser");
    if (empty($keranjang)) return ["error" => "Keranjang kosong!"];
    foreach ($keranjang as $item) { if ($item['jumlah'] > $item['stok']) return ["error" => "Stok alat tidak mencukupi."]; }
    mysqli_query($connect, "INSERT INTO transaksi (id_user, status, waktu_pinjam, batas_kembali) VALUES ($idUser, 'menunggu', '$waktuPinjam', '$batasKembali')");
    $idTransaksi = mysqli_insert_id($connect);
    foreach ($keranjang as $item) {
        $idA = $item['id_alat_olahraga']; $jml = $item['jumlah'];
        mysqli_query($connect, "INSERT INTO detail_transaksi (id_transaksi, id_alat_olahraga, jumlah) VALUES ($idTransaksi, '$idA', $jml)");
        mysqli_query($connect, "UPDATE alat_olahraga SET stok = stok - $jml WHERE id_alat_olahraga = '$idA'");
    }
    mysqli_query($connect, "DELETE FROM keranjang WHERE id_user = $idUser");
    tambahLog($idUser, "Checkout keranjang ID: $idTransaksi");
    $staffs = mysqli_query($connect, "SELECT id_user FROM users WHERE role IN ('admin utama', 'petugas')");
    while($s = mysqli_fetch_assoc($staffs)) tambahNotif($s['id_user'], "Ada pengajuan baru.", "transaksi/transaksi.php");
    return ["success" => true];
}

/* LOG & NOTIF */
function tambahLog($idUser, $aktivitas) {
    global $connect;
    $wkt = date("Y-m-d H:i:s");
    $akt = mysqli_real_escape_string($connect, $aktivitas);
    mysqli_query($connect, "INSERT INTO log_aktivitas (id_user, aktivitas, waktu_aktivitas) VALUES ($idUser, '$akt', '$wkt')");
}

function tambahNotif($idUser, $pesan, $link = null) {
    global $connect;
    $wkt = date("Y-m-d H:i:s");
    $psn = mysqli_real_escape_string($connect, $pesan);
    $lnk = mysqli_real_escape_string($connect, $link);
    mysqli_query($connect, "INSERT INTO notifikasi (id_user, pesan, link, waktu_notif) VALUES ($idUser, '$psn', '$lnk', '$wkt')");
}

function bayarDenda($idTransaksi, $idPetugas) {
    global $connect;
    mysqli_query($connect, "UPDATE transaksi SET pembayaran = 'lunas' WHERE id_transaksi = $idTransaksi");
    tambahLog($idPetugas, "Denda Transaksi #$idTransaksi Lunas");
}
?>
