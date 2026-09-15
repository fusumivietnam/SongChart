<?php

declare(strict_types=1);

namespace App\Application\ProductSignals;

use Illuminate\Support\Facades\DB;
use Throwable;

final class RecordSearchProductSignal
{
    /** @param array<string, mixed> $result */
    public function record(string $query, string $entityType, string $sortOrder, int $page, array $result): void
    {
        if (! (bool) config('product-signals.search.enabled', true) || $query === '' || $page !== 1) {
            return;
        }

        $resultCount = max(0, (int) ($result['total'] ?? 0));
        $zeroResultCount = $resultCount === 0 ? 1 : 0;

        try {
            DB::statement(
                <<<'SQL'
                INSERT INTO product_search_daily_aggregates (
                    day,
                    entity_type_filter,
                    sort_order,
                    search_count,
                    zero_result_count,
                    result_count_sum,
                    created_at,
                    updated_at
                ) VALUES (
                    CURRENT_DATE,
                    ?,
                    ?,
                    1,
                    ?,
                    ?,
                    CURRENT_TIMESTAMP,
                    CURRENT_TIMESTAMP
                )
                ON CONFLICT (day, entity_type_filter, sort_order)
                DO UPDATE SET
                    search_count = product_search_daily_aggregates.search_count + 1,
                    zero_result_count = product_search_daily_aggregates.zero_result_count + EXCLUDED.zero_result_count,
                    result_count_sum = product_search_daily_aggregates.result_count_sum + EXCLUDED.result_count_sum,
                    updated_at = CURRENT_TIMESTAMP
                SQL,
                [$entityType, $sortOrder, $zeroResultCount, $resultCount],
            );
        } catch (Throwable $exception) {
            report($exception);
        }
    }
}
