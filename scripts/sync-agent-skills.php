<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$write = in_array('--write', array_slice($argv, 1), true);

/** @return array<string, mixed> */
function loadSkillContract(string $path): array
{
    $decoded = is_file($path) ? json_decode((string) file_get_contents($path), true) : null;

    return is_array($decoded) ? $decoded : [];
}

/** @return list<string> */
function relativeFiles(string $base): array
{
    if (is_dir($base) === false) {
        return [];
    }

    $files = [];
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($base, FilesystemIterator::SKIP_DOTS));
    foreach ($iterator as $file) {
        if ($file->isFile() === false) {
            continue;
        }

        $relative = substr($file->getPathname(), strlen($base) + 1);
        if ($relative !== false) {
            $files[] = $relative;
        }
    }

    sort($files);

    return $files;
}

$contract = loadSkillContract($root.'/docs/project/engineering/ai-control-plane-contract.json');
$registry = is_array($contract['skill_registry'] ?? null) ? $contract['skill_registry'] : [];
$canonicalRoot = is_string($registry['canonical_root'] ?? null) ? $registry['canonical_root'] : '.agents/skills';
$projectionRoots = is_array($registry['projection_roots'] ?? null) ? $registry['projection_roots'] : [];
$managedSkills = is_array($registry['skills'] ?? null) ? array_keys($registry['skills']) : [];

$drift = [];
$written = [];

foreach ($managedSkills as $skill) {
    if (is_string($skill) === false) {
        continue;
    }

    $sourceDir = $root.'/'.$canonicalRoot.'/'.$skill;
    if (is_dir($sourceDir) === false) {
        $drift[] = 'canonical skill missing: '.$canonicalRoot.'/'.$skill;
        continue;
    }

    foreach (relativeFiles($sourceDir) as $relative) {
        $source = $sourceDir.'/'.$relative;
        $sourceContent = (string) file_get_contents($source);

        foreach ($projectionRoots as $projectionRoot) {
            if (is_string($projectionRoot) === false) {
                continue;
            }

            $target = $root.'/'.$projectionRoot.'/'.$skill.'/'.$relative;
            $targetContent = is_file($target) ? (string) file_get_contents($target) : null;
            if ($targetContent === $sourceContent) {
                continue;
            }

            $label = $projectionRoot.'/'.$skill.'/'.$relative;
            $drift[] = $label;
            if ($write === false) {
                continue;
            }

            $directory = dirname($target);
            if (is_dir($directory) === false && mkdir($directory, 0775, true) === false && is_dir($directory) === false) {
                fwrite(STDERR, 'Unable to create skill projection directory: '.$directory.PHP_EOL);
                exit(1);
            }

            file_put_contents($target, $sourceContent);
            $written[] = $label;
        }
    }
}

$result = [
    'canonical_root' => $canonicalRoot,
    'projection_roots' => array_values(array_filter($projectionRoots, 'is_string')),
    'managed_skills' => array_values(array_filter($managedSkills, 'is_string')),
    'drift_count' => count($drift),
    'drift' => $drift,
    'written_count' => count($written),
    'written' => $written,
    'mode' => $write ? 'write' : 'check',
];

echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL;

if ($write === false && $drift !== []) {
    exit(1);
}
