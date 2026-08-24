<?php

declare(strict_types=1);

namespace App\Support\Admin;

use App\Domain\Audit\Contracts\PrivilegedAuditLogger;
use App\Models\Catalog\Artist;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class CanonicalArtistAdministration
{
    public function __construct(private readonly PrivilegedAuditLogger $audit) {}

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function applyArtistUpdate(string $artistId, array $attributes, User $actor, string $rationale): Artist
    {
        return DB::transaction(function () use ($artistId, $attributes, $actor, $rationale): Artist {
            $artist = Artist::query()->lockForUpdate()->find($artistId);
            if (! $artist) {
                throw new NotFoundHttpException;
            }

            $before = $this->snapshot($artist);

            $artist->fill($attributes);
            $artist->save();
            $artist->refresh();

            $this->audit->record(
                event: 'catalog.artist.updated',
                description: 'Canonical Artist metadata updated by an administrator.',
                subject: $artist,
                actor: $actor,
                before: $before,
                after: $this->snapshot($artist),
                rationale: $rationale,
            );

            return $artist;
        });
    }

    /** @return array<string, mixed> */
    private function snapshot(Artist $artist): array
    {
        return [
            'name' => $artist->name,
            'sort_name' => $artist->sort_name,
            'slug' => $artist->slug,
            'artist_type' => $artist->artist_type,
            'country_code' => $artist->country_code,
            'verification_state' => $artist->getRawOriginal('verification_state'),
        ];
    }
}
