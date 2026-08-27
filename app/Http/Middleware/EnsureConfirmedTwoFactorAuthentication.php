<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class EnsureConfirmedTwoFactorAuthentication
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        $requiresTwoFactor = (string) config('songchart.security.admin_2fa_mode', 'required') === 'required';

        if (! $requiresTwoFactor) {
            return $next($request);
        }

        if ($user === null || ! $user->hasConfirmedTwoFactorAuthentication()) {
            return redirect()
                ->route('account.security')
                ->with('status', 'Confirm two-factor authentication before accessing privileged operations.');
        }

        return $next($request);
    }
}
