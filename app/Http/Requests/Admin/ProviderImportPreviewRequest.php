<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class ProviderImportPreviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, list<mixed>> */
    public function rules(): array
    {
        return [
            'provider_slug' => ['required', 'string', Rule::in(['musicbrainz'])],
            'entity_type' => ['required', 'string', Rule::in(['artist', 'recording'])],
            'external_id' => ['required', 'string', 'max:255'],
            'payload_json' => ['required', 'string', 'max:1048576', 'json'],
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'provider_slug' => 'nguồn dữ liệu',
            'entity_type' => 'loại thực thể',
            'external_id' => 'mã định danh bên nguồn',
            'payload_json' => 'dữ liệu JSON',
        ];
    }
}
