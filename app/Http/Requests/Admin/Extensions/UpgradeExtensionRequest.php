<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\Extensions;

use Illuminate\Foundation\Http\FormRequest;

final class UpgradeExtensionRequest extends FormRequest
{
    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'package' => ['required', 'file', 'mimes:zip', 'max:102400'],
        ];
    }
}
