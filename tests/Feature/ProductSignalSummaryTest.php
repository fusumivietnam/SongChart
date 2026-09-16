<?php

declare(strict_types=1);

use App\Application\ProductSignals\ProductSignalSummary;
use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

function stage27ProductSignalsAdmin(): User
{
    return User::factory()->create([
        'role' => UserRole::SuperAdmin,
        'is_active' => true,
        'email_verified_at' => now(),
        'two_factor_secret' => encrypt('stage-27-product-signals-secret'),
        'two_factor_confirmed_at' => now(),
    ]);
}

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

it('bounds the aggregate reporting window', function (): void {
    expect(app(ProductSignalSummary::class)->forDays(0)['window_days'])->toBe(1)
        ->and(app(ProductSignalSummary::class)->forDays(365)['window_days'])->toBe(90);
});

it('does not invent retention when aggregate evidence is empty', function (): void {
    $summary = app(ProductSignalSummary::class)->forDays(28);

    expect($summary['search_count'])->toBe(0)
        ->and($summary['zero_result_rate'])->toBe(0.0)
        ->and($summary['average_results_per_search'])->toBe(0.0)
        ->and($summary['retention_status'])->toBe('insufficient_evidence');
});

it('renders accepted product signal evidence through the existing admin system surface', function (): void {
    DB::table('product_search_daily_aggregates')->insert([
        'day' => now()->toDateString(),
        'entity_type_filter' => 'all',
        'sort_order' => 'relevance',
        'search_count' => 4,
        'zero_result_count' => 1,
        'result_count_sum' => 12,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $this->actingAs(stage27ProductSignalsAdmin())
        ->get(route('admin.system.index'))
        ->assertOk()
        ->assertSee('data-admin-section="product-signals"', false)
        ->assertSee('data-retention-status="insufficient_evidence"', false)
        ->assertSee('Lượt tìm kiếm')
        ->assertSee('4')
        ->assertSee('25.0%')
        ->assertSee('3.00')
        ->assertSee('no user/session linkage');
});
