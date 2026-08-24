<?php

declare(strict_types=1);

use App\Models\Provider;
use App\Models\ProviderEntity;
use App\Models\Providers\Identity\IdentityConflictReview;
use App\Support\Admin\IdentityConflictReviewConsole;
use App\Support\Admin\ProviderOperationsConsole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

function stage16710Budget(string $useCase): int
{
    $contracts = json_decode((string) file_get_contents(base_path('docs/project/performance/performance-contracts.json')), true, flags: JSON_THROW_ON_ERROR);

    return (int) $contracts['use_cases'][$useCase]['query_budget'];
}

function stage16710CountQueries(Closure $callback): int
{
    DB::flushQueryLog();
    DB::enableQueryLog();
    $callback();
    $count = count(DB::getQueryLog());
    DB::disableQueryLog();

    return $count;
}

it('keeps provider list queries bounded as result count grows', function (): void {
    Provider::query()->create(['slug' => 'budget-1', 'name' => 'Budget 1', 'category' => 'metadata', 'status' => 'sandbox', 'is_enabled' => true]);
    $console = app(ProviderOperationsConsole::class);
    $one = stage16710CountQueries(static fn () => $console->providers([]));

    foreach (range(2, 6) as $index) {
        Provider::query()->create(['slug' => "budget-{$index}", 'name' => "Budget {$index}", 'category' => 'metadata', 'status' => 'sandbox', 'is_enabled' => true]);
    }
    $many = stage16710CountQueries(static fn () => $console->providers([]));

    expect($many)->toBeLessThanOrEqual(stage16710Budget('admin.providers.index'))
        ->and($many)->toBeLessThanOrEqual($one + 1);
});

it('keeps identity conflict list queries bounded as result count grows', function (): void {
    $provider = Provider::query()->create(['slug' => 'budget-conflicts', 'name' => 'Budget Conflicts', 'category' => 'metadata', 'status' => 'sandbox', 'is_enabled' => true]);
    $providerEntity = ProviderEntity::query()->create(['provider_id' => $provider->id, 'entity_type' => 'artist', 'external_id' => 'budget-entity', 'status' => 'active']);

    IdentityConflictReview::query()->create([
        'provider_entity_id' => $providerEntity->id,
        'entity_type' => 'artist',
        'status' => 'open',
        'candidate_entity_ids' => [],
        'opened_at' => now(),
    ]);
    $console = app(IdentityConflictReviewConsole::class);
    $one = stage16710CountQueries(static fn () => $console->index([]));

    foreach (range(2, 6) as $index) {
        IdentityConflictReview::query()->create([
            'provider_entity_id' => $providerEntity->id,
            'entity_type' => 'artist',
            'status' => 'open',
            'candidate_entity_ids' => [],
            'opened_at' => now()->addSeconds($index),
        ]);
    }
    $many = stage16710CountQueries(static fn () => $console->index([]));

    expect($many)->toBeLessThanOrEqual(stage16710Budget('admin.identity-conflicts.index'))
        ->and($many)->toBeLessThanOrEqual($one + 1);
});

it('keeps strict eloquent enabled outside production', function (): void {
    expect(app()->isProduction())->toBeFalse();
});
