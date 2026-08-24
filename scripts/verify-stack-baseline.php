<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$failures = [];

$requiredAuthorities = [
    'STACK_OVERVIEW.md', 'FRAMEWORK_BASELINE.md', 'PACKAGE_REGISTRY.md',
    'CAPABILITY_OWNERSHIP.md', 'LARAVEL_CONVENTIONS.md', 'FRONTEND_STACK.md',
    'DATABASE_CONVENTIONS.md', 'TESTING_TOOLCHAIN.md', 'STATIC_ANALYSIS.md',
    'PACKAGE_ADOPTION_POLICY.md', 'DEPRECATION_REGISTRY.md',
    'STAGE_12_2_TASK_CONTRACT.md', 'STAGE_12_2_VALIDATION_REPORT.md',
    'stack-manifest.json',
];

foreach ($requiredAuthorities as $authority) {
    if (! is_file($root.'/docs/project/stack/'.$authority)) {
        $failures[] = 'Missing stack authority: '.$authority;
    }
}

try {
    $manifest = json_decode((string) file_get_contents($root.'/docs/project/stack/stack-manifest.json'), true, 512, JSON_THROW_ON_ERROR);
    $composer = json_decode((string) file_get_contents($root.'/composer.json'), true, 512, JSON_THROW_ON_ERROR);
    $package = json_decode((string) file_get_contents($root.'/package.json'), true, 512, JSON_THROW_ON_ERROR);
} catch (JsonException $exception) {
    $failures[] = 'Invalid JSON: '.$exception->getMessage();
    $manifest = $composer = $package = [];
}

$phpPackages = array_merge($composer['require'] ?? [], $composer['require-dev'] ?? []);
foreach ($manifest['required_php_packages'] ?? [] as $required) {
    if (! array_key_exists($required, $phpPackages)) {
        $failures[] = 'Required PHP package is not declared: '.$required;
    }
}

$nodePackages = array_merge($package['dependencies'] ?? [], $package['devDependencies'] ?? []);
foreach ($manifest['required_node_packages'] ?? [] as $required) {
    if (! array_key_exists($required, $nodePackages)) {
        $failures[] = 'Required Node package is not declared: '.$required;
    }
}

foreach ($manifest['forbidden_packages'] ?? [] as $forbidden) {
    if (array_key_exists($forbidden, $phpPackages) || array_key_exists($forbidden, $nodePackages)) {
        $failures[] = 'Forbidden package is declared: '.$forbidden;
    }
}

$capabilities = $manifest['capabilities'] ?? [];
if (count($capabilities) !== count(array_unique(array_keys($capabilities)))) {
    $failures[] = 'Capability names must be unique.';
}

$policies = $manifest['policies'] ?? [];
foreach (['laravel_native_first', 'new_packages_require_review', 'provider_ids_may_be_primary_keys'] as $policy) {
    if (! array_key_exists($policy, $policies)) {
        $failures[] = 'Missing stack policy: '.$policy;
    }
}
if (($policies['provider_ids_may_be_primary_keys'] ?? true) !== false) {
    $failures[] = 'Provider identifiers must not be canonical primary keys.';
}

$scripts = $composer['scripts'] ?? [];
if (($scripts['stack:verify'] ?? null) !== '@php scripts/verify-stack-baseline.php') {
    $failures[] = 'Composer stack:verify command is missing or incorrect.';
}
if (! in_array('@stack:verify', $scripts['quality:verify'] ?? [], true)) {
    $failures[] = 'stack:verify is not included in quality:verify.';
}

$agents = (string) file_get_contents($root.'/AGENTS.md');
$aiProtocol = (string) file_get_contents($root.'/docs/project/engineering/AI_DEVELOPMENT_PROTOCOL.md');
$aiContract = json_decode(
    (string) file_get_contents($root.'/docs/project/engineering/ai-development-contract.json'),
    true,
    512,
    JSON_THROW_ON_ERROR,
);

if (! str_contains($agents, 'AI_DEVELOPMENT_PROTOCOL.md')) {
    $failures[] = 'AGENTS.md must point to the AI development protocol authority.';
}

foreach (['docs/project/stack/STACK_OVERVIEW.md', 'docs/project/stack/CAPABILITY_OWNERSHIP.md'] as $authority) {
    if (! str_contains($aiProtocol, $authority)) {
        $failures[] = "AI development protocol does not require stack authority review [{$authority}].";
    }
}

$beforeImplementation = $aiContract['before_implementation'] ?? [];
if (
    ! is_array($beforeImplementation)
    || ! array_filter(
        $beforeImplementation,
        static fn (mixed $rule): bool => is_string($rule)
            && str_contains($rule, 'STACK_OVERVIEW.md')
            && str_contains($rule, 'CAPABILITY_OWNERSHIP.md'),
    )
) {
    $failures[] = 'AI development contract must encode the conditional stack authority review rule.';
}

if ($failures !== []) {
    foreach ($failures as $failure) {
        fwrite(STDERR, '[FAIL] '.$failure.PHP_EOL);
    }
    exit(1);
}

echo 'Technology stack authority verification passed.'.PHP_EOL;
