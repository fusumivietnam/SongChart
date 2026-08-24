<?php

declare(strict_types=1);

use App\Contracts\Providers\ProviderAdapter;
use App\Contracts\Providers\ProviderHealth;
use App\Support\Providers\ProviderAdapterRegistry;

function providerAdapterFixture(string $slug): ProviderAdapter
{
    return new class($slug) implements ProviderAdapter
    {
        public function __construct(private readonly string $slug) {}

        public function providerSlug(): string
        {
            return $this->slug;
        }

        public function capabilities(): array
        {
            return [];
        }

        public function healthCheck(): ProviderHealth
        {
            return new ProviderHealth(true, 'ok');
        }
    };
}

it('resolves adapters by provider slug', function (): void {
    $adapter = providerAdapterFixture('fixture');
    $registry = new ProviderAdapterRegistry([$adapter]);

    expect($registry->for('fixture'))->toBe($adapter)
        ->and($registry->for('missing'))->toBeNull()
        ->and($registry->slugs())->toBe(['fixture']);
});

it('rejects duplicate provider adapter slugs', function (): void {
    expect(fn (): ProviderAdapterRegistry => new ProviderAdapterRegistry([
        providerAdapterFixture('duplicate'),
        providerAdapterFixture('duplicate'),
    ]))->toThrow(InvalidArgumentException::class);
});
