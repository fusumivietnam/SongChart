<?php

declare(strict_types=1);

namespace App\Contracts\Search;

interface SearchCatalog
{
    /**
     * @return array{
     *   items: list<array<string,mixed>>,
     *   total: int,
     *   total_all: int,
     *   counts: array<string,int>,
     *   related: list<string>,
     *   page: int,
     *   per_page: int,
     *   last_page: int,
     *   from: int,
     *   to: int
     * }
     */
    public function search(string $query, string $type = 'all', string $sort = 'relevance', int $page = 1): array;

    /** @return array<string,mixed>|null */
    public function find(string $type, string $slug): ?array;

    /**
     * Deterministic homepage discovery payload.
     *
     * @return array{
     *   examples: list<string>,
     *   entity_entries: list<array{type:string,label:string,description:string,example:string}>,
     *   featured: list<array<string,mixed>>,
     *   editorial: array<string,mixed>
     * }
     */
    public function homepage(): array;
}
