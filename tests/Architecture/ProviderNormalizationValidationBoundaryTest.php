<?php

declare(strict_types=1);

use App\Domain\Providers\Normalization\Validation\Contracts\NormalizedProviderEntityValidator;
use App\Support\Providers\Normalization\DefaultNormalizedProviderEntityValidator;

it('binds normalization validation behind a contract', function (): void {
    expect(app(NormalizedProviderEntityValidator::class))
        ->toBeInstanceOf(DefaultNormalizedProviderEntityValidator::class);
});

it('keeps the validator independent of eloquent models', function (): void {
    $source = file_get_contents(app_path('Support/Providers/Normalization/DefaultNormalizedProviderEntityValidator.php'));

    expect($source)->not->toContain('App\\Models\\');
});
