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

class ProxmoxProvider implements ProvisioningProviderInterface
{
    protected array $config;
    protected string $baseUrl;
    protected string $ticket;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->baseUrl = rtrim($config['base_url'] ?? '', '/');
    }

    protected function authenticate(): bool
    {
        try {
            $response = Http::post($this->baseUrl . '/api2/json/access/ticket', [
                'username' => $this->config['username'],
                'password' => $this->config['password'],
            ]);

            if ($response->successful()) {
                $data = $response->json('data');
                $this->ticket = $data['ticket'];
                return true;
            }

            return false;
        } catch (\Exception $e) {
            Log::error('Proxmox auth error', ['error' => $e->getMessage()]);
            return false;
        }
    }

    public function createService(Service $service, array $config, Client $client): ProvisionResult
    {
        $result = new ProvisionResult();

        try {
            if (!$this->authenticate()) {
                $result->success = false;
                $result->errorMessage = 'Authentication failed';
                return $result;
            }

            $node = $config['node'] ?? $this->config['default_node'];
            $vmid = $this->getNextVMID($node);

            $vmData = [
                'vmid' => $vmid,
                'name' => 'vm-' . $service->id,
                'ostype' => $config['os_type'] ?? 'l26',
                'memory' => $config['memory'] ?? 1024,
                'cores' => $config['cores'] ?? 1,
                'net0' => 'virtio,bridge=vmbr0',
                'scsi0' => 'local-lvm:' . ($config['disk'] ?? 10),
                'cdrom' => $config['iso'] ?? 'none',
                'password' => bin2hex(random_bytes(8)),
            ];

            $response = Http::withHeaders([
                'Cookie' => 'PVEAuthCookie=' . $this->ticket,
            ])->post($this->baseUrl . "/api2/json/nodes/{$node}/qemu", $vmData);

            if ($response->successful()) {
                $result->success = true;
                $result->serviceIdentifier = (string) $vmid;
                $result->ipAddress = null; // Will be assigned after boot
                $result->username = 'root';
                $result->password = $vmData['password'];
                $result->metadata = [
                    'node' => $node,
                    'vmid' => $vmid,
                    'memory' => $vmData['memory'],
                    'cores' => $vmData['cores'],
                ];
            } else {
                $result->success = false;
                $result->errorMessage = 'Failed to create VM: ' . $response->body();
            }
        } catch (\Exception $e) {
            Log::error('Proxmox createService error', ['error' => $e->getMessage()]);
            $result->success = false;
            $result->errorMessage = $e->getMessage();
        }

        return $result;
    }

    protected function getNextVMID(string $node): int
    {
        $response = Http::withHeaders([
            'Cookie' => 'PVEAuthCookie=' . $this->ticket,
        ])->get($this->baseUrl . '/api2/json/cluster/nextid');

        if ($response->successful()) {
            return (int) $response->json('data');
        }

        return 100; // Default fallback
    }

    public function suspendService(Service $service): bool
    {
        try {
            if (!$this->authenticate()) return false;

            $vmid = $service->provisioning_data['vmid'] ?? null;
            $node = $service->provisioning_data['node'] ?? $this->config['default_node'];

            if (!$vmid) return false;

            $response = Http::withHeaders([
                'Cookie' => 'PVEAuthCookie=' . $this->ticket,
            ])->post($this->baseUrl . "/api2/json/nodes/{$node}/qemu/{$vmid}/status/stop");

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('Proxmox suspend error', ['error' => $e->getMessage()]);
            return false;
        }
    }

    public function unsuspendService(Service $service): bool
    {
        try {
            if (!$this->authenticate()) return false;

            $vmid = $service->provisioning_data['vmid'] ?? null;
            $node = $service->provisioning_data['node'] ?? $this->config['default_node'];

            if (!$vmid) return false;

            $response = Http::withHeaders([
                'Cookie' => 'PVEAuthCookie=' . $this->ticket,
            ])->post($this->baseUrl . "/api2/json/nodes/{$node}/qemu/{$vmid}/status/start");

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('Proxmox unsuspend error', ['error' => $e->getMessage()]);
            return false;
        }
    }

    public function terminateService(Service $service): bool
    {
        try {
            if (!$this->authenticate()) return false;

            $vmid = $service->provisioning_data['vmid'] ?? null;
            $node = $service->provisioning_data['node'] ?? $this->config['default_node'];

            if (!$vmid) return false;

            $response = Http::withHeaders([
                'Cookie' => 'PVEAuthCookie=' . $this->ticket,
            ])->delete($this->baseUrl . "/api2/json/nodes/{$node}/qemu/{$vmid}");

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('Proxmox terminate error', ['error' => $e->getMessage()]);
            return false;
        }
    }

    public function rebootService(Service $service): bool
    {
        try {
            if (!$this->authenticate()) return false;

            $vmid = $service->provisioning_data['vmid'] ?? null;
            $node = $service->provisioning_data['node'] ?? $this->config['default_node'];

            if (!$vmid) return false;

            $response = Http::withHeaders([
                'Cookie' => 'PVEAuthCookie=' . $this->ticket,
            ])->post($this->baseUrl . "/api2/json/nodes/{$node}/qemu/{$vmid}/status/reboot");

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('Proxmox reboot error', ['error' => $e->getMessage()]);
            return false;
        }
    }

    public function getServiceStatus(Service $service): ServiceStatus
    {
        $status = new ServiceStatus();
        $status->serviceId = $service->id;

        try {
            if (!$this->authenticate()) {
                $status->status = 'unknown';
                return $status;
            }

            $vmid = $service->provisioning_data['vmid'] ?? null;
            $node = $service->provisioning_data['node'] ?? $this->config['default_node'];

            if (!$vmid) {
                $status->status = 'unknown';
                return $status;
            }

            $response = Http::withHeaders([
                'Cookie' => 'PVEAuthCookie=' . $this->ticket,
            ])->get($this->baseUrl . "/api2/json/nodes/{$node}/qemu/{$vmid}/status/current");

            if ($response->successful()) {
                $data = $response->json('data');
                $vmStatus = $data['status'];

                $status->status = match($vmStatus) {
                    'running' => 'online',
                    'stopped' => 'offline',
                    default => 'unknown',
                };
                $status->uptime = $data['uptime'] ?? 0;
                $status->resourceUsage = [
                    'cpu' => $data['cpu'] ?? 0,
                    'memory' => $data['mem'] ?? 0,
                    'memory_max' => $data['maxmem'] ?? 0,
                ];
            }
        } catch (\Exception $e) {
            Log::error('Proxmox getStatus error', ['error' => $e->getMessage()]);
            $status->status = 'unknown';
        }

        $status->checkedAt = now();
        return $status;
    }

    public function getServiceDetails(Service $service): array
    {
        try {
            if (!$this->authenticate()) return [];

            $vmid = $service->provisioning_data['vmid'] ?? null;
            $node = $service->provisioning_data['node'] ?? $this->config['default_node'];

            if (!$vmid) return [];

            $response = Http::withHeaders([
                'Cookie' => 'PVEAuthCookie=' . $this->ticket,
            ])->get($this->baseUrl . "/api2/json/nodes/{$node}/qemu/{$vmid}/config");

            if ($response->successful()) {
                return $response->json('data');
            }

            return [];
        } catch (\Exception $e) {
            Log::error('Proxmox getDetails error', ['error' => $e->getMessage()]);
            return [];
        }
    }

    public function getConfigSchema(): array
    {
        return [
            'base_url' => [
                'type' => 'text',
                'label' => 'Proxmox URL',
                'required' => true,
                'description' => 'Proxmox API endpoint (e.g., https://proxmox.example.com:8006)',
            ],
            'username' => [
                'type' => 'text',
                'label' => 'Username',
                'required' => true,
                'description' => 'Proxmox username (e.g., root@pam)',
            ],
            'password' => [
                'type' => 'password',
                'label' => 'Password',
                'required' => true,
            ],
            'default_node' => [
                'type' => 'text',
                'label' => 'Default Node',
                'required' => true,
                'description' => 'Default Proxmox node name',
            ],
        ];
    }

    public function validateConfig(array $config): bool
    {
        return !empty($config['base_url']) && !empty($config['username']) && !empty($config['password']);
    }

    public function healthCheck(): HealthCheckResult
    {
        $result = new HealthCheckResult();
        $result->serviceName = 'Proxmox Provisioning Panel';

        try {
            if ($this->authenticate()) {
                $result->status = 'ok';
                $result->message = 'Connection successful';
            } else {
                $result->status = 'error';
                $result->message = 'Authentication failed';
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
            // Attempt to fetch templates/images from Proxmox cluster storages
            $nodesResponse = Http::withHeaders([
                'Cookie' => 'PVEAuthCookie=' . $this->ticket,
            ])->get($this->baseUrl . '/api2/json/nodes');

            if ($nodesResponse->successful()) {
                $nodes = $nodesResponse->json('data') ?? [];
                $items = [];

                foreach ($nodes as $n) {
                    $nodeName = $n['node'] ?? ($n['name'] ?? null);
                    if (!$nodeName) continue;

                    $storageResp = Http::withHeaders([
                        'Cookie' => 'PVEAuthCookie=' . $this->ticket,
                    ])->get($this->baseUrl . "/api2/json/nodes/{$nodeName}/storage");

                    if ($storageResp->successful()) {
                        $storages = $storageResp->json('data') ?? [];
                        foreach ($storages as $s) {
                            $contentResp = Http::withHeaders([
                                'Cookie' => 'PVEAuthCookie=' . $this->ticket,
                            ])->get($this->baseUrl . "/api2/json/nodes/{$nodeName}/storage/{$s['storage']}/content");

                            if ($contentResp->successful()) {
                                $items = array_merge($items, array_map(function ($it) {
                                    return [
                                        'id' => $it['volid'] ?? ($it['id'] ?? null),
                                        'name' => $it['name'] ?? ($it['filename'] ?? 'Template'),
                                    ];
                                }, $contentResp->json('data') ?? []));
                            }
                        }
                    }
                }

                return $items;
            }

            return [];
        } catch (\Exception $e) {
            Log::error('Proxmox getAvailableTemplates error', ['error' => $e->getMessage()]);
            return [];
        }
    }
}
