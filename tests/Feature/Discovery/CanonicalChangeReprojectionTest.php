<?php

declare(strict_types=1);

use App\Domain\Catalog\Enums\EntityType;
use App\Domain\Catalog\Events\CanonicalEntityChanged;
use App\Jobs\Chart\RefreshYouTubeViewChartJob;
use App\Jobs\Discovery\BuildDiscoveryProjection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

it('queues only active discovery channels for the changed canonical entity type', function (): void {
    Bus::fake();

    $artistChannel = (string) Str::ulid();
    $recordingChannel = (string) Str::ulid();
    $draftArtistChannel = (string) Str::ulid();

    foreach ([
        [$artistChannel, 'artists-active', EntityType::Artist->value, 'active'],
        [$recordingChannel, 'recordings-active', EntityType::Recording->value, 'active'],
        [$draftArtistChannel, 'artists-draft', EntityType::Artist->value, 'draft'],
    ] as [$id, $key, $entityType, $status]) {
        DB::table('discovery_channels')->insert([
            'id' => $id,
            'key' => $key,
            'slug' => $key,
            'name' => $key,
            'entity_type' => $entityType,
            'mode' => 'manual',
            'status' => $status,
            'rule_schema_version' => 1,
            'presentation' => json_encode([], JSON_THROW_ON_ERROR),
            'default_limit' => 20,
            'publication_timezone' => 'UTC',
            'revision' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    event(new CanonicalEntityChanged(EntityType::Artist, (string) Str::ulid(), 'name'));

    Bus::assertDispatched(BuildDiscoveryProjection::class, fn (BuildDiscoveryProjection $job): bool => $job->channelId === $artistChannel);
    Bus::assertNotDispatched(BuildDiscoveryProjection::class, fn (BuildDiscoveryProjection $job): bool => in_array($job->channelId, [$recordingChannel, $draftArtistChannel], true));
    Bus::assertNotDispatched(RefreshYouTubeViewChartJob::class);
});

it('queues one deduplicated chart refresh for canonical Recording changes when YouTube is enabled', function (): void {
    config()->set('songchart.providers.youtube.enabled', true);
    Bus::fake();

    event(new CanonicalEntityChanged(EntityType::Recording, (string) Str::ulid(), 'title'));

    Bus::assertDispatched(RefreshYouTubeViewChartJob::class, 1);
});
