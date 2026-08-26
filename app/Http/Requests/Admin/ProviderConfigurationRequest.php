<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

final class ProviderConfigurationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, list<mixed>> */
    public function rules(): array
    {
        return [
            'musicbrainz_user_agent' => ['nullable', 'string', 'min:12', 'max:255'],
            'youtube_api_key' => ['nullable', 'string', 'min:20', 'max:512'],
            'rationale' => ['required', 'string', 'min:10', 'max:2000'],
            'idempotency_key' => ['required', 'string', 'max:96'],
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'musicbrainz_user_agent' => 'thông tin nhận diện MusicBrainz',
            'youtube_api_key' => 'YouTube API key',
            'rationale' => 'lý do thay đổi',
            'idempotency_key' => 'mã chống lặp',
        ];
    }
}
