<?php

declare(strict_types=1);

$roots = ['app', 'bootstrap', 'config', 'database/migrations', 'routes'];
$extensions = ['php'];
$pattern = '/\b(TODO|FIXME|TBD|IMPLEMENT LATER|QUICK FIX)\b/i';
$violations = [];
foreach ($roots as $root) {
    if (! is_dir($root)) {
        continue;
    }
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS));
    foreach ($iterator as $file) {
        if (! $file->isFile() || ! in_array(strtolower($file->getExtension()), $extensions, true)) {
            continue;
        }
        $lines = file($file->getPathname(), FILE_IGNORE_NEW_LINES) ?: [];
        foreach ($lines as $index => $line) {
            if (preg_match($pattern, $line) === 1) {
                $violations[] = $file->getPathname().':'.($index + 1);
            }
        }
    }
}
if ($violations !== []) {
    fwrite(STDERR, "Unmanaged production placeholders:\n- ".implode("\n- ", $violations)."\n");
    exit(1);
}
echo "No unmanaged production placeholders detected.\n";
