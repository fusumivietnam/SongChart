<?php

declare(strict_types=1);

namespace App\Application\Catalog\Queries;

use App\Domain\Catalog\Enums\EntityType;
use App\Domain\Providers\Destinations\ProviderDestinationPreference;
use App\Models\Catalog\Recording;
use App\Models\Provider;
use App\Support\Providers\Destinations\EloquentProviderDestinationSelector;
use App\Support\Providers\Destinations\PublicProviderDestinationProjection;

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

        $projection = $this->selector->project(EntityType::Recording, (string) $recording->getKey());
        if ($projection->selection === null) {
            return [
                'state' => PublicProviderDestinationProjection::STATE_NO_SELECTION,
                'reason_codes' => $projection->reasonCodes,
                'selection_reason' => 'Không có destination nào đáp ứng đầy đủ policy công khai hiện tại.',
                'availability_label' => 'Chưa có media khả dụng',
                'availability_reason' => $this->noSelectionReason($projection->reasonCodes),
                'can_embed' => false,
                'fresh' => false,
                'embed_url' => null,
                'url' => null,
            ];
        }

        $selection = $projection->selection;
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
            'state' => $canEmbed
                ? PublicProviderDestinationProjection::STATE_PLAYABLE
                : PublicProviderDestinationProjection::STATE_OUTBOUND_ONLY,
            'reason_codes' => $projection->reasonCodes,
            'selection_reason' => 'Destination công khai đủ điều kiện có thứ hạng deterministic cao nhất được chọn.',
            'provider' => $providerName,
            'provider_key' => $providerSlug,
            'title' => (string) ($destination->getAttribute('title') ?: 'Video'),
            'channel_title' => (string) ($destination->getAttribute('channel_title') ?: ''),
            'fresh' => $selection->fresh,
            'checked_at' => $lastCheckedAt instanceof \DateTimeInterface ? $lastCheckedAt->format('Y-m-d') : null,
            'can_embed' => $canEmbed,
            'embed_url' => $canEmbed ? 'https://www.youtube-nocookie.com/embed/'.rawurlencode($resourceId) : null,
            'url' => is_string($url) && $url !== '' ? $url : null,
            'availability_label' => $canEmbed ? 'Phát video đã xác minh' : 'Chỉ mở trên provider',
            'availability_reason' => $canEmbed
                ? 'Destination đã được duyệt, còn mới, công khai và cho phép nhúng.'
                : 'Destination đã được duyệt, còn mới và công khai nhưng chỉ đủ điều kiện mở ngoài SongChart.',
        ];
    }

    /** @param list<string> $reasonCodes */
    private function noSelectionReason(array $reasonCodes): string
    {
        if ($reasonCodes === []) {
            return 'Recording chưa có destination media đã được xác minh để sử dụng công khai.';
        }

        $messages = [];
        foreach ($reasonCodes as $reasonCode) {
            $message = match ($reasonCode) {
                ProviderDestinationPreference::ISSUE_PROVIDER_UNAPPROVED,
                ProviderDestinationPreference::ISSUE_PROVIDER_DISABLED => 'Provider hiện chưa đủ điều kiện công khai.',
                ProviderDestinationPreference::ISSUE_REVIEW_UNAPPROVED => 'Destination chưa được phê duyệt.',
                ProviderDestinationPreference::ISSUE_PRIVACY_NOT_PUBLIC => 'Media chưa được xác nhận là public.',
                ProviderDestinationPreference::ISSUE_FRESHNESS_UNKNOWN,
                ProviderDestinationPreference::ISSUE_FRESHNESS_STALE => 'Evidence media chưa đủ mới để sử dụng công khai.',
                ProviderDestinationPreference::ISSUE_UNSAFE_URL => 'Destination không có URL HTTPS hợp lệ.',
                default => 'Destination chưa đáp ứng policy công khai.',
            };
            $messages[$message] = true;
        }

        return 'Không có destination đủ điều kiện. '.implode(' ', array_keys($messages));
    }
}
