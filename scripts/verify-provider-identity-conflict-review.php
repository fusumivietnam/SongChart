<?php

declare(strict_types=1);

$required = [
    'app/Domain/Providers/Identity/Review/Contracts/IdentityConflictReviewService.php',
    'app/Support/Providers/Identity/Review/EloquentIdentityConflictReviewService.php',
    'app/Models/Providers/Identity/IdentityConflictReview.php',
    'app/Models/Providers/Identity/IdentityConflictDecision.php',
    'database/migrations/2026_08_05_000100_create_identity_conflict_review_tables.php',
    'tests/Feature/Providers/IdentityConflictReviewTest.php',
    'tests/Architecture/ProviderIdentityConflictReviewBoundaryTest.php',
    'docs/providers/IDENTITY_CONFLICT_REVIEW.md',
];

foreach ($required as $file) {
    if (! is_file($file)) {
        fwrite(STDERR, "Missing Stage 15.2 file: {$file}\n");
        exit(1);
    }
}

$service = file_get_contents('app/Support/Providers/Identity/Review/EloquentIdentityConflictReviewService.php');
if (! str_contains($service, 'lockForUpdate') || ! str_contains($service, 'before_snapshot') || ! str_contains($service, 'after_snapshot')) {
    fwrite(STDERR, "Conflict review audit boundary is incomplete.\n");
    exit(1);
}

echo "Provider identity conflict review verification passed.\n";
