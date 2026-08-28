<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\Admin\PrivilegedUserAdministration;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use LogicException;

final class UserAdministrationController extends Controller
{
    public function role(
        User $user,
        Request $request,
        PrivilegedUserAdministration $administration,
    ): RedirectResponse {
        $validated = $request->validate([
            'role' => ['required', Rule::enum(UserRole::class)],
            'rationale' => ['required', 'string', 'max:500'],
        ]);

        /** @var User $actor */
        $actor = $request->user();

        try {
            $administration->setRole(
                target: $user,
                role: UserRole::from((string) $validated['role']),
                actor: $actor,
                rationale: (string) $validated['rationale'],
            );
        } catch (LogicException $exception) {
            return back()->withErrors(['user' => $exception->getMessage()]);
        }

        return back()->with('status', 'Vai trò người dùng đã được cập nhật.');
    }

    public function active(
        User $user,
        Request $request,
        PrivilegedUserAdministration $administration,
    ): RedirectResponse {
        $validated = $request->validate([
            'active' => ['required', 'boolean'],
            'rationale' => ['required', 'string', 'max:500'],
        ]);

        /** @var User $actor */
        $actor = $request->user();

        try {
            $administration->setActive(
                target: $user,
                active: (bool) $validated['active'],
                actor: $actor,
                rationale: (string) $validated['rationale'],
            );
        } catch (LogicException $exception) {
            return back()->withErrors(['user' => $exception->getMessage()]);
        }

        return back()->with('status', 'Trạng thái tài khoản đã được cập nhật.');
    }
}
