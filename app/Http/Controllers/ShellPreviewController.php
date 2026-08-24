<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;

final class ShellPreviewController
{
    public function frontend(): View
    {
        return view('shell-preview.frontend');
    }

    public function admin(): View
    {
        return view('shell-preview.admin');
    }
}
