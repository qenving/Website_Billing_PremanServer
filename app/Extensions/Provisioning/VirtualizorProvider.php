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

class VirtualizorProvider implements ProvisioningProviderInterface
{
    protected array $config;
    protected string $baseUrl;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->baseUrl = rtrim($config['base_url'] ?? '', '/');
    }

    protected function makeRequest(string $endpoint, array $params = [], string $method = 'GET'): array
    {
        $params['api'] = 'json';
        $params['apikey'] = $this->config['api_key'];
        $params['apipass'] = $this->config['api_password'];

        $url = $this->baseUrl . '/index.php?act=' . $endpoint;

        try {
            if ($method === 'POST') {
                $response = Http::asForm()->post($url, $params);
            } else {
                $response = Http::get($url, $params);
            }

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Virtualizor API error', [
                'endpoint' => $endpoint,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return ['error' => $response->body()];
        } catch (\Exception $e) {
            Log::error('Virtualizor request exception', ['error' => $e->getMessage()]);
            return ['error' => $e->getMessage()];
        }
    }

    public function createService(Service $service, array $config, Client $client): ProvisionResult
    {
        $result = new ProvisionResult();

        try {
            $params = [
                'virt' => $config['virtualization_type'] ?? 'kvm', // kvm, openvz, xen, etc.
                'plid' => $config['plan_id'] ?? '',
                'hostname' => 'vm-' . $service->id . '.example.com',
                'password' => bin2hex(random_bytes(8)),
                'email' => $client->user->email,
                'osid' => $config['os_template_id'] ?? 1,
                'ips' => $config['ip_count'] ?? 1,
                'space' => $config['disk_gb'] ?? 10,
                'ram' => $config['ram_mb'] ?? 1024,
                'swapram' => $config['swap_mb'] ?? 512,
                'bandwidth' => $config['bandwidth_gb'] ?? 1000,
                'cpu_cores' => $config['cpu_cores'] ?? 1,
                'cpu_percent' => $config['cpu_percent'] ?? 100,
                'cpu_units' => $config['cpu_units'] ?? 1000,
            ];

            $response = $this->makeRequest('addvs', $params, 'POST');

            if (isset($response['done']) && isset($response['done']['vpsid'])) {
                $vpsId = $response['done']['vpsid'];
                $vpsInfo = $response['done']['addvs'] ?? [];

                $result->success = true;
                $result->serviceIdentifier = (string) $vpsId;
                $result->ipAddress = $vpsInfo['ips'][0] ?? null;
                $result->username = 'root';
                $result->password = $params['password'];
                $result->metadata = [
                    'vpsid' => $vpsId,
                    'hostname' => $params['hostname'],
                    'virtualization' => $params['virt'],
                    'plan_id' => $params['plid'],
                ];
            } else {
                $result->success = false;
                $result->errorMessage = $response['error'] ?? 'Failed to create VPS';
            }
        } catch (\Exception $e) {
            Log::error('Virtualizor createService error', ['error' => $e->getMessage()]);
            $result->success = false;
            $result->errorMessage = $e->getMessage();
        }

        return $result;
    }

    public function suspendService(Service $service): bool
    {
        try {
            $vpsId = $service->provisioning_data['vpsid'] ?? null;
            if (!$vpsId) return false;

            $response = $this->makeRequest('vs', [
                'suspend' => $vpsId,
            ], 'POST');

            return isset($response['done']);
        } catch (\Exception $e) {
            Log::error('Virtualizor suspend error', ['error' => $e->getMessage()]);
            return false;
        }
    }

    public function unsuspendService(Service $service): bool
    {
        try {
            $vpsId = $service->provisioning_data['vpsid'] ?? null;
            if (!$vpsId) return false;

            $response = $this->makeRequest('vs', [
                'unsuspend' => $vpsId,
            ], 'POST');

            return isset($response['done']);
        } catch (\Exception $e) {
            Log::error('Virtualizor unsuspend error', ['error' => $e->getMessage()]);
            return false;
        }
    }

    public function terminateService(Service $service): bool
    {
        try {
            $vpsId = $service->provisioning_data['vpsid'] ?? null;
            if (!$vpsId) return false;

            $response = $this->makeRequest('vs', [
                'delete' => $vpsId,
                'deletevps' => 1,
            ], 'POST');

            return isset($response['done']);
        } catch (\Exception $e) {
            Log::error('Virtualizor terminate error', ['error' => $e->getMessage()]);
            return false;
        }
    }

    public function rebootService(Service $service): bool
    {
        try {
            $vpsId = $service->provisioning_data['vpsid'] ?? null;
            if (!$vpsId) return false;

            $response = $this->makeRequest('vs', [
                'restart' => $vpsId,
            ], 'POST');

            return isset($response['done']);
        } catch (\Exception $e) {
            Log::error('Virtualizor reboot error', ['error' => $e->getMessage()]);
            return false;
        }
    }

    public function getServiceStatus(Service $service): ServiceStatus
    {
        $status = new ServiceStatus();
        $status->serviceId = $service->id;

        try {
            $vpsId = $service->provisioning_data['vpsid'] ?? null;
            if (!$vpsId) {
                $status->status = 'unknown';
                return $status;
            }

            $response = $this->makeRequest('vs', ['vpsid' => $vpsId]);

            if (isset($response['info'])) {
                $info = $response['info'];
                $vpsStatus = $info['status'] ?? '';

                $status->status = match($vpsStatus) {
                    'running', 'online' => 'online',
                    'stopped', 'offline' => 'offline',
                    'suspended' => 'suspended',
                    default => 'unknown',
                };
                $status->uptime = (int) ($info['uptime'] ?? 0);
                $status->resourceUsage = [
                    'cpu_usage' => $info['cpu_usage'] ?? 0,
                    'ram_usage_mb' => $info['ram_usage'] ?? 0,
                    'disk_usage_gb' => $info['disk_usage'] ?? 0,
                    'bandwidth_usage_gb' => $info['bandwidth_usage'] ?? 0,
                ];
            }
        } catch (\Exception $e) {
            Log::error('Virtualizor getStatus error', ['error' => $e->getMessage()]);
            $status->status = 'unknown';
        }

        $status->checkedAt = now();
        return $status;
    }

    public function getServiceDetails(Service $service): array
    {
        try {
            $vpsId = $service->provisioning_data['vpsid'] ?? null;
            if (!$vpsId) return [];

            $response = $this->makeRequest('vs', ['vpsid' => $vpsId]);

            if (isset($response['info'])) {
                return [
                    'vpsid' => $vpsId,
                    'hostname' => $response['info']['hostname'] ?? '',
                    'os' => $response['info']['os_name'] ?? '',
                    'primary_ip' => $response['info']['ip'] ?? [],
                    'status' => $response['info']['status'] ?? '',
                    'ram_mb' => $response['info']['ram'] ?? 0,
                    'disk_gb' => $response['info']['space'] ?? 0,
                    'cpu_cores' => $response['info']['cpu_cores'] ?? 0,
                    'bandwidth_gb' => $response['info']['bandwidth'] ?? 0,
                    'uptime' => $response['info']['uptime'] ?? 0,
                ];
            }

            return [];
        } catch (\Exception $e) {
            Log::error('Virtualizor getDetails error', ['error' => $e->getMessage()]);
            return [];
        }
    }

    public function getConfigSchema(): array
    {
        return [
            'base_url' => [
                'type' => 'text',
                'label' => 'Virtualizor URL',
                'required' => true,
                'description' => 'Virtualizor panel URL (e.g., https://panel.example.com:4085)',
            ],
            'api_key' => [
                'type' => 'text',
                'label' => 'API Key',
                'required' => true,
                'description' => 'Virtualizor API key',
            ],
            'api_password' => [
                'type' => 'password',
                'label' => 'API Password',
                'required' => true,
                'description' => 'Virtualizor API password',
            ],
        ];
    }

    public function validateConfig(array $config): bool
    {
        return !empty($config['base_url'])
            && !empty($config['api_key'])
            && !empty($config['api_password']);
    }

    public function healthCheck(): HealthCheckResult
    {
        $result = new HealthCheckResult();
        $result->serviceName = 'Virtualizor Provisioning Panel';

        try {
            // Test connection by listing VPS (limit 1)
            $response = $this->makeRequest('listvs', ['noc' => 1]);

            if (isset($response['vps']) || isset($response['vs'])) {
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
            // Virtualizor templates / OS list via oslist
            $resp = $this->makeRequest('oslist', [], 'GET');
            $items = [];
            if (isset($resp['oses']) && is_array($resp['oses'])) {
                foreach ($resp['oses'] as $os) {
                    $items[] = [
                        'id' => $os['osid'] ?? ($os['id'] ?? null),
                        'name' => $os['osname'] ?? ($os['name'] ?? 'OS'),
                    ];
                }
            }

            return $items;
        } catch (\Exception $e) {
            Log::error('Virtualizor getAvailableTemplates error', ['error' => $e->getMessage()]);
            return [];
        }
    }
}
