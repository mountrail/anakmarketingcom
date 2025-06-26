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

            // Check if WordPress content exists (quick HEAD request)
            $response = Http::timeout(5)
                ->withHeaders([
                    'User-Agent' => $request->header('User-Agent', 'Laravel-WordPress-Checker'),
                ])
                ->head($wordpressUrl);

            // Only redirect if WordPress content actually exists (200 status)
            if ($response->status() === 200) {
                Log::info('WordPress content found, redirecting', [
                    'original_slug' => $slug,
                    'redirect_url' => $wordpressUrl
                ]);

                return redirect($wordpressUrl, 301);
            }

            // If WordPress returns 404 or any other error, show Laravel 404
            Log::info('WordPress content not found, showing Laravel 404', [
                'slug' => $slug,
                'wordpress_status' => $response->status()
            ]);

            abort(404);

        } catch (\Exception $e) {
            Log::warning('WordPress fallback check failed for slug: ' . $slug, [
                'error' => $e->getMessage(),
                'url' => $wordpressUrl ?? null
            ]);

            // If we can't check WordPress (network error, etc.), show Laravel 404
            // This is safer than redirecting to potentially non-existent content
            abort(404);
        }
    }
}
