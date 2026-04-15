<?php

$dir = new RecursiveDirectoryIterator(__DIR__ . '/../');
$ite = new RecursiveIteratorIterator($dir);
$files = new RegexIterator($ite, '/^.+\.php$/i', RecursiveRegexIterator::GET_MATCH);

$old_string = '<script src="https://unpkg.com/@phosphor-icons/web"></script>';
$new_string = '<link rel="stylesheet" type="text/css" href="https://unpkg.com/@phosphor-icons/web@2.1.1/src/duotone/style.css" />' . "\n    " . '<script src="https://unpkg.com/@phosphor-icons/web@2.1.1"></script>';

$count = 0;
foreach($files as $file) {
    if (strpos($file[0], 'scratch') !== false) continue;
    $content = file_get_contents($file[0]);

    if (strpos($content, $old_string) !== false) {
        $content = str_replace($old_string, $new_string, $content);
        file_put_contents($file[0], $content);
        echo "Fixed: {$file[0]}\n";
        $count++;
    }
}
echo "Total updated files: $count\n";

// Also fix the AJAX icons in petugas.php if any
$petugas = __DIR__ . '/../dashboardAdmin/petugas/petugas.php';
$p_content = file_get_contents($petugas);
if (strpos($p_content, 'ph-pencil-simple-bold') !== false) {
    $p_content = str_replace('ph ph-pencil-simple-bold', 'ph-bold ph-pencil-simple', $p_content);
    $p_content = str_replace('ph ph-trash-bold', 'ph-bold ph-trash', $p_content);
    file_put_contents($petugas, $p_content);
    echo "Fixed AJAX in petugas.php\n";
}
