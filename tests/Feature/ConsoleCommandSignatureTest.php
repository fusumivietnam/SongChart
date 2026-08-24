<?php

declare(strict_types=1);

use App\Console\Commands\RollbackExtensionCommand;

test('extension rollback command avoids Symfony global option collisions', function (): void {
    $command = app(RollbackExtensionCommand::class);

    expect($command->getDefinition()->hasOption('release'))->toBeTrue()
        ->and($command->getDefinition()->hasOption('version'))->toBeFalse();
});
