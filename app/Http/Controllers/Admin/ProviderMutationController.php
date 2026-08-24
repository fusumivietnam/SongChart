<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Provider;
use App\Models\Providers\Ingestion\ProviderImportRun;
use App\Models\User;
use App\Support\Providers\Operations\ProviderMutationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

final class ProviderMutationController extends Controller
{
    public const PROVIDER_MUTATE_USE_CASE = 'admin.providers.mutate';

    public const IMPORT_RECOVER_USE_CASE = 'admin.imports.recover';

    public function provider(Request $request, Provider $provider, ProviderMutationService $mutations): RedirectResponse
    {
        $validated = $request->validate(['action' => ['required', Rule::in(['enable', 'disable', 'retire'])], 'rationale' => ['required', 'string', 'min:10', 'max:2000'], 'idempotency_key' => ['required', 'string', 'max:96']]);
        /** @var User $actor */ $actor = $request->user();
        $mutations->mutateProvider($provider, (string) $validated['action'], $actor, (string) $validated['rationale'], (string) $validated['idempotency_key']);

        return back()->with('status', 'Provider operation completed.');
    }

    public function import(Request $request, ProviderImportRun $run, ProviderMutationService $mutations): RedirectResponse
    {
        $validated = $request->validate(['action' => ['required', Rule::in(['retry', 'resume', 'cancel'])], 'rationale' => ['required', 'string', 'min:10', 'max:2000'], 'idempotency_key' => ['required', 'string', 'max:96']]);
        /** @var User $actor */ $actor = $request->user();
        $mutations->recoverImport($run, (string) $validated['action'], $actor, (string) $validated['rationale'], (string) $validated['idempotency_key']);

        return back()->with('status', 'Import recovery operation completed.');
    }
}
