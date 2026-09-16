<?php

declare(strict_types=1);

use App\Application\ProductSignals\ProductSignalSummary;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

it('summarizes only accepted aggregate search evidence', function (): void {
    DB::table('product_search_daily_aggregates')->insert([
        [
            'day' => now()->toDateString(),
            'entity_type_filter' => 'all',
            'sort_order' => 'relevance',
            'search_count' => 8,
            'zero_result_count' => 2,
            'result_count_sum' => 40,
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'day' => now()->subDays(3)->toDateString(),
            'entity_type_filter' => 'artist',
            'sort_order' => 'relevance',
            'search_count' => 2,
            'zero_result_count' => 1,
            'result_count_sum' => 4,
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'day' => now()->subDays(40)->toDateString(),
            'entity_type_filter' => 'all',
            'sort_order' => 'relevance',
            'search_count' => 50,
            'zero_result_count' => 50,
            'result_count_sum' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ]);

    $summary = app(ProductSignalSummary::class)->forDays(28);

    expect($summary)
        ->toMatchArray([
            'window_days' => 28,
            'search_count' => 10,
            'zero_result_count' => 3,
            'zero_result_rate' => 0.3,
            'average_results_per_search' => 4.4,
            'retention_status' => 'insufficient_evidence',
        ])
        ->and($summary['retention_reason'])
        ->toContain('no user/session linkage');
});

it('does not invent retention when aggregate evidence is empty', function (): void {
    $summary = app(ProductSignalSummary::class)->forDays(28);

    expect($summary['search_count'])->toBe(0)
        ->and($summary['zero_result_rate'])->toBe(0.0)
        ->and($summary['average_results_per_search'])->toBe(0.0)
        ->and($summary['retention_status'])->toBe('insufficient_evidence');
});
