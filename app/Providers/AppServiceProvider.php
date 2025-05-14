<?php

namespace App\Providers;
// Register the PostList component
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Blade;
use App\View\Components\PostListComponent;
use Illuminate\Support\Facades\View;
use App\View\Composers\SidebarComposer;

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

        // Register the PostList component
        Blade::component('post-list', PostListComponent::class);

        // Register the sidebar composer
        View::composer('layouts.sidebar', SidebarComposer::class);

        // Define a gate for managing editor's picks
        // For now, we'll use a simple approach where only user ID 1 has admin capabilities
        // In a real application, you'd want to use roles and permissions
        Gate::define('manage-editor-picks', function ($user) {
            return $user->id === 1; // User ID 1 is assumed to be admin
            // You can expand this with proper roles later
        });
    }
}
