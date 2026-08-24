<?php

declare(strict_types=1);

namespace App\Actions\Search;

use App\Contracts\Search\SearchCatalog;

final readonly class BuildSearchPage
{
    public function __construct(private SearchCatalog $catalog) {}

    /** @return array{query:string,type:string,sort:string,page:int,result:array<string,mixed>} */
    public function handle(string $query, string $type, string $sort, int $page): array
    {
        $result = $query === '' ? $this->emptyResult() : $this->catalog->search($query, $type, $sort, $page);

        if ($query !== '' && $page > (int) $result['last_page']) {
            abort(404);
        }

        return compact('query', 'type', 'sort', 'page', 'result');
    }

    /** @return array{items:list<never>,total:int,total_all:int,counts:array<never,never>,related:list<never>,page:int,per_page:int,last_page:int,from:int,to:int} */
    private function emptyResult(): array
    {
        return [
            'items' => [],
            'total' => 0,
            'total_all' => 0,
            'counts' => [],
            'related' => [],
            'page' => 1,
            'per_page' => 5,
            'last_page' => 1,
            'from' => 0,
            'to' => 0,
        ];
    }
}
