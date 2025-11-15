<?php

namespace App\Extensions;

use App\Extensions\Contracts\ExtensionInterface;
use App\Models\Setting;

abstract class Extension implements ExtensionInterface
{
    /**
     * Extension configuration
     */
    protected array $config = [];

    /**
     * Extension enabled status
     */
    protected bool $enabled = true;

    /**
     * Extension path
     */
    protected string $path;

    public function __construct()
    {
        $this->path = $this->getExtensionPath();
        $this->loadConfig();
    }

    /**
     * Get extension configuration from database
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * Set extension configuration
     */
    public function setConfig(array $config): void
    {
        $this->config = array_merge($this->config, $config);
        $this->saveConfig();
    }

    /**
     * Load configuration from database
     */
    protected function loadConfig(): void
    {
        $configKey = 'extension_' . $this->getExtensionId();
        $savedConfig = Setting::get($configKey, []);

        if (is_array($savedConfig)) {
            $this->config = array_merge($this->config, $savedConfig);
        }

        // Load enabled status
        $this->enabled = Setting::get($configKey . '_enabled', true);
    }

    /**
     * Save configuration to database
     */
    protected function saveConfig(): void
    {
        $configKey = 'extension_' . $this->getExtensionId();
        Setting::set($configKey, $this->config);
    }

    /**
     * Check if extension is enabled
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * Enable extension
     */
    public function enable(): void
    {
        $this->enabled = true;
        Setting::set('extension_' . $this->getExtensionId() . '_enabled', true);
    }

    /**
     * Disable extension
     */
    public function disable(): void
    {
        $this->enabled = false;
        Setting::set('extension_' . $this->getExtensionId() . '_enabled', false);
    }

    /**
     * Get extension unique identifier (lowercase, no spaces)
     */
    abstract public function getExtensionId(): string;

    /**
     * Get extension path
     */
    protected function getExtensionPath(): string
    {
        return base_path('extensions/' . $this->getExtensionId());
    }

    /**
     * Load extension view
     */
    protected function view(string $view, array $data = []): string
    {
        $viewPath = $this->path . '/views/' . str_replace('.', '/', $view) . '.blade.php';

        if (file_exists($viewPath)) {
            return view()->file($viewPath, $data)->render();
        }

        return '';
    }

    /**
     * Get extension asset URL
     */
    protected function asset(string $path): string
    {
        return url('extensions/' . $this->getExtensionId() . '/assets/' . $path);
    }

    /**
     * Default register implementation
     */
    public function register(): void
    {
        // Override in child class if needed
    }

    /**
     * Default boot implementation
     */
    public function boot(): void
    {
        // Override in child class if needed
    }

    /**
     * Default install implementation
     */
    public function install(): bool
    {
        // Run migrations if exist
        $migrationsPath = $this->path . '/database/migrations';
        if (file_exists($migrationsPath)) {
            \Artisan::call('migrate', [
                '--path' => 'extensions/' . $this->getExtensionId() . '/database/migrations',
                '--force' => true,
            ]);
        }

        $this->enable();
        return true;
    }

    /**
     * Default uninstall implementation
     */
    public function uninstall(): bool
    {
        $this->disable();

        // Remove configuration
        Setting::where('key', 'like', 'extension_' . $this->getExtensionId() . '%')->delete();

        return true;
    }
}
