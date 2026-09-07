<?php

declare(strict_types=1);

namespace App\Application\Catalog\Queries;

use App\Domain\Catalog\Enums\EntityType;
use App\Models\Provider;
use App\Models\ProviderDestination;
use Illuminate\Database\Eloquent\Model;

final class PublicProviderDestinationReadModel
{
    /** @return list<array<string, mixed>> */
    public function for(EntityType $type, Model $model): array
    {
        $destinations = ProviderDestination::query()
            ->where('entity_type', $type->value)
            ->where('entity_id', $model->getKey())
            ->where('review_state', 'approved')
            ->with('provider')
            ->latest('verified_at')
            ->get();

        $items = [];
        foreach ($destinations as $destination) {
            $providerRelation = $destination->getRelation('provider');
            if (! $providerRelation instanceof Provider) {
                continue;
            }

            $lastCheckedAt = $destination->getAttribute('last_checked_at');
            $verifiedAt = $destination->getAttribute('verified_at');
            $fresh = $lastCheckedAt instanceof \DateTimeInterface
                && $lastCheckedAt >= now()->subDays(30);
            $url = $destination->getAttribute('url');
            $embeddable = $destination->getAttribute('is_embeddable') === true;

            $items[] = [
                'key' => $providerRelation->slug,
                'name' => $providerRelation->name,
                'provider_resource_id' => (string) $destination->getAttribute('provider_resource_id'),
                'title' => (string) ($destination->getAttribute('title') ?? ''),
                'channel_id' => $destination->getAttribute('channel_id'),
                'channel_title' => $destination->getAttribute('channel_title'),
                'duration_ms' => $destination->getAttribute('duration_ms'),
                'status' => $fresh ? 'available' : 'stale',
                'status_label' => $fresh ? 'Có sẵn' : 'Cần kiểm tra lại',
                'availability_reason' => $fresh
                    ? 'Destination đã được người quản trị xác minh từ metadata provider.'
                    : 'Destination đã quá thời hạn freshness 30 ngày.',
                'compliance_state' => 'approved',
                'url' => is_string($url) ? $url : null,
                'market' => 'Theo khả dụng của provider',
                'checked_at' => $lastCheckedAt instanceof \DateTimeInterface ? $lastCheckedAt->format('Y-m-d') : null,
                'verified_at' => $verifiedAt instanceof \DateTimeInterface ? $verifiedAt->format('Y-m-d') : null,
                'embeddable' => $embeddable,
                'privacy_status' => $destination->getAttribute('privacy_status'),
                'capability_label' => $embeddable ? 'Video embeddable' : 'Liên kết ngoài',
                'attribution' => $providerRelation->name,
                'action_label' => 'Mở '.$providerRelation->name,
            ];
        }

        return $items;
    }
}
