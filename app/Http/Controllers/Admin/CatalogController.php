<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Domain\Catalog\Enums\VerificationState;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\Admin\CanonicalArtistAdministration;
use App\Support\Admin\CatalogAdministration;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

final class CatalogController extends Controller
{
    public const INDEX_USE_CASE = 'admin.catalog.index';

    public const SHOW_USE_CASE = 'admin.catalog.show';

    public const UPDATE_ARTIST_USE_CASE = 'admin.catalog.artist.update';

    public function index(string $type, Request $request, CatalogAdministration $catalog): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'in:unverified,candidate,verified,disputed,rejected'],
            'sort' => ['nullable', 'in:title,newest,oldest'],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);

        return view('admin.catalog.index', $catalog->index($type, $filters));
    }

    public function show(string $type, string $id, CatalogAdministration $catalog): View
    {
        return view('admin.catalog.show', $catalog->show($type, $id));
    }

    public function updateArtist(Request $request, string $id, CanonicalArtistAdministration $catalog): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'sort_name' => ['nullable', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', Rule::unique('artists', 'slug')->ignore($id)],
            'artist_type' => ['required', 'string', 'max:64'],
            'country_code' => ['nullable', 'string', 'size:2', 'regex:/^[A-Za-z]{2}$/'],
            'verification_state' => ['required', Rule::enum(VerificationState::class)],
            'rationale' => ['required', 'string', 'min:10', 'max:2000'],
        ]);

        $rationale = (string) $validated['rationale'];
        unset($validated['rationale']);
        if (isset($validated['country_code']) && is_string($validated['country_code'])) {
            $validated['country_code'] = strtoupper($validated['country_code']);
        }

        /** @var User $actor */
        $actor = $request->user();
        $artist = $catalog->applyArtistUpdate($id, $validated, $actor, $rationale);

        return redirect()->route('admin.catalog.entities.show', ['type' => 'artist', 'id' => $artist->getKey()])
            ->with('status', 'Artist canonical metadata updated.');
    }
}
