<?php

declare(strict_types=1);

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

final class AccountController extends Controller
{
    public function overview(Request $request): View
    {
        return view('account.overview', ['user' => $request->user()]);
    }

    public function profile(Request $request): View
    {
        return view('account.profile', ['user' => $request->user()]);
    }

    public function security(Request $request): View
    {
        return view('account.security', ['user' => $request->user()]);
    }
}
