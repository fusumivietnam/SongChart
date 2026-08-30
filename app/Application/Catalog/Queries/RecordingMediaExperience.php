<?php

declare(strict_types=1);

namespace App\Application\Catalog\Queries;

use App\Domain\Catalog\Enums\EntityType;
use App\Models\Catalog\Recording;
use App\Models\Provider;
use App\Support\Providers\Destinations\EloquentProviderDestinationSelector;

final readonly class RecordingMediaExperience
{
    public function __construct(private EloquentProviderDestinationSelector $selector) {}

    /** @return array<string, mixed>|null */
    public function forSlug(string $recordingSlug): ?array
    {
        $recording = Recording::query()->where('slug', $recordingSlug)->first();
        if (! $recording instanceof Recording) {
            return null;
        }

        $selection = $this->selector->select(EntityType::Recording, (string) $recording->getKey());
        if ($selection === null) {
            return null;
        }

        $destination = $selection->destination;
        $providerRelation = $destination->getRelation('provider');
        $providerSlug = '';
        $providerName = 'Provider';
        if ($providerRelation instanceof Provider) {
            $providerSlug = $providerRelation->slug;
            $providerName = $providerRelation->name;
        }

        $lastCheckedAt = $destination->getAttribute('last_checked_at');
        $resourceId = (string) $destination->getAttribute('provider_resource_id');
        $url = $destination->getAttribute('url');
        $youtube = $providerSlug === 'youtube';
        $canEmbed = $youtube && $selection->canEmbed;

        return [
            'provider' => $providerName,
            'provider_key' => $providerSlug,
            'title' => (string) ($destination->getAttribute('title') ?: 'Video'),
            'channel_title' => (string) ($destination->getAttribute('channel_title') ?: ''),
            'fresh' => $selection->fresh,
            'checked_at' => $lastCheckedAt instanceof \DateTimeInterface ? $lastCheckedAt->format('Y-m-d') : null,
            'can_embed' => $canEmbed,
            'embed_url' => $canEmbed ? 'https://www.youtube-nocookie.com/embed/'.rawurlencode($resourceId) : null,
            'url' => is_string($url) && $url !== '' ? $url : null,
            'availability_label' => $canEmbed ? 'Phát video đã xác minh' : 'Mở trên provider',
            'availability_reason' => $canEmbed
                ? 'Destination đã được duyệt, còn mới, công khai và cho phép nhúng.'
                : 'Destination đã được duyệt, còn mới và công khai nhưng không đủ điều kiện nhúng trong SongChart.',
        ];
    }
}
