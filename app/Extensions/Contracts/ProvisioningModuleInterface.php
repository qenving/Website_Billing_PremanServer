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
     * Get configuration fields for admin settings
     * Returns array of field definitions
     */
    public function getConfigFields(): array;

    /**
     * Get module logo URL
     */
    public function getLogo(): ?string;

    /**
     * Create/provision a new service
     * Returns array with:
     * - status: 'success' or 'failed'
     * - message: string
     * - remote_id: remote service identifier
     * - username: service username
     * - password: service password
     * - additional_data: array (server details, etc.)
     */
    public function createAccount(Service $service, array $params): array;

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
     * Upgrade/downgrade service
     */
    public function changePackage(Service $service, array $params): array;

    /**
     * Get service account information and statistics
     * Returns array with status, usage stats, etc.
     */
    public function getAccountInfo(Service $service): array;

    /**
     * Test connection to provisioning server
     * Returns array with status and message
     */
    public function testConnection(): array;
}
