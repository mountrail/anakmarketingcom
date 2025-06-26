<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WordPressFallbackController extends Controller
{
    /**
     * Handle fallback to WordPress content
     */
    public function handleWordPressFallback(Request $request, string $slug)
    {
        try {
            // Make internal request to WordPress subdirectory
            $wordpressUrl = config('app.url') . '/insights/' . $slug;

            // Forward query parameters if any
            if ($request->getQueryString()) {
                $wordpressUrl .= '?' . $request->getQueryString();
            }

            // Make HTTP request to WordPress
            $response = Http::timeout(10)
                ->withHeaders([
                    'User-Agent' => $request->header('User-Agent', 'Laravel-WordPress-Fallback'),
                    'Accept' => $request->header('Accept', 'text/html,application/xhtml+xml'),
                ])
                ->get($wordpressUrl);

            // If WordPress returns 404, don't serve it
            if ($response->status() === 404) {
                abort(404);
            }

            // If successful, serve the WordPress content
            if ($response->successful()) {
                return response($response->body(), $response->status())
                    ->withHeaders([
                        'Content-Type' => $response->header('Content-Type') ?? 'text/html; charset=UTF-8',
                        // Preserve other important headers
                        'Cache-Control' => $response->header('Cache-Control') ?? 'no-cache',
                    ]);
            }

            // If WordPress is unreachable, return 404
            abort(404);

        } catch (\Exception $e) {
            Log::warning('WordPress fallback failed for slug: ' . $slug, [
                'error' => $e->getMessage(),
                'url' => $wordpressUrl ?? null
            ]);

            abort(404);
        }
    }
}
