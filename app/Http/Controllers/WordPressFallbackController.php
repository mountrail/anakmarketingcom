<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WordPressFallbackController extends Controller
{
    /**
     * Handle fallback to WordPress content via redirect
     */
    public function handleWordPressFallback(Request $request, string $slug)
    {
        try {
            // Build WordPress URL
            $wordpressUrl = config('app.url') . '/insights/' . $slug;

            // Forward query parameters if any
            if ($request->getQueryString()) {
                $wordpressUrl .= '?' . $request->getQueryString();
            }

            // Check if WordPress content exists with a GET request to validate content
            $response = Http::timeout(5)
                ->withHeaders([
                    'User-Agent' => $request->header('User-Agent', 'Laravel-WordPress-Checker'),
                ])
                ->get($wordpressUrl);

            // Check if it's a proper 200 response
            if ($response->status() !== 200) {
                Log::info('WordPress returned non-200 status', [
                    'slug' => $slug,
                    'status' => $response->status()
                ]);
                abort(404);
            }

            $content = $response->body();

            // Check for WordPress 404 indicators in the content
            $wp404Indicators = [
                'Page not found',
                'Nothing here',
                'Sorry, but nothing matched',
                'It looks like nothing was found',
                'The page you requested could not be found',
                'Error 404',
                'not found',
                'wp-die-message', // WordPress error message class
                'page-not-found',
                '<title>Page not found'
            ];

            foreach ($wp404Indicators as $indicator) {
                if (stripos($content, $indicator) !== false) {
                    Log::info('WordPress content contains 404 indicator', [
                        'slug' => $slug,
                        'indicator' => $indicator
                    ]);
                    abort(404);
                }
            }

            // Check if WordPress redirected to a different slug (partial matching)
            $finalUrl = $response->effectiveUri();
            $expectedPath = '/insights/' . $slug;
            $actualPath = parse_url($finalUrl, PHP_URL_PATH);

            // If WordPress redirected to a different slug, it's doing partial matching
            if ($actualPath && $actualPath !== $expectedPath && $actualPath !== $expectedPath . '/') {
                Log::info('WordPress redirected to different slug (partial match)', [
                    'original_slug' => $slug,
                    'expected_path' => $expectedPath,
                    'actual_path' => $actualPath
                ]);
                abort(404);
            }

            // If we get here, it's likely legitimate WordPress content
            Log::info('WordPress content validated, redirecting', [
                'original_slug' => $slug,
                'redirect_url' => $wordpressUrl
            ]);

            return redirect($wordpressUrl, 301);

        } catch (\Exception $e) {
            Log::warning('WordPress fallback check failed for slug: ' . $slug, [
                'error' => $e->getMessage(),
                'url' => $wordpressUrl ?? null
            ]);

            // If we can't check WordPress, show Laravel 404
            abort(404);
        }
    }
}
