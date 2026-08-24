<?php

declare(strict_types=1);

use App\Support\Engineering\RepositoryContractResolver;

$root = dirname(__DIR__);
require_once $root.'/app/Support/Engineering/RepositoryContractResolver.php';

$errors = [];

try {
    $resolver = new RepositoryContractResolver($root);

    foreach ($resolver->authorityNames() as $name) {
        $authority = $resolver->authority($name);
        if (! is_string($authority['source'] ?? null) || ! is_string($authority['fingerprint'] ?? null)) {
            $errors[] = "Authority [{$name}] did not resolve to a source and fingerprint.";
        }

        foreach (($authority['consumers'] ?? []) as $consumer) {
            if (! is_file($root.'/'.$consumer)) {
                $errors[] = "Authority [{$name}] registered missing consumer [{$consumer}].";
            }
        }
    }

    $composer = json_decode((string) file_get_contents($root.'/composer.json'), true, 512, JSON_THROW_ON_ERROR);
    $composerScripts = is_array($composer['scripts'] ?? null) ? $composer['scripts'] : [];

    $databaseScripts = $resolver->value('database-test', 'scripts');
    if (! is_array($databaseScripts)) {
        $errors[] = 'database-test scripts authority must resolve to an object.';
    } else {
        foreach (['test', 'test:feature', 'test:postgres'] as $name) {
            if (($composerScripts[$name] ?? null) !== ($databaseScripts[$name] ?? null)) {
                $errors[] = "Composer script [{$name}] drifted from executable database-test authority.";
            }
        }
    }

    $releasePipeline = $resolver->value('release-pipeline');
    if (($composerScripts['stage:verify'] ?? null) !== ($releasePipeline['stage_steps'] ?? null)) {
        $errors[] = 'Composer stage:verify drifted from executable release-pipeline authority.';
    }
    if (($composerScripts['canonical:verify'] ?? null) !== ($releasePipeline['canonical_steps'] ?? null)) {
        $errors[] = 'Composer canonical:verify drifted from executable release-pipeline authority.';
    }
    foreach (['verify', 'release:verify', 'test:all', 'test:postgres-clean', 'release-contract:verify', 'delivery:verify'] as $removedAlias) {
        if (array_key_exists($removedAlias, $composerScripts)) {
            $errors[] = "Removed Composer alias [{$removedAlias}] must not be restored.";
        }
    }

    foreach ($resolver->staleConsumers() as $violation) {
        $errors[] = sprintf(
            '%s: %s contains %s; resolve authority through RepositoryContractResolver instead.',
            $violation['authority'],
            $violation['consumer'],
            $violation['reason'],
        );
    }

    foreach ($resolver->verificationConsumerGraphViolations() as $violation) {
        $errors[] = $violation;
    }

    foreach ($resolver->verifierExecutionOwners() as $violation) {
        $errors[] = $violation;
    }

    $compiled = $resolver->compileManifest();
    $storedPath = $root.'/docs/project/generated/repository-contract-manifest.json';
    if (! is_file($storedPath)) {
        $errors[] = 'Compiled repository contract manifest is missing.';
    } else {
        $stored = json_decode((string) file_get_contents($storedPath), true, 512, JSON_THROW_ON_ERROR);
        if (($stored['graph_fingerprint'] ?? null) !== ($compiled['graph_fingerprint'] ?? null)) {
            $errors[] = 'Compiled repository contract graph fingerprint is stale.';
        }
    }
} catch (Throwable $exception) {
    $errors[] = $exception->getMessage();
}

if ($errors !== []) {
    fwrite(STDERR, "Executable repository authority verification failed:\n- ".implode("\n- ", array_unique($errors)).PHP_EOL);
    exit(1);
}

fwrite(STDOUT, "Executable repository authority verification passed.\n");
