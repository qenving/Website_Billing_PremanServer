<?php

namespace App\Extensions\Contracts;

use App\Models\Service;

interface ProvisioningModuleInterface extends ExtensionInterface
{
    /**
     * Get module unique identifier
     */
    public function getModuleId(): string;

    /**
     * Get module display name
     */
    public function getDisplayName(): string;

    /**
     * Get configuration fields for server settings
     * Returns array of field definitions
     */
    public function getServerConfigFields(): array;

    /**
     * Get configuration fields for product/service settings
     * Returns array of field definitions
     */
    public function getProductConfigFields(): array;

    /**
     * Create/provision a new service
     * Returns array with:
     * - success: boolean
     * - message: string
     * - service_data: array (username, password, server_ip, etc.)
     */
    public function createAccount(Service $service): array;

    /**
     * Suspend service
     */
    public function suspendAccount(Service $service): array;

    /**
     * Unsuspend service
     */
    public function unsuspendAccount(Service $service): array;

    /**
     * Terminate/delete service
     */
    public function terminateAccount(Service $service): array;

    /**
     * Change service password
     */
    public function changePassword(Service $service, string $newPassword): array;

    /**
     * Upgrade/downgrade service
     */
    public function changePackage(Service $service, array $newPackageConfig): array;

    /**
     * Get service usage statistics
     * Returns array with disk, bandwidth, etc.
     */
    public function getUsageStats(Service $service): array;

    /**
     * Test connection to server
     */
    public function testConnection(array $serverConfig): array;

    /**
     * Get available packages from server
     */
    public function getAvailablePackages(array $serverConfig): array;

    /**
     * Check if service exists on server
     */
    public function accountExists(Service $service): bool;
}
