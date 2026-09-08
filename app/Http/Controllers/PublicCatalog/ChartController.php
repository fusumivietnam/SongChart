<?php

declare(strict_types=1);

namespace App\Http\Controllers\PublicCatalog;

use App\Application\Chart\Queries\PublicChartProjection;
use App\Http\Controllers\Controller;
use App\Support\Chart\DatabaseChartSnapshotStore;
use Illuminate\Contracts\View\View;

final class ChartController extends Controller
{
    public function show(
        string $chart,
        DatabaseChartSnapshotStore $snapshots,
        PublicChartProjection $projection,
    ): View {
        $snapshot = $snapshots->latest($chart);
        abort_if($snapshot === null, 404);

        return view('charts.show', [
            'chart' => $projection->fromSnapshot($snapshot),
        ]);
    }
}
