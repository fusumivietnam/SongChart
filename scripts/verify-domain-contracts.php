<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$registryPath = $root.'/docs/project/domain/domain-contracts.json';
$errors = [];

$fail = static function (string $message) use (&$errors): void {
    $errors[] = $message;
};

if (! is_file($registryPath)) {
    fwrite(STDERR, "Domain contract registry is missing.\n");
    exit(1);
}

try {
    $contracts = json_decode((string) file_get_contents($registryPath), true, flags: JSON_THROW_ON_ERROR);
} catch (JsonException $exception) {
    fwrite(STDERR, 'Domain contract registry is invalid JSON: '.$exception->getMessage().PHP_EOL);
    exit(1);
}

if (! is_array($contracts) || ($contracts['schema_version'] ?? null) !== 1) {
    $fail('Domain contract schema_version must be 1.');
}

$enumSource = (string) file_get_contents($root.'/app/Domain/Catalog/Enums/EntityType.php');
preg_match_all("/case\\s+\\w+\\s*=\\s*'([^']+)'/", $enumSource, $enumMatches);
$enumTypes = $enumMatches[1] ?? [];
$contractTypes = array_keys(is_array($contracts['entities'] ?? null) ? $contracts['entities'] : []);
sort($enumTypes);
sort($contractTypes);
if ($enumTypes !== $contractTypes) {
    $fail('EntityType values and domain contract entity keys differ.');
}

$relationshipSource = (string) file_get_contents($root.'/app/Domain/Catalog/Enums/RelationshipType.php');
preg_match_all("/case\\s+\\w+\\s*=\\s*'([^']+)'/", $relationshipSource, $relationshipMatches);
$relationshipTypes = $relationshipMatches[1] ?? [];
$declaredRelationships = $contracts['relationships']['allowed_types'] ?? [];
sort($relationshipTypes);
if (is_array($declaredRelationships)) {
    sort($declaredRelationships);
}
if ($relationshipTypes !== $declaredRelationships) {
    $fail('RelationshipType values and domain contract relationship values differ.');
}

$canonicalMigrationPaths = glob($root.'/database/migrations/*.php') ?: [];
sort($canonicalMigrationPaths, SORT_STRING);
$migration = implode("\n", array_map(static fn (string $path): string => (string) file_get_contents($path), $canonicalMigrationPaths));

/** @return array<string, string> */
$extractTableColumns = static function (string $source, string $table): array {
    $columns = [];
    foreach (["Schema::create('{$table}'", "Schema::table('{$table}'"] as $needle) {
        $offset = 0;
        while (($start = strpos($source, $needle, $offset)) !== false) {
            $end = strpos($source, '});', $start);
            $block = $end === false ? substr($source, $start) : substr($source, $start, $end - $start + 3);
            preg_match_all('/\$table->(ulid|foreignUlid|string|text|unsignedInteger|boolean|date|jsonb)\(\'([^\']+)\'/', $block, $matches, PREG_SET_ORDER);
            foreach ($matches as $match) {
                $columns[$match[2]] = $match[1];
            }
            if (str_contains($block, '$table->timestamps()')) {
                $columns['created_at'] = 'timestamp';
                $columns['updated_at'] = 'timestamp';
            }
            if (str_contains($block, '$table->softDeletes()')) {
                $columns['deleted_at'] = 'timestamp';
            }
            $offset = $end === false ? strlen($source) : $end + 3;
        }
    }

    return $columns;
};

$typeMap = [
    'ulid' => ['ulid', 'foreignUlid'],
    'string' => ['string'],
    'text' => ['text'],
    'duration_ms' => ['unsignedInteger'],
    'boolean' => ['boolean'],
    'local_date' => ['date'],
    'json' => ['jsonb'],
    'verification_state' => ['string'],
    'timestamp' => ['timestamp'],
];

