<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use App\Helpers\ExcerptHelper;

class ExcerptServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Register the ExcerptHelper as a singleton
        $this->app->singleton('excerpt', function ($app) {
            return new ExcerptHelper();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Create a new Blade directive for clean excerpts
        Blade::directive('excerpt', function ($expression) {
            // Parse expression which should be in format: 'content, limit, end'
            return "<?php echo \App\Helpers\ExcerptHelper::clean($expression); ?>";
        });

        // Create a Blade directive for excerpts with basic formatting preserved
        Blade::directive('excerptWithFormatting', function ($expression) {
            return "<?php echo \App\Helpers\ExcerptHelper::preserveBasicFormatting($expression); ?>";
        });
    }
}
