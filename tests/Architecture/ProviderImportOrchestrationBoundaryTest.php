<?php

declare(strict_types=1);

use App\Domain\Providers\Ingestion\Enums\ProviderImportRunStatus;
use App\Jobs\Providers\Ingestion\FetchProviderImportPage;
use App\Jobs\Providers\Ingestion\FinalizeProviderImport;
use App\Jobs\Providers\Ingestion\ProcessProviderImportPayload;
use App\Support\Providers\Ingestion\ProviderImportOrchestrator;
use Illuminate\Contracts\Queue\ShouldQueue;

it('keeps provider imports behind queue orchestration boundaries', function (): void {
    expect(is_subclass_of(FetchProviderImportPage::class, ShouldQueue::class))->toBeTrue()
        ->and(is_subclass_of(ProcessProviderImportPayload::class, ShouldQueue::class))->toBeTrue()
        ->and(is_subclass_of(FinalizeProviderImport::class, ShouldQueue::class))->toBeTrue()
        ->and(method_exists(ProviderImportOrchestrator::class, 'resume'))->toBeTrue()
        ->and(ProviderImportRunStatus::Completed->isTerminal())->toBeTrue()
        ->and(ProviderImportRunStatus::Paused->canTransitionTo(ProviderImportRunStatus::Running))->toBeTrue();
});
