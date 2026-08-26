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

$skillsRoot = $root.'/.agents/skills';
if (! is_dir($skillsRoot)) {
    $errors[] = 'Repository-local AI skills directory [.agents/skills] is missing.';
} else {
    $skillDirectories = glob($skillsRoot.'/*', GLOB_ONLYDIR) ?: [];
    sort($skillDirectories);

    foreach ($skillDirectories as $skillDirectory) {
        $expectedName = basename($skillDirectory);
        $skillPath = $skillDirectory.'/SKILL.md';
        if (! is_file($skillPath)) {
            $errors[] = "AI skill [{$expectedName}] is missing SKILL.md.";
            continue;
        }

        $source = (string) file_get_contents($skillPath);
        if (! str_starts_with($source, "---\n")) {
            $errors[] = "AI skill [{$expectedName}] must start with YAML frontmatter at byte 0.";
            continue;
        }

        if (preg_match('/\A---\n(?<frontmatter>.*?)\n---(?:\n|\z)/s', $source, $matches) !== 1) {
            $errors[] = "AI skill [{$expectedName}] has invalid YAML frontmatter delimiters.";
            continue;
        }

        $frontmatter = (string) ($matches['frontmatter'] ?? '');
        $fields = [];
        foreach (preg_split('/\R/', $frontmatter) ?: [] as $line) {
            if (trim($line) === '' || str_starts_with(ltrim($line), '#')) {
                continue;
            }
            if (preg_match('/^(?<key>[A-Za-z0-9_-]+):\s*(?<value>.*)$/', $line, $fieldMatch) !== 1) {
                $errors[] = "AI skill [{$expectedName}] frontmatter contains unsupported YAML structure [{$line}].";
                continue 2;
            }
            $fields[$fieldMatch['key']] = trim((string) $fieldMatch['value'], " \t\n\r\0\x0B\"'");
        }

        $allowed = ['name', 'description', 'license', 'allowed-tools', 'metadata'];
        foreach (array_keys($fields) as $key) {
            if (! in_array($key, $allowed, true)) {
                $errors[] = "AI skill [{$expectedName}] frontmatter uses unsupported key [{$key}].";
            }
        }

        $name = $fields['name'] ?? '';
        $description = $fields['description'] ?? '';
        if ($name === '') {
            $errors[] = "AI skill [{$expectedName}] frontmatter is missing name.";
        } elseif ($name !== $expectedName) {
            $errors[] = "AI skill directory [{$expectedName}] must match frontmatter name [{$name}].";
        } elseif (preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $name) !== 1 || strlen($name) > 64) {
            $errors[] = "AI skill [{$expectedName}] name must be lowercase hyphen-case and at most 64 characters.";
        }

        if ($description === '') {
            $errors[] = "AI skill [{$expectedName}] frontmatter is missing a non-empty description.";
        }

        $body = preg_replace('/\A---\n.*?\n---(?:\n|\z)/s', '', $source, 1);
        if (trim((string) $body) === '') {
            $errors[] = "AI skill [{$expectedName}] must contain an actionable Markdown body.";
        }
    }
}

if ($errors !== []) {
    fwrite(STDERR, "AI development protocol verification failed:\n- ".implode("\n- ", array_unique($errors)).PHP_EOL);
    exit(1);
}

fwrite(STDOUT, "AI development protocol verification passed.\n");
