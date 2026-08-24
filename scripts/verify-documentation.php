<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$errors = [];

$readmePath = $root.'/README.md';
if (is_file($readmePath)) {
    $readme = (string) file_get_contents($readmePath);
    $currentStageHeadings = preg_match_all('/^## Current development stage$/m', $readme);
    if ($currentStageHeadings !== 1) {
        $errors[] = sprintf('README.md must contain exactly one current development stage heading; found %d.', $currentStageHeadings);
    }
}

$requiredFiles = [
    'AGENTS.md',
    'README.md',
    'docs/START_HERE.md',
    'docs/DOCUMENTATION_GOVERNANCE.md',
    'docs/DOCUMENTATION_INDEX.md',
    'docs/project/docs/ARCHITECTURE.md',
    'docs/project/docs/ENGINEERING_RULES.md',
    'docs/project/docs/WORKFLOW.md',
    'docs/project/docs/SECURITY.md',
    'docs/project/docs/TESTING.md',
    'docs/project/docs/DECISIONS.md',
    'docs/foundation/STAGE_11_FOUNDATION_BASELINE.md',
    'docs/foundation/STAGE_11_IMPLEMENTATION_STATUS.md',
];

foreach ($requiredFiles as $relativePath) {
    if (! is_file($root.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $relativePath))) {
        $errors[] = "Missing required documentation file: {$relativePath}";
    }
}

$prohibitedRootAuthorities = [
    'ARCHITECTURE.md',
    'SECURITY.md',
    'WORKFLOW.md',
    'TESTING.md',
    'DECISIONS.md',
];

foreach ($prohibitedRootAuthorities as $filename) {
    if (is_file($root.DIRECTORY_SEPARATOR.$filename)) {
        $errors[] = "Duplicate root authority is prohibited: {$filename}";
    }
}

$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($root.DIRECTORY_SEPARATOR.'docs', FilesystemIterator::SKIP_DOTS)
);

/** @var SplFileInfo $file */
foreach ($iterator as $file) {
    if (! $file->isFile() || strtolower($file->getExtension()) !== 'md') {
        continue;
    }

    $contents = file_get_contents($file->getPathname());

    if ($contents === false) {
        $errors[] = 'Unable to read: '.str_replace($root.DIRECTORY_SEPARATOR, '', $file->getPathname());

        continue;
    }

    preg_match_all('/\[[^\]]+\]\(([^)]+)\)/', $contents, $matches);

    foreach ($matches[1] as $target) {
        $target = trim($target);

        if ($target === '' || str_starts_with($target, '#') || preg_match('/^[a-z][a-z0-9+.-]*:/i', $target) === 1) {
            continue;
        }

        $path = rawurldecode(explode('#', $target, 2)[0]);

        if ($path === '') {
            continue;
        }

        $resolved = str_starts_with($path, '/')
            ? $root.DIRECTORY_SEPARATOR.ltrim(str_replace('/', DIRECTORY_SEPARATOR, $path), DIRECTORY_SEPARATOR)
            : $file->getPath().DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $path);

        if (! file_exists($resolved)) {
            $relativeFile = str_replace($root.DIRECTORY_SEPARATOR, '', $file->getPathname());
            $errors[] = "Broken local Markdown link in {$relativeFile}: {$target}";
        }
    }
}

if ($errors !== []) {
    fwrite(STDERR, "Documentation verification failed:\n- ".implode("\n- ", array_unique($errors))."\n");
    exit(1);
}

fwrite(STDOUT, "Documentation verification passed.\n");
