<?php

declare(strict_types=1);

use App\Domain\Providers\Identity\Review\Contracts\IdentityConflictReviewService;
use App\Support\Providers\Identity\Review\EloquentIdentityConflictReviewService;

it('keeps conflict decisions behind an audited review service', function (): void {
    expect(app(IdentityConflictReviewService::class))->toBeInstanceOf(EloquentIdentityConflictReviewService::class);

    $resolver = file_get_contents(app_path('Support/Providers/Identity/EloquentExactIdentityResolver.php'));
    $service = file_get_contents(app_path('Support/Providers/Identity/Review/EloquentIdentityConflictReviewService.php'));

    expect($resolver)->toContain('IdentityConflictReviewService')
        ->and($service)->toContain('lockForUpdate')
        ->and($service)->toContain('before_snapshot')
        ->and($service)->toContain('after_snapshot');
});
