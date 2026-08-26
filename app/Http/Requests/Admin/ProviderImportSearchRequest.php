<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class ProviderImportSearchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, list<mixed>> */
    public function rules(): array
    {
        return [
            'intent' => ['required', 'string', Rule::in(['artist', 'recording', 'lyrics'])],
            'query' => ['required', 'string', 'min:2', 'max:200'],
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'intent' => 'loại thông tin',
            'query' => 'nội dung tìm kiếm',
        ];
    }
}
