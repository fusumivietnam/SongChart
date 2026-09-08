<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$errors = [];

$required = [
    'docs/project/engineering/system-knowledge-contract.json',
    'docs/project/domain/product-user-journeys.json',
    'docs/project/domain/use-case-contracts.json',
    'docs/project/engineering/stage-plan.json',
    'docs/project/engineering/roadmap.json',
    'scripts/system-knowledge.php',
];

foreach ($required as $relative) {
    if (! is_file($root.'/'.$relative)) {
        $errors[] = 'Missing system knowledge source: '.$relative;
    }
}

if ($errors === []) {
    try {
        $contract = json_decode((string) file_get_contents($root.'/docs/project/engineering/system-knowledge-contract.json'), true, 512, JSON_THROW_ON_ERROR);
        $journeys = json_decode((string) file_get_contents($root.'/docs/project/domain/product-user-journeys.json'), true, 512, JSON_THROW_ON_ERROR);
        $useCases = json_decode((string) file_get_contents($root.'/docs/project/domain/use-case-contracts.json'), true, 512, JSON_THROW_ON_ERROR);

        foreach (['project', 'journeys', 'use_cases', 'domain', 'schema_ownership', 'stage', 'roadmap', 'control_plane', 'architecture_graph'] as $source) {
            $path = $contract['source_authorities'][$source] ?? null;
            if (! is_string($path) || ! is_file($root.'/'.$path)) {
                $errors[] = 'System knowledge authority is missing or unresolved: '.$source;
            }
        }

        foreach (['new_developer', 'ai', 'product_owner', 'operator', 'end_user'] as $audience) {
            if (! isset($contract['audience_projections'][$audience])) {
                $errors[] = 'Missing audience projection: '.$audience;
            }
        }

        $knownUseCases = is_array($useCases['use_cases'] ?? null) ? $useCases['use_cases'] : [];
        foreach (($journeys['journeys'] ?? []) as $journeyId => $journey) {
            if (! is_array($journey)) {
                continue;
            }
            if (! isset($journey['goal'], $journey['status'])) {
                $errors[] = 'Journey lacks goal/status: '.$journeyId;
            }
            foreach (($journey['use_cases'] ?? []) as $useCaseId) {
                if (isset($knownUseCases[$useCaseId])) {
                    continue;
                }
                $gaps = implode(' ', array_map('strval', $journey['stage_20_gaps'] ?? []));
                if (! str_contains($gaps, 'contract') && ! str_contains($gaps, 'represented')) {
                    $errors[] = 'Unmapped journey use case is not explicitly documented as a gap: '.$journeyId.' -> '.$useCaseId;
                }
            }
        }
    } catch (Throwable $exception) {
        $errors[] = 'System knowledge contract decode failed: '.$exception->getMessage();
    }
}

if ($errors !== []) {
    foreach ($errors as $error) {
        fwrite(STDERR, '[FAIL] '.$error.PHP_EOL);
    }
    exit(1);
}

fwrite(STDOUT, 'System knowledge coverage contract passed.'.PHP_EOL);
