<?php

declare(strict_types=1);

namespace App\Support\Local;

use App\Contracts\Providers\Catalog\ProviderCatalogAdapterRegistry;
use App\Contracts\Providers\Rate\ProviderRatePolicyRegistry;
use App\Contracts\Providers\Rate\ProviderRequestGate;
use App\Domain\Providers\Identity\Review\Enums\IdentityConflictReviewStatus;
use App\Domain\Providers\Ingestion\Enums\ProviderImportItemStatus;
use App\Domain\Providers\Ingestion\Enums\ProviderImportRunStatus;
use App\Models\Catalog\Artist;
use App\Models\Provider;
use App\Models\Providers\Identity\IdentityConflictReview;
use App\Models\Providers\Ingestion\ProviderImportItem;
use App\Models\Providers\Ingestion\ProviderImportRun;
use Illuminate\Database\QueryException;
use Throwable;

final class DevelopmentControlCenter
{
    public function __construct(
        private readonly ProviderCatalogAdapterRegistry $adapters,
        private readonly ProviderRatePolicyRegistry $ratePolicies,
        private readonly ProviderRequestGate $requestGate,
    ) {}

    /** @return array<string, array{status: string, detail: string}> */
    public function providers(): array
    {
        $configured = (bool) config('songchart.providers.musicbrainz.enabled', false);
        $userAgent = (string) config('songchart.providers.musicbrainz.user_agent', '');
        $contactReady = $userAgent !== '' && ! str_contains($userAgent, 'contact@example.com');
        $registered = in_array('musicbrainz', $this->adapters->slugs(), true);

        try {
            $provider = Provider::query()->where('slug', 'musicbrainz')->first();
            $databaseState = $provider === null ? 'not seeded' : ($provider->is_enabled ? 'enabled' : 'disabled');
        } catch (Throwable) {
            $databaseState = 'database unavailable';
        }

        $rateState = $this->requestGate->state($this->ratePolicies->for('musicbrainz', 'artist.search'));
        $ready = $configured && $contactReady && $registered;

        return [
            'MusicBrainz' => [
                'status' => $ready ? 'READY' : 'CONFIGURE',
                'detail' => sprintf('adapter=%s · live=%s · user-agent=%s · registry=%s · rate=%s%s', $registered ? 'registered' : 'missing', $configured ? 'on' : 'off', $contactReady ? 'valid' : 'placeholder', $databaseState, $rateState->status(), $rateState->cooldownRemainingSeconds > 0 ? '('.$rateState->cooldownRemainingSeconds.'s)' : ''),
            ],
        ];
    }

    /** @return array<string, int|string> */
    public function pipeline(): array
    {
        try {
            $latest = ProviderImportRun::query()->latest('created_at')->first();

            return [
                'canonical_artists' => Artist::query()->count(),
                'queued_imports' => ProviderImportRun::query()->where('status', ProviderImportRunStatus::Queued->value)->count(),
                'running_imports' => ProviderImportRun::query()->where('status', ProviderImportRunStatus::Running->value)->count(),
                'failed_imports' => ProviderImportRun::query()->where('status', ProviderImportRunStatus::Failed->value)->count(),
                'import_items' => ProviderImportItem::query()->count(),
                'applied_items' => ProviderImportItem::query()->where('status', ProviderImportItemStatus::Applied->value)->count(),
                'identity_conflicts_open' => IdentityConflictReview::query()->where('status', IdentityConflictReviewStatus::Open->value)->count(),
                'latest_import' => $latest === null ? 'none' : ProviderImportRunStatus::from((string) $latest->getRawOriginal('status'))->value.' · '.(string) $latest->getKey(),
            ];
        } catch (QueryException) {
            return ['state' => 'database unavailable'];
        } catch (Throwable $exception) {
            return ['state' => 'control-center error: '.$exception::class];
        }
    }
}
