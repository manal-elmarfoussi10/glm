<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // When the app is deployed with everything in document root (no "public" subfolder),
        // e.g. using index.document_root.php, make public_path() point to base_path() so
        // assets (build/, Filament CSS/JS) load correctly.
        if (! is_dir(base_path('public')) && file_exists(base_path('build/manifest.json'))) {
            $this->app->instance('path.public', base_path());
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
