<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$errors = [];
$read = static fn (string $path): string => (string) file_get_contents($root.'/'.$path);

$rules = $read('docs/foundation/CODE_GENERATION_RULES.md');
foreach (['Pest TestCase context', 'Static-analysis-safe Pest assertions', 'Persistence enum boundary', 'Datetime boundary', 'Iterable value types', 'Semantic UI assertions', 'Authority dependency closure', 'Contradiction scan', 'Packaging preflight'] as $heading) {
    if (! str_contains($rules, $heading)) {
        $errors[] = "CODE_GENERATION_RULES.md is missing {$heading}.";
    }
}

foreach (['AGENTS.md', 'CLAUDE.md', 'GEMINI.md'] as $file) {
    $source = $read($file);
    if (! str_contains($source, 'CODE_GENERATION_RULES.md')) {
        $errors[] = "{$file} must reference CODE_GENERATION_RULES.md.";
    }
}

$sidebar = $read('resources/views/components/admin/sidebar.blade.php');
if (! str_contains($sidebar, 'data-admin-nav="{{ $item[\'key\'] }}"')) {
    $errors[] = 'Admin navigation must expose stable data-admin-nav selectors.';
}

$uxTest = $read('tests/Feature/AdminOperationsUxTest.php');
foreach (['data-admin-nav="identity-conflicts"', 'data-admin-nav="imports"', 'data-admin-nav="providers"'] as $selector) {
    if (! str_contains($uxTest, $selector)) {
        $errors[] = "AdminOperationsUxTest must assert semantic selector {$selector}.";
    }
}
if (str_contains($uxTest, "assertDontSee('Tác vụ dữ liệu')")) {
    $errors[] = 'Role-aware navigation tests must not use page-wide negative text assertions.';
}

$developmentState = $read('docs/project/DEVELOPMENT_STATE.md');
if (preg_match('/^- Stage\s+`([0-9]+(?:\.[0-9]+)+)\s+—/m', $developmentState, $stageMatch) !== 1) {
    $errors[] = 'Unable to resolve current stage for changed-test guardrails.';
} else {
    $stage = str_replace('.', '_', $stageMatch[1]);
    $taskPath = "docs/foundation/STAGE_{$stage}_TASK_CONTRACT.md";
    if (! is_file($root.'/'.$taskPath)) {
        $errors[] = "Missing current-stage task contract {$taskPath}.";
    } else {
        $task = $read($taskPath);
        $expected = '';
        if (preg_match('/## Expected files\R(.*?)(?:\R## |\z)/s', $task, $expectedMatch) === 1) {
            $expected = $expectedMatch[1];
        }
        preg_match_all('/`(tests\/[^`]+\.php)`/', $expected, $testMatches);
        foreach (array_unique($testMatches[1]) as $relative) {
            if (! is_file($root.'/'.$relative)) {
                continue;
            }
            $source = $read($relative);
            if (str_contains($source, '->not')) {
                $errors[] = $relative.' must not use Pest dynamic ->not expectations; express negative invariants with statically visible boolean predicates.';
            }
            if (preg_match('/is_subclass_of\(\s*[^,]+::class\s*,\s*[^)]+::class\s*\)/', $source) === 1) {
                $errors[] = $relative.' must not use is_subclass_of() with two known class constants; use ReflectionClass or another non-tautological architecture assertion.';
            }
            if (! preg_match('/\$this->(?:get|post|put|patch|delete|actingAs|artisan|assertDatabase[A-Za-z]*)\s*\(/', $source)) {
                continue;
            }
            $hasTestCaseImport = str_contains($source, 'use Tests\\TestCase;');
            $hasTestCaseAnnotation = preg_match('/@var\s+TestCase\s+\$this\b/', $source) === 1;
            $hasFullyQualifiedAnnotation = str_contains($source, '/** @var \\Tests\\TestCase $this */') || str_contains($source, '/** @var Tests\\TestCase $this */');
            if (! $hasTestCaseImport || ! $hasTestCaseAnnotation || $hasFullyQualifiedAnnotation) {
                $hash = hash('sha256', $source);
                $errors[] = $relative.' must import Tests\\TestCase and annotate Laravel Pest helper closures with unqualified /** @var TestCase $this */. Source SHA-256: '.$hash;
            }
        }
    }
}

$composer = json_decode($read('composer.json'), true, 512, JSON_THROW_ON_ERROR);
$scripts = is_array($composer['scripts'] ?? null) ? $composer['scripts'] : [];
foreach (['test' => '@test:postgres'] as $name => $expected) {
    if (($scripts[$name] ?? null) !== $expected) {
        $errors[] = "{$name} must resolve to PostgreSQL.";
    }
}
$stage = $scripts['stage:verify'] ?? [];
$canonical = $scripts['canonical:verify'] ?? [];
if (! is_array($stage) || ! in_array('@test:postgres', $stage, true)) {
    $errors[] = 'stage:verify must include PostgreSQL tests.';
}
if (is_array($stage) && in_array('@test:sqlite:compat', $stage, true)) {
    $errors[] = 'stage:verify must not include SQLite compatibility tests.';
}
if (! is_array($canonical) || count(array_keys($canonical, '@stage:verify', true)) !== 1) {
    $errors[] = 'canonical:verify must invoke stage:verify exactly once.';
}
if (array_key_exists('release:verify', $scripts) || array_key_exists('verify', $scripts) || array_key_exists('test:all', $scripts)) {
    $errors[] = 'Legacy verification aliases must remain removed.';
}

if ($errors !== []) {
    fwrite(STDERR, "Code-generation guardrail verification failed:\n- ".implode("\n- ", array_unique($errors))."\n");
    exit(1);
}

fwrite(STDOUT, "Code-generation guardrail verification passed.\n");
