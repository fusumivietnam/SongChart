<?php

declare(strict_types=1);

use App\Application\ProductSignals\RecordSearchProductSignal;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

it('aggregates first-page search and zero-result signals without user-level data', function (): void {
    config(['product-signals.search.enabled' => true]);

    $recorder = app(RecordSearchProductSignal::class);
    $recorder->record('beatles', 'all', 'relevance', 1, ['total' => 3]);
    $recorder->record('unknown query', 'all', 'relevance', 1, ['total' => 0]);

    $row = DB::table('product_search_daily_aggregates')
        ->whereDate('day', now()->toDateString())
        ->where('entity_type_filter', 'all')
        ->where('sort_order', 'relevance')
        ->first();

    expect($row)->not->toBeNull()
        ->and((int) $row->search_count)->toBe(2)
        ->and((int) $row->zero_result_count)->toBe(1)
        ->and((int) $row->result_count_sum)->toBe(3);

    $columns = array_map(
        static fn (object $column): string => (string) $column->name,
        DB::select("SELECT column_name AS name FROM information_schema.columns WHERE table_name = 'product_search_daily_aggregates'"),
    );

    expect($columns)
        ->not->toContain('query', 'normalized_query', 'user_id', 'session_id', 'ip_address', 'user_agent');
});

it('uses the application clock for the aggregate day instead of the database server clock', function (): void {
    config(['product-signals.search.enabled' => true]);

    $this->travelTo(Carbon::parse('2030-01-02 00:05:00', config('app.timezone')));

    try {
        app(RecordSearchProductSignal::class)
            ->record('beatles', 'artist', 'relevance', 1, ['total' => 3]);

        $row = DB::table('product_search_daily_aggregates')
            ->where('entity_type_filter', 'artist')
            ->where('sort_order', 'relevance')
            ->first();

        expect($row)->not->toBeNull()
            ->and((string) $row->day)->toBe('2030-01-02')
            ->and((string) $row->created_at)->toStartWith('2030-01-02 00:05:00')
            ->and((string) $row->updated_at)->toStartWith('2030-01-02 00:05:00');
    } finally {
        $this->travelBack();
    }
});

it('does not count pagination, empty searches or disabled telemetry', function (): void {
    $recorder = app(RecordSearchProductSignal::class);

    config(['product-signals.search.enabled' => true]);
    $recorder->record('beatles', 'all', 'relevance', 2, ['total' => 3]);
    $recorder->record('', 'all', 'relevance', 1, ['total' => 0]);

    config(['product-signals.search.enabled' => false]);
    $recorder->record('beatles', 'all', 'relevance', 1, ['total' => 3]);

    expect(DB::table('product_search_daily_aggregates')->count())->toBe(0);
});
