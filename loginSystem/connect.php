<?php   
$host = "127.0.0.1";  
$username = "root";  
$password = "";  
$database = "peminjaman_alat";
$connect = mysqli_connect($host, $username, $password, $database);  

if (!$connect) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

/* REGISTER (MASUK KE USERS) */  
function signUp($data) {  
    global $connect;  

    $nis = htmlspecialchars($data["nis"]);  
    $nama = mysqli_real_escape_string($connect, $data["nama"]);  
    $username = mysqli_real_escape_string($connect, strtolower($data["username"]));  
    $password = $data["password"];  
    $confirmPw = $data["confirmPw"];  
    $kelas = htmlspecialchars($data["kelas"]);  
    $noTlp = htmlspecialchars($data["no_telp"]);  
    $tglDaftar = date("Y-m-d H:i:s");  

    /* CEK NIS */
    $cekNis = mysqli_query($connect, "SELECT nis FROM users WHERE nis='$nis'");
    if(mysqli_fetch_assoc($cekNis)) {  
        echo "<script>alert('NIS sudah terdaftar!');</script>";  
        return 0;  
    }  

    /* CEK USERNAME */
    $cekUser = mysqli_query($connect, "SELECT username FROM users WHERE username='$username'");
    if(mysqli_fetch_assoc($cekUser)){  
        echo "<script>alert('Username sudah digunakan!');</script>";  
        return 0;  
    }  

    /* CEK PASSWORD */
    if($password !== $confirmPw) {  
        echo "<script>alert('Password tidak sama!');</script>";  
        return 0;  
    }  

    /* HASH PASSWORD */
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);  

    /* INSERT KE USERS */
    $query = "INSERT INTO users 
        (nama, username, password, role, nis, kelas, no_telp, tgl_daftar)
        VALUES 
        ('$nama', '$username', '$passwordHash', 'siswa', '$nis', '$kelas', '$noTlp', '$tglDaftar')";

    mysqli_query($connect, $query);  

    return mysqli_affected_rows($connect);  
}
?>