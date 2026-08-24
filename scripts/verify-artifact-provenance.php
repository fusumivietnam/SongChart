<?php

declare(strict_types=1);

use App\Support\Engineering\RepositoryContractResolver;

$root = dirname(__DIR__);
require_once $root.'/app/Support/Engineering/RepositoryContractResolver.php';

$manifestPath = $root.'/candidate-verification.json';
$errors = [];
if (! is_file($manifestPath)) {
    $errors[] = 'candidate-verification.json is missing.';
} else {
    $candidate = json_decode((string) file_get_contents($manifestPath), true, 512, JSON_THROW_ON_ERROR);
    if (($candidate['closure_ready'] ?? false) !== true) {
        $errors[] = 'Artifact packaging requires closure_ready=true from canonical verification.';
    }

    $resolver = new RepositoryContractResolver($root);
    $runtime = $resolver->compileManifest(true);
    $recorded = $candidate['evidence']['repository_contracts'] ?? null;

    if (! is_array($recorded)) {
        $errors[] = 'Canonical repository-contract evidence is missing.';
    } else {
        foreach (['graph_fingerprint', 'source_tree_sha256', 'composer_lock_sha256', 'package_lock_sha256'] as $key) {
            $actual = $key === 'graph_fingerprint'
                ? ($runtime['graph_fingerprint'] ?? null)
                : ($runtime['runtime'][$key] ?? null);

            if (($recorded[$key] ?? null) !== $actual) {
                $errors[] = "Artifact provenance drift detected [{$key}].";
            }
        }
    }
}

if ($errors !== []) {
    fwrite(STDERR, "Artifact provenance verification failed:\n- ".implode("\n- ", array_unique($errors)).PHP_EOL);
    exit(1);
}

fwrite(STDOUT, "Artifact provenance verification passed for the exact canonical tree.\n");
