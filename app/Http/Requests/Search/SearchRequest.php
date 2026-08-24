<?php

declare(strict_types=1);

namespace App\Http\Requests\Search;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class SearchRequest extends FormRequest
{
    /** @return array<string, list<mixed>> */
    public function rules(): array
    {
        return [
            'q' => ['nullable', 'string', 'max:100'],
            'type' => ['nullable', Rule::in(['all', 'artist', 'recording', 'release', 'version', 'work', 'collection'])],
            'sort' => ['nullable', Rule::in(['relevance', 'title', 'year_desc'])],
            'page' => ['nullable', 'integer', 'min:1', 'max:1000'],
        ];
    }

    public function queryText(): string
    {
        return trim((string) $this->validated('q', ''));
    }

    public function entityType(): string
    {
        return (string) $this->validated('type', 'all');
    }

    public function sortOrder(): string
    {
        return (string) $this->validated('sort', 'relevance');
    }

    public function pageNumber(): int
    {
        return (int) $this->validated('page', 1);
    }
}
