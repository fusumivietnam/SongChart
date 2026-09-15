<?php

declare(strict_types=1);

$root = dirname(__DIR__);

/** @return array<string, mixed> */
function readJsonFile(string $path): array
{
    if (! is_file($path)) {
        return [];
    }

    $decoded = json_decode((string) file_get_contents($path), true);

    return is_array($decoded) ? $decoded : [];
}

/** @return list<string> */
function gitLines(string $root, string $command): array
{
    $output = [];
    $code = 0;
    exec('git -C '.escapeshellarg($root).' '.$command.' 2>/dev/null', $output, $code);

    return $code === 0 ? array_values(array_filter($output, static fn (string $line): bool => $line !== '')) : [];
}

/** @return array<string, string> */
function skillFiles(string $root, string $relativeRoot): array
{
    $base = $root.'/'.$relativeRoot;
    if (! is_dir($base)) {
        return [];
    }

    $files = [];
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($base, FilesystemIterator::SKIP_DOTS));

    foreach ($iterator as $file) {
        if (! $file->isFile()) {
            continue;
        }

        $absolute = $file->getPathname();
        $relative = substr($absolute, strlen($base) + 1);
        if ($relative === false) {
            continue;
        }

        $files[$relative] = hash_file('sha256', $absolute) ?: '';
    }

    ksort($files);

    return $files;
}

/** @return array<string, mixed> */
function hygieneSnapshot(string $root, array $contract, string $currentStage): array
{
    $tracked = gitLines($root, 'ls-files');
    $verifyScripts = array_values(array_filter($tracked, static fn (string $path): bool => str_starts_with($path, 'scripts/verify-') && str_ends_with($path, '.php')));
    $architectureTests = array_values(array_filter($tracked, static fn (string $path): bool => str_starts_with($path, 'tests/Architecture/') && str_ends_with($path, '.php')));
    $legacyStageDocs = array_values(array_filter($tracked, static fn (string $path): bool => str_contains(basename($path), 'STAGE_')));
    $phpFiles = array_values(array_filter($tracked, static fn (string $path): bool => str_ends_with($path, '.php') && (str_starts_with($path, 'app/') || str_starts_with($path, 'scripts/'))));

    $phpLoc = 0;
    foreach ($phpFiles as $path) {
        $lines = @file($root.'/'.$path);
        $phpLoc += is_array($lines) ? count($lines) : 0;
    }

    $skillRegistry = is_array($contract['skill_registry'] ?? null) ? $contract['skill_registry'] : [];
    $canonicalRoot = is_string($skillRegistry['canonical_root'] ?? null) ? $skillRegistry['canonical_root'] : '.agents/skills';
    $projectionRoots = is_array($skillRegistry['projection_roots'] ?? null) ? $skillRegistry['projection_roots'] : [];
    $canonicalSkills = skillFiles($root, $canonicalRoot);
    $skillDrift = [];

    foreach ($projectionRoots as $projectionRoot) {
        if (! is_string($projectionRoot)) {
            continue;
        }

        $projection = skillFiles($root, $projectionRoot);
        foreach ($canonicalSkills as $path => $hash) {
            if (! array_key_exists($path, $projection)) {
                $skillDrift[] = $projectionRoot.'/'.$path.' missing';
            } elseif ($projection[$path] !== $hash) {
                $skillDrift[] = $projectionRoot.'/'.$path.' differs';
            }
        }
    }

    $diff = gitLines($root, 'diff --name-status main...HEAD');
    $added = 0;
    $deleted = 0;
    foreach ($diff as $line) {
        if (str_starts_with($line, 'A\t')) {
            $added++;
        } elseif (str_starts_with($line, 'D\t')) {
            $deleted++;
        }
    }

    $staleSignals = [];
    $learningLedger = $root.'/docs/project/engineering/AI_LEARNING_LEDGER.md';
    if (is_file($learningLedger) && str_contains((string) file_get_contents($learningLedger), 'Stage 18.4 provisional learning queue')) {
        $staleSignals[] = 'AI_LEARNING_LEDGER.md still contains Stage 18.4 provisional queue';
    }

    $consolidation = readJsonFile($root.'/docs/project/engineering/consolidation-plan.json');
    $currentMajor = (int) explode('.', $currentStage)[0];
    if ($currentMajor >= 25) {
        foreach (['retirement_queue', 'automation_backlog'] as $section) {
            $entries = is_array($consolidation[$section] ?? null) ? $consolidation[$section] : [];
            foreach ($entries as $entry) {
                if (! is_array($entry)) {
                    continue;
                }
                $phase = $entry['phase'] ?? $entry['target_phase'] ?? null;
                if (is_string($phase) && preg_match('/(?:^|\D)19(?:\.|\+|\D|$)/', $phase) === 1) {
                    $id = is_string($entry['id'] ?? null) ? $entry['id'] : 'unknown';
                    $staleSignals[] = 'consolidation-plan '.$id.' still targets '.$phase;
                }
            }
        }
    }

    return [
        'tracked_source_files' => count($tracked),
        'branch_added_files_vs_main' => $added,
        'branch_deleted_files_vs_main' => $deleted,
        'custom_php_loc' => $phpLoc,
        'verify_script_count' => count($verifyScripts),
        'architecture_test_count' => count($architectureTests),
        'legacy_stage_document_count' => count($legacyStageDocs),
        'skill_projection_drift_count' => count($skillDrift),
        'skill_projection_drift' => $skillDrift,
        'stale_active_record_count' => count($staleSignals),
        'stale_active_record_signals' => $staleSignals,
    ];
}

