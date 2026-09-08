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

$controlPlaneAuthorities = [
    'CONTROL_PLANE.md',
    'COMPATIBILITY_POLICY.md',
    'project-control-plane.json',
    'pre-data-freeze.json',
    'resilience-matrix.json',
];

foreach ($controlPlaneAuthorities as $authority) {
    if (! is_file($root.'/docs/project/governance/'.$authority)) {
        $failures[] = 'Missing control-plane authority: '.$authority;
    }
}

if (! is_file($root.'/docs/project/engineering/roadmap.json')) {
    $failures[] = 'Missing repository-native roadmap authority.';
}

try {
    $manifest = json_decode((string) file_get_contents($root.'/docs/project/stack/stack-manifest.json'), true, 512, JSON_THROW_ON_ERROR);
    $composer = json_decode((string) file_get_contents($root.'/composer.json'), true, 512, JSON_THROW_ON_ERROR);
    $package = json_decode((string) file_get_contents($root.'/package.json'), true, 512, JSON_THROW_ON_ERROR);
    $controlPlane = json_decode((string) file_get_contents($root.'/docs/project/governance/project-control-plane.json'), true, 512, JSON_THROW_ON_ERROR);
    $preData = json_decode((string) file_get_contents($root.'/docs/project/governance/pre-data-freeze.json'), true, 512, JSON_THROW_ON_ERROR);
    $resilience = json_decode((string) file_get_contents($root.'/docs/project/governance/resilience-matrix.json'), true, 512, JSON_THROW_ON_ERROR);
    $roadmap = json_decode((string) file_get_contents($root.'/docs/project/engineering/roadmap.json'), true, 512, JSON_THROW_ON_ERROR);
} catch (JsonException $exception) {
    $failures[] = 'Invalid JSON: '.$exception->getMessage();
    $manifest = $composer = $package = $controlPlane = $preData = $resilience = $roadmap = [];
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
foreach (['laravel_native_first', 'new_packages_require_review', 'provider_ids_may_be_primary_keys', 'node_runtime_major', 'release_database_major'] as $policy) {
    if (! array_key_exists($policy, $policies)) {
        $failures[] = 'Missing stack policy: '.$policy;
    }
}
if (($policies['provider_ids_may_be_primary_keys'] ?? true) !== false) {
    $failures[] = 'Provider identifiers must not be canonical primary keys.';
}

$nodeMajor = $policies['node_runtime_major'] ?? null;
if (! is_int($nodeMajor) || $nodeMajor < 20) {
    $failures[] = 'Node runtime authority must declare a supported current major target.';
}

$postgresMajor = $policies['release_database_major'] ?? null;
if (! is_int($postgresMajor) || $postgresMajor < 14) {
    $failures[] = 'Release PostgreSQL authority must declare a supported current major target.';
}

if (($manifest['version_semantics'] ?? null) !== 'runtime and framework versions are current approved targets, not permanent architecture invariants') {
    $failures[] = 'Stack manifest must declare version targets as lifecycle-managed implementation state.';
}

$dockerfile = is_file($root.'/docker/verify/Dockerfile') ? (string) file_get_contents($root.'/docker/verify/Dockerfile') : '';
$workflow = is_file($root.'/.github/workflows/tests.yml') ? (string) file_get_contents($root.'/.github/workflows/tests.yml') : '';
if (is_int($nodeMajor)) {
    if (! str_contains($dockerfile, 'FROM node:'.$nodeMajor.'-bookworm-slim@sha256:')) {
        $failures[] = "Docker verification/development runtime must use digest-pinned approved Node {$nodeMajor}.";
    }
    if (substr_count($workflow, "node-version: '{$nodeMajor}'") < 2) {
        $failures[] = "GitHub browser and frontend-build jobs must use approved Node {$nodeMajor}.";
    }
}

$lifecycleStates = ['proposed', 'experimental', 'adopted', 'compatibility', 'deprecated', 'retired'];
$actions = ['keep', 'harden', 'upgrade', 'migrate', 'replace', 'retire', 'investigate'];
$stabilityClasses = ['locked', 'durable', 'extensible', 'replaceable', 'experimental'];
$riskClasses = ['R0', 'R1', 'R2', 'R3', 'R4'];

foreach ($controlPlane['components'] ?? [] as $id => $component) {
    if (! in_array($component['lifecycle'] ?? null, $lifecycleStates, true)) {
        $failures[] = "Control-plane component [{$id}] has invalid lifecycle.";
    }
    if (! in_array($component['action'] ?? null, $actions, true)) {
        $failures[] = "Control-plane component [{$id}] has invalid action.";
    }
    if (! in_array($component['stability'] ?? null, $stabilityClasses, true)) {
        $failures[] = "Control-plane component [{$id}] has invalid stability class.";
    }
    if (! in_array($component['risk'] ?? null, $riskClasses, true)) {
        $failures[] = "Control-plane component [{$id}] has invalid risk class.";
    }
    if (($component['lifecycle'] ?? null) === 'compatibility' && ($component['new_feature_target'] ?? false) !== false) {
        $failures[] = "Compatibility component [{$id}] must not be a new-feature target.";
    }
}

if (($preData['state'] ?? null) === 'data-bearing' && ! in_array('schema-baseline-approved', $preData['states'] ?? [], true)) {
    $failures[] = 'Data-bearing lifecycle must retain schema-baseline-approved predecessor state.';
}

foreach (['light', 'standard', 'full'] as $profile) {
    if (! isset($resilience['execution_profiles'][$profile])) {
        $failures[] = "Missing resilience execution profile [{$profile}].";
    }
}
if (($resilience['capabilities']['development_environment']['codespaces_required'] ?? true) !== false) {
    $failures[] = 'Codespaces must remain an optional development adapter, not continuity authority.';
}
if (($resilience['quota_policy']['never_skip_required_semantic_verification_due_to_quota'] ?? false) !== true) {
    $failures[] = 'Quota fallback must not weaken required semantic verification.';
}

$stage22 = array_values(array_filter(
    $roadmap['stages'] ?? [],
    static fn (mixed $stage): bool => is_array($stage) && ($stage['id'] ?? null) === '22.0',
));
if (count($stage22) !== 1 || ($stage22[0]['status'] ?? null) !== 'committed') {
    $failures[] = 'Roadmap must contain exactly one committed Stage 22.0 entry.';
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

echo 'Technology stack and system control-plane authority verification passed.'.PHP_EOL;
