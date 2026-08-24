<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$errors = [];
$required = [
    'app/Support/Admin/IdentityConflictReviewConsole.php',
    'app/Http/Controllers/Admin/IdentityConflictReviewController.php',
    'resources/views/admin/identity-conflicts/index.blade.php',
    'resources/views/admin/identity-conflicts/show.blade.php',
    'tests/Feature/IdentityConflictReviewUiTest.php',
    'docs/foundation/history/STAGE_16_7_IDENTITY_CONFLICT_REVIEW_UI_TASK_CONTRACT.md',
    'docs/foundation/history/STAGE_16_7_IDENTITY_CONFLICT_REVIEW_UI_VALIDATION_REPORT.md',
];
foreach ($required as $file) {
    if (! is_file($root.'/'.$file)) {
        $errors[] = "Missing {$file}.";
    }
}

$routes = (string) file_get_contents($root.'/routes/web.php');
foreach (["name('identity-conflicts.show')", "name('identity-conflicts.decide')", 'can:manage-identity-conflicts', 'password.confirm'] as $needle) {
    if (! str_contains($routes, $needle)) {
        $errors[] = "routes/web.php missing {$needle}.";
    }
}

$provider = (string) file_get_contents($root.'/app/Providers/AuthorizationServiceProvider.php');
$capabilities = (string) file_get_contents($root.'/app/Enums/Capability.php');
$authorization = json_decode(
    (string) file_get_contents($root.'/docs/project/security/authorization-contract.json'),
    true,
    512,
    JSON_THROW_ON_ERROR,
);

if (! str_contains($capabilities, "case ManageIdentityConflicts = 'manage-identity-conflicts';")
    || ! str_contains($provider, 'foreach (Capability::cases() as $capability)')
    || ! str_contains($provider, 'AuthorizationMatrix::class')
    || ! in_array('manage-identity-conflicts', $authorization['roles']['reviewer'] ?? [], true)
    || ! in_array('manage-identity-conflicts', $authorization['roles']['super_admin'] ?? [], true)
    || in_array('manage-identity-conflicts', $authorization['roles']['system_operator'] ?? [], true)
) {
    $errors[] = 'Identity conflict mutation capability must resolve through the shared authorization authority.';
}

$controller = (string) file_get_contents($root.'/app/Http/Controllers/Admin/IdentityConflictReviewController.php');
foreach (['admin.identity-conflicts.index', 'admin.identity-conflicts.show', 'admin.identity-conflicts.decide', 'IdentityConflictReviewService'] as $needle) {
    if (! str_contains($controller, $needle)) {
        $errors[] = "IdentityConflictReviewController missing {$needle}.";
    }
}
if (str_contains($controller, 'DB::') || str_contains($controller, '->update(') || str_contains($controller, '->create(')) {
    $errors[] = 'IdentityConflictReviewController must not bypass the audited domain review service.';
}

$contracts = json_decode((string) file_get_contents($root.'/docs/project/domain/use-case-contracts.json'), true);
$decision = $contracts['use_cases']['admin.identity-conflicts.decide'] ?? null;
if (! is_array($decision) || ($decision['read_only'] ?? true) !== false || ($decision['support_surface_writes'] ?? []) === []) {
    $errors[] = 'Identity decision use-case must explicitly declare operational writes.';
}

if ($errors !== []) {
    fwrite(STDERR, "Identity conflict review UI verification failed:\n- ".implode("\n- ", $errors)."\n");
    exit(1);
}

fwrite(STDOUT, "Identity conflict review UI verification passed.\n");
