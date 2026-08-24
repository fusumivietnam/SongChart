<?php

declare(strict_types=1);

use App\Support\Engineering\RepositoryContractResolver;

$root = dirname(__DIR__);
require_once $root.'/app/Support/Engineering/RepositoryContractResolver.php';

$paths = array_values(array_filter(array_slice($argv, 1), static fn (string $path): bool => $path !== ''));
if ($paths === []) {
    fwrite(STDERR, "Usage: php scripts/resolve-repository-impact.php <changed-path> [changed-path...]\n");
    exit(2);
}

$resolver = new RepositoryContractResolver($root);
$impacted = $resolver->impactedAuthorities($paths);

fwrite(STDOUT, "Changed paths:\n");
foreach ($paths as $path) {
    fwrite(STDOUT, "- {$path}\n");
}

fwrite(STDOUT, "\nImpacted authorities:\n");
if ($impacted === []) {
    fwrite(STDOUT, "- none resolved\n");
    exit(0);
}

foreach ($impacted as $authority) {
    $definition = $resolver->authority($authority);
    fwrite(STDOUT, "- {$authority} [{$definition['source']}]\n");
    foreach ($definition['consumers'] as $consumer) {
        fwrite(STDOUT, "  consumer: {$consumer}\n");
    }
}

fwrite(STDOUT, "\nVerification consumer ownership:\n");
$consumerMap = [];
foreach ($resolver->verificationConsumers() as $consumer) {
    $consumerMap[$consumer['target']] = $consumer;
}
$foundConsumer = false;
foreach ($paths as $path) {
    if (! isset($consumerMap[$path])) {
        continue;
    }

    $foundConsumer = true;
    $consumer = $consumerMap[$path];
    fwrite(
        STDOUT,
        "- {$path} => {$consumer['rule']} [{$consumer['classification']}] => "
        .implode(', ', $consumer['semantic_authorities'])."\n",
    );
}
if (! $foundConsumer) {
    fwrite(STDOUT, "- none of the changed paths are registered verification consumers\n");
}
