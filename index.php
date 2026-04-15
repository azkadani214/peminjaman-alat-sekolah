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

// Fetch 4 items for the catalog section
$catalog = queryReadData("SELECT * FROM alat_olahraga ORDER BY id_alat_olahraga DESC LIMIT 4");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PopFit - Peminjaman Alat SMKN 1 Sukorejo</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" type="text/css" href="https://unpkg.com/@phosphor-icons/web@2.1.1/src/duotone/style.css" />
    <script src="https://unpkg.com/@phosphor-icons/web@2.1.1"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Plus Jakarta Sans', 'sans-serif'] },
                    colors: {
                        popfit: {
                            dark: '#2A4736', 
                            light: '#3E614C', 
                            accent: '#F5C460', 
                            accentHover: '#E3B24F',
                            bg: '#F4F4F5', 
                            surface: '#FFFFFF', 
                            border: '#E4E4E7', 
                            text: '#1F2937',
                            textMuted: '#6B7280'
                        }
                    },
                    borderRadius: {
                        'none': '0px',
                        'sm': '2px', 
                        DEFAULT: '4px',
                    }
                }
            }
        }
    </script>
    <style>
        /* Smooth scrolling */
        html { scroll-behavior: smooth; }
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-thumb { background: #E4E4E7; }
        * { box-shadow: none !important; }
    </style>
</head>
<body class="bg-popfit-bg text-popfit-text font-sans selection:bg-popfit-accent selection:text-popfit-dark">

    <!-- NAVBAR -->
    <nav class="bg-popfit-surface border-b border-popfit-border sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <!-- Logo -->
                <div class="flex items-center">
                    <i class="ph-fill ph-paw-print text-popfit-dark text-3xl mr-2"></i>
                    <div>
                        <span class="text-xl font-bold text-popfit-dark leading-none block">PopFit</span>
                        <span class="text-[10px] text-popfit-textMuted uppercase tracking-widest font-bold block">SMKN 1 Sukorejo</span>
                    </div>
                </div>
                
                <!-- Desktop Menu -->
                <div class="hidden md:flex items-center space-x-8">
                    <a href="#beranda" class="text-sm font-bold text-popfit-dark hover:text-popfit-light transition-colors border-b-2 border-transparent hover:border-popfit-dark py-5">Beranda</a>
                    <a href="#fitur" class="text-sm font-bold text-popfit-textMuted hover:text-popfit-dark transition-colors border-b-2 border-transparent hover:border-popfit-dark py-5 text-[11px] uppercase tracking-widest">Fitur</a>
                    <a href="#katalog" class="text-sm font-bold text-popfit-textMuted hover:text-popfit-dark transition-colors border-b-2 border-transparent hover:border-popfit-dark py-5 text-[11px] uppercase tracking-widest">Katalog Alat</a>
                </div>

                <!-- CTA Login -->
                <div class="flex items-center">
                    <a href="login.php" class="bg-popfit-dark text-white px-5 py-2 text-[11px] font-black uppercase tracking-widest rounded-sm border border-popfit-dark hover:bg-popfit-light transition-colors flex items-center">
                        <i class="ph ph-sign-in mr-2 text-lg"></i> Masuk
                    </a>
                </div>
            </div>
        </div>
        <!-- Mobile Menu Bawah -->
        <div class="md:hidden border-t border-popfit-border bg-gray-50 flex justify-around p-2">
            <a href="#beranda" class="text-[10px] font-black uppercase tracking-widest text-popfit-dark">Beranda</a>
            <a href="#fitur" class="text-[10px] font-black uppercase tracking-widest text-popfit-textMuted">Fitur</a>
            <a href="#katalog" class="text-[10px] font-black uppercase tracking-widest text-popfit-textMuted">Katalog</a>
        </div>
    </nav>

    <!-- HERO SECTION -->
    <section id="beranda" class="bg-popfit-dark text-white py-16 md:py-24 border-b border-popfit-dark relative overflow-hidden">
        <!-- Dekorasi Background -->
        <div class="absolute top-[-50px] right-[-50px] w-64 h-64 bg-popfit-light rounded-full opacity-20 pointer-events-none"></div>
        <div class="absolute bottom-[-100px] left-[-50px] w-96 h-96 bg-popfit-light rounded-full opacity-20 pointer-events-none"></div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="flex flex-col md:flex-row items-center gap-12">
                <div class="flex-1 text-center md:text-left">
                    <div class="inline-block border border-popfit-accent text-popfit-accent px-3 py-1 rounded-sm text-[9px] font-black mb-6 tracking-[0.2em] uppercase bg-popfit-dark">
                        Sistem Inventaris Resmi
                    </div>
                    <h1 class="text-4xl md:text-6xl lg:text-7xl font-black mb-4 leading-none tracking-tighter uppercase">
                        Pinjam Alat Olahraga <br>Lebih <span class="text-popfit-accent border-b-8 border-popfit-accent">Praktis & Cepat</span>
                    </h1>
                    <p class="text-[12px] font-bold text-gray-300 mb-8 max-w-2xl mx-auto md:mx-0 uppercase tracking-widest leading-relaxed">
                        Sistem Layanan Peminjaman Alat dan Inventaris Sekolah terpadu untuk siswa dan guru SMKN 1 Sukorejo — Cek stok, ajukan peminjaman, dan pantau status secara real-time.
                    </p>
                    <div class="flex flex-col sm:flex-row gap-4 justify-center md:justify-start">
                        <a href="#katalog" class="bg-popfit-accent text-popfit-dark px-10 py-5 text-[11px] font-black uppercase tracking-widest rounded-sm hover:bg-popfit-accentHover transition-colors border border-transparent text-center">
                            Lihat Katalog Alat
                        </a>
                        <a href="register.php" class="bg-transparent text-white px-10 py-5 text-[11px] font-black uppercase tracking-widest rounded-sm border border-white hover:bg-white hover:text-popfit-dark transition-colors text-center">
                            Daftar Akun Siswa
                        </a>
                    </div>
                </div>
                <!-- Hero Image Illustration -->
                <div class="flex-1 w-full max-w-md hidden md:block">
                    <div class="aspect-square bg-popfit-light border-4 border-popfit-accent rounded-sm flex items-center justify-center relative p-8">
                        <div class="absolute top-4 right-4 w-12 h-12 bg-white border border-popfit-border rounded-sm flex items-center justify-center text-popfit-dark transform rotate-12">
                            <i class="ph ph-basketball text-2xl"></i>
                        </div>
                        <div class="absolute bottom-10 left-[-20px] w-16 h-16 bg-popfit-accent border border-popfit-border rounded-sm flex items-center justify-center text-popfit-dark transform -rotate-6">
                            <i class="ph ph-tennis-racquet text-3xl"></i>
                        </div>
                        <div class="w-full h-full bg-white border border-popfit-border rounded-sm flex flex-col p-4">
                            <div class="h-8 border-b border-popfit-border flex items-center mb-4">
                                <div class="w-3 h-3 bg-[#EF4444] rounded-full mr-1.5"></div>
                                <div class="w-3 h-3 bg-[#F5C460] rounded-full mr-1.5"></div>
                                <div class="w-3 h-3 bg-[#22C55E] rounded-full"></div>
                            </div>
                            <div class="flex-1 flex items-center justify-center text-popfit-textMuted border-2 border-dashed border-popfit-border">
                                <i class="ph-duotone ph-circles-four text-6xl opacity-30"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FITUR SECTION -->
    <section id="fitur" class="py-20 bg-popfit-surface border-b border-popfit-border">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-black text-popfit-dark mb-4 uppercase tracking-tighter">Fitur Utama PopFit</h2>
                <p class="text-[11px] font-black text-popfit-textMuted max-w-2xl mx-auto uppercase tracking-widest">Sistem kami dirancang untuk mempermudah sirkulasi inventaris sekolah dengan transparansi penuh antara siswa, guru, dan admin.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Fitur 1 -->
                <div class="border border-popfit-border p-8 rounded-sm bg-popfit-bg hover:bg-white transition-all group">
                    <div class="w-14 h-14 bg-popfit-dark text-popfit-accent border border-popfit-dark rounded-sm flex items-center justify-center mb-8 group-hover:scale-105 transition-transform">
                        <i class="ph ph-arrows-left-right text-3xl"></i>
                    </div>
                    <h3 class="text-lg font-black text-popfit-dark mb-4 uppercase tracking-tight">Sirkulasi Real-time</h3>
                    <p class="text-[11px] font-bold text-popfit-textMuted uppercase leading-loose">Proses peminjaman dan pengembalian tercatat langsung, meminimalisir kehilangan alat olahraga.</p>
                </div>
                
                <!-- Fitur 2 -->
                <div class="border border-popfit-border p-8 rounded-sm bg-popfit-bg hover:bg-white transition-all group">
                    <div class="w-14 h-14 bg-popfit-dark text-popfit-accent border border-popfit-dark rounded-sm flex items-center justify-center mb-8 group-hover:scale-105 transition-transform">
                        <i class="ph ph-shopping-cart text-3xl"></i>
                    </div>
                    <h3 class="text-lg font-black text-popfit-dark mb-4 uppercase tracking-tight">Keranjang Pinjaman</h3>
                    <p class="text-[11px] font-bold text-popfit-textMuted uppercase leading-loose">Pilih beberapa alat sekaligus (seperti bola dan cone) ke dalam keranjang sebelum checkout pengajuan.</p>
                </div>

                <!-- Fitur 3 -->
                <div class="border border-popfit-border p-8 rounded-sm bg-popfit-bg hover:bg-white transition-all group">
                    <div class="w-14 h-14 bg-popfit-accent/20 text-yellow-800 border border-popfit-accent/30 rounded-sm flex items-center justify-center mb-8 group-hover:scale-105 transition-transform">
                        <i class="ph ph-receipt text-3xl"></i>
                    </div>
                    <h3 class="text-lg font-black text-popfit-dark mb-4 uppercase tracking-tight">Denda Otomatis</h3>
                    <p class="text-[11px] font-bold text-popfit-textMuted uppercase leading-loose">Sistem pintar yang akan menghitung denda secara otomatis jika alat dikembalikan melewati batas waktu.</p>
                </div>

                <!-- Fitur 4 -->
                <div class="border border-popfit-border p-8 rounded-sm bg-popfit-bg hover:bg-white transition-all group">
                    <div class="w-14 h-14 bg-blue-50 text-blue-800 border border-blue-100 rounded-sm flex items-center justify-center mb-8 group-hover:scale-105 transition-transform">
                        <i class="ph ph-chart-bar text-3xl"></i>
                    </div>
                    <h3 class="text-lg font-black text-popfit-dark mb-4 uppercase tracking-tight">Laporan & Ekspor</h3>
                    <p class="text-[11px] font-bold text-popfit-textMuted uppercase leading-loose">Admin dapat memonitor log aktivitas dan mengunduh laporan transaksi dalam format PDF atau Excel.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- KATALOG SECTION -->
    <section id="katalog" class="py-20 bg-popfit-bg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row justify-between items-end mb-12 gap-6">
                <div>
                    <h2 class="text-4xl font-black text-popfit-dark mb-2 uppercase tracking-tighter">Katalog Alat Tersedia</h2>
                    <p class="text-[11px] font-black text-popfit-textMuted uppercase tracking-widest">Data tersinkronisasi otomatis dengan gudang penyimpanan admin.</p>
                </div>
                <div class="flex gap-2 w-full md:w-auto">
                    <a href="login.php" class="bg-popfit-dark text-white px-8 py-3 text-[11px] font-black uppercase tracking-widest rounded-sm hover:bg-popfit-light border border-popfit-dark transition-all">
                        Masuk untuk Pinjam
                    </a>
                </div>
            </div>

            <!-- Grid Katalog Dynamic -->
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                <?php foreach($catalog as $row): 
                    $foto = (!empty($row['foto_alat_olahraga'])) ? $row['foto_alat_olahraga'] : 'default.png';
                    $imgSrc = (strpos($foto, 'http') === 0) ? $foto : "asset/$foto";
                    $stokColor = ($row['stok'] > 0) ? 'bg-[#DCFCE7] text-[#166534] border-[#BBF7D0]' : 'bg-[#FEE2E2] text-[#991B1B] border-[#FECACA]';
                    $stokText = ($row['stok'] > 0) ? $row['stok'] . " Tersedia" : "Habis Dipinjam";
                ?>
                <div class="bg-white border border-popfit-border rounded-sm flex flex-col hover:border-popfit-dark transition-all group shadow-sm hover:shadow-md">
                    <div class="h-48 bg-gray-50 border-b border-popfit-border flex items-center justify-center relative overflow-hidden p-6">
                        <img src="<?= $imgSrc ?>" class="max-h-full object-contain filter group-hover:scale-110 transition-transform duration-500">
                        <div class="absolute top-2 right-2 bg-popfit-dark text-white text-[9px] font-black px-2 py-1 rounded-sm uppercase tracking-widest"><?= htmlspecialchars($row['kategori']) ?></div>
                    </div>
                    <div class="p-6 flex-1 flex flex-col">
                        <div class="text-[9px] text-popfit-textMuted font-black mb-1 uppercase tracking-widest">#<?= htmlspecialchars($row['id_alat_olahraga']) ?></div>
                        <h3 class="font-black text-popfit-dark text-sm leading-tight mb-4 uppercase tracking-tight"><?= htmlspecialchars($row['nama_alat_olahraga']) ?></h3>
                        
                        <div class="mt-auto">
                            <div class="flex justify-between items-center mb-6">
                                <span class="text-[9px] text-popfit-textMuted font-black uppercase tracking-widest">Status:</span>
                                <span class="<?= $stokColor ?> border px-2 py-1 rounded-sm text-[9px] font-black uppercase"><?= $stokText ?></span>
                            </div>
                            <?php if($row['stok'] > 0): ?>
                                <a href="login.php" class="w-full bg-white text-popfit-dark border border-popfit-dark py-3 text-[10px] font-black uppercase tracking-widest rounded-sm hover:bg-popfit-dark hover:text-white transition-all flex justify-center items-center">
                                    <i class="ph ph-plus mr-2 text-lg"></i> Pinjam Alat
                                </a>
                            <?php else: ?>
                                <button class="w-full bg-gray-100 text-gray-400 border border-gray-200 py-3 text-[10px] font-black uppercase tracking-widest rounded-sm cursor-not-allowed flex justify-center items-center" disabled>
                                    Kosong
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <div class="mt-16 text-center">
                <a href="login.php" class="bg-popfit-dark text-white px-10 py-5 text-[11px] font-black uppercase tracking-[0.2em] rounded-sm hover:bg-popfit-light transition-all inline-flex items-center">
                    Cek Katalog Lengkap <i class="ph ph-caret-right ml-3"></i>
                </a>
            </div>
        </div>
    </section>

    <!-- FOOTER -->
    <footer class="bg-popfit-dark text-white border-t-4 border-popfit-accent pt-20 pb-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-16 border-b border-popfit-light pb-20 mb-10">
                <!-- Info Brand -->
                <div>
                    <div class="flex items-center mb-8">
                        <i class="ph-fill ph-paw-print text-popfit-accent text-4xl mr-3"></i>
                        <span class="text-3xl font-black tracking-tighter uppercase">PopFit</span>
                    </div>
                    <p class="text-[11px] font-bold text-gray-400 leading-loose uppercase tracking-wide">
                        Sistem Layanan Peminjaman Alat dan Inventaris terintegrasi untuk mendukung kegiatan belajar mengajar dan ekstrakurikuler di SMKN 1 Sukorejo.
                    </p>
                </div>

                <!-- Tautan Cepat -->
                <div>
                    <h4 class="text-xs font-black mb-8 text-popfit-accent uppercase tracking-[0.3em]">Tautan Cepat</h4>
                    <ul class="space-y-4">
                        <li><a href="#beranda" class="text-gray-400 hover:text-white transition-all text-[11px] font-bold uppercase tracking-widest flex items-center"><i class="ph ph-caret-right mr-3 text-popfit-accent"></i> Beranda</a></li>
                        <li><a href="#fitur" class="text-gray-400 hover:text-white transition-all text-[11px] font-bold uppercase tracking-widest flex items-center"><i class="ph ph-caret-right mr-3 text-popfit-accent"></i> Fitur Sistem</a></li>
                        <li><a href="#katalog" class="text-gray-400 hover:text-white transition-all text-[11px] font-bold uppercase tracking-widest flex items-center"><i class="ph ph-caret-right mr-3 text-popfit-accent"></i> Katalog Inventaris</a></li>
                        <li><a href="login.php" class="text-gray-400 hover:text-white transition-all text-[11px] font-bold uppercase tracking-widest flex items-center"><i class="ph ph-caret-right mr-3 text-popfit-accent"></i> Masuk Sistem</a></li>
                    </ul>
                </div>

                <!-- Kontak -->
                <div>
                    <h4 class="text-xs font-black mb-8 text-popfit-accent uppercase tracking-[0.3em]">Hubungi Kami</h4>
                    <ul class="space-y-6">
                        <li class="flex items-start">
                            <i class="ph ph-map-pin text-2xl text-popfit-accent mr-4 mt-0.5"></i>
                            <span class="text-[11px] font-bold text-gray-400 uppercase leading-relaxed tracking-wide">Jl. Raya Sukorejo, Pasuruan<br>Jawa Timur, Indonesia</span>
                        </li>
                        <li class="flex items-center">
                            <i class="ph ph-envelope-simple text-2xl text-popfit-accent mr-4"></i>
                            <span class="text-[11px] font-bold text-gray-400 uppercase tracking-widest">popfit@smkn1sukorejo.sch.id</span>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="flex flex-col md:flex-row justify-between items-center text-[9px] font-black uppercase tracking-[0.5em] text-gray-500">
                <p>&copy; 2026 PopFit - SMKN 1 Sukorejo. All Rights Reserved.</p>
                <div class="mt-6 md:mt-0 flex space-x-8">
                    <a href="#" class="hover:text-white transition-colors">Terms</a>
                    <a href="#" class="hover:text-white transition-colors">Privacy</a>
                </div>
            </div>
        </div>
    </footer>

</body>
</html>

