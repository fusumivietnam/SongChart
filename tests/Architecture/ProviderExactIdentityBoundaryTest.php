<?php

declare(strict_types=1);

use App\Domain\Providers\Identity\Contracts\ExactIdentityResolver;
use App\Support\Providers\Identity\EloquentExactIdentityResolver;

it('keeps exact identity resolution behind a deterministic contract', function (): void {
    expect(app(ExactIdentityResolver::class))->toBeInstanceOf(EloquentExactIdentityResolver::class);

    $resolver = file_get_contents(app_path('Support/Providers/Identity/EloquentExactIdentityResolver.php'));
    $mutation = file_get_contents(app_path('Support/Providers/Mutation/EloquentCanonicalMutationAction.php'));

    expect($resolver)
        ->toContain('IdentityResolutionOutcome::Conflict')
        ->toContain('MatchStatus::NeedsReview')
        ->not->toContain('levenshtein')
        ->not->toContain('similar_text')
        ->and($mutation)
        ->toContain('ExactIdentityResolver')
        ->toContain('CanonicalMutationOutcome::Conflict');
});
