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
            $provider = $providerRelation instanceof Provider ? $providerRelation : null;
            $lastCheckedAt = $destination->last_checked_at;
            $fresh = $lastCheckedAt instanceof \DateTimeInterface
                && $lastCheckedAt >= now()->subDays(30);
            $url = $destination->url;

            $items[] = [
                'key' => $provider?->slug ?? '',
                'name' => $provider?->name ?? 'Provider',
                'provider_resource_id' => (string) $destination->provider_resource_id,
                'title' => (string) ($destination->title ?? ''),
                'channel_id' => $destination->channel_id,
                'channel_title' => $destination->channel_title,
                'duration_ms' => $destination->duration_ms,
                'status' => $fresh ? 'available' : 'stale',
                'status_label' => $fresh ? 'Có sẵn' : 'Cần kiểm tra lại',
                'availability_reason' => $fresh
                    ? 'Destination đã được người quản trị xác minh từ metadata provider.'
                    : 'Destination đã quá thời hạn freshness 30 ngày.',
                'compliance_state' => 'approved',
                'url' => is_string($url) ? $url : null,
                'market' => 'Theo khả dụng của provider',
                'checked_at' => $lastCheckedAt instanceof \DateTimeInterface ? $lastCheckedAt->format('Y-m-d') : null,
                'verified_at' => $destination->verified_at?->format('Y-m-d'),
                'embeddable' => $destination->is_embeddable === true,
                'privacy_status' => $destination->privacy_status,
                'capability_label' => $destination->is_embeddable === true ? 'Video embeddable' : 'Liên kết ngoài',
                'attribution' => $provider?->name ?? 'Provider',
                'action_label' => 'Mở '.($provider?->name ?? 'provider'),
            ];
        }

        return $items;
    }
}
