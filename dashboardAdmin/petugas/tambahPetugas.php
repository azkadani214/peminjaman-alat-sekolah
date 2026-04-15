<?php
require '../../config/config.php';
session_start();

if (!isset($_SESSION["login"]) || ($_SESSION["role"] != "admin utama" && $_SESSION["role"] != "admin")) {
    header("Location: ../../login.php");
    exit;
}

if(isset($_POST['simpan'])){
    $nama = mysqli_real_escape_string($connect, $_POST['nama']);
    $username = mysqli_real_escape_string($connect, $_POST['username']);
    $no_telp = mysqli_real_escape_string($connect, $_POST['no_telp']);
    $password = $_POST['password'];
    
    // Check if Username exists
    $check = mysqli_query($connect, "SELECT * FROM users WHERE username = '$username'");
    if(mysqli_num_rows($check) > 0){
        $error = "Username sudah digunakan!";
    } else {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $query = "INSERT INTO users (nama, username, password, role, no_telp, tgl_daftar)
                  VALUES ('$nama', '$username', '$password_hash', 'petugas', '$no_telp', NOW())";
        
        if(mysqli_query($connect, $query)){
            header("Location: petugas.php?msg=tambah_success");
            exit;
        } else {
            $error = "Gagal menambahkan data: " . mysqli_error($connect);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Petugas - PopFit Admin</title>
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
        .nav-active { background-color: #3E614C; border-left: 4px solid #F5C460; }
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-thumb { background: #E4E4E7; }
        * { box-shadow: none !important; }
    </style>
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
</head>
<body class="bg-popfit-bg text-popfit-text font-sans h-screen overflow-hidden flex text-[13px]">

    <aside class="hidden md:flex flex-col w-64 bg-popfit-dark text-white border-r border-popfit-dark h-full flex-shrink-0">
        <div class="h-16 flex items-center px-6 border-b border-popfit-light bg-popfit-dark">
            <i class="ph-fill ph-paw-print text-popfit-accent text-2xl mr-3"></i>
            <span class="text-xl font-black tracking-wide uppercase">PopFit</span>
        </div>
        <nav class="flex-1 overflow-y-auto py-4">
            <ul class="space-y-1">
                <li><a href="../dashboardAdmin.php" class="flex items-center px-6 py-3 text-gray-200 hover:bg-popfit-light transition-colors border-l-4 border-transparent"><i class="ph ph-squares-four text-xl w-6"></i><span class="ml-3 font-bold">Dashboard</span></a></li>
                <li class="px-6 py-2 mt-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Manajemen</li>
                <li><a href="../petugas/petugas.php" class="nav-active flex items-center px-6 py-3 text-gray-200 hover:bg-popfit-light transition-colors border-l-4 border-transparent"><i class="ph ph-user-tie text-xl w-6"></i><span class="ml-3 font-bold">Petugas</span></a></li>
            </ul>
        </nav>
    </aside>

    <div class="flex-1 flex flex-col h-screen overflow-hidden">
        <header class="h-16 bg-popfit-surface border-b border-popfit-border flex items-center justify-between px-6 flex-shrink-0">
            <div class="flex items-center">
                <a href="petugas.php" class="mr-4 text-popfit-dark hover:scale-110 transition-transform"><i class="ph ph-arrow-left text-2xl"></i></a>
                <h2 class="text-lg font-black text-popfit-dark uppercase tracking-tight">Tambah Petugas Baru</h2>
            </div>
        </header>

        <main class="flex-1 overflow-y-auto p-6 flex items-center justify-center">
            <div class="w-full max-w-2xl bg-white border border-popfit-border rounded-sm p-8">
                <?php if(isset($error)): ?>
                <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 text-[10px] font-black uppercase text-red-700 tracking-widest"><?= $error ?></div>
                <?php endif; ?>

                <form method="POST" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-popfit-textMuted uppercase tracking-widest">Nama Lengkap Petugas</label>
                            <input type="text" name="nama" required placeholder="NAMA LENGKAP" class="w-full bg-popfit-bg border border-popfit-border rounded-sm px-4 py-3 text-xs font-bold text-popfit-dark focus:border-popfit-dark outline-none transition-all uppercase placeholder:text-gray-300">
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-popfit-textMuted uppercase tracking-widest">Username Login</label>
                            <input type="text" name="username" required placeholder="USERNAME" class="w-full bg-popfit-bg border border-popfit-border rounded-sm px-4 py-3 text-xs font-bold text-popfit-dark focus:border-popfit-dark outline-none transition-all lowercase placeholder:text-gray-300">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-popfit-textMuted uppercase tracking-widest">Kata Sandi Akun</label>
                            <input type="password" name="password" required placeholder="********" class="w-full bg-popfit-bg border border-popfit-border rounded-sm px-4 py-3 text-xs font-bold text-popfit-dark focus:border-popfit-dark outline-none transition-all">
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-popfit-textMuted uppercase tracking-widest">Nomor Telepon</label>
                            <input type="text" name="no_telp" required placeholder="08XXXXXXXXXX" class="w-full bg-popfit-bg border border-popfit-border rounded-sm px-4 py-3 text-xs font-bold text-popfit-dark focus:border-popfit-dark outline-none transition-all placeholder:text-gray-300">
                        </div>
                    </div>

                    <div class="pt-6 flex space-x-4">
                        <button type="submit" name="simpan" class="flex-1 bg-popfit-dark text-white py-4 text-[10px] font-black uppercase tracking-widest rounded-sm hover:bg-popfit-light transition-all">Daftarkan Petugas</button>
                        <a href="petugas.php" class="flex-1 bg-white border border-popfit-border text-popfit-dark py-4 text-center text-[10px] font-black uppercase tracking-widest rounded-sm hover:bg-popfit-bg transition-all">Batal</a>
                    </div>
                </form>
            </div>
        </main>
    </div>
</body>
</html>