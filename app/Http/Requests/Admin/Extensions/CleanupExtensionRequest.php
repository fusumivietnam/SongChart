<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\Extensions;

use Illuminate\Foundation\Http\FormRequest;

final class CleanupExtensionRequest extends FormRequest
{
    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'keep' => ['nullable', 'integer', 'min:1', 'max:50'],
        ];
    }
}
