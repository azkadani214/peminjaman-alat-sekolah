<?php
// layout/sidebar_admin.php
$rel = $rel ?? './';
$adminName = $_SESSION['nama'] ?? 'Admin';
$adminUsername = $_SESSION['username'] ?? 'admin';
$activeIndex = $activeIndex ?? '';

function isActive($current, $target) {
    return $current === $target ? 'nav-active' : '';
}
?>
<aside id="sidebar" class="fixed inset-y-0 left-0 w-64 bg-popfit-dark text-white border-r border-popfit-light h-full flex-shrink-0 z-50 sidebar -translate-x-full md:translate-x-0 md:static flex flex-col">
    <div class="h-16 flex items-center px-6 border-b border-popfit-light bg-popfit-dark justify-between">
        <div class="flex items-center">
            <i class="ph-fill ph-paw-print text-popfit-accent text-2xl mr-3"></i>
            <span class="text-xl font-black tracking-wide uppercase">PopFit Admin</span>
        </div>
        <button id="closeSidebar" class="md:hidden text-gray-400 hover:text-white"><i class="ph ph-x text-2xl"></i></button>
    </div>

    <nav class="flex-1 overflow-y-auto py-4">
        <ul class="space-y-1">
            <li><a href="<?= $rel ?>dashboardAdmin.php" class="<?= isActive($activeIndex, 'dashboard') ?> flex items-center px-6 py-3 text-gray-200 hover:bg-popfit-light transition-colors border-l-4 border-transparent">
                <i class="ph ph-squares-four text-xl w-6"></i><span class="ml-3 font-bold">Dashboard</span>
            </a></li>
            
            <li class="px-6 py-2 mt-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Katalog</li>
            <li><a href="<?= $rel ?>alat/daftarAlat.php" class="<?= isActive($activeIndex, 'alat') ?> flex items-center px-6 py-3 text-gray-200 hover:bg-popfit-light transition-colors border-l-4 border-transparent">
                <i class="ph ph-basketball text-xl w-6"></i><span class="ml-3 font-bold">Alat Olahraga</span>
            </a></li>
            <li><a href="<?= $rel ?>alat/kategori.php" class="<?= isActive($activeIndex, 'kategori') ?> flex items-center px-6 py-3 text-gray-200 hover:bg-popfit-light transition-colors border-l-4 border-transparent">
                <i class="ph ph-list-bullets text-xl w-6"></i><span class="ml-3 font-bold">Kategori</span>
            </a></li>

            <li class="px-6 py-2 mt-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Pengguna</li>
            <li><a href="<?= $rel ?>siswa/siswa.php" class="<?= isActive($activeIndex, 'siswa') ?> flex items-center px-6 py-3 text-gray-200 hover:bg-popfit-light transition-colors border-l-4 border-transparent">
                <i class="ph ph-student text-xl w-6"></i><span class="ml-3 font-bold">Siswa</span>
            </a></li>
            <li><a href="<?= $rel ?>petugas/petugas.php" class="<?= isActive($activeIndex, 'petugas') ?> flex items-center px-6 py-3 text-gray-200 hover:bg-popfit-light transition-colors border-l-4 border-transparent">
                <i class="ph ph-user-tie text-xl w-6"></i><span class="ml-3 font-bold">Petugas</span>
            </a></li>

            <li class="px-6 py-2 mt-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Data</li>
            <li><a href="<?= $rel ?>transaksi/transaksi.php" class="<?= isActive($activeIndex, 'transaksi') ?> flex items-center px-6 py-3 text-gray-200 hover:bg-popfit-light transition-colors border-l-4 border-transparent">
                <i class="ph ph-arrows-left-right text-xl w-6"></i><span class="ml-3 font-bold">Transaksi</span>
            </a></li>
            <li><a href="<?= $rel ?>denda/denda.php" class="<?= isActive($activeIndex, 'denda') ?> flex items-center px-6 py-3 text-gray-200 hover:bg-popfit-light transition-colors border-l-4 border-transparent">
                <i class="ph ph-wallet text-xl w-6"></i><span class="ml-3 font-bold">Denda</span>
            </a></li>

            <li class="px-6 py-2 mt-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Sistem</li>
            <li><a href="<?= $rel ?>log/log.php" class="<?= isActive($activeIndex, 'log') ?> flex items-center px-6 py-3 text-gray-200 hover:bg-popfit-light transition-colors border-l-4 border-transparent">
                <i class="ph ph-article text-xl w-6"></i><span class="ml-3 font-bold">Log Aktivitas</span>
            </a></li>
            <li><a href="<?= $rel ?>laporan/laporan.php" class="<?= isActive($activeIndex, 'laporan') ?> flex items-center px-6 py-3 text-gray-200 hover:bg-popfit-light transition-colors border-l-4 border-transparent">
                <i class="ph ph-file-text text-xl w-6"></i><span class="ml-3 font-bold">Laporan</span>
            </a></li>
        </ul>
    </nav>

    <div class="border-t border-popfit-light p-4">
        <div class="flex items-center w-full">
            <div class="w-8 h-8 rounded-sm bg-popfit-accent flex items-center justify-center text-popfit-dark font-black">A</div>
            <div class="ml-3 flex-1 overflow-hidden">
                <p class="text-[12px] font-black text-white truncate uppercase"><?= $adminName ?></p>
                <p class="text-[10px] text-gray-400 truncate uppercase"><?= $adminUsername ?></p>
            </div>
            <a href="<?= $rel ?>../logout.php" class="text-gray-400 hover:text-white transition-colors"><i class="ph ph-sign-out text-xl"></i></a>
        </div>
    </div>
</aside>
