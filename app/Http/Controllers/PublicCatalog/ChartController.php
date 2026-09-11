<?php

declare(strict_types=1);

namespace App\Http\Controllers\PublicCatalog;

use App\Application\Chart\Queries\PublicChartProjection;
use App\Http\Controllers\Controller;
use App\Support\Chart\ChartDefinitionRegistry;
use App\Support\Chart\DatabaseChartSnapshotStore;
use Illuminate\Contracts\View\View;
use RuntimeException;

final class ChartController extends Controller
{
    public function show(
        string $chart,
        DatabaseChartSnapshotStore $snapshots,
        PublicChartProjection $projection,
        ChartDefinitionRegistry $definitions,
    ): View {
        try {
            $definition = $definitions->get($chart);
        } catch (RuntimeException) {
            abort(404);
        }

        $snapshot = $snapshots->latest($chart);

        return view('charts.show', [
            'chart' => $snapshot === null
                ? $projection->unavailable($definition)
                : $projection->fromSnapshot($snapshot),
        ]);
    }
}
