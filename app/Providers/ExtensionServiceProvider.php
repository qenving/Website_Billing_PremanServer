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
     * Load routes from extensions (categorized structure)
     */
    protected function loadExtensionRoutes(): void
    {
        $extensionsPath = base_path('extensions');

        if (!is_dir($extensionsPath)) {
            return;
        }

        // Support categorized structure: extensions/category/extension-name/routes/*.php
        $categories = ['payment-gateways', 'provisioning-modules'];

        foreach ($categories as $category) {
            $categoryPath = $extensionsPath . '/' . $category;

            if (!is_dir($categoryPath)) {
                continue;
            }

            foreach (glob($categoryPath . '/*/routes/*.php') as $routeFile) {
                $this->loadRoutesFrom($routeFile);
            }
        }
    }

    /**
     * Load views from extensions (categorized structure)
     */
    protected function loadExtensionViews(): void
    {
        $extensionsPath = base_path('extensions');

        if (!is_dir($extensionsPath)) {
            return;
        }

        // Support categorized structure: extensions/category/extension-name/views/
        $categories = ['payment-gateways', 'provisioning-modules'];

        foreach ($categories as $category) {
            $categoryPath = $extensionsPath . '/' . $category;

            if (!is_dir($categoryPath)) {
                continue;
            }

            $extensionDirs = glob($categoryPath . '/*', GLOB_ONLYDIR);

            foreach ($extensionDirs as $dir) {
                $viewsPath = $dir . '/views';

                if (is_dir($viewsPath)) {
                    $extensionName = basename($dir);
                    $this->loadViewsFrom($viewsPath, 'extension_' . $extensionName);
                }
            }
        }
    }
}
