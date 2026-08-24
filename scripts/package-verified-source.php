<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$target = $argv[1] ?? ($root.'/songchart-verified-source.zip');

$verify = proc_open(
    [PHP_BINARY, 'scripts/verify-artifact-provenance.php'],
    [STDIN, STDOUT, STDERR],
    $pipes,
    $root,
);
if (! is_resource($verify) || proc_close($verify) !== 0) {
    fwrite(STDERR, "Refusing to package a source tree that is not the exact canonical-verified tree.\n");
    exit(1);
}

if (! class_exists(ZipArchive::class)) {
    fwrite(STDERR, "ZipArchive is required to package the verified source tree.\n");
    exit(1);
}

$zip = new ZipArchive;
if ($zip->open($target, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
    fwrite(STDERR, "Unable to create verified source package [{$target}].\n");
    exit(1);
}

$excludedDirectories = ['.git/', '.songchart-backups/', 'node_modules/', 'storage/', 'vendor/'];
$excludedFiles = ['.env', 'public/hot'];

$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS),
);
foreach ($iterator as $file) {
    if (! $file->isFile()) {
        continue;
    }

    $relative = str_replace('\\', '/', substr($file->getPathname(), strlen($root) + 1));
    if (
        in_array($relative, $excludedFiles, true)
        || (str_starts_with($relative, '.songchart-stage-') && str_ends_with($relative, '-resume.json'))
    ) {
        continue;
    }

    $excluded = false;
    foreach ($excludedDirectories as $directory) {
        if (str_starts_with($relative, $directory)) {
            $excluded = true;
            break;
        }
    }
    if ($excluded) {
        continue;
    }

    if (realpath($file->getPathname()) === realpath($target)) {
        continue;
    }

    $zip->addFile($file->getPathname(), $relative);
}
$zip->close();

$sha = hash_file('sha256', $target);
file_put_contents($target.'.sha256', "{$sha}  ".basename($target).PHP_EOL);

fwrite(STDOUT, "Verified source package created: {$target}\nSHA256: {$sha}\n");
