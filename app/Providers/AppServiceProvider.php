<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (config('app.env') === 'production') {
            \URL::forceScheme('https');
        }

        // Define a gate for managing editor's picks
        // For now, we'll use a simple approach where only user ID 1 has admin capabilities
        // In a real application, you'd want to use roles and permissions
        Gate::define('manage-editor-picks', function ($user) {
            return $user->id === 1; // User ID 1 is assumed to be admin
            // You can expand this with proper roles later
        });
    }
}
