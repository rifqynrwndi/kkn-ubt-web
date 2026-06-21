<?php
$files = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator(__DIR__ . '/resources/views')
);

$pattern = "/storage_url\('\.\s*(\$[^)]+)\)/";
$count = 0;

foreach ($files as $file) {
    if ($file->getExtension() !== 'php') continue;

    $content = file_get_contents($file->getRealPath());
    $newContent = preg_replace($pattern, 'storage_url(${1})', $content, -1, $c);

    if ($c > 0) {
        file_put_contents($file->getRealPath(), $newContent);
        echo "Fixed: {$file->getFilename()} ({$c})\n";
        $count += $c;
    }
}

echo "\nTotal: {$count} replacements\n";

// Also check for remaining broken patterns
foreach ($files as $file) {
    if ($file->getExtension() !== 'php') continue;
    $content = file_get_contents($file->getRealPath());
    if (preg_match("/storage_url\('\./", $content)) {
        echo "WARN: Still broken: {$file->getFilename()}\n";
    }
}

unlink(__FILE__);
