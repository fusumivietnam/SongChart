<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Catalog\Artist;
use App\Models\Catalog\Collection;
use App\Models\Catalog\CollectionItem;
use App\Models\Catalog\EntityMatch;
use App\Models\Catalog\EntityRelationship;
use App\Models\Catalog\ExternalIdentifier;
use App\Models\Catalog\MetadataAssertion;
use App\Models\Catalog\MetadataConflict;
use App\Models\Catalog\MetadataSource;
use App\Models\Catalog\Recording;
use App\Models\Catalog\RecordingVersion;
use App\Models\Catalog\Release;
use App\Models\Catalog\ReleaseTrack;
use App\Models\Catalog\Work;
use App\Models\Provider;
use App\Models\ProviderEntity;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

final class CatalogFixtureSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            $musicBrainz = Provider::query()->firstOrCreate(
                ['slug' => 'musicbrainz'],
                ['name' => 'MusicBrainz', 'category' => 'music', 'status' => 'approved', 'is_enabled' => false],
            );

            $source = MetadataSource::query()->firstOrCreate(
                ['key' => 'musicbrainz-fixture'],
                ['provider_id' => $musicBrainz->id, 'name' => 'MusicBrainz fixture', 'source_type' => 'provider', 'reference_url' => 'https://musicbrainz.org', 'license_name' => 'CC0', 'retention_policy' => 'fixture_only'],
            );

            $radiohead = Artist::query()->firstOrCreate(
                ['slug' => 'radiohead'],
                ['name' => 'Radiohead', 'sort_name' => 'Radiohead', 'artist_type' => 'group', 'country_code' => 'GB', 'verification_state' => 'verified'],
            );
            $work = Work::query()->firstOrCreate(
                ['slug' => 'paranoid-android-work'],
                ['title' => 'Paranoid Android', 'work_type' => 'song', 'language_code' => 'en', 'verification_state' => 'verified'],
            );
            $recording = Recording::query()->firstOrCreate(
                ['slug' => 'paranoid-android'],
                ['work_id' => $work->id, 'title' => 'Paranoid Android', 'duration_ms' => 383000, 'is_explicit' => false, 'verification_state' => 'verified'],
            );
            $release = Release::query()->firstOrCreate(
                ['slug' => 'ok-computer'],
                ['title' => 'OK Computer', 'release_type' => 'album', 'released_on' => '1997-05-21', 'country_code' => 'GB', 'barcode' => '724385522925', 'verification_state' => 'verified'],
            );

            DB::table('artist_recording')->updateOrInsert(
                ['artist_id' => $radiohead->id, 'recording_id' => $recording->id, 'credit_role' => 'primary'],
                ['position' => 1, 'created_at' => now(), 'updated_at' => now()],
            );
            DB::table('artist_release')->updateOrInsert(
                ['artist_id' => $radiohead->id, 'release_id' => $release->id, 'credit_role' => 'primary'],
                ['position' => 1, 'created_at' => now(), 'updated_at' => now()],
            );
            ReleaseTrack::query()->firstOrCreate(
                ['release_id' => $release->id, 'disc_number' => 1, 'track_number' => 2],
                ['recording_id' => $recording->id],
            );
            RecordingVersion::query()->firstOrCreate(
                ['recording_id' => $recording->id, 'name' => 'Album version'],
                ['slug' => 'paranoid-android-album-version', 'version_type' => 'album', 'duration_ms' => 383000, 'verification_state' => 'verified'],
            );

            $collection = Collection::query()->firstOrCreate(
                ['slug' => 'alternative-essentials'],
                ['title' => 'Alternative Essentials', 'description' => 'Deterministic Stage 12 catalog fixture.', 'visibility' => 'public', 'verification_state' => 'verified'],
            );
            CollectionItem::query()->firstOrCreate(
                ['collection_id' => $collection->id, 'entity_type' => 'release', 'entity_id' => $release->id],
                ['position' => 1],
            );

            ExternalIdentifier::query()->firstOrCreate(
                ['namespace' => 'musicbrainz_artist', 'value' => 'a74b1b7f-71a5-4011-9441-d0b5e4122711'],
                ['entity_type' => 'artist', 'entity_id' => $radiohead->id, 'metadata_source_id' => $source->id, 'is_primary' => true, 'verification_state' => 'verified'],
            );
            $assertionA = MetadataAssertion::query()->firstOrCreate(
                ['entity_type' => 'release', 'entity_id' => $release->id, 'field_name' => 'released_on', 'metadata_source_id' => $source->id, 'value_fingerprint' => hash('sha256', '1997-05-21')],
                ['value' => ['date' => '1997-05-21'], 'verification_state' => 'verified', 'confidence' => 1, 'observed_at' => now()],
            );
            $editorialSource = MetadataSource::query()->firstOrCreate(
                ['key' => 'songchart-editorial-fixture'],
                ['name' => 'SongChart Editorial fixture', 'source_type' => 'editorial', 'retention_policy' => 'project_owned'],
            );
            $assertionB = MetadataAssertion::query()->firstOrCreate(
                ['entity_type' => 'release', 'entity_id' => $release->id, 'field_name' => 'released_on', 'metadata_source_id' => $editorialSource->id, 'value_fingerprint' => hash('sha256', '1997-06-16')],
                ['value' => ['date' => '1997-06-16'], 'verification_state' => 'candidate', 'confidence' => 0.7, 'observed_at' => now()],
            );
            MetadataConflict::query()->firstOrCreate(
                ['left_assertion_id' => $assertionA->id, 'right_assertion_id' => $assertionB->id],
                ['entity_type' => 'release', 'entity_id' => $release->id, 'field_name' => 'released_on', 'status' => 'open'],
            );

            $providerEntity = ProviderEntity::query()->firstOrCreate(
                ['provider_id' => $musicBrainz->id, 'entity_type' => 'artist', 'external_id' => 'a74b1b7f-71a5-4011-9441-d0b5e4122711', 'market' => null],
                ['canonical_url' => 'https://musicbrainz.org/artist/a74b1b7f-71a5-4011-9441-d0b5e4122711', 'status' => 'active', 'fetched_at' => now()],
            );
            EntityMatch::query()->firstOrCreate(
                ['provider_entity_id' => $providerEntity->id, 'entity_type' => 'artist', 'entity_id' => $radiohead->id],
                ['status' => 'matched', 'match_method' => 'fixture', 'confidence' => 1, 'evidence' => 'Deterministic Stage 12 fixture.'],
            );
            EntityRelationship::query()->firstOrCreate(
                ['subject_type' => 'recording', 'subject_id' => $recording->id, 'relationship_type' => 'performed_by', 'object_type' => 'artist', 'object_id' => $radiohead->id],
                ['metadata_source_id' => $source->id, 'verification_state' => 'verified'],
            );
        });
    }
}