foreach ($contracts['entities'] ?? [] as $type => $entity) {
    if (! is_array($entity)) {
        $fail("Entity contract [{$type}] must be an object.");

        continue;
    }
    foreach (['model', 'table', 'display_field', 'slug_field', 'verification_field', 'fields'] as $required) {
        if (! array_key_exists($required, $entity)) {
            $fail("Entity contract [{$type}] is missing [{$required}].");
        }
    }
    $fields = is_array($entity['fields'] ?? null) ? $entity['fields'] : [];
    foreach (['display_field', 'slug_field', 'verification_field', 'date_field', 'description_field'] as $fieldPointer) {
        if (isset($entity[$fieldPointer]) && ! array_key_exists((string) $entity[$fieldPointer], $fields)) {
            $fail("Entity [{$type}] points [{$fieldPointer}] to an undeclared field.");
        }
    }

    $table = (string) ($entity['table'] ?? '');
    $columns = $extractTableColumns($migration, $table);
    if ($columns === []) {
        $fail("Storage table [{$table}] for [{$type}] was not found in canonical migration.");

        continue;
    }

    $declaredColumns = array_keys($fields);
    $actualColumns = array_keys($columns);
    sort($declaredColumns);
    sort($actualColumns);
    if ($declaredColumns !== $actualColumns) {
        $fail("Declared fields for [{$type}] do not exactly match migration columns for [{$table}].");
    }

    foreach ($fields as $field => $definition) {
        if (! is_array($definition)) {
            $fail("Field [{$type}.{$field}] must be an object.");

            continue;
        }
        $logicalType = (string) ($definition['type'] ?? '');
        $storageType = $columns[$field] ?? null;
        if ($storageType !== null && isset($typeMap[$logicalType]) && ! in_array($storageType, $typeMap[$logicalType], true)) {
            $fail("Field [{$type}.{$field}] type [{$logicalType}] does not match migration primitive [{$storageType}].");
        }
    }

    $model = (string) ($entity['model'] ?? '');
    $relativeModel = 'app/'.str_replace(['App\\', '\\'], ['', '/'], $model).'.php';
    $modelPath = $root.'/'.$relativeModel;
    if (! is_file($modelPath)) {
        $fail("Model file missing for [{$type}]: {$relativeModel}");

        continue;
    }
    $modelSource = (string) file_get_contents($modelPath);
    if (! str_contains($modelSource, 'protected $table = \''.$table.'\';')) {
        $fail("Model [{$model}] does not declare expected table [{$table}].");
    }
    preg_match('/protected \$fillable = \[(.*?)\];/s', $modelSource, $fillableMatch);
    preg_match_all("/'([^']+)'/", $fillableMatch[1] ?? '', $fillableValues);
    $fillable = $fillableValues[1] ?? [];
    $contractWritable = [];
    foreach ($fields as $field => $definition) {
        if (($definition['writable'] ?? false) === true) {
            $contractWritable[] = $field;
        }
    }
    sort($fillable);
    sort($contractWritable);
    if ($fillable !== $contractWritable) {
        $fail("Model fillable fields for [{$type}] differ from writable contract fields.");
    }
    $softDeletes = (bool) ($entity['soft_deletes'] ?? false);
    if ($softDeletes !== str_contains($modelSource, 'use SoftDeletes;')) {
        $fail("Soft-delete contract for [{$type}] differs from model behavior.");
    }
    foreach ($fields as $field => $definition) {
        $cast = is_array($definition) ? ($definition['php_cast'] ?? null) : null;
        if (! is_string($cast)) {
            continue;
        }
        $needle = str_contains($cast, '\\') ? $field."' => VerificationState::class" : "'{$field}' => '{$cast}'";
        if (! str_contains($modelSource, $needle)) {
            $fail("Model cast for [{$type}.{$field}] does not match the contract.");
        }
    }
}

$routes = (string) file_get_contents($root.'/routes/web.php');
$ulidPattern = $contracts['identity']['internal_id']['route_pattern'] ?? null;
if (! is_string($ulidPattern) || $ulidPattern === '') {
    $fail('Internal ULID route pattern is missing.');
}
if (! str_contains($routes, 'app(DomainContractRegistry::class)->adminUlidPattern()')) {
    $fail('Admin catalog route does not consume the domain ULID route contract.');
}

$search = (string) file_get_contents($root.'/app/Support/Search/EloquentSearchCatalog.php');
foreach (['displayField($type)', 'dateField($type)', 'descriptionField($type)', 'slugField($type)'] as $needle) {
    if (! str_contains($search, $needle)) {
        $fail("EloquentSearchCatalog is not consuming contract method [{$needle}].");
    }
}
if (str_contains($search, "EntityType::Artist, EntityType::Version => 'name'")) {
    $fail('EloquentSearchCatalog still contains inferred display-field mapping.');
}

if ($errors !== []) {
    foreach ($errors as $error) {
        fwrite(STDERR, $error.PHP_EOL);
    }
    exit(1);
}

echo "Domain contract registry verification passed.\n";
