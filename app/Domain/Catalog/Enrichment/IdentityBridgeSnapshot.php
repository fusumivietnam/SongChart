<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Enrichment;

final readonly class IdentityBridgeSnapshot
{
    /**
     * @param  list<array{namespace:string,value:string,primary:bool,verified:string}>  $identifiers
     * @param  list<array{provider:string,resource_id:string,url:string|null,review_state:string}>  $destinations
     */
    public function __construct(
        public array $identifiers,
        public array $destinations,
        public int $connectedSourceCount,
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'identifiers' => $this->identifiers,
            'destinations' => $this->destinations,
            'connected_source_count' => $this->connectedSourceCount,
        ];
    }
}
