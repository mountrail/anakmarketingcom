<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
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
        // Write to a file for easier debugging
        file_put_contents(
            storage_path('logs/admin_debug.log'),
            date('Y-m-d H:i:s') . " - CheckAdminRole middleware called\n",
            FILE_APPEND
        );

        if (!Auth::check()) {
            file_put_contents(
                storage_path('logs/admin_debug.log'),
                date('Y-m-d H:i:s') . " - User not authenticated\n",
                FILE_APPEND
            );
            return redirect()->route('filament.admin.auth.login');
        }

        $user = Auth::user();

        // Debug logging
        file_put_contents(
            storage_path('logs/admin_debug.log'),
            date('Y-m-d H:i:s') . " - User ID: " . $user->id . "\n",
            FILE_APPEND
        );
        file_put_contents(
            storage_path('logs/admin_debug.log'),
            date('Y-m-d H:i:s') . " - User email: " . $user->email . "\n",
            FILE_APPEND
        );
        file_put_contents(
            storage_path('logs/admin_debug.log'),
            date('Y-m-d H:i:s') . " - User roles: " . $user->roles->pluck('name')->toJson() . "\n",
            FILE_APPEND
        );

        $hasRole = $user->hasRole(['admin', 'editor']);
        file_put_contents(
            storage_path('logs/admin_debug.log'),
            date('Y-m-d H:i:s') . " - Has admin/editor role: " . ($hasRole ? 'YES' : 'NO') . "\n",
            FILE_APPEND
        );

        // Check if user has admin or editor role
        if (!$hasRole) {
            file_put_contents(
                storage_path('logs/admin_debug.log'),
                date('Y-m-d H:i:s') . " - Access denied for user " . $user->id . " - insufficient privileges\n",
                FILE_APPEND
            );
            abort(403, 'Access denied. You need admin or editor privileges.');
        }

        file_put_contents(
            storage_path('logs/admin_debug.log'),
            date('Y-m-d H:i:s') . " - Access granted for user " . $user->id . "\n",
            FILE_APPEND
        );
        return $next($request);
    }
}
