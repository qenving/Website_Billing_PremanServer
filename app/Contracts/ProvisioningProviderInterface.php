<?php

namespace App\Contracts;

use App\DTO\HealthCheckResult;
use App\DTO\ProvisionResult;
use App\DTO\ServiceStatus;
use App\Models\Client;
use App\Models\Service;

interface ProvisioningProviderInterface
{
    /**
     * Create/provision service di panel
     *
     * @param Service $service
     * @param array $config Provisioning config dari product
     * @param Client $client
     * @return ProvisionResult
     */
    public function createService(Service $service, array $config, Client $client): ProvisionResult;

    /**
     * Suspend service
     *
     * @param Service $service
     * @return bool
     */
    public function suspendService(Service $service): bool;

    /**
     * Unsuspend service
     *
     * @param Service $service
     * @return bool
     */
    public function unsuspendService(Service $service): bool;

    /**
     * Terminate/delete service
     *
     * @param Service $service
     * @return bool
     */
    public function terminateService(Service $service): bool;

    /**
     * Reboot service
     *
     * @param Service $service
     * @return bool
     */
    public function rebootService(Service $service): bool;

    /**
     * Get service status
     *
     * @param Service $service
     * @return ServiceStatus
     */
    public function getServiceStatus(Service $service): ServiceStatus;

    /**
     * Get service details (IP, credentials, URLs, etc)
     *
     * @param Service $service
     * @return array
     */
    public function getServiceDetails(Service $service): array;

    /**
     * Get configuration schema untuk form admin
     *
     * @return array
     */
    public function getConfigSchema(): array;

    /**
     * Validate configuration
     *
     * @param array $config
     * @return bool
     */
    public function validateConfig(array $config): bool;

    /**
     * Health check ke panel API
     *
     * @return HealthCheckResult
     */
    public function healthCheck(): HealthCheckResult;

    /**
     * Get available templates/plans dari panel
     *
     * @return array Format: [['id' => 1, 'name' => 'Template Name']]
     */
    public function getAvailableTemplates(): array;
}
