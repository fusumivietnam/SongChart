<?php

declare(strict_types=1);

$options = getopt('', ['plan::', 'base::']);
$plan = is_string($options['plan'] ?? null) ? $options['plan'] : getenv('CHANGE_SURFACE_PLAN');
$base = is_string($options['base'] ?? null) ? $options['base'] : getenv('CHANGE_SURFACE_BASE');

if (! is_string($plan) || $plan === '') {
    echo "Change-surface verification skipped: pass --plan=<file> or CHANGE_SURFACE_PLAN for diff enforcement.\n";
    exit(0);
}
if (! is_file($plan)) {
    fwrite(STDERR, "Plan not found: {$plan}\n");
    exit(1);
}

$content = (string) file_get_contents($plan);
$section = static function (string $heading) use ($content): ?string {
    $pattern = '/^'.preg_quote($heading, '/').'\R(.*?)(?=^##\s|\z)/ms';

    return preg_match($pattern, $content, $match) === 1 ? trim($match[1]) : null;
};

$expected = $section('## Expected files');
$incidental = $section('## Allowed incidental files');
$deviations = $section('## Scope deviations');
if ($expected === null || $incidental === null || $deviations === null) {
    fwrite(STDERR, "Plan lacks required change-surface sections.\n");
    exit(1);
}

$extract = static function (string $body): array {
    preg_match_all('/`([^`]+)`/', $body, $matches);

    return array_values(array_unique(array_filter(
        $matches[1] ?? [],
        static fn (string $value): bool => $value !== '' && ! str_contains($value, 'impact-test-map'),
    )));
};

$declared = array_values(array_unique([...$extract($expected), ...$extract($incidental)]));
if ($declared === []) {
    fwrite(STDERR, "Plan declares no expected or incidental files.\n");
    exit(1);
}

if (! is_string($base) || $base === '') {
    echo "Plan structure verified; diff enforcement skipped because no base ref was supplied.\n";
    exit(0);
}

exec('git diff --name-only '.escapeshellarg($base).' --', $changed, $status);
if ($status !== 0) {
    fwrite(STDERR, "Unable to calculate git diff from {$base}.\n");
    exit(1);
}

$matchesPattern = static function (string $file, string $pattern): bool {
    $normalized = str_replace('\\', '/', $pattern);
    if (! str_contains($normalized, '*') && ! str_contains($normalized, '?') && ! str_contains($normalized, '[')) {
        return $file === $normalized;
    }

    return fnmatch($normalized, $file, FNM_PATHNAME);
};

$outside = [];
foreach ($changed as $file) {
    $allowed = false;
    foreach ($declared as $pattern) {
        if ($matchesPattern($file, $pattern)) {
            $allowed = true;
            break;
        }
    }
    if (! $allowed) {
        $outside[] = $file;
    }
}

if ($outside !== [] && preg_match('/^None\.?$/mi', trim($deviations)) === 1) {
    fwrite(STDERR, "Changed files outside declared surface:\n- ".implode("\n- ", $outside)."\n");
    exit(1);
}

echo "Change-surface verification passed.\n";
