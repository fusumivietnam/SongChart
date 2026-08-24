<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Contracts\Search\SearchCatalog;
use Illuminate\Contracts\View\View;

final class HomeController extends Controller
{
    public function __invoke(SearchCatalog $catalog): View
    {
        return view('home', $catalog->homepage());
    }
}
