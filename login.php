<?php
require 'config/config.php';
session_start();

if(isset($_SESSION["login"])) {
    if($_SESSION["role"] == "admin" || $_SESSION["role"] == "admin utama") {
        header("Location: dashboardAdmin/dashboardAdmin.php");
    } elseif($_SESSION["role"] == "petugas") {
        header("Location: dashboardPetugas/dashboardPetugas.php");
    } else {
        header("Location: dashboardSiswa/dashboardSiswa.php");
    }
    exit;
}

if(isset($_POST["login"])) {
    $username = strtolower(trim($_POST["username"]));
    $password = $_POST["password"];

    $stmt = mysqli_prepare($connect, "SELECT id_user, nama, username, password, role FROM users WHERE LOWER(username) = LOWER(?) LIMIT 1");
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if(mysqli_num_rows($result) === 1){
        $user = mysqli_fetch_assoc($result);
        if(password_verify($password, $user["password"])){
            $selectedRole = $_POST["role_choice"] ?? "siswa";
            $actualRole = $user["role"];
            
            // Map selected role to database roles
            $isValidRole = false;
            if($selectedRole == "admin" && ($actualRole == "admin" || $actualRole == "admin utama")) $isValidRole = true;
            if($selectedRole == "staff" && $actualRole == "petugas") $isValidRole = true;
            if($selectedRole == "siswa" && $actualRole == "siswa") $isValidRole = true;

            if(!$isValidRole){
                $error = "Akun Anda tidak terdaftar sebagai " . strtoupper($selectedRole) . ". Silakan pilih peran yang benar.";
            } else {
                session_regenerate_id(true);
                $_SESSION["login"] = true;
                $_SESSION["id_user"] = $user["id_user"];
                $_SESSION["nama"] = $user["nama"];
                $_SESSION["username"] = $user["username"];
                $_SESSION["role"] = $actualRole;

                if($actualRole == "admin utama" || $actualRole == "admin") {
                    tambahLog($user["id_user"], "Berhasil login sebagai Administrator");
                    header("Location: dashboardAdmin/dashboardAdmin.php");
                } elseif($actualRole == "petugas") {
                    tambahLog($user["id_user"], "Berhasil login sebagai Petugas");
                    header("Location: dashboardPetugas/dashboardPetugas.php");
                } else {
                    tambahLog($user["id_user"], "Berhasil login sebagai Siswa");
                    header("Location: dashboardSiswa/dashboardSiswa.php");
                }
                exit;
            }
        } else { $error = "Kata sandi yang Anda masukkan salah."; }
    } else { $error = "Nama pengguna tidak ditemukan."; }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk - PopFit</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Plus Jakarta Sans', 'sans-serif'],
                    },
                    colors: {
                        popfit: {
                            dark: '#2A4736',
                            light: '#3E614C',
                            accent: '#F5C460',
                            accentHover: '#E3B24F',
                            bg: '#F4F4F5',
                            border: '#E4E4E7'
                        }
                    }
                }
            }
        }
    </script>
    <style>
        .carousel-item { display: none; transition: opacity 1s ease-in-out; }
        .carousel-item.active { display: block; opacity: 1; }
        @keyframes fade { from { opacity: 0.4 } to { opacity: 1 } }
        .animate-fade { animation: fade 1.5s; }
        input:focus { box-shadow: none !important; }
    </style>
