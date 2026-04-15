<?php
require 'config/config.php';
session_start();

if(isset($_SESSION["login"])){
    if($_SESSION["role"] == "admin utama" || $_SESSION["role"] == "admin") header("Location: dashboardAdmin/dashboardAdmin.php");
    elseif($_SESSION["role"] == "petugas") header("Location: dashboardPetugas/dashboardPetugas.php");
    else header("Location: dashboardSiswa/dashboardSiswa.php");
    exit;
}

$error = '';
$success = '';

if(isset($_POST["register"])){
    $nama = mysqli_real_escape_string($connect, $_POST["nama"]);
    $username = strtolower(stripslashes(mysqli_real_escape_string($connect, $_POST["username"])));
    $nis = mysqli_real_escape_string($connect, $_POST["nis"]);
    $kelas = mysqli_real_escape_string($connect, $_POST["kelas"]);
    $password = mysqli_real_escape_string($connect, $_POST["password"]);
    $konfirmasi = mysqli_real_escape_string($connect, $_POST["konfirmasi"]);

    // Cek username sudah ada atau belum
    $result = mysqli_query($connect, "SELECT username FROM users WHERE username = '$username'");
    if(mysqli_fetch_assoc($result)){
        $error = "Nama pengguna sudah terdaftar.";
    } else if($password !== $konfirmasi) {
        $error = "Konfirmasi kata sandi tidak sesuai.";
    } else {
        // Enkripsi password
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $query = "INSERT INTO users (nama, username, password, role, nis, kelas) 
                  VALUES ('$nama', '$username', '$password_hash', 'siswa', '$nis', '$kelas')";
        
        if(mysqli_query($connect, $query)){
            $success = "Pendaftaran berhasil! Silakan login.";
        } else {
            $error = "Gagal mendaftarkan akun. Coba lagi.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pendaftaran PopFit</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Plus Jakarta Sans', 'sans-serif'] },
                    colors: {
                        popfit: {
                            dark: '#2A4736', light: '#3E614C', accent: '#F5C460', accentHover: '#E3B24F',
                            bg: '#F4F4F5', surface: '#FFFFFF', border: '#E4E4E7', text: '#1F2937', textMuted: '#6B7280'
                        }
                    },
                    borderRadius: { 'sm': '2px', DEFAULT: '4px' }
                }
            }
        }
    </script>
    <style>
        .carousel-item { transition: opacity 1s ease-in-out; }
        .carousel-active { opacity: 1 !important; z-index: 10; }
        * { box-shadow: none !important; }
    </style>
