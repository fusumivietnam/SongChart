<?php

declare(strict_types=1);

namespace App\Application\ProductSignals;

use Illuminate\Support\Facades\DB;

final class ProductSignalSummary
{
    /** @return array<string, int|float|string> */
    public function forDays(int $days = 28): array
    {
        $windowDays = max(1, min(90, $days));
        $row = DB::table('product_search_daily_aggregates')
            ->whereDate('day', '>=', now()->subDays($windowDays - 1)->toDateString())
            ->selectRaw('COALESCE(SUM(search_count), 0) AS search_count')
            ->selectRaw('COALESCE(SUM(zero_result_count), 0) AS zero_result_count')
            ->selectRaw('COALESCE(SUM(result_count_sum), 0) AS result_count_sum')
            ->first();

        $searchCount = (int) ($row->search_count ?? 0);
        $zeroResultCount = (int) ($row->zero_result_count ?? 0);
        $resultCountSum = (int) ($row->result_count_sum ?? 0);

        return [
            'window_days' => $windowDays,
            'search_count' => $searchCount,
            'zero_result_count' => $zeroResultCount,
            'zero_result_rate' => $searchCount > 0 ? round($zeroResultCount / $searchCount, 4) : 0.0,
            'average_results_per_search' => $searchCount > 0 ? round($resultCountSum / $searchCount, 2) : 0.0,
            'retention_status' => 'insufficient_evidence',
            'retention_reason' => 'Accepted telemetry contains no user/session linkage, so D1/D7 retention cannot be computed without reopening privacy authority.',
        ];
    }
}
