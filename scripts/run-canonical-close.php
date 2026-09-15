<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$topologyPath = $root.'/docs/project/engineering/verification-topology.json';
$topology = json_decode((string) file_get_contents($topologyPath), true, 512, JSON_THROW_ON_ERROR);
$steps = $topology['lanes']['canonical']['ordered_steps'] ?? null;

if (! is_array($steps)) {
    fwrite(STDERR, "Canonical verification topology is missing ordered_steps.\n");
    exit(1);
}

$stageCount = count(array_keys($steps, '@stage:verify', true));
if ($stageCount !== 1) {
    fwrite(STDERR, "Canonical verification topology must contain @stage:verify exactly once before CI evidence can be reused.\n");
    exit(1);
}

foreach ($steps as $step) {
    if ($step === '@stage:verify') {
        fwrite(STDOUT, "[SongChart close] Reusing exact-head CHECK evidence for stage verification.\n");
        continue;
    }

    if (! is_string($step) || ! str_starts_with($step, '@') || str_contains($step, ' ')) {
        fwrite(STDERR, "Unsupported canonical close step [".(is_scalar($step) ? (string) $step : gettype($step))."].\n");
        exit(1);
    }

    $script = substr($step, 1);
    fwrite(STDOUT, "[SongChart close] Running {$script}.\n");
    $process = proc_open(
        ['composer', '--no-interaction', 'run-script', $script],
        [STDIN, STDOUT, STDERR],
        $pipes,
        $root,
    );

    if (! is_resource($process) || proc_close($process) !== 0) {
        fwrite(STDERR, "Canonical close step failed [{$script}].\n");
        exit(1);
    }
}

fwrite(STDOUT, "[SongChart close] Canonical-only closure PASSED.\n");