$intent = '';
$json = false;
$includeHygiene = false;
$args = array_slice($argv, 1);

for ($i = 0, $count = count($args); $i < $count; $i++) {
    $arg = $args[$i];
    if ($arg === '--json') {
        $json = true;
        continue;
    }
    if ($arg === '--hygiene') {
        $includeHygiene = true;
        continue;
    }
    if ($arg === '--intent') {
        $i++;
        if (! isset($args[$i])) {
            fwrite(STDERR, "Missing value after --intent.\n");
            exit(2);
        }
        $intent = $args[$i];
        continue;
    }
    if (str_starts_with($arg, '--')) {
        fwrite(STDERR, 'Unknown ai brief option: '.$arg."\n");
        exit(2);
    }

    $intent = trim($intent.' '.$arg);
}

if ($intent === '') {
    fwrite(STDERR, "Usage: ./songchart ai brief --intent <text> [--json] [--hygiene]\n");
    exit(2);
}

$contract = readJsonFile($root.'/docs/project/engineering/ai-control-plane-contract.json');
$stagePlan = readJsonFile($root.'/docs/project/engineering/stage-plan.json');
$currentStage = is_string($stagePlan['current_stage']['id'] ?? null) ? $stagePlan['current_stage']['id'] : 'unknown';
$activeTranche = is_string($stagePlan['active_tranche'] ?? null) ? $stagePlan['active_tranche'] : null;

$normalizedIntent = strtolower($intent);
$bestRoute = is_array($contract['default_route'] ?? null) ? $contract['default_route'] : [];
$bestScore = 0;

$routes = is_array($contract['intent_routes'] ?? null) ? $contract['intent_routes'] : [];
foreach ($routes as $route) {
    if (! is_array($route)) {
        continue;
    }

    $score = 0;
    $keywords = is_array($route['keywords'] ?? null) ? $route['keywords'] : [];
    foreach ($keywords as $keyword) {
        if (is_string($keyword) && $keyword !== '' && str_contains($normalizedIntent, strtolower($keyword))) {
            $score += max(1, substr_count(strtolower($keyword), ' ') + 1);
        }
    }

    if ($score > $bestScore) {
        $bestScore = $score;
        $bestRoute = $route;
    }
}

