<?php

declare(strict_types=1);

namespace App\Support\Operations;

use Illuminate\Support\Arr;

final class OperationalIntelligenceSnapshot
{
    /**
     * @param  array<string, int|float|null>  $evidence
     * @return array<string, mixed>
     */
    public function build(array $evidence): array
    {
        /** @var array<string, array<string, mixed>> $definitions */
        $definitions = config('songchart.operational_intelligence.metrics', []);
        $metrics = [];
        $availableDimensions = [];
        $worst = 'healthy';

        foreach ($definitions as $key => $definition) {
            $value = $evidence[$key] ?? null;
            $dimension = (string) ($definition['dimension'] ?? 'unknown');
            $status = $value === null ? 'unavailable' : $this->classify((float) $value, $definition);

            if ($status !== 'unavailable') {
                $availableDimensions[$dimension] = true;
                $worst = $this->worse($worst, $status);
            }

            $metrics[$key] = [
                'value' => $value,
                'unit' => (string) ($definition['unit'] ?? 'count'),
                'status' => $status,
                'dimension' => $dimension,
                'owner' => (string) ($definition['owner'] ?? 'songchart'),
                'warning' => $definition['warning'] ?? null,
                'critical' => $definition['critical'] ?? null,
            ];
        }

        /** @var list<string> $requiredDimensions */
        $requiredDimensions = config('songchart.operational_intelligence.required_scale_dimensions', []);
        $missingDimensions = array_values(array_filter(
            $requiredDimensions,
            fn (string $dimension): bool => ! isset($availableDimensions[$dimension]),
        ));

        $scaleStatus = $missingDimensions === []
            ? ($worst === 'critical' ? 'stabilize_before_scaling' : ($worst === 'warning' ? 'investigate' : 'within_baseline'))
            : 'insufficient_evidence';

        return [
            'schema_version' => 1,
            'observed_at' => now()->toIso8601String(),
            'status' => $worst,
            'metrics' => $metrics,
            'scale_scorecard' => [
                'status' => $scaleStatus,
                'required_dimensions' => $requiredDimensions,
                'available_dimensions' => array_keys($availableDimensions),
                'missing_dimensions' => $missingDimensions,
                'automatic_infrastructure_mutation' => false,
            ],
            'external_observability' => [
                'decision' => (string) config('songchart.operational_intelligence.external_observability.decision', 'deferred'),
                'reason' => (string) config('songchart.operational_intelligence.external_observability.reason', 'No demonstrated internal observability gap.'),
            ],
        ];
    }

    /** @param array<string, mixed> $definition */
    private function classify(float $value, array $definition): string
    {
        $direction = (string) ($definition['direction'] ?? 'higher_is_worse');
        $warning = Arr::get($definition, 'warning');
        $critical = Arr::get($definition, 'critical');

        if (! is_numeric($warning) || ! is_numeric($critical)) {
            return 'healthy';
        }

        if ($direction === 'lower_is_worse') {
            if ($value <= (float) $critical) {
                return 'critical';
            }

            return $value <= (float) $warning ? 'warning' : 'healthy';
        }

        if ($value >= (float) $critical) {
            return 'critical';
        }

        return $value >= (float) $warning ? 'warning' : 'healthy';
    }

    private function worse(string $left, string $right): string
    {
        $rank = ['healthy' => 0, 'warning' => 1, 'critical' => 2];

        return ($rank[$right] ?? 0) > ($rank[$left] ?? 0) ? $right : $left;
    }
}
