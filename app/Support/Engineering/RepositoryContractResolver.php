<?php

declare(strict_types=1);

namespace App\Support\Engineering;

use JsonException;
use RuntimeException;

final class RepositoryContractResolver
{
    /** @var array<string, array<string, mixed>> */
    private array $authorities = [];

    public function __construct(private readonly string $root)
    {
        $compiler = $this->readJson('docs/project/engineering/repository-contract-compiler.json');
        foreach (($compiler['authorities'] ?? []) as $name => $definition) {
            if (! is_array($definition)) {
                throw new RuntimeException("Invalid authority definition [{$name}].");
            }

            $source = (string) ($definition['source'] ?? '');
            $payload = $this->readJson($source);
            $this->authorities[(string) $name] = [
                'source' => $source,
                'fingerprint' => $this->fingerprint($payload),
                'payload' => $payload,
                'consumers' => array_values(array_filter(
                    (array) ($definition['consumers'] ?? []),
                    'is_string',
                )),
            ];
        }
    }

    /** @return list<string> */
    public function authorityNames(): array
    {
        return array_keys($this->authorities);
    }

    /** @return array<string, mixed> */
    public function authority(string $name): array
    {
        $authority = $this->authorities[$name] ?? null;
        if (! is_array($authority)) {
            throw new RuntimeException("Unknown repository authority [{$name}].");
        }

        return $authority;
    }

    /** @return array<string, mixed> */
    public function value(string $authority, string $path = ''): mixed
    {
        $value = $this->authority($authority)['payload'];
        if ($path === '') {
            return $value;
        }

        foreach (explode('.', $path) as $segment) {
            if (! is_array($value) || ! array_key_exists($segment, $value)) {
                throw new RuntimeException("Unable to resolve [{$authority}.{$path}].");
            }

            $value = $value[$segment];
        }

        return $value;
    }

    /** @return array<string, mixed> */
    public function compileManifest(bool $includeRuntime = false): array
    {
        $authorities = [];
        foreach ($this->authorities as $name => $authority) {
            $authorities[$name] = [
                'source' => $authority['source'],
                'fingerprint' => $authority['fingerprint'],
                'consumers' => $authority['consumers'],
            ];
        }

        $verificationConsumers = $this->verificationConsumers();
        $graph = [
            'authorities' => $authorities,
            'verification_consumers' => $verificationConsumers,
        ];

        $manifest = [
            'schema_version' => 2,
            'authorities' => $authorities,
            'verification_consumers' => $verificationConsumers,
            'graph_fingerprint' => $this->fingerprint($graph),
        ];

        if ($includeRuntime) {
            $manifest['runtime'] = [
                'source_tree_sha256' => $this->sourceTreeFingerprint(),
                'composer_lock_sha256' => $this->fileHash('composer.lock'),
                'package_lock_sha256' => $this->fileHash('package-lock.json'),
                'git_commit' => $this->git('rev-parse HEAD'),
                'git_tree' => $this->git('rev-parse HEAD^{tree}'),
                'git_dirty' => $this->gitDirty(),
            ];
        }

        return $manifest;
    }

    public function sourceTreeFingerprint(): string
    {
        $excludedDirectories = [
            '.git/',
            '.songchart-backups/',
            'node_modules/',
            'storage/',
            'vendor/',
        ];
        $excludedFiles = [
            '.env',
            'candidate-verification.json',
            'docs/project/generated/repository-contract-manifest.json',
        ];

        $files = [];
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($this->root, \FilesystemIterator::SKIP_DOTS),
        );

        foreach ($iterator as $file) {
            if (! $file->isFile()) {
                continue;
            }

            $relative = str_replace('\\', '/', substr($file->getPathname(), strlen($this->root) + 1));
            if (
                in_array($relative, $excludedFiles, true)
                || (str_starts_with($relative, '.songchart-stage-') && str_ends_with($relative, '-resume.json'))
            ) {
                continue;
            }

            $excluded = false;
            foreach ($excludedDirectories as $directory) {
                if (str_starts_with($relative, $directory)) {
                    $excluded = true;
                    break;
                }
            }
            if ($excluded) {
                continue;
            }

            $files[$relative] = hash_file('sha256', $file->getPathname());
        }

        ksort($files);

