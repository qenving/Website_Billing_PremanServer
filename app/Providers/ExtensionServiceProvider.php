<?php

namespace App\Providers;

use App\Extensions\Managers\ExtensionManager;
use Illuminate\Support\ServiceProvider;

class ExtensionServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Register Extension Manager as singleton
        $this->app->singleton(ExtensionManager::class, function ($app) {
            return new ExtensionManager();
        });

        // Create helper alias
        $this->app->alias(ExtensionManager::class, 'extensions');
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Discover and load extensions
        $extensionManager = $this->app->make(ExtensionManager::class);
        $extensionManager->discoverExtensions();

        // Register extension routes if they exist
        $this->loadExtensionRoutes();

        // Register extension views
        $this->loadExtensionViews();
    }

    /**
     * Load routes from extensions
     */
    protected function loadExtensionRoutes(): void
    {
        $extensionsPath = base_path('extensions');

        if (!is_dir($extensionsPath)) {
            return;
        }

        foreach (glob($extensionsPath . '/*/routes/*.php') as $routeFile) {
            $this->loadRoutesFrom($routeFile);
        }
    }

    /**
     * Load views from extensions
     */
    protected function loadExtensionViews(): void
    {
        $extensionsPath = base_path('extensions');

        if (!is_dir($extensionsPath)) {
            return;
        }

        $extensionDirs = glob($extensionsPath . '/*', GLOB_ONLYDIR);

        foreach ($extensionDirs as $dir) {
            $viewsPath = $dir . '/views';

            if (is_dir($viewsPath)) {
                $extensionName = basename($dir);
                $this->loadViewsFrom($viewsPath, 'extension_' . $extensionName);
            }
        }
    }
}
