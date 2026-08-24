<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\Extensions;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class InstallExtensionRequest extends FormRequest
{
    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'stored' => ['required', 'string', 'regex:/\Aextensions\/uploads\/[A-Za-z0-9._-]+\z/'],
            'type' => ['required', Rule::in(['plugin', 'theme'])],
            'enable' => ['nullable', 'boolean'],
        ];
    }
}
