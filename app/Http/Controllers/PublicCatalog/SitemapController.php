<?php

declare(strict_types=1);

namespace App\Http\Controllers\PublicCatalog;

use App\Application\Catalog\Queries\PublicSitemap;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response;

final class SitemapController extends Controller
{
    public function __invoke(PublicSitemap $sitemap): Response
    {
        $items = array_map(
            static fn (string $url): string => '<url><loc>'.htmlspecialchars($url, ENT_XML1 | ENT_QUOTES, 'UTF-8').'</loc></url>',
            $sitemap->urls(),
        );

        $xml = '<?xml version="1.0" encoding="UTF-8"?>'
            .'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'
            .implode('', $items)
            .'</urlset>';

        return response($xml, 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }
}
