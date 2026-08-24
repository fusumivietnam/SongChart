<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$required = [
    'app/Domain/Discovery/Contracts/DiscoveryEntitySource.php',
    'app/Domain/Discovery/Contracts/DiscoveryEditorialStateReader.php',
    'app/Domain/Discovery/Contracts/DiscoveryProjectionWriter.php',
    'app/Domain/Discovery/Contracts/DiscoveryProjectionPipeline.php',
    'app/Application/Discovery/Projections/DefaultDiscoveryProjectionPipeline.php',
    'app/Support/Discovery/EloquentDiscoveryEntitySource.php',
    'app/Support/Discovery/DatabaseDiscoveryProjectionStore.php',
    'app/Jobs/Discovery/BuildDiscoveryProjection.php',
    'app/Console/Commands/RebuildDiscoveryProjectionsCommand.php',
    'tests/Unit/DiscoveryProjectionPipelineTest.php',
];

foreach ($required as $file) {
    if (! is_file($root.'/'.$file)) {
        fwrite(STDERR, "Missing Discovery projection pipeline file: {$file}\n");

        exit(1);
    }
}

$pipeline = (string) file_get_contents($root.'/app/Application/Discovery/Projections/DefaultDiscoveryProjectionPipeline.php');
foreach (['ProviderAdapter', 'Http::', 'request()', 'DB::select', 'DB::raw'] as $forbidden) {
    if (str_contains($pipeline, $forbidden)) {
        fwrite(STDERR, "Forbidden projection pipeline coupling: {$forbidden}\n");

        exit(1);
    }
}

$boundedSignals = [
    'array_slice',
    'private readonly int $batchSize',
    '$this->batchSize < 1 || $this->batchSize > 1000',
    '$this->entities->batches($channel->entityType, $this->batchSize)',
];

foreach ($boundedSignals as $signal) {
    if (! str_contains($pipeline, $signal)) {
        fwrite(STDERR, "Projection pipeline must remain bounded; missing signal: {$signal}\n");

        exit(1);
    }
}

echo "Discovery projection pipeline verification passed.\n";
