<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\Admin\PrivilegedAuditConsole;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

final class PrivilegedAuditController extends Controller
{
    public const INDEX_USE_CASE = 'admin.audit.index';

    public function __invoke(Request $request, PrivilegedAuditConsole $console): View
    {
        return view('admin.audit.index', [
            'activities' => $console->activities($request->only(['event', 'actor'])),
            'filters' => $request->only(['event', 'actor']),
        ]);
    }
}
