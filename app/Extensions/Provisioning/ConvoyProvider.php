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

class ConvoyProvider implements ProvisioningProviderInterface
{
    protected array $config;
    protected string $baseUrl;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->baseUrl = rtrim($config['base_url'] ?? '', '/') . '/api/application';
    }

    protected function makeRequest(string $method, string $endpoint, array $data = []): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->config['api_key'],
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ])->$method($this->baseUrl . $endpoint, $data);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Convoy API error', [
                'endpoint' => $endpoint,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return ['error' => $response->json('message') ?? $response->body()];
        } catch (\Exception $e) {
            Log::error('Convoy request exception', ['error' => $e->getMessage()]);
            return ['error' => $e->getMessage()];
        }
    }

    protected function getClientUrl(): string
    {
        return rtrim($this->config['base_url'] ?? '', '/') . '/api/client';
    }

    public function createService(Service $service, array $config, Client $client): ProvisionResult
    {
        $result = new ProvisionResult();

        try {
            // First, create or get user
            $userData = [
                'email' => $client->user->email,
                'username' => 'client_' . $client->id,
                'first_name' => explode(' ', $client->user->name)[0] ?? 'Client',
                'last_name' => explode(' ', $client->user->name, 2)[1] ?? $client->id,
                'password' => bin2hex(random_bytes(8)),
            ];

            $userResponse = $this->makeRequest('post', '/users', $userData);
            $userId = $userResponse['attributes']['id'] ?? null;

            if (!$userId) {
                // Try to find existing user
                $usersResponse = $this->makeRequest('get', '/users?filter[email]=' . $client->user->email, []);
                $userId = $usersResponse['data'][0]['attributes']['id'] ?? null;
            }

            if (!$userId) {
                $result->success = false;
                $result->errorMessage = 'Failed to create or find user';
                return $result;
            }

            // Create server
            $serverData = [
                'name' => 'server-' . $service->id,
                'user_id' => $userId,
                'node_id' => $config['node_id'],
                'vmid' => $config['vmid'] ?? null,
                'hostname' => 'vm-' . $service->id . '.example.com',
                'limits' => [
                    'cpu' => $config['cpu_cores'] ?? 1,
                    'memory' => $config['memory_mb'] ?? 1024,
                    'disk' => $config['disk_gb'] ?? 10,
                    'snapshots' => $config['snapshots'] ?? 0,
                    'backups' => $config['backups'] ?? 0,
                ],
                'template_uuid' => $config['template_uuid'] ?? null,
                'start_on_completion' => true,
            ];

            $response = $this->makeRequest('post', '/servers', $serverData);

            if (isset($response['attributes']['id'])) {
                $server = $response['attributes'];

                $result->success = true;
                $result->serviceIdentifier = (string) $server['uuid'];
                $result->ipAddress = $server['ip_address'] ?? null;
                $result->username = 'root';
                $result->password = $config['password'] ?? '';
                $result->metadata = [
                    'server_id' => $server['id'],
                    'server_uuid' => $server['uuid'],
                    'user_id' => $userId,
                    'node_id' => $config['node_id'],
                    'vmid' => $server['vmid'] ?? null,
                ];
            } else {
                $result->success = false;
                $result->errorMessage = $response['error'] ?? 'Failed to create server';
            }
        } catch (\Exception $e) {
            Log::error('Convoy createService error', ['error' => $e->getMessage()]);
            $result->success = false;
            $result->errorMessage = $e->getMessage();
        }

        return $result;
    }

    public function suspendService(Service $service): bool
    {
        try {
            $serverUuid = $service->provisioning_data['server_uuid'] ?? null;
            if (!$serverUuid) return false;

            $response = $this->makeRequest('post', "/servers/{$serverUuid}/suspend", []);

            return !isset($response['error']);
        } catch (\Exception $e) {
            Log::error('Convoy suspend error', ['error' => $e->getMessage()]);
            return false;
        }
    }

    public function unsuspendService(Service $service): bool
    {
        try {
            $serverUuid = $service->provisioning_data['server_uuid'] ?? null;
            if (!$serverUuid) return false;

            $response = $this->makeRequest('post', "/servers/{$serverUuid}/unsuspend", []);

            return !isset($response['error']);
        } catch (\Exception $e) {
            Log::error('Convoy unsuspend error', ['error' => $e->getMessage()]);
            return false;
        }
    }

    public function terminateService(Service $service): bool
    {
        try {
            $serverUuid = $service->provisioning_data['server_uuid'] ?? null;
            if (!$serverUuid) return false;

            $response = $this->makeRequest('delete', "/servers/{$serverUuid}", []);

            return !isset($response['error']);
        } catch (\Exception $e) {
            Log::error('Convoy terminate error', ['error' => $e->getMessage()]);
            return false;
        }
    }

    public function rebootService(Service $service): bool
    {
        try {
            $serverUuid = $service->provisioning_data['server_uuid'] ?? null;
            if (!$serverUuid) return false;

            // Use client API for power actions
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->config['api_key'],
                'Accept' => 'application/json',
            ])->post($this->getClientUrl() . "/servers/{$serverUuid}/power/restart", []);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('Convoy reboot error', ['error' => $e->getMessage()]);
            return false;
        }
    }

    public function getServiceStatus(Service $service): ServiceStatus
    {
        $status = new ServiceStatus();
        $status->serviceId = $service->id;

        try {
            $serverUuid = $service->provisioning_data['server_uuid'] ?? null;
            if (!$serverUuid) {
                $status->status = 'unknown';
                return $status;
            }

            $response = $this->makeRequest('get', "/servers/{$serverUuid}", []);

            if (isset($response['attributes'])) {
                $server = $response['attributes'];
                $serverStatus = $server['status'] ?? '';

                $status->status = match($serverStatus) {
                    'running' => 'online',
                    'stopped', 'offline' => 'offline',
                    'suspended' => 'suspended',
                    'installing' => 'provisioning',
                    default => 'unknown',
                };

                // Get resource usage from client API
                $resourceResponse = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $this->config['api_key'],
                    'Accept' => 'application/json',
                ])->get($this->getClientUrl() . "/servers/{$serverUuid}/resources");

                if ($resourceResponse->successful()) {
                    $resources = $resourceResponse->json('attributes');
                    $status->resourceUsage = [
                        'cpu_usage' => $resources['cpu_usage'] ?? 0,
                        'memory_usage_mb' => $resources['memory_usage'] ?? 0,
                        'disk_usage_gb' => $resources['disk_usage'] ?? 0,
                    ];
                    $status->uptime = $resources['uptime'] ?? 0;
                }
            }
        } catch (\Exception $e) {
            Log::error('Convoy getStatus error', ['error' => $e->getMessage()]);
            $status->status = 'unknown';
        }

        $status->checkedAt = now();
        return $status;
    }

    public function getServiceDetails(Service $service): array
    {
        try {
            $serverUuid = $service->provisioning_data['server_uuid'] ?? null;
            if (!$serverUuid) return [];

            $response = $this->makeRequest('get', "/servers/{$serverUuid}", []);

            if (isset($response['attributes'])) {
                $server = $response['attributes'];
                return [
                    'server_id' => $server['id'],
                    'server_uuid' => $server['uuid'],
                    'name' => $server['name'],
                    'hostname' => $server['hostname'] ?? '',
                    'vmid' => $server['vmid'] ?? null,
                    'status' => $server['status'] ?? '',
                    'ip_address' => $server['ip_address'] ?? '',
                    'cpu_cores' => $server['limits']['cpu'] ?? 0,
                    'memory_mb' => $server['limits']['memory'] ?? 0,
                    'disk_gb' => $server['limits']['disk'] ?? 0,
                    'snapshots_limit' => $server['limits']['snapshots'] ?? 0,
                    'backups_limit' => $server['limits']['backups'] ?? 0,
                    'node' => $server['node']['name'] ?? '',
                ];
            }

            return [];
        } catch (\Exception $e) {
            Log::error('Convoy getDetails error', ['error' => $e->getMessage()]);
            return [];
        }
    }

    public function getConfigSchema(): array
    {
        return [
            'base_url' => [
                'type' => 'text',
                'label' => 'Convoy Panel URL',
                'required' => true,
                'description' => 'Convoy panel URL (e.g., https://convoy.example.com)',
            ],
            'api_key' => [
                'type' => 'password',
                'label' => 'API Key',
                'required' => true,
                'description' => 'Convoy Application API key (Bearer token)',
            ],
        ];
    }

    public function validateConfig(array $config): bool
    {
        return !empty($config['base_url']) && !empty($config['api_key']);
    }

    public function healthCheck(): HealthCheckResult
    {
        $result = new HealthCheckResult();
        $result->serviceName = 'Convoy Provisioning Panel';

        try {
            // Test connection by getting nodes
            $response = $this->makeRequest('get', '/nodes', []);

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

    public function getAvailableTemplates(): array
    {
        try {
            $response = $this->makeRequest('get', '/templates', []);

            $items = [];
            if (isset($response['data']) && is_array($response['data'])) {
                foreach ($response['data'] as $item) {
                    $attributes = $item['attributes'] ?? [];
                    $items[] = [
                        'id' => $attributes['uuid'] ?? ($attributes['id'] ?? null),
                        'name' => $attributes['name'] ?? ($attributes['label'] ?? 'Unnamed'),
                    ];
                }
            }

            // Fallback: if no templates endpoint, attempt images
            if (empty($items)) {
                $images = $this->makeRequest('get', '/images', []);
                if (isset($images['data']) && is_array($images['data'])) {
                    foreach ($images['data'] as $img) {
                        $attr = $img['attributes'] ?? [];
                        $items[] = [
                            'id' => $attr['uuid'] ?? ($attr['id'] ?? null),
                            'name' => $attr['name'] ?? ($attr['label'] ?? 'Image'),
                        ];
                    }
                }
            }

            return $items;
        } catch (\Exception $e) {
            Log::error('Convoy getAvailableTemplates error', ['error' => $e->getMessage()]);
            return [];
        }
    }
}
