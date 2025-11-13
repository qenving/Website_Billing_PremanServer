<?php

namespace App\Extensions\Provisioning;

use App\Contracts\ProvisioningProviderInterface;
use App\DTO\HealthCheckResult;
use App\DTO\ProvisionResult;
use App\DTO\ServiceStatus;
use App\Models\Client;
use App\Models\Service;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class VirtfusionProvider implements ProvisioningProviderInterface
{
    protected array $config;
    protected string $baseUrl;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->baseUrl = rtrim($config['base_url'] ?? '', '/') . '/api';
    }

    protected function makeRequest(string $method, string $endpoint, array $data = []): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->config['api_token'],
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ])->$method($this->baseUrl . $endpoint, $data);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Virtfusion API error', [
                'endpoint' => $endpoint,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return ['error' => $response->json('message') ?? $response->body()];
        } catch (\Exception $e) {
            Log::error('Virtfusion request exception', ['error' => $e->getMessage()]);
            return ['error' => $e->getMessage()];
        }
    }

    public function createService(Service $service, array $config, Client $client): ProvisionResult
    {
        $result = new ProvisionResult();

        try {
            $data = [
                'package_id' => $config['package_id'],
                'user_id' => $config['virtfusion_user_id'] ?? null, // If user exists in Virtfusion
                'hostname' => 'vm-' . $service->id . '.example.com',
                'password' => bin2hex(random_bytes(8)),
                'image_id' => $config['image_id'] ?? null,
                'hypervisor_id' => $config['hypervisor_id'] ?? null,
                'network_id' => $config['network_id'] ?? null,
            ];

            // Create user if not exists
            if (!$data['user_id']) {
                $userData = [
                    'name' => $client->user->name,
                    'email' => $client->user->email,
                    'password' => bin2hex(random_bytes(8)),
                ];
                $userResponse = $this->makeRequest('post', '/users', $userData);
                if (isset($userResponse['data']['id'])) {
                    $data['user_id'] = $userResponse['data']['id'];
                }
            }

            $response = $this->makeRequest('post', '/servers', $data);

            if (isset($response['data']['id'])) {
                $server = $response['data'];

                $result->success = true;
                $result->serviceIdentifier = (string) $server['id'];
                $result->ipAddress = $server['ip_address'] ?? null;
                $result->username = 'root';
                $result->password = $data['password'];
                $result->metadata = [
                    'server_id' => $server['id'],
                    'hostname' => $server['hostname'],
                    'package_id' => $data['package_id'],
                    'user_id' => $data['user_id'],
                ];
            } else {
                $result->success = false;
                $result->errorMessage = $response['error'] ?? 'Failed to create server';
            }
        } catch (\Exception $e) {
            Log::error('Virtfusion createService error', ['error' => $e->getMessage()]);
            $result->success = false;
            $result->errorMessage = $e->getMessage();
        }

        return $result;
    }

    public function suspendService(Service $service): bool
    {
        try {
            $serverId = $service->provisioning_data['server_id'] ?? null;
            if (!$serverId) return false;

            $response = $this->makeRequest('post', "/servers/{$serverId}/suspend", []);

            return !isset($response['error']);
        } catch (\Exception $e) {
            Log::error('Virtfusion suspend error', ['error' => $e->getMessage()]);
            return false;
        }
    }

    public function unsuspendService(Service $service): bool
    {
        try {
            $serverId = $service->provisioning_data['server_id'] ?? null;
            if (!$serverId) return false;

            $response = $this->makeRequest('post', "/servers/{$serverId}/unsuspend", []);

            return !isset($response['error']);
        } catch (\Exception $e) {
            Log::error('Virtfusion unsuspend error', ['error' => $e->getMessage()]);
            return false;
        }
    }

    public function terminateService(Service $service): bool
    {
        try {
            $serverId = $service->provisioning_data['server_id'] ?? null;
            if (!$serverId) return false;

            $response = $this->makeRequest('delete', "/servers/{$serverId}", []);

            return !isset($response['error']);
        } catch (\Exception $e) {
            Log::error('Virtfusion terminate error', ['error' => $e->getMessage()]);
            return false;
        }
    }

    public function rebootService(Service $service): bool
    {
        try {
            $serverId = $service->provisioning_data['server_id'] ?? null;
            if (!$serverId) return false;

            $response = $this->makeRequest('post', "/servers/{$serverId}/reboot", []);

            return !isset($response['error']);
        } catch (\Exception $e) {
            Log::error('Virtfusion reboot error', ['error' => $e->getMessage()]);
            return false;
        }
    }

    public function getServiceStatus(Service $service): ServiceStatus
    {
        $status = new ServiceStatus();
        $status->serviceId = $service->id;

        try {
            $serverId = $service->provisioning_data['server_id'] ?? null;
            if (!$serverId) {
                $status->status = 'unknown';
                return $status;
            }

            $response = $this->makeRequest('get', "/servers/{$serverId}", []);

            if (isset($response['data'])) {
                $server = $response['data'];
                $serverStatus = $server['status'] ?? '';

                $status->status = match($serverStatus) {
                    'running', 'active' => 'online',
                    'stopped', 'off' => 'offline',
                    'suspended' => 'suspended',
                    'installing' => 'provisioning',
                    default => 'unknown',
                };

                $status->uptime = (int) ($server['uptime'] ?? 0);
                $status->resourceUsage = [
                    'cpu_usage' => $server['cpu_usage'] ?? 0,
                    'ram_usage_mb' => $server['ram_usage'] ?? 0,
                    'disk_usage_gb' => $server['disk_usage'] ?? 0,
                ];
            }
        } catch (\Exception $e) {
            Log::error('Virtfusion getStatus error', ['error' => $e->getMessage()]);
            $status->status = 'unknown';
        }

        $status->checkedAt = now();
        return $status;
    }

    public function getServiceDetails(Service $service): array
    {
        try {
            $serverId = $service->provisioning_data['server_id'] ?? null;
            if (!$serverId) return [];

            $response = $this->makeRequest('get', "/servers/{$serverId}", []);

            if (isset($response['data'])) {
                $server = $response['data'];
                return [
                    'server_id' => $server['id'],
                    'hostname' => $server['hostname'] ?? '',
                    'ip_address' => $server['ip_address'] ?? '',
                    'status' => $server['status'] ?? '',
                    'package' => $server['package']['name'] ?? '',
                    'memory_mb' => $server['memory'] ?? 0,
                    'disk_gb' => $server['disk'] ?? 0,
                    'cpu_cores' => $server['cpu_cores'] ?? 0,
                    'bandwidth_gb' => $server['bandwidth'] ?? 0,
                    'operating_system' => $server['image']['name'] ?? '',
                    'hypervisor' => $server['hypervisor']['name'] ?? '',
                ];
            }

            return [];
        } catch (\Exception $e) {
            Log::error('Virtfusion getDetails error', ['error' => $e->getMessage()]);
            return [];
        }
    }

    public function getConfigSchema(): array
    {
        return [
            'base_url' => [
                'type' => 'text',
                'label' => 'Virtfusion URL',
                'required' => true,
                'description' => 'Virtfusion panel URL (e.g., https://panel.example.com)',
            ],
            'api_token' => [
                'type' => 'password',
                'label' => 'API Token',
                'required' => true,
                'description' => 'Virtfusion API token (Bearer token)',
            ],
        ];
    }

    public function validateConfig(array $config): bool
    {
        return !empty($config['base_url']) && !empty($config['api_token']);
    }

    public function healthCheck(): HealthCheckResult
    {
        $result = new HealthCheckResult();
        $result->serviceName = 'Virtfusion Provisioning Panel';

        try {
            // Test connection by getting packages
            $response = $this->makeRequest('get', '/packages', []);

            if (isset($response['data'])) {
                $result->status = 'ok';
                $result->message = 'Connection successful';
            } else {
                $result->status = 'error';
                $result->message = 'Failed to connect: ' . ($response['error'] ?? 'Unknown error');
            }
        } catch (\Exception $e) {
            $result->status = 'error';
            $result->message = 'Connection error: ' . $e->getMessage();
        }

        $result->checkedAt = now();
        return $result;
    }
}
