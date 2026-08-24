<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\Admin\AdminDashboardSnapshot;
use Illuminate\Contracts\View\View;

final class DashboardController extends Controller
{
    public function __invoke(AdminDashboardSnapshot $snapshot): View
    {
        return view('admin.dashboard', $snapshot->build());
    }
}
