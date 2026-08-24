<?php

declare(strict_types=1);

namespace App\Support\Providers\Mutation;

use App\Domain\Providers\Catalog\DTO\NormalizedProviderEntity;
use App\Domain\Providers\Mutation\Contracts\CanonicalMutationAction;
use App\Domain\Providers\Mutation\Contracts\CanonicalMutationPipeline;
use App\Domain\Providers\Mutation\DTO\CanonicalMutationResult;
use App\Models\Catalog\MetadataSource;
use App\Models\Provider;
use RuntimeException;

final readonly class DefaultCanonicalMutationPipeline implements CanonicalMutationPipeline
{
    /** @param iterable<CanonicalMutationAction> $actions */
    public function __construct(private iterable $actions) {}

    public function apply(NormalizedProviderEntity $entity): CanonicalMutationResult
    {
        $provider = Provider::query()->where('slug', $entity->providerSlug)->firstOrFail();
        $source = MetadataSource::query()->firstOrCreate([
            'key' => 'provider:'.$entity->providerSlug,
        ], [
            'provider_id' => $provider->getKey(),
            'name' => (string) $provider->name,
            'source_type' => 'provider-import',
        ]);

        foreach ($this->actions as $action) {
            if ($action->supports($entity->entityType)) {
                return $action->mutate($entity, $source);
            }
        }

        throw new RuntimeException('No canonical mutation action supports the normalized entity type.');
    }
}
