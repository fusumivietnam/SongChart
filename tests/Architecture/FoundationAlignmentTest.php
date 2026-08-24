<?php

declare(strict_types=1);

use App\Http\Controllers\Admin\ExtensionController;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Extensions\CleanupExtensionRequest;
use App\Http\Requests\Admin\Extensions\InstallExtensionRequest;
use App\Http\Requests\Admin\Extensions\RollbackExtensionRequest;
use App\Http\Requests\Admin\Extensions\UpgradeExtensionRequest;
use App\Http\Requests\Admin\Extensions\UploadExtensionRequest;
use Illuminate\Foundation\Http\FormRequest;

it('keeps admin controllers on the shared controller foundation', function (): void {
    expect(is_subclass_of(ExtensionController::class, Controller::class))->toBeTrue();
});

it('uses form requests at extension write boundaries', function (string $requestClass): void {
    expect(is_subclass_of($requestClass, FormRequest::class))->toBeTrue();
})->with([
    UploadExtensionRequest::class,
    InstallExtensionRequest::class,
    UpgradeExtensionRequest::class,
    RollbackExtensionRequest::class,
    CleanupExtensionRequest::class,
]);

it('does not restore a custom admin middleware alias', function (): void {
    $bootstrap = file_get_contents(base_path('bootstrap/app.php'));
    $routes = file_get_contents(base_path('routes/web.php'));

    expect($bootstrap)
        ->not->toContain('EnsureUserIsAdmin')
        ->not->toContain("'admin' =>");

    expect($routes)
        ->toContain('can:access-admin')
        ->not->toContain("'auth', 'verified', 'admin'");
});

it('keeps controllers free from inline validation', function (): void {
    $controller = file_get_contents(app_path('Http/Controllers/Admin/ExtensionController.php'));

    expect($controller)
        ->not->toContain('->validate(')
        ->not->toContain('Illuminate\\Http\\Request');
});