$skillRegistry = is_array($contract['skill_registry']['skills'] ?? null) ? $contract['skill_registry']['skills'] : [];
$selectedSkills = is_array($bestRoute['skills'] ?? null) ? $bestRoute['skills'] : [];
$skillAuthorities = [];
foreach ($selectedSkills as $skill) {
    if (! is_string($skill)) {
        continue;
    }
    $definition = is_array($skillRegistry[$skill] ?? null) ? $skillRegistry[$skill] : [];
    $required = is_array($definition['required_authorities'] ?? null) ? $definition['required_authorities'] : [];
    foreach ($required as $authority) {
        if (is_string($authority)) {
            $skillAuthorities[] = $authority;
        }
    }
}

$routeAuthorities = is_array($bestRoute['authorities'] ?? null) ? $bestRoute['authorities'] : [];
$authorities = array_values(array_unique(array_merge($routeAuthorities, $skillAuthorities)));

$result = [
    'intent' => $intent,
    'current_stage' => $currentStage,
    'active_tranche' => $activeTranche,
    'route' => $bestRoute['id'] ?? 'repository-development',
    'semantic_owner' => $bestRoute['semantic_owner'] ?? 'resolve-before-write',
    'authorities' => $authorities,
    'preferred_paths' => is_array($bestRoute['preferred_paths'] ?? null) ? $bestRoute['preferred_paths'] : [],
    'avoid_new' => is_array($bestRoute['avoid_new'] ?? null) ? $bestRoute['avoid_new'] : [],
    'skills' => $selectedSkills,
    'focused_checks' => is_array($bestRoute['focused_checks'] ?? null) ? $bestRoute['focused_checks'] : [],
    'new_surface_default' => $contract['new_surface_admission']['default_decision'] ?? 'extend_existing',
    'fanout_budget' => $contract['hand_written_fanout_budget'] ?? [],
];

if ($includeHygiene) {
    $result['hygiene'] = hygieneSnapshot($root, $contract, $currentStage);
}

if ($json) {
    echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL;
    exit(0);
}

echo 'SongChart AI Brief'.PHP_EOL;
echo 'Intent:          '.$intent.PHP_EOL;
echo 'Stage:           '.$currentStage.($activeTranche !== null ? ' / '.$activeTranche : '').PHP_EOL;
echo 'Route:           '.($result['route'] ?? 'repository-development').PHP_EOL;
echo 'Semantic owner:  '.($result['semantic_owner'] ?? 'resolve-before-write').PHP_EOL;
echo 'New surface:     '.($result['new_surface_default'] ?? 'extend_existing').PHP_EOL;

echo PHP_EOL.'Authorities'.PHP_EOL;
foreach ($authorities as $authority) {
    echo '  - '.$authority.PHP_EOL;
}

echo PHP_EOL.'Preferred paths'.PHP_EOL;
foreach ($result['preferred_paths'] as $path) {
    echo '  - '.$path.PHP_EOL;
}

echo PHP_EOL.'Avoid creating'.PHP_EOL;
foreach ($result['avoid_new'] as $item) {
    echo '  - '.$item.PHP_EOL;
}

echo PHP_EOL.'Skills'.PHP_EOL;
foreach ($selectedSkills as $skill) {
    echo '  - '.$skill.PHP_EOL;
}

echo PHP_EOL.'Focused checks'.PHP_EOL;
foreach ($result['focused_checks'] as $check) {
    echo '  - '.$check.PHP_EOL;
}

if ($includeHygiene && is_array($result['hygiene'] ?? null)) {
    echo PHP_EOL.'Repository hygiene'.PHP_EOL;
    foreach ($result['hygiene'] as $key => $value) {
        if (is_array($value)) {
            continue;
        }
        echo '  '.str_pad((string) $key, 34).': '.(string) $value.PHP_EOL;
    }
}
