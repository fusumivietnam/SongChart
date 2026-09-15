<?php

declare(strict_types=1);

use App\Support\Engineering\RepositoryContractResolver;

$root = dirname(__DIR__);
require_once $root.'/app/Support/Engineering/RepositoryContractResolver.php';

$errors = [];
$composer = json_decode((string) file_get_contents($root.'/composer.json'), true, 512, JSON_THROW_ON_ERROR);
$scripts = is_array($composer['scripts'] ?? null) ? $composer['scripts'] : [];
$topology = json_decode(
    (string) file_get_contents($root.'/docs/project/engineering/verification-topology.json'),
    true,
    512,
    JSON_THROW_ON_ERROR,
);
$release = (new RepositoryContractResolver($root))->value('release-pipeline');

$stageExpected = $topology['lanes']['stage']['ordered_steps'] ?? null;
$canonicalExpected = $topology['lanes']['canonical']['ordered_steps'] ?? null;

if (($scripts['stage:verify'] ?? null) !== $stageExpected) {
    $errors[] = 'composer stage:verify drifted from verification-topology.json.';
}
if (($scripts['canonical:verify'] ?? null) !== $canonicalExpected) {
    $errors[] = 'composer canonical:verify drifted from verification-topology.json.';
}
foreach (['verify', 'release:verify', 'test:all', 'test:postgres-clean', 'release-contract:verify', 'delivery:verify'] as $removedAlias) {
    if (array_key_exists($removedAlias, $scripts)) {
        $errors[] = "Removed verification alias [{$removedAlias}] must not be restored.";
    }
}
if ($stageExpected !== ($release['stage_steps'] ?? null) || $canonicalExpected !== ($release['canonical_steps'] ?? null)) {
    $errors[] = 'release-pipeline contract and verification topology disagree.';
}

$quality = $scripts['quality:verify'] ?? [];
if (! is_array($quality)) {
    $errors[] = 'quality:verify must remain an ordered static/governance lane.';
} else {
    foreach (($topology['lanes']['quality']['must_not_call'] ?? []) as $forbidden) {
        if (in_array($forbidden, $quality, true)) {
            $errors[] = "quality:verify must not call higher/runtime lane [{$forbidden}].";
        }
    }
}

if (is_array($canonicalExpected) && count(array_keys($canonicalExpected, '@stage:verify', true)) !== 1) {
    $errors[] = 'canonical:verify must call @stage:verify exactly once.';
}

$evidenceReuse = $topology['evidence_reuse'] ?? null;
$ciClose = $topology['lanes']['ci_close'] ?? null;
if (! is_array($evidenceReuse)
    || ($evidenceReuse['enabled'] ?? null) !== true
    || ($evidenceReuse['reusable_lane'] ?? null) !== 'stage'
    || ($evidenceReuse['consumer'] ?? null) !== 'ci_close'
) {
    $errors[] = 'Exact-head CI evidence reuse topology is incomplete.';
}
if (! is_array($ciClose)
    || ($ciClose['owner'] ?? null) !== 'scripts/run-canonical-close.php'
    || ($ciClose['source_lane'] ?? null) !== 'canonical'
    || ($ciClose['reuse_step'] ?? null) !== '@stage:verify'
) {
    $errors[] = 'ci_close must derive canonical-only closure from the canonical topology.';
}

$canonicalClose = (string) file_get_contents($root.'/scripts/run-canonical-close.php');
foreach (['verification-topology.json', "'@stage:verify'", 'composer', 'run-script'] as $required) {
    if (! str_contains($canonicalClose, $required)) {
        $errors[] = "Canonical close adapter is missing required evidence-reuse behavior [{$required}].";
    }
}
if (str_contains($canonicalClose, 'canonical_steps') || str_contains($canonicalClose, 'stage_steps')) {
    $errors[] = 'Canonical close adapter must consume verification topology rather than duplicate release-pipeline step lists.';
}

$canonicalShell = (string) file_get_contents($root.'/scripts/canonical-verify.sh');
if (substr_count($canonicalShell, 'composer canonical:verify') !== 1) {
    $errors[] = 'Canonical shell must call composer canonical:verify exactly once.';
}
foreach (['composer release:verify', 'composer stage:verify', 'composer quality:verify', 'composer test:postgres', 'npm run build'] as $forbidden) {
    if (str_contains($canonicalShell, $forbidden)) {
        $errors[] = "Canonical shell duplicates closure gate [{$forbidden}].";
    }
}
foreach (['composer install --no-interaction --prefer-dist --no-progress', 'npm ci --no-audit --no-fund', 'composer quality:normalize'] as $required) {
    if (! str_contains($canonicalShell, $required)) {
        $errors[] = "Canonical shell is missing preparation step [{$required}].";
    }
}
foreach (['SONGCHART_VERIFICATION_MODE', "mode\" == 'close'", 'php scripts/run-canonical-close.php'] as $requiredCloseMode) {
    if (! str_contains($canonicalShell, $requiredCloseMode)) {
        $errors[] = "Canonical shell is missing close-mode behavior [{$requiredCloseMode}].";
    }
}

$architectureFiles = glob($root.'/tests/Architecture/*.php') ?: [];
foreach ($architectureFiles as $architectureFile) {
    $source = (string) file_get_contents($architectureFile);
    $relative = str_replace('\\', '/', substr($architectureFile, strlen($root) + 1));

    foreach ([
        "['release:verify'])->toContain",
        "['release:verify'] ?? [])->toContain",
        "['verify'] ?? [])->toContain",
        "['preflight_order']",
    ] as $stalePattern) {
        if (str_contains($source, $stalePattern)) {
            $errors[] = "Architecture test retains pre-consolidation verification assumption [{$stalePattern}] in [{$relative}].";
        }
    }
}

if ($errors !== []) {
    fwrite(STDERR, "Verification topology failed:\n- ".implode("\n- ", array_unique($errors)).PHP_EOL);
    exit(1);
}

fwrite(STDOUT, "Verification topology passed: impact/focused/quality/stage/canonical ownership is non-duplicative with exact-head CI evidence reuse.\n");