        return $this->fingerprint($files);
    }

    /** @return list<array{target:string,rule:string,classification:string,semantic_authorities:list<string>}> */
    public function verificationConsumers(): array
    {
        $graph = $this->value('verification-consumer-graph');
        $inventory = is_array($graph['inventory'] ?? null) ? $graph['inventory'] : [];
        $patterns = array_values(array_filter([
            $inventory['verifier_glob'] ?? null,
            $inventory['architecture_glob'] ?? null,
        ], 'is_string'));

        $targets = $this->expand($patterns);
        $rules = is_array($graph['rules'] ?? null) ? $graph['rules'] : [];
        $resolved = [];

        foreach ($targets as $target) {
            $matches = [];
            foreach ($rules as $rule) {
                if (! is_array($rule)) {
                    continue;
                }

                foreach (array_values(array_filter((array) ($rule['targets'] ?? []), 'is_string')) as $pattern) {
                    if (fnmatch($pattern, $target)) {
                        $matches[] = $rule;
                        break;
                    }
                }
            }

            if (count($matches) !== 1) {
                $resolved[] = [
                    'target' => $target,
                    'rule' => '',
                    'classification' => 'unresolved',
                    'semantic_authorities' => [],
                ];

                continue;
            }

            $rule = $matches[0];
            $resolved[] = [
                'target' => $target,
                'rule' => (string) ($rule['id'] ?? ''),
                'classification' => (string) ($rule['classification'] ?? ''),
                'semantic_authorities' => array_values(array_filter(
                    (array) ($rule['semantic_authorities'] ?? []),
                    'is_string',
                )),
            ];
        }

        return $resolved;
    }

    /** @return list<string> */
    public function verificationConsumerGraphViolations(): array
    {
        $violations = [];
        $graph = $this->value('verification-consumer-graph');
        $rules = is_array($graph['rules'] ?? null) ? $graph['rules'] : [];

        foreach ($this->verificationConsumers() as $consumer) {
            if ($consumer['rule'] === '') {
                $violations[] = "Verification consumer [{$consumer['target']}] must match exactly one ownership rule.";

                continue;
            }

            if (! in_array($consumer['classification'], ['declarative', 'behavioral'], true)) {
                $violations[] = "Verification consumer [{$consumer['target']}] has invalid classification.";
            }

            if ($consumer['semantic_authorities'] === []) {
                $violations[] = "Verification consumer [{$consumer['target']}] has no semantic authority.";

                continue;
            }

            foreach ($consumer['semantic_authorities'] as $authority) {
                if (! in_array($authority, $this->authorityNames(), true)) {
                    $violations[] = "Verification consumer [{$consumer['target']}] references unknown semantic authority [{$authority}].";
                }
            }
        }

        // Detect overlapping rules, including future files that happen to match more than one route.
        $inventory = is_array($graph['inventory'] ?? null) ? $graph['inventory'] : [];
        $targets = $this->expand(array_values(array_filter([
            $inventory['verifier_glob'] ?? null,
            $inventory['architecture_glob'] ?? null,
        ], 'is_string')));

        foreach ($targets as $target) {
            $matchingRuleIds = [];
            foreach ($rules as $rule) {
                if (! is_array($rule)) {
                    continue;
                }
                foreach (array_values(array_filter((array) ($rule['targets'] ?? []), 'is_string')) as $pattern) {
                    if (fnmatch($pattern, $target)) {
                        $matchingRuleIds[] = (string) ($rule['id'] ?? '');
                        break;
                    }
                }
            }
            if (count($matchingRuleIds) !== 1) {
                $violations[] = "Verification consumer [{$target}] ownership cardinality is ".count($matchingRuleIds).'.';
            }
        }

        foreach (($graph['literal_boundaries'] ?? []) as $boundary) {
            if (! is_array($boundary)) {
                continue;
            }

            $allowed = array_fill_keys(
                array_values(array_filter((array) ($boundary['allowed'] ?? []), 'is_string')),
                true,
            );
            foreach ($this->expand(array_values(array_filter((array) ($boundary['scan'] ?? []), 'is_string'))) as $relative) {
                if (isset($allowed[$relative])) {
                    continue;
                }

                $source = (string) @file_get_contents($this->root.'/'.$relative);
                $forbiddenLiterals = array_values(array_filter((array) ($boundary['forbidden_literals'] ?? []), 'is_string'));
                foreach ($forbiddenLiterals as $literal) {
                    if ($literal !== '' && str_contains($source, $literal)) {
                        $violations[] = "Verification consumer [{$relative}] crosses literal boundary [{$boundary['id']}] with [{$literal}].";
                    }
                }

                $combined = array_values(array_filter((array) ($boundary['forbidden_when_combined'] ?? []), 'is_string'));
                if ($combined !== [] && count(array_filter(
                    $combined,
                    static fn (string $literal): bool => str_contains($source, $literal),
                )) === count($combined)) {
                    $violations[] = "Verification consumer [{$relative}] crosses combined literal boundary [{$boundary['id']}].";
                }
            }
        }

        return array_values(array_unique($violations));
    }

    /** @return list<string> */
    public function verifierExecutionOwners(): array
    {
        $composer = $this->readJson('composer.json');
        $owners = [];
        foreach (($composer['scripts'] ?? []) as $name => $definition) {
            $commands = is_array($definition) ? $definition : [$definition];
            foreach ($commands as $command) {
                if (! is_string($command)) {
                    continue;
                }
                if (preg_match('/@php\s+(scripts\/verify-[^\s]+\.php)/', $command, $matches) === 1) {
                    $owners[$matches[1]][] = (string) $name;
                }
            }
        }

        $violations = [];
        foreach ($this->verificationConsumers() as $consumer) {
            if (! str_starts_with($consumer['target'], 'scripts/verify-')) {
                continue;
            }

            $commands = $owners[$consumer['target']] ?? [];
            if ($commands === []) {
                $violations[] = "Verifier [{$consumer['target']}] has no Composer execution owner.";
            }
        }

        return $violations;
    }

    /** @return list<array{authority:string, consumer:string, reason:string}> */
    public function staleConsumers(): array
    {
        $compiler = $this->readJson('docs/project/engineering/repository-contract-compiler.json');
        $violations = [];

        foreach (($compiler['raw_literal_rules'] ?? []) as $rule) {
            if (! is_array($rule)) {
                continue;
            }

            $authority = (string) ($rule['authority'] ?? '');
            $literal = (string) ($rule['literal'] ?? '');
            $scanPaths = array_values(array_filter((array) ($rule['scan_paths'] ?? []), 'is_string'));
            $allowed = array_fill_keys(
                array_values(array_filter((array) ($rule['allowed_files'] ?? []), 'is_string')),
                true,
            );

            foreach ($this->expand($scanPaths) as $relative) {
                if (isset($allowed[$relative])) {
                    continue;
                }

                $source = (string) @file_get_contents($this->root.'/'.$relative);
                if ($source !== '' && str_contains($source, $literal)) {
                    $violations[] = [
                        'authority' => $authority,
                        'consumer' => $relative,
                        'reason' => "raw authority literal [{$literal}]",
                    ];
                }
            }
        }

        return $violations;
    }

    /**
     * @param  list<string>  $changedPaths
     * @return list<string>
     */
    public function impactedAuthorities(array $changedPaths): array
    {
        $impacted = [];
        foreach ($this->authorities as $name => $authority) {
            if (in_array($authority['source'], $changedPaths, true)) {
                $impacted[] = $name;

                continue;
            }

            foreach ($authority['consumers'] as $consumer) {
                if (in_array($consumer, $changedPaths, true)) {
                    $impacted[] = $name;
                    break;
                }
            }
        }

        foreach ($this->verificationConsumers() as $consumer) {
            if (! in_array($consumer['target'], $changedPaths, true)) {
                continue;
            }

            foreach ($consumer['semantic_authorities'] as $authority) {
                $impacted[] = $authority;
            }
        }

        return array_values(array_unique($impacted));
    }

    /** @return array<string, mixed> */
    private function readJson(string $relative): array
    {
        $path = $this->root.'/'.$relative;
        if (! is_file($path)) {
            throw new RuntimeException("Repository authority is missing [{$relative}].");
        }

        try {
            $decoded = json_decode((string) file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new RuntimeException("Invalid repository authority JSON [{$relative}]: {$exception->getMessage()}", previous: $exception);
        }

        if (! is_array($decoded)) {
            throw new RuntimeException("Repository authority must decode to an object [{$relative}].");
        }

        return $decoded;
    }

    private function fingerprint(mixed $value): string
    {
        $normalize = function (mixed $item) use (&$normalize): mixed {
            if (! is_array($item)) {
                return $item;
            }

            if (! array_is_list($item)) {
                ksort($item);
            }

            foreach ($item as $key => $child) {
                $item[$key] = $normalize($child);
            }

            return $item;
        };

        return hash('sha256', json_encode($normalize($value), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));
    }

    private function fileHash(string $relative): ?string
    {
        $path = $this->root.'/'.$relative;

        return is_file($path) ? hash_file('sha256', $path) : null;
    }

    private function git(string $arguments): ?string
    {
        $command = 'git -C '.escapeshellarg($this->root).' '.$arguments.' 2>/dev/null';
        $value = trim((string) shell_exec($command));

        return $value !== '' ? $value : null;
    }

    private function gitDirty(): ?bool
    {
        $value = $this->git('status --porcelain');

        return $value === null ? null : $value !== '';
    }

    /**
     * @param  list<string>  $patterns
     * @return list<string>
     */
    private function expand(array $patterns): array
    {
        $files = [];
        foreach ($patterns as $pattern) {
            $absolute = $this->root.'/'.$pattern;
            if (! strpbrk($pattern, '*?[')) {
                if (is_file($absolute)) {
                    $files[] = $pattern;
                }

                continue;
            }

            foreach (glob($absolute) ?: [] as $match) {
                if (is_file($match)) {
                    $files[] = str_replace('\\', '/', substr($match, strlen($this->root) + 1));
                }
            }
        }

        sort($files);

        return array_values(array_unique($files));
    }
}
