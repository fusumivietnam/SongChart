<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class UiPreviewController
{
    /** @var array<string, array{label:string,description:string}> */
    private const SECTIONS = [
        'foundations' => ['label' => 'Nền tảng', 'description' => 'Token, typography, spacing và bề mặt.'],
        'components' => ['label' => 'Components', 'description' => 'Primitive dùng chung và các biến thể được duyệt.'],
        'patterns' => ['label' => 'Patterns', 'description' => 'Mẫu ghép cho search, entity và provider routing.'],
        'states' => ['label' => 'Trạng thái', 'description' => 'Loading, empty, error và partial data.'],
        'admin' => ['label' => 'Admin', 'description' => 'Mẫu vận hành trong light workspace.'],
    ];

    public function __invoke(?string $section = null): View
    {
        $activeSection = $section ?? 'foundations';

        if (! array_key_exists($activeSection, self::SECTIONS)) {
            throw new NotFoundHttpException;
        }

        return view('ui-preview.index', [
            'sections' => self::SECTIONS,
            'activeSection' => $activeSection,
            'activeMeta' => self::SECTIONS[$activeSection],
        ]);
    }
}
