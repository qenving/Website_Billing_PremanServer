<?php

namespace App\Extensions\Contracts;

interface ExtensionInterface
{
    /**
     * Get extension name
     */
    public function getName(): string;

    /**
     * Get extension description
     */
    public function getDescription(): string;

    /**
     * Get extension version
     */
    public function getVersion(): string;

    /**
     * Get extension author
     */
    public function getAuthor(): string;

    /**
     * Boot the extension
     * Called when extension is loaded
     */
    public function boot(): void;

    /**
     * Register extension services
     * Called before boot
     */
    public function register(): void;

    /**
     * Get extension configuration
     */
    public function getConfig(): array;

    /**
     * Check if extension is enabled
     */
    public function isEnabled(): bool;

    /**
     * Install extension
     * Run migrations, seeders, etc.
     */
    public function install(): bool;

    /**
     * Uninstall extension
     * Cleanup database, files, etc.
     */
    public function uninstall(): bool;
}
