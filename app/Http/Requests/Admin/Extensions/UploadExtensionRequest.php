<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\Extensions;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UploadExtensionRequest extends FormRequest
{
    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'package' => ['required', 'file', 'mimes:zip', 'max:102400'],
            'type' => ['required', Rule::in(['plugin', 'theme'])],
        ];
    }
}
