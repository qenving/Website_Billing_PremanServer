<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register ExtensionManager as singleton
        $this->app->singleton(\App\Services\ExtensionManager::class, function ($app) {
            return new \App\Services\ExtensionManager();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Set default string length for MySQL
        Schema::defaultStringLength(191);

        // Share settings with all views
        if (config('hbm.installed')) {
            view()->composer('*', function ($view) {
                $settings = \App\Models\Setting::pluck('value', 'key')->toArray();
                $view->with('appSettings', $settings);
            });
        }
    }
}
