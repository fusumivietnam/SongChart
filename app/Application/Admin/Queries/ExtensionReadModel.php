<?php

declare(strict_types=1);

namespace App\Application\Admin\Queries;

use App\Models\Extension;
use Illuminate\Database\Eloquent\Collection;

final class ExtensionReadModel
{
    /** @return Collection<int, Extension> */
    public function index(): Collection
    {
        return Extension::query()
            ->with([
                'releases' => fn ($query) => $query->latest('installed_at'),
                'operations' => fn ($query) => $query->latest()->limit(5),
            ])
            ->orderBy('type')
            ->orderBy('name')
            ->get();
    }

    public function show(Extension $extension): Extension
    {
        return $extension->load([
            'releases' => fn ($query) => $query->latest('installed_at'),
            'operations' => fn ($query) => $query->latest(),
            'snapshots' => fn ($query) => $query->latest(),
        ]);
    }
}
