<?php

declare(strict_types=1);

use App\Domain\Providers\Mutation\Contracts\CanonicalMutationPipeline;
use App\Support\Providers\Mutation\DefaultCanonicalMutationPipeline;

it('keeps canonical mutations behind a tagged pipeline boundary', function (): void {
    expect(app(CanonicalMutationPipeline::class))->toBeInstanceOf(DefaultCanonicalMutationPipeline::class);

    $job = file_get_contents(app_path('Jobs/Providers/Ingestion/ProcessProviderImportPayload.php'));
    expect($job)
        ->toContain('CanonicalMutationPipeline')
        ->toContain('ProviderImportItemStatus::Applied');
});
