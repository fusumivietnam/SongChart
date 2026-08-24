<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\Extensions;

use Illuminate\Foundation\Http\FormRequest;

final class RollbackExtensionRequest extends FormRequest
{
    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'version' => ['nullable', 'string', 'max:64'],
        ];
    }
}
