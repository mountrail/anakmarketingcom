<?php
// app/Http/Controllers/SitemapController.php

namespace App\Http\Controllers;

use App\Models\Sitemap;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function index()
    {
        $sitemaps = Sitemap::where('is_active', true)->get();

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<?xml-stylesheet type="text/xsl" href="/sitemap-index.xsl"?>' . "\n";
        $xml .= '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        foreach ($sitemaps as $sitemap) {
            $xml .= '  <sitemap>' . "\n";
            $xml .= '    <loc>' . $sitemap->url . '</loc>' . "\n";
            if ($sitemap->last_generated) {
                $xml .= '    <lastmod>' . $sitemap->last_generated->toISOString() . '</lastmod>' . "\n";
            }
            $xml .= '  </sitemap>' . "\n";
        }

        $xml .= '</sitemapindex>';

        return response($xml, 200, [
            'Content-Type' => 'application/xml'
        ]);
    }

    public function show($filename)
    {
        $path = public_path("sitemaps/{$filename}");

        if (!file_exists($path)) {
            abort(404);
        }

        return response()->file($path, [
            'Content-Type' => 'application/xml'
        ]);
    }
}
