<?php

declare(strict_types=1);

namespace App\Actions\Admin\Extensions;

use App\Extensions\ExtensionPreflight;
use App\Extensions\PreflightResult;
use Illuminate\Http\UploadedFile;

final readonly class InspectExtensionPackage
{
    public function __construct(private ExtensionPreflight $preflight) {}

    /** @return array{result:PreflightResult,stored:string} */
    public function handle(UploadedFile $package, string $type): array
    {
        $stored = $package->store('extensions/uploads');

        return [
            'result' => $this->preflight->inspect(storage_path('app/private/'.$stored), $type),
            'stored' => $stored,
        ];
    }
}
