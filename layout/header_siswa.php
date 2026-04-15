<?php
// layout/header_siswa.php
// Expected variables: $pageTitle, $connect, $id_user, $rel (e.g., './')

$rel = $rel ?? './';
$pageTitle = $pageTitle ?? 'PopFit Siswa';

// Count items in keranjang
$countKeranjang = 0;
if(isset($id_user)) {
    $qK = mysqli_query($connect, "SELECT COUNT(*) as total FROM keranjang WHERE id_user = '$id_user'");
    $countKeranjang = mysqli_fetch_assoc($qK)['total'] ?? 0;
}
?>
<header class="h-16 bg-popfit-surface border-b border-popfit-border flex items-center justify-between px-6 flex-shrink-0">
    <div class="flex items-center">
        <button id="openSidebar" class="md:hidden mr-4 text-popfit-dark transition-transform hover:scale-110 active:scale-95">
            <i class="ph ph-list text-2xl"></i>
        </button>
        <h2 class="text-lg font-black text-popfit-dark uppercase tracking-tight"><?= $pageTitle ?></h2>
    </div>
    
    <div class="flex items-center space-x-5">
        <a href="<?= $rel ?>keranjang/keranjang.php" class="relative group">
            <div class="w-10 h-10 flex items-center justify-center rounded-sm bg-popfit-bg border border-popfit-border group-hover:border-popfit-dark transition-all">
                <i class="ph ph-shopping-cart text-xl text-popfit-textMuted group-hover:text-popfit-dark"></i>
            </div>
            <?php if($countKeranjang > 0): ?>
            <span class="absolute -top-1.5 -right-1.5 bg-popfit-accent text-popfit-dark text-[10px] font-black w-5 h-5 flex items-center justify-center rounded-full border-2 border-white shadow-sm ring-1 ring-black/5 animate-in zoom-in duration-300">
                <?= $countKeranjang ?>
            </span>
            <?php endif; ?>
        </a>
    </div>
</header>
