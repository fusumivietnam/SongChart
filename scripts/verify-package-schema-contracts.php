<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$errors = [];

try {
    $contracts = json_decode((string) file_get_contents($root.'/docs/project/stack/package-schema-contracts.json'), true, flags: JSON_THROW_ON_ERROR);
    $composer = json_decode((string) file_get_contents($root.'/composer.json'), true, flags: JSON_THROW_ON_ERROR);
} catch (Throwable $exception) {
    fwrite(STDERR, 'Package schema verification failed: '.$exception->getMessage().PHP_EOL);
    exit(1);
}

$migrations = '';
foreach (glob($root.'/database/migrations/*.php') ?: [] as $migration) {
    $migrations .= "\n".(string) file_get_contents($migration);
}

foreach (($contracts['contracts'] ?? []) as $package => $contract) {
    if (! is_array($contract)) {
        $errors[] = "Invalid package schema contract [{$package}].";

        continue;
    }

    $constraint = $composer['require'][$package] ?? $composer['require-dev'][$package] ?? null;
    if (! is_string($constraint)) {
        $errors[] = "Package schema contract references package not present in composer.json [{$package}].";
    } elseif (($contract['constraint'] ?? null) !== $constraint) {
        $errors[] = "Package schema contract constraint for {$package} does not match composer.json.";
    }

    $table = (string) ($contract['table'] ?? '');
    if ($table === '' || ! str_contains($migrations, "Schema::create('{$table}'")) {
        $errors[] = "Package table migration is missing [{$package}:{$table}].";

        continue;
    }

    foreach (($contract['required_columns'] ?? []) as $column => $definition) {
        if (! preg_match("/\\\$table->[^;\\n]*\\(['\"]".preg_quote((string) $column, '/')."['\"]/", $migrations)) {
            $errors[] = "Package schema {$package} is missing required column [{$column}].";
        }

        if (is_array($definition) && ($definition['kind'] ?? null) === 'char' && isset($definition['length'])) {
            $needle = "\$table->char('{$column}', ".(int) $definition['length'].')';
            if (! str_contains($migrations, $needle)) {
                $errors[] = "Package schema {$package}.{$column} must preserve {$needle}.";
            }
        }
    }
}

if ($errors !== []) {
    fwrite(STDERR, "Package schema contract verification failed:\n- ".implode("\n- ", array_unique($errors)).PHP_EOL);
    exit(1);
}

fwrite(STDOUT, 'Package schema contract verification passed.'.PHP_EOL);