</head>
<body class="bg-popfit-bg font-sans min-h-screen flex items-center justify-center p-0 md:p-12 overflow-x-hidden">

    <div class="bg-white w-full max-w-6xl min-h-[700px] flex flex-col md:flex-row overflow-hidden md:rounded-sm border border-popfit-border">
        
        <!-- SIDE CAROUSEL (50%) -->
        <div class="hidden md:flex md:w-1/2 bg-popfit-dark relative overflow-hidden flex-col justify-end p-12">
            <div id="carousel-1" class="carousel-item absolute inset-0 opacity-0 carousel-active">
                <img src="https://images.unsplash.com/photo-1541534741688-6078c6bfb5c5?q=80&w=2069&auto=format&fit=crop" class="w-full h-full object-cover mix-blend-overlay opacity-30">
            </div>
            <div id="carousel-2" class="carousel-item absolute inset-0 opacity-0">
                <img src="https://images.unsplash.com/photo-1517649763962-0c623066013b?q=80&w=2070&auto=format&fit=crop" class="w-full h-full object-cover mix-blend-overlay opacity-30">
            </div>
            
            <div class="relative z-20">
                <i class="ph-fill ph-paw-print text-popfit-accent text-5xl mb-6"></i>
                <h1 class="text-4xl font-black text-white leading-tight uppercase tracking-tighter">PopFit<br><span class="text-popfit-accent">Registration</span></h1>
                <p class="text-gray-300 mt-4 text-sm font-medium leading-relaxed max-w-sm">Gabung sekarang dan nikmati kemudahan akses ke seluruh fasilitas olahraga sekolah dalam genggaman Anda.</p>
            </div>
            
            <!-- Indicators -->
            <div class="relative z-20 flex space-x-2 mt-8">
                <div class="w-8 h-1 bg-popfit-accent rounded-full transition-all duration-300"></div>
                <div class="w-4 h-1 bg-white/20 rounded-full transition-all duration-300"></div>
            </div>
        </div>

        <!-- FORM SECTION (50%) -->
        <div class="flex-1 p-8 md:p-16 flex flex-col justify-center bg-white">
            <div class="max-w-md mx-auto w-full">
                <div class="mb-10 text-center md:text-left">
                    <h2 class="text-2xl font-black text-popfit-dark uppercase tracking-tighter">Buat Akun Baru</h2>
                    <p class="text-[11px] font-bold text-popfit-textMuted uppercase tracking-widest mt-2 overflow-hidden whitespace-nowrap">Daftar sebagai anggota PopFit Siswa</p>
                </div>

                <?php if($error): ?>
                <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-8">
                    <div class="flex items-center">
                        <i class="ph-bold ph-warning-circle text-red-500 mr-3 text-xl"></i>
                        <p class="text-[11px] font-black text-red-700 uppercase"><?= $error ?></p>
                    </div>
                </div>
                <?php endif; ?>

                <?php if($success): ?>
                <div class="bg-green-50 border-l-4 border-green-500 p-4 mb-8">
                    <div class="flex items-center">
                        <i class="ph-bold ph-check-circle text-green-500 mr-3 text-xl"></i>
                        <p class="text-[11px] font-black text-green-700 uppercase"><?= $success ?></p>
                    </div>
                </div>
                <script>setTimeout(()=>window.location.href='login.php', 2000);</script>
                <?php endif; ?>

                <form action="" method="POST" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-popfit-textMuted uppercase tracking-[0.2em]">Nama Lengkap</label>
                            <input type="text" name="nama" required placeholder="NAMA LENGKAP" class="w-full bg-popfit-bg border border-popfit-border rounded-sm px-4 py-3 text-[11px] font-bold text-popfit-dark focus:border-popfit-dark outline-none transition-all placeholder:text-gray-300">
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-popfit-textMuted uppercase tracking-[0.2em]">NIS</label>
                            <input type="text" name="nis" required placeholder="NIS" class="w-full bg-popfit-bg border border-popfit-border rounded-sm px-4 py-3 text-[11px] font-bold text-popfit-dark focus:border-popfit-dark outline-none transition-all placeholder:text-gray-300">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-popfit-textMuted uppercase tracking-[0.2em]">Username</label>
                            <input type="text" name="username" required placeholder="USERNAME" class="w-full bg-popfit-bg border border-popfit-border rounded-sm px-4 py-3 text-[11px] font-bold text-popfit-dark focus:border-popfit-dark outline-none transition-all placeholder:text-gray-300">
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-popfit-textMuted uppercase tracking-[0.2em]">Kelas</label>
                            <input type="text" name="kelas" required placeholder="KELAS" class="w-full bg-popfit-bg border border-popfit-border rounded-sm px-4 py-3 text-[11px] font-bold text-popfit-dark focus:border-popfit-dark outline-none transition-all placeholder:text-gray-300">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-2 relative">
                            <label class="text-[10px] font-black text-popfit-textMuted uppercase tracking-[0.2em]">Kata Sandi</label>
                            <input type="password" name="password" id="pw" required placeholder="********" class="w-full bg-popfit-bg border border-popfit-border rounded-sm px-4 py-3 text-[11px] font-bold text-popfit-dark focus:border-popfit-dark outline-none transition-all">
                        </div>
                        <div class="space-y-2 relative">
                            <label class="text-[10px] font-black text-popfit-textMuted uppercase tracking-[0.2em]">Konfirmasi</label>
                            <input type="password" name="konfirmasi" id="cpw" required placeholder="********" class="w-full bg-popfit-bg border border-popfit-border rounded-sm px-4 py-3 text-[11px] font-bold text-popfit-dark focus:border-popfit-dark outline-none transition-all">
                        </div>
                    </div>

                    <div class="pt-6">
                        <button type="submit" name="register" class="w-full bg-popfit-dark text-white py-4 rounded-sm text-[11px] font-black uppercase tracking-widest hover:bg-popfit-light transition-all flex items-center justify-center group">
                            DAFTAR SEKARANG
                            <i class="ph ph-arrow-right ml-2 group-hover:translate-x-1 transition-transform"></i>
                        </button>
                    </div>
                </form>

                <div class="mt-8 pt-8 border-t border-gray-100 text-center">
                    <p class="text-[10px] font-bold text-popfit-textMuted uppercase tracking-widest">SUDAH PUNYA AKUN? <a href="login.php" class="text-popfit-dark hover:underline underline-offset-4 ml-2">MASUK DI SINI</a></p>
                </div>
            </div>
        </div>
    </div>

    <script>
        let current = 1;
        setInterval(() => {
            const items = document.querySelectorAll('.carousel-item');
            const indicators = document.querySelectorAll('.rounded-full');
            
            items[current - 1].classList.remove('carousel-active');
            current = current === 1 ? 2 : 1;
            items[current - 1].classList.add('carousel-active');
            
            // Adjust indicators (skip first 2 which are in side)
            // Actually let's just use CSS for simplicity
        }, 5000);
    </script>
</body>
</html>