</head>
<body class="bg-popfit-bg font-sans min-h-screen flex flex-col lg:flex-row overflow-x-hidden">

    <!-- LEFT: FORM (50% on Desktop, 100% on Mobile) -->
    <div class="w-full lg:w-1/2 flex items-center justify-center p-6 sm:p-12 lg:p-20 bg-white z-10 min-h-screen">
        <div class="w-full max-w-md">
            <!-- Branding -->
            <div class="mb-12 flex items-center space-x-3">
                <div class="w-10 h-10 bg-popfit-dark rounded-sm flex items-center justify-center text-popfit-accent">
                    <i class="ph-fill ph-paw-print text-2xl"></i>
                </div>
                <span class="text-2xl font-black text-popfit-dark tracking-tighter uppercase">PopFit</span>
            </div>

            <div class="mb-10">
                <h1 class="text-4xl font-black text-popfit-dark tracking-tight leading-none mb-3">MASUK</h1>
                <p class="text-popfit-light/60 font-black uppercase text-[10px] tracking-[0.2em]">Peminjaman Alat Olahraga Sekolah</p>
            </div>

            <?php if(isset($error)) : ?>
                <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded-sm text-[11px] font-black uppercase tracking-widest mb-8 flex items-center">
                    <i class="ph-fill ph-warning-circle mr-3 text-lg"></i>
                    <?= $error; ?>
                </div>
            <?php endif; ?>

            <form method="post" class="space-y-6">
                <!-- Role Selection -->
                <div class="grid grid-cols-3 gap-2 mb-8">
                    <label class="cursor-pointer group">
                        <input type="radio" name="role_choice" value="siswa" checked class="peer hidden">
                        <div class="flex flex-col items-center justify-center p-3 border border-popfit-border rounded-sm peer-checked:border-popfit-dark peer-checked:bg-popfit-dark peer-checked:text-white transition-all">
                            <i class="ph ph-student text-xl mb-1"></i>
                            <span class="text-[9px] font-black uppercase tracking-tighter">Siswa</span>
                        </div>
                    </label>
                    <label class="cursor-pointer group">
                        <input type="radio" name="role_choice" value="petugas" class="peer hidden">
                        <div class="flex flex-col items-center justify-center p-3 border border-popfit-border rounded-sm peer-checked:border-popfit-dark peer-checked:bg-popfit-dark peer-checked:text-white transition-all">
                            <i class="ph ph-user-tie text-xl mb-1"></i>
                            <span class="text-[9px] font-black uppercase tracking-tighter">Staff</span>
                        </div>
                    </label>
                    <label class="cursor-pointer group">
                        <input type="radio" name="role_choice" value="admin" class="peer hidden">
                        <div class="flex flex-col items-center justify-center p-3 border border-popfit-border rounded-sm peer-checked:border-popfit-dark peer-checked:bg-popfit-dark peer-checked:text-white transition-all">
                            <i class="ph ph-shield-check text-xl mb-1"></i>
                            <span class="text-[9px] font-black uppercase tracking-tighter">Admin</span>
                        </div>
                    </label>
                </div>

                <div>
                    <label class="block text-[10px] font-black text-popfit-dark uppercase tracking-[0.2em] mb-2.5">Username Pelanggan</label>
                    <div class="relative group">
                        <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-gray-300 group-focus-within:text-popfit-dark transition-colors">
                            <i class="ph-bold ph-user-circle text-lg"></i>
                        </span>
                        <input type="text" name="username" required
                               class="block w-full pl-12 pr-4 py-4 bg-gray-50 border border-popfit-border text-xs font-black focus:outline-none focus:border-popfit-dark focus:bg-white rounded-sm transition-all text-popfit-dark placeholder:text-gray-300" 
                               placeholder="Masukkan username Anda">
                    </div>
                </div>

                <div>
                    <div class="flex justify-between items-center mb-2.5">
                        <label class="block text-[10px] font-black text-popfit-dark uppercase tracking-[0.2em]">Password Akun</label>
                    </div>
                    <div class="relative group">
                        <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-gray-300 group-focus-within:text-popfit-dark transition-colors">
                            <i class="ph-bold ph-lock-key text-lg"></i>
                        </span>
                        <input type="password" name="password" id="password" required
                               class="block w-full pl-12 pr-12 py-4 bg-gray-50 border border-popfit-border text-xs font-black focus:outline-none focus:border-popfit-dark focus:bg-white rounded-sm transition-all text-popfit-dark placeholder:text-gray-300"
                               placeholder="••••••••">
                        <button type="button" onclick="togglePassword()" class="absolute inset-y-0 right-0 pr-4 flex items-center text-gray-300 hover:text-popfit-dark transition-colors">
                            <i class="ph-bold ph-eye" id="eyeIcon"></i>
                        </button>
                    </div>
                </div>

                <div class="pt-4">
                    <button type="submit" name="login" 
                            class="w-full bg-popfit-dark text-white py-5 text-[11px] font-black uppercase tracking-[0.3em] rounded-sm hover:bg-popfit-light transition-all flex items-center justify-center border-none outline-none">
                        Login Sekarang <i class="ph-bold ph-arrow-right ml-3"></i>
                    </button>
                    <a href="index.php" class="block w-full mt-4 text-center py-4 text-[10px] font-black uppercase tracking-[0.2em] text-popfit-textMuted hover:text-popfit-dark transition-colors border border-popfit-border rounded-sm">
                        Kembali ke Beranda
                    </a>
                </div>
            </form>

            <div class="mt-16 pt-10 border-t border-popfit-border flex flex-col sm:flex-row items-center justify-between gap-6">
                <p class="text-[10px] font-black text-popfit-light/40 uppercase tracking-widest">
                    Belum punya akun?
                </p>
                <a href="register.php" class="text-[10px] font-black text-popfit-dark uppercase tracking-widest border-b-2 border-popfit-accent hover:border-popfit-dark transition-all">
                    Registrasi Siswa
                </a>
            </div>
        </div>
    </div>

    <!-- RIGHT: CAROUSEL (50% on Desktop, Hidden on Mobile for better focus) -->
    <div class="hidden lg:block lg:w-1/2 relative overflow-hidden bg-popfit-dark">
        <div id="carousel" class="h-full w-full">
            <div class="carousel-item active h-full w-full relative animate-fade">
                <img src="asset/login_bg_1.png" class="h-full w-full object-cover opacity-50 mix-blend-overlay scale-110" alt="Sports Gear">
                <div class="absolute inset-0 flex flex-col justify-end p-24 text-white">
                    <h2 class="text-6xl font-black tracking-tighter leading-[0.9] mb-6 uppercase">MANAGE <br>EQUIPMENT.</h2>
                    <p class="text-[11px] font-black text-popfit-accent uppercase tracking-[0.3em] max-w-sm leading-loose">Sistem manajemen peminjaman alat olahraga sekolah yang modern, cepat, dan terpercaya.</p>
                </div>
            </div>
            <div class="carousel-item h-full w-full relative animate-fade">
                <img src="asset/login_bg_2.png" class="h-full w-full object-cover opacity-50 mix-blend-overlay scale-110" alt="Field">
                <div class="absolute inset-0 flex flex-col justify-end p-24 text-white">
                    <h2 class="text-6xl font-black tracking-tighter leading-[0.9] mb-6 uppercase">BOOST <br>ACTIVITY.</h2>
                    <p class="text-[11px] font-black text-popfit-accent uppercase tracking-[0.3em] max-w-sm leading-loose">Dukung prestasi olahraga siswa dengan kemudahan akses fasilitas sekolah.</p>
                </div>
            </div>
        </div>

        <!-- Indicators -->
        <div class="absolute bottom-16 left-24 flex space-x-3">
            <div class="indicator w-16 h-1 bg-white opacity-20 transition-all rounded-full" id="ind-0"></div>
            <div class="indicator w-16 h-1 bg-white opacity-20 transition-all rounded-full" id="ind-1"></div>
        </div>
    </div>

    <script>
        function togglePassword() {
            const password = document.getElementById("password");
            const eyeIcon = document.getElementById("eyeIcon");
            if(password.type === "password"){
                password.type = "text";
                eyeIcon.classList.replace('ph-bold', 'ph-fill');
            } else {
                password.type = "password";
                eyeIcon.classList.replace('ph-fill', 'ph-bold');
            }
        }

        let currentSlide = 0;
        const slides = document.querySelectorAll('.carousel-item');
        const indicators = document.querySelectorAll('.indicator');

        function showSlide(index) {
            slides.forEach((s, i) => {
                s.classList.remove('active');
                indicators[i].classList.replace('opacity-100', 'opacity-20');
                indicators[i].classList.remove('w-24');
                indicators[i].classList.add('w-16');
            });
            slides[index].classList.add('active');
            indicators[index].classList.replace('opacity-20', 'opacity-100');
            indicators[index].classList.replace('w-16', 'w-24');
        }

        setInterval(() => {
            currentSlide = (currentSlide + 1) % slides.length;
            showSlide(currentSlide);
        }, 6000);

        showSlide(0);
    </script>
</body>
</html>