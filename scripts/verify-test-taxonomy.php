<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$unitRoot = $root.'/tests/Unit';
$forbidden = [
    'app()',
    'config(',
    'base_path(',
    'DB::',
    'Cache::',
    'Http::',
    'Storage::',
    'Artisan::',
];

$errors = [];
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($unitRoot));
foreach ($iterator as $file) {
    if (! $file->isFile() || $file->getExtension() !== 'php') {
        continue;
    }

    $source = (string) file_get_contents($file->getPathname());
    foreach ($forbidden as $signal) {
        if (str_contains($source, $signal)) {
            $relative = str_replace('\\', '/', substr($file->getPathname(), strlen($root) + 1));
            $errors[] = "{$relative} uses framework-dependent Unit-test signal [{$signal}]";
        }
    }
}

if ($errors !== []) {
    fwrite(STDERR, "Unit test taxonomy verification failed:\n - ".implode("\n - ", $errors)."\n");
    exit(1);
}

echo "Unit test taxonomy verification passed.\n";
