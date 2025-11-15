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
use Illuminate\Support\Str;

class PterodactylProvider implements ProvisioningProviderInterface
{
    protected array $config;
    protected string $apiUrl;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->apiUrl = rtrim($config['api_endpoint'] ?? '', '/') . '/api/application';
    }

    public function createService(Service $service, array $config, Client $client): ProvisionResult
    {
        try {
            // 1. Create user jika belum ada
            $userId = $this->getOrCreateUser($client);

            if (!$userId) {
                return ProvisionResult::failed('Failed to create user');
            }

            // 2. Create server
            $serverData = [
                'name' => $config['server_name'] ?? "Server-{$service->id}",
                'user' => $userId,
                'egg' => (int) $config['egg_id'],
                'docker_image' => $config['docker_image'] ?? 'ghcr.io/pterodactyl/yolks:java_17',
                'startup' => $config['startup_command'] ?? '',
                'environment' => $config['environment'] ?? [],
                'limits' => [
                    'memory' => (int) $config['memory'], // MB
                    'swap' => (int) ($config['swap'] ?? 0),
                    'disk' => (int) $config['disk'], // MB
                    'io' => (int) ($config['io'] ?? 500),
                    'cpu' => (int) $config['cpu'], // percentage
                ],
                'feature_limits' => [
                    'databases' => (int) ($config['databases'] ?? 0),
                    'allocations' => (int) ($config['allocations'] ?? 1),
                    'backups' => (int) ($config['backups'] ?? 0),
                ],
                'allocation' => [
                    'default' => (int) $config['allocation_id'],
                ],
            ];

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->config['api_key'],
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ])->post("{$this->apiUrl}/servers", $serverData);

            if (!$response->successful()) {
                Log::error('Pterodactyl createService failed', [
                    'response' => $response->json(),
                ]);

                return ProvisionResult::failed(
                    'Failed to create server: ' . ($response->json()['errors'][0]['detail'] ?? 'Unknown error'),
                    $response->json()
                );
            }

            $serverInfo = $response->json();
            $serverId = $serverInfo['attributes']['id'] ?? null;
            $uuid = $serverInfo['attributes']['uuid'] ?? null;

            return ProvisionResult::success(
                externalId: (string) $serverId,
                credentials: [
                    'username' => $client->user->email,
                    'panel_url' => $this->config['panel_url'] ?? rtrim($this->config['api_endpoint'], '/api'),
                    'server_uuid' => $uuid,
                ],
                accessInfo: [
                    'server_id' => $serverId,
                    'uuid' => $uuid,
                    'identifier' => $serverInfo['attributes']['identifier'] ?? null,
                ],
                rawResponse: $serverInfo
            );
        } catch (\Exception $e) {
            Log::error('Pterodactyl createService exception', [
                'message' => $e->getMessage(),
                'service_id' => $service->id,
            ]);

            return ProvisionResult::failed($e->getMessage());
        }
    }

    protected function getOrCreateUser(Client $client): ?int
    {
        // Cek apakah user sudah ada
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->config['api_key'],
            'Accept' => 'application/json',
        ])->get("{$this->apiUrl}/users", [
            'filter[email]' => $client->user->email,
        ]);

        if ($response->successful()) {
            $users = $response->json()['data'] ?? [];

            if (!empty($users)) {
                return $users[0]['attributes']['id'];
            }
        }

        // Create user baru
        $userData = [
            'email' => $client->user->email,
            'username' => Str::slug($client->user->name) . rand(100, 999),
            'first_name' => $client->user->name,
            'last_name' => '',
        ];

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->config['api_key'],
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ])->post("{$this->apiUrl}/users", $userData);

        if ($response->successful()) {
            return $response->json()['attributes']['id'] ?? null;
        }

        return null;
    }

    public function suspendService(Service $service): bool
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->config['api_key'],
                'Accept' => 'application/json',
            ])->post("{$this->apiUrl}/servers/{$service->provisioning_external_id}/suspend");

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('Pterodactyl suspendService exception', [
                'message' => $e->getMessage(),
                'service_id' => $service->id,
            ]);

            return false;
        }
    }

    public function unsuspendService(Service $service): bool
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->config['api_key'],
                'Accept' => 'application/json',
            ])->post("{$this->apiUrl}/servers/{$service->provisioning_external_id}/unsuspend");

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('Pterodactyl unsuspendService exception', [
                'message' => $e->getMessage(),
                'service_id' => $service->id,
            ]);

            return false;
        }
    }

    public function terminateService(Service $service): bool
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->config['api_key'],
                'Accept' => 'application/json',
            ])->delete("{$this->apiUrl}/servers/{$service->provisioning_external_id}");

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('Pterodactyl terminateService exception', [
                'message' => $e->getMessage(),
                'service_id' => $service->id,
            ]);

            return false;
        }
    }

    public function rebootService(Service $service): bool
    {
        try {
            // Menggunakan client API untuk power actions
            $clientApiUrl = str_replace('/application', '/client', $this->apiUrl);
            $uuid = $service->getProvisioningData('uuid');

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->config['api_key'],
                'Accept' => 'application/json',
            ])->post("{$clientApiUrl}/servers/{$uuid}/power", [
                'signal' => 'restart',
            ]);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('Pterodactyl rebootService exception', [
                'message' => $e->getMessage(),
                'service_id' => $service->id,
            ]);

            return false;
        }
    }

    public function getServiceStatus(Service $service): ServiceStatus
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->config['api_key'],
                'Accept' => 'application/json',
            ])->get("{$this->apiUrl}/servers/{$service->provisioning_external_id}");

            if ($response->successful()) {
                $data = $response->json()['attributes'] ?? [];
                $status = $data['status'] ?? 'unknown';
                $suspended = $data['suspended'] ?? false;

                if ($suspended) {
                    return ServiceStatus::suspended();
                }

                return match ($status) {
                    'running' => ServiceStatus::running(),
                    'offline', 'stopped' => ServiceStatus::stopped(),
                    default => ServiceStatus::unknown(),
                };
            }

            return ServiceStatus::error('Failed to get server status');
        } catch (\Exception $e) {
            return ServiceStatus::error($e->getMessage());
        }
    }

    public function getServiceDetails(Service $service): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->config['api_key'],
                'Accept' => 'application/json',
            ])->get("{$this->apiUrl}/servers/{$service->provisioning_external_id}");

            if ($response->successful()) {
                $data = $response->json()['attributes'] ?? [];

                return [
                    'name' => $data['name'] ?? '',
                    'uuid' => $data['uuid'] ?? '',
                    'identifier' => $data['identifier'] ?? '',
                    'memory' => $data['limits']['memory'] ?? 0,
                    'disk' => $data['limits']['disk'] ?? 0,
                    'cpu' => $data['limits']['cpu'] ?? 0,
                    'panel_url' => $this->config['panel_url'] ?? '',
                ];
            }

            return [];
        } catch (\Exception $e) {
            return [];
        }
    }

    public function getConfigSchema(): array
    {
        return [
            'api_endpoint' => [
                'type' => 'text',
                'label' => 'API Endpoint',
                'placeholder' => 'https://panel.example.com',
                'required' => true,
            ],
            'api_key' => [
                'type' => 'text',
                'label' => 'API Key (Application)',
                'required' => true,
                'encrypted' => true,
            ],
            'panel_url' => [
                'type' => 'text',
                'label' => 'Panel URL (for clients)',
                'placeholder' => 'https://panel.example.com',
                'required' => true,
            ],
        ];
    }

    public function validateConfig(array $config): bool
    {
        return !empty($config['api_endpoint']) && !empty($config['api_key']);
    }

    public function healthCheck(): HealthCheckResult
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->config['api_key'],
                'Accept' => 'application/json',
            ])->get("{$this->apiUrl}/servers");

            if ($response->successful()) {
                return HealthCheckResult::ok('Pterodactyl API is reachable');
            }

            if ($response->status() === 401 || $response->status() === 403) {
                return HealthCheckResult::error('Invalid API credentials');
            }

            return HealthCheckResult::error('Failed to connect', [
                'status_code' => $response->status(),
            ]);
        } catch (\Exception $e) {
            return HealthCheckResult::error('Connection error: ' . $e->getMessage());
        }
    }

    public function getAvailableTemplates(): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->config['api_key'],
                'Accept' => 'application/json',
            ])->get("{$this->apiUrl}/eggs");

            if ($response->successful()) {
                $eggs = $response->json()['data'] ?? [];

                return array_map(function ($egg) {
                    return [
                        'id' => $egg['attributes']['id'],
                        'name' => $egg['attributes']['name'],
                    ];
                }, $eggs);
            }

            return [];
        } catch (\Exception $e) {
            return [];
        }
    }
}
