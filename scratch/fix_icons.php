<?php

$dir = new RecursiveDirectoryIterator(__DIR__ . '/../');
$ite = new RecursiveIteratorIterator($dir);
$files = new RegexIterator($ite, '/^.+\.php$/i', RecursiveRegexIterator::GET_MATCH);

$new = '<link rel="stylesheet" type="text/css" href="https://unpkg.com/@phosphor-icons/web@2.1.1/src/duotone/style.css" />' . "\n    " . '<script src="https://unpkg.com/@phosphor-icons/web@2.1.1"></script>';

$count = 0;
foreach($files as $file) {
    if (strpos($file[0], 'scratch') !== false) continue; // skip scratch dir
    $content = file_get_contents($file[0]);

    $hasPhosphor = preg_match('/<script\s+src=[\'"]https:\/\/unpkg\.com\/@phosphor-icons\/web[\'"]><\/script>/', $content);
    if ($hasPhosphor && strpos($content, '@2.1.1') === false) {
        $content = preg_replace('/<script\s+src=[\'"]https:\/\/unpkg\.com\/@phosphor-icons\/web[\'"]><\/script>/', $new, $content);
        file_put_contents($file[0], $content);
        echo "Fixed: {$file[0]}\n";
        $count++;
    }
}
echo "Total updated files: $count\n";
