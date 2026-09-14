@php
    $resolvedCanonicalUrl = $canonicalUrl ?? request()->url();
    $resolvedSocialImage = $socialImage ?? asset('images/social/songchart-default.png');
    $resolvedSocialImageAlt = $socialImageAlt ?? ($pageTitle.' — '.config('app.name'));
    $resolvedSocialType = $socialType ?? 'website';
    $resolvedLocale = str_replace('-', '_', app()->getLocale());
@endphp
<link rel="canonical" href="{{ $resolvedCanonicalUrl }}">
<meta property="og:site_name" content="{{ config('app.name') }}">
<meta property="og:type" content="{{ $resolvedSocialType }}">
<meta property="og:locale" content="{{ $resolvedLocale }}">
<meta property="og:title" content="{{ $pageTitle }}">
<meta property="og:description" content="{{ $pageDescription }}">
<meta property="og:url" content="{{ $resolvedCanonicalUrl }}">
<meta property="og:image" content="{{ $resolvedSocialImage }}">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta property="og:image:type" content="image/png">
<meta property="og:image:alt" content="{{ $resolvedSocialImageAlt }}">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $pageTitle }}">
<meta name="twitter:description" content="{{ $pageDescription }}">
<meta name="twitter:image" content="{{ $resolvedSocialImage }}">
<meta name="twitter:image:alt" content="{{ $resolvedSocialImageAlt }}">
