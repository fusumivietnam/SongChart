<?php

declare(strict_types=1);

namespace App\Http\Controllers\Search;

use App\Actions\Search\BuildSearchPage;
use App\Application\ProductSignals\RecordSearchProductSignal;
use App\Http\Controllers\Controller;
use App\Http\Requests\Search\SearchRequest;
use Illuminate\Contracts\View\View;

final class SearchController extends Controller
{
    public const USE_CASE = 'public.search';

    public function __invoke(
        SearchRequest $request,
        BuildSearchPage $action,
        RecordSearchProductSignal $signals,
    ): View {
        $data = $action->handle(
            $request->queryText(),
            $request->entityType(),
            $request->sortOrder(),
            $request->pageNumber(),
        );

        $signals->record(
            $data['query'],
            $data['type'],
            $data['sort'],
            $data['page'],
            $data['result'],
        );

        return view('search.index', $data);
    }
}
