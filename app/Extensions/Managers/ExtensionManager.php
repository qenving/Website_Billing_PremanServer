<?php

namespace App\Extensions\Managers;

use App\Extensions\Contracts\ExtensionInterface;
use App\Extensions\Contracts\PaymentGatewayInterface;
use App\Extensions\Contracts\ProvisioningModuleInterface;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class ExtensionManager
{
    /**
     * Loaded extensions
     */
    protected array $extensions = [];

    /**
     * Payment gateways
     */
    protected array $paymentGateways = [];

    /**
     * Provisioning modules
     */
    protected array $provisioningModules = [];

    /**
     * Extensions path
     */
    protected string $extensionsPath;

    public function __construct()
    {
        $this->extensionsPath = base_path('extensions');
    }

    /**
     * Discover and load all extensions
     */
    public function discoverExtensions(): void
    {
        if (!File::exists($this->extensionsPath)) {
            File::makeDirectory($this->extensionsPath, 0755, true);
            return;
        }

        $extensionDirs = File::directories($this->extensionsPath);

        foreach ($extensionDirs as $dir) {
            $this->loadExtension($dir);
        }
    }

    /**
     * Load a single extension
     */
    protected function loadExtension(string $path): void
    {
        $extensionFile = $path . '/Extension.php';

        if (!File::exists($extensionFile)) {
            return;
        }

        try {
            require_once $extensionFile;

            $extensionId = basename($path);
            $className = $this->getExtensionClassName($extensionId);

            if (!class_exists($className)) {
                Log::warning("Extension class not found: {$className}");
                return;
            }

            $extension = new $className();

            if (!$extension instanceof ExtensionInterface) {
                Log::warning("Extension does not implement ExtensionInterface: {$className}");
                return;
            }

            // Only load enabled extensions
            if (!$extension->isEnabled()) {
                return;
            }

            // Register and boot extension
            $extension->register();
            $extension->boot();

            // Store extension
            $this->extensions[$extensionId] = $extension;

            // Categorize by type
            if ($extension instanceof PaymentGatewayInterface) {
                $this->paymentGateways[$extension->getGatewayId()] = $extension;
            }

            if ($extension instanceof ProvisioningModuleInterface) {
                $this->provisioningModules[$extension->getModuleId()] = $extension;
            }

            Log::info("Extension loaded: {$extension->getName()} v{$extension->getVersion()}");

        } catch (\Exception $e) {
            Log::error("Failed to load extension from {$path}: " . $e->getMessage());
        }
    }

    /**
     * Get extension class name from extension ID
     */
    protected function getExtensionClassName(string $extensionId): string
    {
        // Convert extension-id to ExtensionId
        $className = str_replace('-', '', ucwords($extensionId, '-'));
        return "Extensions\\{$className}\\Extension";
    }

    /**
     * Get all loaded extensions
     */
    public function getExtensions(): array
    {
        return $this->extensions;
    }

    /**
     * Get extension by ID
     */
    public function getExtension(string $id): ?ExtensionInterface
    {
        return $this->extensions[$id] ?? null;
    }

    /**
     * Get all payment gateways
     */
    public function getPaymentGateways(): array
    {
        return $this->paymentGateways;
    }

    /**
     * Get payment gateway by ID
     */
    public function getPaymentGateway(string $id): ?PaymentGatewayInterface
    {
        return $this->paymentGateways[$id] ?? null;
    }

    /**
     * Get all provisioning modules
     */
    public function getProvisioningModules(): array
    {
        return $this->provisioningModules;
    }

    /**
     * Get provisioning module by ID
     */
    public function getProvisioningModule(string $id): ?ProvisioningModuleInterface
    {
        return $this->provisioningModules[$id] ?? null;
    }

    /**
     * Install extension
     */
    public function installExtension(string $id): bool
    {
        $extension = $this->getExtension($id);

        if (!$extension) {
            return false;
        }

        try {
            return $extension->install();
        } catch (\Exception $e) {
            Log::error("Failed to install extension {$id}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Uninstall extension
     */
    public function uninstallExtension(string $id): bool
    {
        $extension = $this->getExtension($id);

        if (!$extension) {
            return false;
        }

        try {
            return $extension->uninstall();
        } catch (\Exception $e) {
            Log::error("Failed to uninstall extension {$id}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Reload extensions
     */
    public function reload(): void
    {
        $this->extensions = [];
        $this->paymentGateways = [];
        $this->provisioningModules = [];
        $this->discoverExtensions();
    }
}
