<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$errors = [];

$contractPath = $root.'/docs/project/engineering/ai-development-contract.json';
$protocolPath = $root.'/docs/project/engineering/AI_DEVELOPMENT_PROTOCOL.md';

if (! is_file($contractPath) || ! is_file($protocolPath)) {
    $errors[] = 'AI development protocol authority files are missing.';
} else {
    $contract = json_decode((string) file_get_contents($contractPath), true, 512, JSON_THROW_ON_ERROR);
    $protocol = (string) file_get_contents($protocolPath);

    foreach ([
        'php scripts/resolve-repository-impact.php',
        'composer stage:verify',
        'composer canonical:verify',
        'composer release:package',
        'Do not add another verifier',
    ] as $needle) {
        if (! str_contains($protocol, $needle)) {
            $errors[] = "AI development protocol is missing required workflow invariant [{$needle}].";
        }
    }

    if (($contract['documentation_rule'] ?? null) === null) {
        $errors[] = 'AI development contract must define the thin-bootstrap documentation rule.';
    }
}

foreach (['AGENTS.md', 'CLAUDE.md', 'GEMINI.md'] as $bootstrap) {
    $source = (string) file_get_contents($root.'/'.$bootstrap);
    $lines = preg_split('/\R/', trim($source)) ?: [];

    if (count($lines) > 30) {
        $errors[] = "{$bootstrap} must remain a thin bootstrap (maximum 30 lines).";
    }
    foreach (['PROJECT_AUTHORITY.md', 'AI_DEVELOPMENT_PROTOCOL.md', 'composer stage:verify', 'composer canonical:verify'] as $needle) {
        if (! str_contains($source, $needle)) {
            $errors[] = "{$bootstrap} is missing bootstrap pointer [{$needle}].";
        }
    }

    foreach (['Stage 06', 'Stage 07', 'Stage 08', 'Stage 09', 'Stage 10', 'Stage 16.8.2'] as $duplicatedHistoricalRule) {
        if (str_contains($source, $duplicatedHistoricalRule)) {
            $errors[] = "{$bootstrap} duplicates historical implementation rules [{$duplicatedHistoricalRule}].";
        }
    }
}

if ($errors !== []) {
    fwrite(STDERR, "AI development protocol verification failed:\n- ".implode("\n- ", array_unique($errors)).PHP_EOL);
    exit(1);
}

fwrite(STDOUT, "AI development protocol verification passed.\n");
