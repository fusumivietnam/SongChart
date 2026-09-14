<?php

declare(strict_types=1);

it('keeps public social preview metadata centralized and crawler-ready', function (): void {
    $root = dirname(__DIR__, 2);
    $layout = (string) file_get_contents($root.'/resources/views/layouts/frontend.blade.php');
    $socialMeta = (string) file_get_contents($root.'/resources/views/partials/social-meta.blade.php');
    $entityView = (string) file_get_contents($root.'/resources/views/entities/show.blade.php');
    $previewPath = $root.'/public/images/social/songchart-default.png';

    expect($layout)
        ->toContain("@include('partials.social-meta'")
        ->toContain('$resolvedTitle')
        ->toContain('$resolvedDescription')
        ->toContain("yieldContent('canonical_url')")
        ->toContain("yieldContent('social_title')")
        ->and($socialMeta)
        ->toContain('rel="canonical"')
        ->toContain('property="og:title"')
        ->toContain('property="og:description"')
        ->toContain('property="og:url"')
        ->toContain('property="og:image"')
        ->toContain('property="og:image:width" content="1200"')
        ->toContain('property="og:image:height" content="630"')
        ->toContain('property="og:image:type" content="image/png"')
        ->toContain('name="twitter:card" content="summary_large_image"')
        ->toContain('name="twitter:image"')
        ->toContain('$socialImage ?? asset(\'images/social/songchart-default.png\')')
        ->toContain('$canonicalUrl ?? request()->url()')
        ->and($entityView)
        ->toContain("@section('canonical_url', url()->current())")
        ->toContain("@section('social_title', \$entity['title'].' · '.\$entity['label'])")
        ->toContain('name="robots" content="index,follow,max-image-preview:large"')
        ->toContain('application/ld+json')
        ->not->toContain('<link rel="canonical"')
        ->not->toContain('<meta property="og:')
        ->not->toContain('<meta name="twitter:')
        ->and(file_exists($previewPath))->toBeTrue();

    $imageSize = getimagesize($previewPath);

    expect($imageSize)->not->toBeFalse()
        ->and($imageSize[0])->toBe(1200)
        ->and($imageSize[1])->toBe(630)
        ->and($imageSize['mime'])->toBe('image/png');
});
