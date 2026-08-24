<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

final class ApproveYouTubeDestinationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, list<string>> */
    public function rules(): array
    {
        return [
            'recording_id' => ['required', 'string', 'max:26'],
            'video_id' => ['required', 'regex:/^[A-Za-z0-9_-]{11}$/'],
        ];
    }
}
