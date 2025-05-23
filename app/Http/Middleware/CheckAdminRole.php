<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CheckAdminRole
{
    public function handle(Request $request, Closure $next)
    {
        // Add extensive logging for debugging
        Log::info('CheckAdminRole middleware triggered', [
            'user_id' => auth()->id(),
            'user_email' => auth()->user()?->email,
            'is_authenticated' => auth()->check(),
            'request_path' => $request->path(),
            'request_url' => $request->fullUrl()
        ]);

        // Check if user is authenticated
        if (!auth()->check()) {
            Log::warning('User not authenticated in CheckAdminRole');
            return redirect()->route('login');
        }

        $user = auth()->user();

        // Log user roles
        $roles = $user->roles()->pluck('name')->toArray();
        Log::info('User roles', [
            'user_id' => $user->id,
            'roles' => $roles,
            'has_admin_role' => in_array('admin', $roles)
        ]);

        // Check if user has admin role
        if (!$user->hasRole('admin')) {
            Log::warning('User does not have admin role', [
                'user_id' => $user->id,
                'user_roles' => $roles
            ]);

            // Return 403 with more detailed error
            abort(403, 'Access denied. Admin role required. Current roles: ' . implode(', ', $roles));
        }

        Log::info('Admin access granted', ['user_id' => $user->id]);
        return $next($request);
    }
}
