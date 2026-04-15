<?php
require 'config/config.php';
session_start();

/* REDIRECT JIKA SUDAH LOGIN */
if(isset($_SESSION["login"])) {
    if($_SESSION["role"] == "admin utama" || $_SESSION["role"] == "admin") {
        header("Location: dashboardAdmin/dashboardAdmin.php");
    } elseif($_SESSION["role"] == "petugas") {
        header("Location: dashboardPetugas/dashboardPetugas.php");
    } else {
        header("Location: dashboardSiswa/dashboardSiswa.php");
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PopFit - Peminjaman Alat Olahraga</title>
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
        * { box-shadow: none !important; }
    </style>
</head>
<body class="bg-popfit-bg text-popfit-text font-sans selection:bg-popfit-accent selection:text-popfit-dark">
    <nav class="bg-popfit-dark text-white px-6 py-4 flex justify-between items-center border-b border-popfit-light sticky top-0 z-50">
        <div class="flex items-center gap-2">
            <i class="ph-fill ph-paw-print text-popfit-accent text-2xl"></i>
            <span class="text-xl font-extrabold tracking-tighter uppercase">PopFit</span>
        </div>
        <div class="space-x-4">
            <a href="login.php" class="text-[11px] font-black uppercase tracking-widest hover:text-popfit-accent transition-colors">Masuk</a>
            <a href="register.php" class="bg-popfit-accent text-popfit-dark px-5 py-2 text-[11px] font-black uppercase tracking-widest rounded-sm hover:bg-popfit-accentHover transition-colors">Daftar</a>
        </div>
    </nav>

    <main class="max-w-6xl mx-auto px-6 py-24 text-center">
        <div class="inline-block px-3 py-1 bg-popfit-dark text-popfit-accent text-[9px] font-black uppercase tracking-[0.3em] rounded-full mb-8">Edisi Modern 2026</div>
        <h1 class="text-6xl md:text-8xl font-black text-popfit-dark mb-8 leading-[0.9] tracking-tighter uppercase">Peminjaman Alat <br><span class="text-popfit-light">Jadi Lebih Mudah.</span></h1>
        <p class="text-[12px] font-bold text-popfit-textMuted mb-12 max-w-2xl mx-auto uppercase tracking-widest leading-relaxed">Sistem manajemen inventaris dan sirkulasi alat olahraga sekolah yang modern, terpantau, dan transparan — Tanpa Gradasi, Tanpa Shadow.</p>
        
        <div class="flex flex-col sm:flex-row justify-center gap-4 mb-24">
            <a href="login.php" class="bg-popfit-dark text-white px-10 py-5 text-[11px] font-black uppercase tracking-[0.2em] rounded-sm hover:bg-popfit-light transition-all flex items-center justify-center group">
                Mulai Pinjam Sekarang <i class="ph ph-arrow-right ml-3 group-hover:translate-x-1 transition-transform"></i>
            </a>
            <a href="register.php" class="bg-white text-popfit-dark px-10 py-5 text-[11px] font-black uppercase tracking-[0.2em] rounded-sm border border-popfit-border hover:bg-popfit-bg transition-all flex items-center justify-center">
                Daftar Akun Siswa
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 text-left">
            <div class="bg-white border border-popfit-border p-10 rounded-sm hover:border-popfit-dark transition-colors">
                <div class="w-14 h-14 bg-popfit-bg border border-popfit-border flex items-center justify-center mb-8 rounded-sm text-popfit-dark">
                    <i class="ph ph-bolt-fill text-3xl"></i>
                </div>
                <h3 class="text-lg font-black text-popfit-dark mb-4 uppercase tracking-tight">Cepat & Flat</h3>
                <p class="text-[11px] font-medium text-popfit-textMuted leading-loose uppercase">Proses peminjaman tanpa ribet, antarmuka bersih tanpa gangguan visual yang berlebihan.</p>
            </div>
            <div class="bg-white border border-popfit-border p-10 rounded-sm hover:border-popfit-dark transition-colors">
                <div class="w-14 h-14 bg-popfit-bg border border-popfit-border flex items-center justify-center mb-8 rounded-sm text-popfit-dark">
                    <i class="ph ph-shield-check-fill text-3xl"></i>
                </div>
                <h3 class="text-lg font-black text-popfit-dark mb-4 uppercase tracking-tight">Keamanan Data</h3>
                <p class="text-[11px] font-medium text-popfit-textMuted leading-loose uppercase">Setiap transaksi tercatat dan terpantau oleh admin sekolah secara real-time.</p>
            </div>
            <div class="bg-white border border-popfit-border p-10 rounded-sm hover:border-popfit-dark transition-colors">
                <div class="w-14 h-14 bg-popfit-bg border border-popfit-border flex items-center justify-center mb-8 rounded-sm text-popfit-dark">
                    <i class="ph ph-chart-bar-fill text-3xl"></i>
                </div>
                <h3 class="text-lg font-black text-popfit-dark mb-4 uppercase tracking-tight">Laporan Otomatis</h3>
                <p class="text-[11px] font-medium text-popfit-textMuted leading-loose uppercase">Dashboard Admin menyediakan statistik instan mengenai penggunaan alat setiap harinya.</p>
            </div>
        </div>
    </main>

    <footer class="border-t border-popfit-border py-16 text-center bg-white mt-20">
        <p class="text-[10px] font-black text-popfit-textMuted uppercase tracking-[0.4em]">&copy; 2026 PopFit / SMK NEGERI / SMKN 1 / SMKN 2 / SMKN 3 / SMKN 4 / SMKN 5 / SMKN 6 / SMKN 7 / SMKN 8 / SMKN 9 / SMKN 10</p>
    </footer>
</body>
</html>
