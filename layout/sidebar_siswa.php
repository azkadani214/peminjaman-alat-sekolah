<?php
// layout/sidebar_siswa.php
// Expected variables: $siswaName, $siswaUsername, $activeIndex (e.g., 'dashboard', 'alat', 'transaksi', 'denda', 'riwayat')
// Expected constant: BASE_URL or manually passed $rel (e.g., '../../')

$rel = $rel ?? './';
$siswaName = $siswaName ?? 'Siswa';
$siswaUsername = $siswaUsername ?? 'siswa';
$activeIndex = $activeIndex ?? '';

function isActive($current, $target) {
    return $current === $target ? 'nav-active' : '';
}
?>
<!-- SIDEBAR OVERLAY -->
<div id="sidebarOverlay" class="fixed inset-0 bg-black/50 z-40 hidden transition-opacity"></div>

<aside id="sidebar" class="fixed inset-y-0 left-0 w-64 bg-popfit-dark text-white border-r border-popfit-dark h-full flex-shrink-0 z-50 sidebar -translate-x-full md:translate-x-0 md:static flex flex-col transition-transform duration-300">
    <div class="h-16 flex items-center px-6 border-b border-popfit-light bg-popfit-dark justify-between">
        <div class="flex items-center">
            <i class="ph-fill ph-paw-print text-popfit-accent text-2xl mr-3"></i>
            <span class="text-xl font-black tracking-wide uppercase">PopFit</span>
        </div>
        <button id="closeSidebar" class="md:hidden text-gray-400 hover:text-white transition-all active:scale-90"><i class="ph ph-x text-2xl"></i></button>
    </div>

    <nav class="flex-1 overflow-y-auto py-4">
        <ul class="space-y-1">
            <li><a href="<?= $rel ?>dashboardSiswa.php" class="<?= isActive($activeIndex, 'dashboard') ?> flex items-center px-6 py-3 text-gray-200 hover:bg-popfit-light transition-colors border-l-4 border-transparent">
                <i class="ph ph-squares-four text-xl w-6"></i><span class="ml-3 font-bold">Beranda</span>
            </a></li>
            <li class="px-6 py-2 mt-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Peminjaman</li>
            <li><a href="<?= $rel ?>alat/daftarAlat.php" class="<?= isActive($activeIndex, 'alat') ?> flex items-center px-6 py-3 text-gray-200 hover:bg-popfit-light transition-colors border-l-4 border-transparent">
                <i class="ph ph-basketball text-xl w-6"></i><span class="ml-3 font-bold">Cari Alat</span>
            </a></li>
            <li><a href="<?= $rel ?>transaksi/transaksi.php" class="<?= isActive($activeIndex, 'transaksi') ?> flex items-center px-6 py-3 text-gray-200 hover:bg-popfit-light transition-colors border-l-4 border-transparent">
                <i class="ph ph-arrows-left-right text-xl w-6"></i><span class="ml-3 font-bold">Transaksi</span>
            </a></li>
            <li><a href="<?= $rel ?>denda/denda.php" class="<?= isActive($activeIndex, 'denda') ?> flex items-center px-6 py-3 text-gray-200 hover:bg-popfit-light transition-colors border-l-4 border-transparent">
                <i class="ph ph-wallet text-xl w-6"></i><span class="ml-3 font-bold">Denda</span>
            </a></li>
            <li><a href="<?= $rel ?>riwayat/riwayat.php" class="<?= isActive($activeIndex, 'riwayat') ?> flex items-center px-6 py-3 text-gray-200 hover:bg-popfit-light transition-colors border-l-4 border-transparent">
                <i class="ph ph-clock-rotate-left text-xl w-6"></i><span class="ml-3 font-bold">Riwayat</span>
            </a></li>
        </ul>
    </nav>

    <div class="border-t border-popfit-light p-4">
        <div class="flex items-center w-full">
            <div class="w-8 h-8 rounded-sm bg-popfit-accent flex items-center justify-center text-popfit-dark font-black tracking-tighter"><?= strtoupper(substr($siswaName, 0, 1)) ?></div>
            <div class="ml-3 flex-1 overflow-hidden">
                <p class="text-[12px] font-black text-white truncate uppercase"><?= $siswaName ?></p>
                <p class="text-[10px] text-gray-400 truncate uppercase"><?= $siswaUsername ?></p>
            </div>
            <a href="<?= $rel ?>../logout.php" class="text-gray-400 hover:text-white transition-colors"><i class="ph ph-sign-out text-xl"></i></a>
        </div>
    </div>
</aside>

<script>
    (function() {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebarOverlay');
        const openBtn = document.getElementById('openSidebar');
        const closeBtn = document.getElementById('closeSidebar');

        if (openBtn && sidebar && overlay) {
            function toggleSidebar() {
                sidebar.classList.toggle('-translate-x-full');
                overlay.classList.toggle('hidden');
            }

            openBtn.addEventListener('click', toggleSidebar);
            if (closeBtn) closeBtn.addEventListener('click', toggleSidebar);
            overlay.addEventListener('click', toggleSidebar);
        }
    })();
</script>
