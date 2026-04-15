<?php
// layout/header_admin.php
$pageTitle = $pageTitle ?? 'PopFit Admin';
?>
<header class="h-16 bg-popfit-surface border-b border-popfit-border flex items-center justify-between px-6 flex-shrink-0">
    <div class="flex items-center">
        <button id="openSidebar" class="md:hidden mr-4 text-popfit-dark transition-transform hover:scale-110 active:scale-95">
            <i class="ph ph-list text-2xl"></i>
        </button>
        <h2 class="text-lg font-black text-popfit-dark uppercase tracking-tight"><?= $pageTitle ?></h2>
    </div>
    <div class="flex items-center space-x-3">
        <div class="px-3 py-1 bg-popfit-dark text-white text-[9px] font-black uppercase tracking-widest rounded-sm">ADMIN MODE</div>
    </div>
</header>
