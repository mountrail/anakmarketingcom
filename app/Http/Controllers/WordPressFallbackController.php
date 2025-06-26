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

            // First, check if WordPress content exists (quick HEAD request)
            $response = Http::timeout(5)
                ->withHeaders([
                    'User-Agent' => $request->header('User-Agent', 'Laravel-WordPress-Checker'),
                ])
                ->head($wordpressUrl);

            // If WordPress content exists, redirect to it
            if ($response->successful()) {
                Log::info('Redirecting to WordPress content', [
                    'original_slug' => $slug,
                    'redirect_url' => $wordpressUrl
                ]);

                return redirect($wordpressUrl, 301);
            }

            // If WordPress returns 404 or is unreachable, show Laravel 404
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

            // If we can't check WordPress, just redirect anyway
            // (in case it's a network issue but WordPress is actually working)
            return redirect(config('app.url') . '/insights/' . $slug, 301);
        }
    }
}
