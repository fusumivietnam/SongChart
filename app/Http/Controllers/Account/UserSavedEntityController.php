<?php

declare(strict_types=1);

namespace App\Http\Controllers\Account;

use App\Application\UserLibrary\SavedEntityLibrary;
use App\Application\UserLibrary\SaveEntity;
use App\Domain\Catalog\Enums\EntityType;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class UserSavedEntityController extends Controller
{
    public function index(Request $request, SavedEntityLibrary $library): View
    {
        /** @var User $user */
        $user = $request->user();

        return view('account.saved', [
            'user' => $user,
            'savedEntities' => $library->forUser($user),
        ]);
    }

    public function store(Request $request, string $type, string $id, SaveEntity $action): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        $action->add($user, EntityType::from($type), $id);

        return back()->with('status', 'saved-entity-added');
    }

    public function destroy(Request $request, string $type, string $id, SaveEntity $action): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        $action->remove($user, EntityType::from($type), $id);

        return back()->with('status', 'saved-entity-removed');
    }
}
