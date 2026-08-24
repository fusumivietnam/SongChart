<?php

declare(strict_types=1);

namespace App\Application\Catalog\Enrichment;

use App\Domain\Catalog\Enrichment\EnrichmentDispatch;
use App\Domain\Catalog\Enrichment\EnrichmentNeed;
use App\Domain\Catalog\Enrichment\EnrichmentPlan;
use App\Domain\Catalog\Enrichment\EnrichmentSchedule;
use App\Domain\Catalog\Enums\EntityType;

final class BuildEnrichmentSchedule
{
    /** @var array<string, int> */
    private const PRIORITY_WEIGHT = ['critical' => 0, 'high' => 10, 'normal' => 20, 'low' => 30];

    /** @var array<string, int> */
    private const COST_WEIGHT = ['low' => 0, 'medium' => 10, 'high' => 20];

    public function handle(EntityType $entityType, string $entityId, EnrichmentPlan $plan): EnrichmentSchedule
    {
        $eligible = [];
        $deferred = [];

        foreach ($plan->needs as $need) {
            if (! $need->providerEnabled) {
                $deferred[] = $need;

                continue;
            }

            $identity = $this->needIdentity($need);
            $eligible[$identity] ??= $need;
        }

        uasort($eligible, fn (EnrichmentNeed $left, EnrichmentNeed $right): int => $this->compare($left, $right));

        $dispatches = [];
        foreach ($eligible as $need) {
            $dispatches[] = new EnrichmentDispatch(
                entityType: $entityType->value,
                entityId: $entityId,
                need: $need,
                idempotencyKey: hash('sha256', implode('|', [
                    $entityType->value,
                    $entityId,
                    $need->kind,
                    $need->key,
                    $need->provider,
                ])),
            );
        }

        return new EnrichmentSchedule($dispatches, $deferred);
    }

    private function needIdentity(EnrichmentNeed $need): string
    {
        return implode('|', [$need->kind, $need->key, $need->provider]);
    }

    private function compare(EnrichmentNeed $left, EnrichmentNeed $right): int
    {
        return [
            self::PRIORITY_WEIGHT[$left->priority] ?? 100,
            self::COST_WEIGHT[$left->costClass] ?? 100,
            $left->provider,
            $left->kind,
            $left->key,
        ] <=> [
            self::PRIORITY_WEIGHT[$right->priority] ?? 100,
            self::COST_WEIGHT[$right->costClass] ?? 100,
            $right->provider,
            $right->kind,
            $right->key,
        ];
    }
}
