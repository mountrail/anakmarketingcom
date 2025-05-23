<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckAdminRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // If user is not logged in, show 404 instead of redirecting to login
        if (!Auth::check()) {
            abort(404);
        }

        $user = Auth::user();

        // Check if user has admin or editor role
        // If not, show 404 instead of 403 to hide the existence of admin panel
        if (!$user->hasRole(['admin', 'editor'])) {
            abort(404);
        }

        return $next($request);
    }
}
