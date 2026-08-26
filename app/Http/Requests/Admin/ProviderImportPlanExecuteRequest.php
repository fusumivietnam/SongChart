<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class ProviderImportPlanExecuteRequest extends FormRequest
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
            'plan_fingerprint' => ['required', 'string', 'size:64'],
        ];
    }
}
