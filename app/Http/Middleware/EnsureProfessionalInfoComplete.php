<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureProfessionalInfoComplete
{
    public function handle(Request $request, Closure $next)
    {
        if (auth()->check() && !auth()->user()->hasProfessionalInfo()) {
            // Skip this check for the professional info routes themselves
            if ($request->routeIs('professional-info.*')) {
                return $next($request);
            }

            // Also skip for logout and auth routes
            $exemptRoutes = [
                'logout',
                'account.destroy',
                'password.confirm',
                'verification.notice',
                'verification.send',
                'verification.verify'
            ];

            if (in_array($request->route()->getName(), $exemptRoutes)) {
                return $next($request);
            }

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'redirect' => route('professional-info.form'),
                    'message' => 'Silakan lengkapi informasi profesional Anda terlebih dahulu.'
                ], 302);
            }

            return redirect()->route('professional-info.form')
                ->with('info', 'Silakan lengkapi informasi profesional Anda terlebih dahulu.');
        }

        return $next($request);
    }
}
