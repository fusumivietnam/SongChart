<?php

declare(strict_types=1);

namespace App\Application\Catalog\Queries;

use App\Domain\Catalog\Enums\EntityType;
use App\Models\Catalog\Recording;
use App\Models\Provider;
use App\Models\ProviderDestination;

final class RecordingMediaExperience
{
    /** @return array<string, mixed>|null */
    public function forSlug(string $recordingSlug): ?array
    {
        $recording = Recording::query()->where('slug', $recordingSlug)->first();
        if (! $recording instanceof Recording) {
            return null;
        }

        $destination = ProviderDestination::query()
            ->where('entity_type', EntityType::Recording->value)
            ->where('entity_id', $recording->getKey())
            ->where('review_state', 'approved')
            ->with('provider')
            ->latest('verified_at')
            ->first();

        if (! $destination instanceof ProviderDestination) {
            return null;
        }

        $providerRelation = $destination->getRelation('provider');
        $providerSlug = '';
        $providerName = 'Provider';
        if ($providerRelation instanceof Provider) {
            $providerSlug = $providerRelation->slug;
            $providerName = $providerRelation->name;
        }

        $lastCheckedAt = $destination->getAttribute('last_checked_at');
        $fresh = $lastCheckedAt instanceof \DateTimeInterface && $lastCheckedAt >= now()->subDays(30);
        $embeddable = $destination->getAttribute('is_embeddable') === true;
        $resourceId = (string) $destination->getAttribute('provider_resource_id');
        $url = $destination->getAttribute('url');
        $youtube = $providerSlug === 'youtube';
        $canEmbed = $youtube && $fresh && $embeddable && $resourceId !== '';

        return [
            'provider' => $providerName,
            'provider_key' => $providerSlug,
            'title' => (string) ($destination->getAttribute('title') ?: 'Video'),
            'channel_title' => (string) ($destination->getAttribute('channel_title') ?: ''),
            'fresh' => $fresh,
            'checked_at' => $lastCheckedAt instanceof \DateTimeInterface ? $lastCheckedAt->format('Y-m-d') : null,
            'can_embed' => $canEmbed,
            'embed_url' => $canEmbed ? 'https://www.youtube-nocookie.com/embed/'.rawurlencode($resourceId) : null,
            'url' => is_string($url) && $url !== '' ? $url : null,
            'availability_label' => $canEmbed ? 'Phát video đã xác minh' : ($fresh ? 'Mở trên provider' : 'Cần kiểm tra lại'),
            'availability_reason' => $canEmbed
                ? 'Video đã được duyệt, còn trong thời hạn kiểm tra và cho phép nhúng.'
                : ($fresh
                    ? 'Destination đã được duyệt nhưng không đủ điều kiện nhúng an toàn trong SongChart.'
                    : 'Destination đã quá thời hạn kiểm tra 30 ngày; SongChart không tự động nhúng nội dung cũ.'),
        ];
    }
}
