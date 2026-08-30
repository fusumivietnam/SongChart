<?php

declare(strict_types=1);

namespace App\Support\Admin;

use App\Domain\Providers\Destinations\DTO\ProviderDestinationSnapshot;
use App\Domain\Providers\Destinations\ProviderDestinationPreference;
use App\Domain\Providers\Enums\ProviderStatus;
use App\Models\Provider;
use App\Models\ProviderDestination;
use Carbon\CarbonImmutable;
use DateTimeInterface;

final readonly class ProviderDestinationAttention
{
    public function __construct(private ProviderDestinationPreference $preference) {}

    /**
     * @return array{
     *   summary: array{total:int,attention:int,unknown:int,stale:int,unavailable:int},
     *   items: list<array<string, mixed>>
     * }
     */
    public function forProvider(Provider $provider): array
    {
        $providerStatus = $provider->getAttribute('status');
        $providerApproved = $providerStatus instanceof ProviderStatus
            && $providerStatus === ProviderStatus::Approved;
        $now = CarbonImmutable::now();

        $destinations = ProviderDestination::query()
            ->where('provider_id', $provider->getKey())
            ->latest('updated_at')
            ->limit(50)
            ->get();

        $items = [];
        $summary = [
            'total' => $destinations->count(),
            'attention' => 0,
            'unknown' => 0,
            'stale' => 0,
            'unavailable' => 0,
        ];

        foreach ($destinations as $destination) {
            $privacyStatus = $destination->getAttribute('privacy_status');
            $url = $destination->getAttribute('url');
            $verifiedAt = $destination->getAttribute('verified_at');
            $lastCheckedAt = $destination->getAttribute('last_checked_at');

            $snapshot = new ProviderDestinationSnapshot(
                id: (string) $destination->getKey(),
                providerKey: (string) $provider->getAttribute('slug'),
                providerApproved: $providerApproved,
                providerEnabled: $provider->getAttribute('is_enabled') === true,
                reviewState: (string) $destination->getAttribute('review_state'),
                privacyStatus: is_string($privacyStatus) ? $privacyStatus : null,
                embeddable: $destination->getAttribute('is_embeddable') === true,
                url: is_string($url) ? $url : null,
                resourceId: (string) $destination->getAttribute('provider_resource_id'),
                matchScore: (int) $destination->getAttribute('match_score'),
                verifiedAt: $verifiedAt instanceof DateTimeInterface ? $verifiedAt : null,
                lastCheckedAt: $lastCheckedAt instanceof DateTimeInterface ? $lastCheckedAt : null,
            );

            $issues = $this->preference->publicEligibilityIssues($snapshot, $now);
            $state = $this->state($snapshot, $issues, $now);
            if ($state['attention']) {
                $summary['attention']++;
                if (isset($summary[$state['key']])) {
                    $summary[$state['key']]++;
                }
            }

            $items[] = [
                'id' => $snapshot->id,
                'title' => (string) ($destination->getAttribute('title') ?: $snapshot->resourceId),
                'resource_id' => $snapshot->resourceId,
                'entity_type' => $destination->getAttribute('entity_type')?->value ?? (string) $destination->getAttribute('entity_type'),
                'entity_id' => (string) $destination->getAttribute('entity_id'),
                'review_state' => $snapshot->reviewState,
                'privacy_status' => $snapshot->privacyStatus,
                'last_checked_at' => $snapshot->lastCheckedAt,
                'verified_at' => $snapshot->verifiedAt,
                'status_key' => $state['key'],
                'status_label' => $state['label'],
                'tone' => $state['tone'],
                'attention' => $state['attention'],
                'next_action' => $state['next_action'],
                'reasons' => array_map($this->reasonLabel(...), $issues),
            ];
        }

        return ['summary' => $summary, 'items' => $items];
    }

    /**
     * @param  list<string>  $issues
     * @return array{key:string,label:string,tone:string,attention:bool,next_action:string}
     */
    private function state(ProviderDestinationSnapshot $snapshot, array $issues, CarbonImmutable $now): array
    {
        if (in_array(ProviderDestinationPreference::ISSUE_FRESHNESS_UNKNOWN, $issues, true)) {
            return [
                'key' => 'unknown',
                'label' => 'Chưa kiểm tra',
                'tone' => 'warning',
                'attention' => true,
                'next_action' => 'Xác minh lại destination trước khi cho phép xuất hiện công khai.',
            ];
        }

        if (in_array(ProviderDestinationPreference::ISSUE_FRESHNESS_STALE, $issues, true)) {
            return [
                'key' => 'stale',
                'label' => 'Quá hạn kiểm tra',
                'tone' => 'warning',
                'attention' => true,
                'next_action' => 'Kiểm tra lại availability, privacy và embeddability với provider.',
            ];
        }

        if ($issues !== []) {
            return [
                'key' => 'unavailable',
                'label' => 'Không khả dụng',
                'tone' => 'danger',
                'attention' => true,
                'next_action' => 'Rà soát nguyên nhân bên dưới; không dùng destination này cho public selection cho đến khi evidence hợp lệ.',
            ];
        }

        if ($this->preference->canEmbed($snapshot, $now)) {
            return [
                'key' => 'ready',
                'label' => 'Sẵn sàng phát',
                'tone' => 'success',
                'attention' => false,
                'next_action' => 'Không cần xử lý.',
            ];
        }

        return [
            'key' => 'outbound_only',
            'label' => 'Chỉ mở ngoài',
            'tone' => 'info',
            'attention' => false,
            'next_action' => 'Destination vẫn hợp lệ để mở ngoài nhưng không đủ điều kiện phát nhúng.',
        ];
    }

    private function reasonLabel(string $issue): string
    {
        return match ($issue) {
            ProviderDestinationPreference::ISSUE_PROVIDER_UNAPPROVED => 'Provider chưa được phê duyệt.',
            ProviderDestinationPreference::ISSUE_PROVIDER_DISABLED => 'Provider đang tạm ngừng.',
            ProviderDestinationPreference::ISSUE_REVIEW_UNAPPROVED => 'Destination chưa được duyệt.',
            ProviderDestinationPreference::ISSUE_PRIVACY_NOT_PUBLIC => 'Privacy chưa xác nhận là public.',
            ProviderDestinationPreference::ISSUE_FRESHNESS_UNKNOWN => 'Chưa có thời điểm kiểm tra gần nhất.',
            ProviderDestinationPreference::ISSUE_FRESHNESS_STALE => 'Lần kiểm tra gần nhất đã quá hạn freshness.',
            ProviderDestinationPreference::ISSUE_UNSAFE_URL => 'URL outbound không phải HTTPS hợp lệ.',
            default => 'Destination chưa đáp ứng policy công khai.',
        };
    }
}
