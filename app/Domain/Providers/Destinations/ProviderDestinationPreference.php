<?php

declare(strict_types=1);

namespace App\Domain\Providers\Destinations;

use App\Domain\Providers\Destinations\DTO\ProviderDestinationSnapshot;
use DateTimeImmutable;

final class ProviderDestinationPreference
{
    public const ISSUE_PROVIDER_UNAPPROVED = 'provider_unapproved';

    public const ISSUE_PROVIDER_DISABLED = 'provider_disabled';

    public const ISSUE_REVIEW_UNAPPROVED = 'review_unapproved';

    public const ISSUE_PRIVACY_NOT_PUBLIC = 'privacy_not_public';

    public const ISSUE_FRESHNESS_UNKNOWN = 'freshness_unknown';

    public const ISSUE_FRESHNESS_STALE = 'freshness_stale';

    public const ISSUE_UNSAFE_URL = 'unsafe_url';

    private const FRESHNESS_DAYS = 30;

    /**
     * @param  list<ProviderDestinationSnapshot>  $candidates
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
        return $this->publicEligibilityIssues($candidate, $now) === [];
    }

    /** @return list<string> */
    public function publicEligibilityIssues(ProviderDestinationSnapshot $candidate, DateTimeImmutable $now): array
    {
        $issues = [];

        if (! $candidate->providerApproved) {
            $issues[] = self::ISSUE_PROVIDER_UNAPPROVED;
        }
        if (! $candidate->providerEnabled) {
            $issues[] = self::ISSUE_PROVIDER_DISABLED;
        }
        if ($candidate->reviewState !== 'approved') {
            $issues[] = self::ISSUE_REVIEW_UNAPPROVED;
        }
        if ($candidate->privacyStatus !== 'public') {
            $issues[] = self::ISSUE_PRIVACY_NOT_PUBLIC;
        }
        if ($candidate->lastCheckedAt === null) {
            $issues[] = self::ISSUE_FRESHNESS_UNKNOWN;
        } elseif (! $this->isFresh($candidate, $now)) {
            $issues[] = self::ISSUE_FRESHNESS_STALE;
        }
        if (! $this->hasSafeOutboundUrl($candidate)) {
            $issues[] = self::ISSUE_UNSAFE_URL;
        }

        return $issues;
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
