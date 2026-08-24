<?php

declare(strict_types=1);

namespace App\Support\Catalog\Enrichment;

use App\Contracts\Catalog\EnrichmentPlanner;
use App\Domain\Catalog\Enrichment\EnrichmentNeed;
use App\Domain\Catalog\Enrichment\EnrichmentPlan;
use App\Domain\Catalog\Enums\EntityType;
use App\Models\Catalog\ExternalIdentifier;
use App\Models\Provider;
use App\Models\ProviderDestination;
use App\Support\Catalog\Fusion\CanonicalFieldResolver;

final readonly class ConfigEnrichmentPlanner implements EnrichmentPlanner
{
    /** @param array<string, mixed> $config */
    public function __construct(
        private CanonicalFieldResolver $resolver,
        private array $config,
    ) {}

    public function plan(EntityType $type, string $entityId): EnrichmentPlan
    {
        $recipe = $this->recipe($type);
        $providers = $this->providerStates();
        $needs = [];
        $checks = 0;
        $satisfied = 0;

        foreach ($this->rows($recipe, 'fields') as $row) {
            $checks++;
            $key = (string) ($row['key'] ?? '');
            if ($key !== '' && $this->resolver->resolve($type, $entityId, $key)->selected !== null) {
                $satisfied++;

                continue;
            }
            $needs[] = $this->need('field', $key, $row, $providers);
        }

        foreach ($this->rows($recipe, 'identifiers') as $row) {
            $checks++;
            $namespace = (string) ($row['key'] ?? '');
            $exists = $namespace !== '' && ExternalIdentifier::query()
                ->where('entity_type', $type->value)
                ->where('entity_id', $entityId)
                ->where('namespace', $namespace)
                ->exists();
            if ($exists) {
                $satisfied++;

                continue;
            }
            $needs[] = $this->need('identifier', $namespace, $row, $providers);
        }

        foreach ($this->rows($recipe, 'destinations') as $row) {
            $checks++;
            $provider = (string) ($row['provider'] ?? '');
            $providerId = $providers[$provider]['id'] ?? null;
            $exists = is_string($providerId) && ProviderDestination::query()
                ->where('entity_type', $type->value)
                ->where('entity_id', $entityId)
                ->where('provider_id', $providerId)
                ->where('review_state', 'approved')
                ->exists();
            if ($exists) {
                $satisfied++;

                continue;
            }
            $needs[] = $this->need('destination', $provider, $row, $providers);
        }

        usort($needs, static fn (EnrichmentNeed $a, EnrichmentNeed $b): int => [self::priorityRank($a->priority), $a->provider, $a->key] <=> [self::priorityRank($b->priority), $b->provider, $b->key]);
        $completeness = $checks === 0 ? 100 : (int) round(($satisfied / $checks) * 100);

        return new EnrichmentPlan($needs, $completeness);
    }

    /** @return array<string, mixed> */
    private function recipe(EntityType $type): array
    {
        $recipes = $this->config['recipes'] ?? [];

        return is_array($recipes) && isset($recipes[$type->value]) && is_array($recipes[$type->value]) ? $recipes[$type->value] : [];
    }

    /** @return list<array<string, mixed>> */
    private function rows(array $recipe, string $key): array
    {
        $rows = $recipe[$key] ?? [];
        if (! is_array($rows)) {
            return [];
        }

        return array_values(array_filter($rows, 'is_array'));
    }

    /** @return array<string, array{id:string,enabled:bool}> */
    private function providerStates(): array
    {
        $states = [];
        foreach (Provider::query()->get(['id', 'slug', 'is_enabled']) as $provider) {
            $states[(string) $provider->slug] = ['id' => (string) $provider->getKey(), 'enabled' => (bool) $provider->is_enabled];
        }

        return $states;
    }

    /** @param array<string,mixed> $row @param array<string,array{id:string,enabled:bool}> $providers */
    private function need(string $kind, string $key, array $row, array $providers): EnrichmentNeed
    {
        $provider = (string) ($row['provider'] ?? 'songchart');

        return new EnrichmentNeed(
            kind: $kind,
            key: $key,
            provider: $provider,
            priority: (string) ($row['priority'] ?? 'normal'),
            costClass: (string) ($row['cost_class'] ?? 'low'),
            reason: (string) ($row['reason'] ?? 'Canonical entity is missing required evidence.'),
            providerEnabled: (bool) ($providers[$provider]['enabled'] ?? ($provider === 'songchart')),
        );
    }

    private static function priorityRank(string $priority): int
    {
        return match ($priority) {
            'critical' => 0,
            'high' => 1,
            'normal' => 2,
            default => 3,
        };
    }
}
