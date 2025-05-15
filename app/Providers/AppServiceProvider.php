<?php

namespace App\Providers;
// Register the PostList component
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;
use App\View\Components\PostListComponent;
use App\Models\Post;

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

        // Define a gate for managing editor's picks
        // For now, we'll use a simple approach where only user ID 1 has admin capabilities
        // In a real application, you'd want to use roles and permissions
        Gate::define('manage-editor-picks', function ($user) {
            return $user->id === 1; // User ID 1 is assumed to be admin
            // You can expand this with proper roles later
        });

        // Share editor's picks data with all views that use the app layout
        View::composer('layouts.app', function ($view) {
            $editorPicks = Post::featured()
                ->where('featured_type', '!=', 'none')
                ->with(['user', 'answers'])
                ->latest()
                ->take(5)
                ->get();

            $view->with('editorPicks', $editorPicks);
        });
    }
}
