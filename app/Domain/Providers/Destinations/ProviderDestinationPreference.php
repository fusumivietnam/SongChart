<?php

declare(strict_types=1);

namespace App\Domain\Providers\Destinations;

use App\Domain\Providers\Destinations\DTO\ProviderDestinationSnapshot;
use DateTimeImmutable;

final class ProviderDestinationPreference
{
    private const FRESHNESS_DAYS = 30;

    /**
     * @param list<ProviderDestinationSnapshot> $candidates
     */
    public function select(array $candidates, DateTimeImmutable $now): ?ProviderDestinationSnapshot
    {
        $eligible = array_values(array_filter(
            $candidates,
            fn (ProviderDestinationSnapshot $candidate): bool => $this->isPublicEligible($candidate, $now),
        ));

        usort($eligible, function (ProviderDestinationSnapshot $left, ProviderDestinationSnapshot $right) use ($now): int {
            $leftRank = $this->rank($left, $now);
            $rightRank = $this->rank($right, $now);

            for ($index = 0; $index < 4; $index++) {
                $comparison = $rightRank[$index] <=> $leftRank[$index];
                if ($comparison !== 0) {
                    return $comparison;
                }
            }

            $providerComparison = $left->providerKey <=> $right->providerKey;
            if ($providerComparison !== 0) {
                return $providerComparison;
            }

            return $left->id <=> $right->id;
        });

        return $eligible[0] ?? null;
    }

    public function isPublicEligible(ProviderDestinationSnapshot $candidate, DateTimeImmutable $now): bool
    {
        return $candidate->providerApproved
            && $candidate->providerEnabled
            && $candidate->reviewState === 'approved'
            && $candidate->privacyStatus === 'public'
            && $this->isFresh($candidate, $now)
            && $this->hasSafeOutboundUrl($candidate);
    }

    public function canEmbed(ProviderDestinationSnapshot $candidate, DateTimeImmutable $now): bool
    {
        return $this->isPublicEligible($candidate, $now)
            && $candidate->embeddable
            && $candidate->resourceId !== '';
    }

    public function isFresh(ProviderDestinationSnapshot $candidate, DateTimeImmutable $now): bool
    {
        if ($candidate->lastCheckedAt === null) {
            return false;
        }

        return $candidate->lastCheckedAt->getTimestamp() >= $now->modify('-'.self::FRESHNESS_DAYS.' days')->getTimestamp();
    }

    private function hasSafeOutboundUrl(ProviderDestinationSnapshot $candidate): bool
    {
        if ($candidate->url === null || $candidate->url === '') {
            return false;
        }

        $parts = parse_url($candidate->url);

        return is_array($parts)
            && ($parts['scheme'] ?? null) === 'https'
            && is_string($parts['host'] ?? null)
            && $parts['host'] !== '';
    }

    /** @return array{0:int,1:int,2:int,3:int} */
    private function rank(ProviderDestinationSnapshot $candidate, DateTimeImmutable $now): array
    {
        return [
            $this->canEmbed($candidate, $now) ? 1 : 0,
            $candidate->lastCheckedAt?->getTimestamp() ?? 0,
            $candidate->matchScore,
            $candidate->verifiedAt?->getTimestamp() ?? 0,
        ];
    }
}
