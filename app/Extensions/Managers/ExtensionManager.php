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
     * Extension categories
     */
    protected array $categories = [
        'payment-gateways',
        'provisioning-modules',
    ];

    /**
     * Loaded extensions by category
     */
    protected array $extensions = [
        'payment-gateways' => [],
        'provisioning-modules' => [],
    ];

    /**
     * Payment gateways (quick access)
     */
    protected array $paymentGateways = [];

    /**
     * Provisioning modules (quick access)
     */
    protected array $provisioningModules = [];

    /**
     * Extensions base path
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

        // Discover extensions in each category
        foreach ($this->categories as $category) {
            $categoryPath = $this->extensionsPath . '/' . $category;

            if (!File::exists($categoryPath)) {
                File::makeDirectory($categoryPath, 0755, true);
                continue;
            }

            $this->discoverCategory($category);
        }

        Log::info('Extensions discovered', [
            'payment_gateways' => count($this->paymentGateways),
            'provisioning_modules' => count($this->provisioningModules),
        ]);
    }

    /**
     * Discover extensions in a category
     */
    protected function discoverCategory(string $category): void
    {
        $categoryPath = $this->extensionsPath . '/' . $category;
        $extensionDirs = File::directories($categoryPath);

        foreach ($extensionDirs as $dir) {
            $this->loadExtension($dir, $category);
        }
    }

    /**
     * Load a single extension
     */
    protected function loadExtension(string $path, string $category): void
    {
        $extensionFile = $path . '/Extension.php';

        if (!File::exists($extensionFile)) {
            Log::warning("Extension.php not found in: {$path}");
            return;
        }

        try {
            require_once $extensionFile;

            $extensionId = basename($path);
            $className = $this->getExtensionClassName($extensionId, $category);

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
                Log::info("Extension disabled: {$extension->getName()}");
                return;
            }

            // Register and boot extension
            $extension->register();
            $extension->boot();

            // Store extension
            $this->extensions[$category][$extensionId] = $extension;

            // Categorize by interface
            if ($extension instanceof PaymentGatewayInterface) {
                $this->paymentGateways[$extension->getGatewayId()] = $extension;
            }

            if ($extension instanceof ProvisioningModuleInterface) {
                $this->provisioningModules[$extension->getModuleId()] = $extension;
            }

            Log::info("Extension loaded: {$extension->getName()} v{$extension->getVersion()} [{$category}]");

        } catch (\Exception $e) {
            Log::error("Failed to load extension from {$path}: " . $e->getMessage(), [
                'exception' => $e,
            ]);
        }
    }

    /**
     * Get extension class name from extension ID and category
     */
    protected function getExtensionClassName(string $extensionId, string $category): string
    {
        // Convert extension-id to ExtensionId (PascalCase)
        $className = str_replace('-', '', ucwords($extensionId, '-'));

        // Convert category to namespace part
        $categoryNamespace = str_replace('-', '', ucwords($category, '-'));

        return "Extensions\\{$categoryNamespace}\\{$className}\\Extension";
    }

    /**
     * Get all extensions
     */
    public function getAllExtensions(): array
    {
        return $this->extensions;
    }

    /**
     * Get extensions (backward compatibility)
     */
    public function getExtensions(): array
    {
        // Return all extensions flattened for backward compatibility
        $allExtensions = [];
        foreach ($this->extensions as $category => $categoryExtensions) {
            $allExtensions = array_merge($allExtensions, $categoryExtensions);
        }
        return $allExtensions;
    }

    /**
     * Get extensions by category
     */
    public function getExtensionsByCategory(string $category): array
    {
        return $this->extensions[$category] ?? [];
    }

    /**
     * Get extension by ID and category
     */
    public function getExtension(string $id, ?string $category = null): ?ExtensionInterface
    {
        // If category specified, search in that category
        if ($category) {
            return $this->extensions[$category][$id] ?? null;
        }

        // Otherwise search in all categories (backward compatibility)
        foreach ($this->extensions as $categoryExtensions) {
            if (isset($categoryExtensions[$id])) {
                return $categoryExtensions[$id];
            }
        }

        return null;
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
    public function installExtension(string $id, ?string $category = null): bool
    {
        $extension = $this->getExtension($id, $category);

        if (!$extension) {
            Log::error("Extension not found: {$id}" . ($category ? " in category {$category}" : ""));
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
    public function uninstallExtension(string $id, ?string $category = null): bool
    {
        $extension = $this->getExtension($id, $category);

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
     * Reload all extensions
     */
    public function reload(): void
    {
        $this->extensions = [
            'payment-gateways' => [],
            'provisioning-modules' => [],
        ];
        $this->paymentGateways = [];
        $this->provisioningModules = [];
        $this->discoverExtensions();
    }

    /**
     * Get extension statistics
     */
    public function getStatistics(): array
    {
        return [
            'total_extensions' => array_sum(array_map('count', $this->extensions)),
            'payment_gateways' => count($this->paymentGateways),
            'provisioning_modules' => count($this->provisioningModules),
            'by_category' => array_map('count', $this->extensions),
        ];
    }
}
