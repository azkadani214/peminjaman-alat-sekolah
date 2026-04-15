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

/* HITUNG DENDA & CEK KETERLAMBATAN */
function cekDetailKeterlambatan($batasKembali, $waktuKembali = null) {
    if (!$waktuKembali) $waktuKembali = date("Y-m-d H:i:s");
    
    $tsBatas = strtotime($batasKembali);
    $tsKembali = strtotime($waktuKembali);
    $selisih = $tsKembali - $tsBatas;
    
    if ($selisih <= 0) {
        return [
            'is_telat' => false,
            'total_detik' => 0,
            'total_menit' => 0,
            'jam' => 0,
            'menit' => 0,
            'denda' => 0,
            'teks' => 'Tepat Waktu'
        ];
    }
    
    $totalMenit = ceil($selisih / 60);
    $jam = floor($totalMenit / 60);
    $menit = $totalMenit % 60;
    
    // REQUIREMENT: Rp 5.000 per 30 menit (Rp10.000/jam)
    $jumlahPeriode = ceil($totalMenit / 30);
    $denda = $jumlahPeriode * 5000;
    
    $teks = "";
    if ($jam > 0) $teks .= $jam . " Jam ";
    if ($menit > 0) $teks .= $menit . " Menit";
    
    return [
        'is_telat' => true,
        'total_detik' => $selisih,
        'total_menit' => $totalMenit,
        'jam' => $jam,
        'menit' => $menit,
        'denda' => $denda,
        'teks' => $teks ?: "Beberapa detik"
    ];
}

function uploadPembayaran($idTransaksi, $file, $metode) {
    global $connect;
    if($file['error'] !== 0) return ["error" => "File bukti wajib diunggah!"];
    
    $namaFile = $file['name'];
    $tmpName = $file['tmp_name'];
    $ext = pathinfo($namaFile, PATHINFO_EXTENSION);
    $newName = "bukti_" . $idTransaksi . "_" . time() . "." . $ext;
    
    if(move_uploaded_file($tmpName, "../../uploads/" . $newName)){
        mysqli_query($connect, "UPDATE transaksi SET bukti_pembayaran = '$newName', metode_pembayaran = '$metode', pembayaran = 'pending' WHERE id_transaksi = $idTransaksi");
        return ["success" => true];
    }
    return ["error" => "Gagal mengunggah file."];
}

function verifikasiPembayaran($idTransaksi, $status, $idPetugas) {
    global $connect;
    $trx = mysqli_fetch_assoc(mysqli_query($connect, "SELECT id_user FROM transaksi WHERE id_transaksi = $idTransaksi"));
    if($status == 'terima'){
        mysqli_query($connect, "UPDATE transaksi SET pembayaran = 'lunas' WHERE id_transaksi = $idTransaksi");
        tambahLog($idPetugas, "Menghapus denda (Approve) ID: $idTransaksi");
        if($trx) tambahNotif($trx['id_user'], "Pembayaran denda Anda telah diverifikasi.", "transaksi/riwayat.php");
    } else {
        mysqli_query($connect, "UPDATE transaksi SET pembayaran = 'ditolak' WHERE id_transaksi = $idTransaksi");
        tambahLog($idPetugas, "Menolak bukti denda ID: $idTransaksi");
        if($trx) tambahNotif($trx['id_user'], "Bukti denda Anda ditolak. Silakan unggah ulang.", "denda/detailDenda.php?id=$idTransaksi");
    }
    return true;
}

function hitungDenda($batasKembali, $waktuKembali = null) {
    $detail = cekDetailKeterlambatan($batasKembali, $waktuKembali);
    return $detail['denda'];
}

/* VALIDASI ATURAN SEKOLAH */
function isOperationalHour($time = null) {
    if (!$time) $time = date("H:i");
    else $time = date("H:i", strtotime($time));
    
    $start = "06:45";
    $end = "17:00";
    return ($time >= $start && $time <= $end);
}

function canUserBorrow($idUser) {
    global $connect;
    // Cek Pinjaman Aktif
    $cekAktif = mysqli_query($connect, "SELECT id_transaksi FROM transaksi WHERE id_user = $idUser AND status = 'dipinjam'");
    if (mysqli_num_rows($cekAktif) > 0) return ["error" => "Anda masih memiliki pinjaman aktif yang belum kembali."];

    // Cek Denda Belum Bayar
    $cekDenda = mysqli_query($connect, "SELECT id_transaksi FROM transaksi WHERE id_user = $idUser AND pembayaran != 'lunas' AND denda > 0");
    if (mysqli_num_rows($cekDenda) > 0) return ["error" => "Anda memiliki denda yang belum dibayar. Harap lunasi terlebih dahulu."];

    return ["success" => true];
}

function validateDuration($waktuPinjam, $batasKembali) {
    $start = strtotime($waktuPinjam);
    $end = strtotime($batasKembali);
    $durasi = $end - $start;
    $maxDurasi = 5 * 60 * 60; // 5 Jam dalam detik

    if ($durasi > $maxDurasi) return ["error" => "Maksimal durasi peminjaman adalah 5 jam."];
    if ($durasi <= 0) return ["error" => "Waktu kembali harus lebih besar dari waktu pinjam."];
    
    return ["success" => true];
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

function checkoutKeranjang($idUser, $waktuPinjam, $batasKembali, $buktiKartu = null) {
    global $connect;
    $waktuPinjam = date("Y-m-d H:i:s", strtotime($waktuPinjam));
    $batasKembali = date("Y-m-d H:i:s", strtotime($batasKembali));
    $keranjang = queryReadData("SELECT k.*, a.stok FROM keranjang k JOIN alat_olahraga a ON k.id_alat_olahraga = a.id_alat_olahraga WHERE k.id_user = $idUser");
    if (empty($keranjang)) return ["error" => "Keranjang kosong!"];
    foreach ($keranjang as $item) { if ($item['jumlah'] > $item['stok']) return ["error" => "Stok alat tidak mencukupi."]; }
    
    $buktiVal = $buktiKartu ? "'$buktiKartu'" : "NULL";
    mysqli_query($connect, "INSERT INTO transaksi (id_user, bukti_kartu, status, waktu_pinjam, batas_kembali) 
                            VALUES ($idUser, $buktiVal, 'menunggu', '$waktuPinjam', '$batasKembali')");
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
