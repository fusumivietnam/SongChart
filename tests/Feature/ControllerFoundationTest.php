<?php

declare(strict_types=1);

use App\Http\Controllers\Account\AccountController;
use App\Http\Controllers\Controller;

it('provides the shared application controller foundation', function (): void {
    expect(class_exists(Controller::class))->toBeTrue()
        ->and(is_subclass_of(AccountController::class, Controller::class))->toBeTrue();
});
