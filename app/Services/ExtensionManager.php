<?php

namespace App\Services;

use App\Contracts\PaymentGatewayInterface;
use App\Contracts\ProvisioningProviderInterface;
use App\Models\Extension;
use App\Models\ExtensionConfig;
use Illuminate\Support\Facades\File;

class ExtensionManager
{
    protected array $paymentGateways = [];
    protected array $provisioningProviders = [];

    /**
     * Register payment gateway extension
     */
    public function registerPaymentGateway(string $name, string $class): void
    {
        if (!in_array(PaymentGatewayInterface::class, class_implements($class))) {
            throw new \InvalidArgumentException("Class {$class} must implement PaymentGatewayInterface");
        }

        $this->paymentGateways[$name] = $class;
    }

    /**
     * Register provisioning provider extension
     */
    public function registerProvisioningProvider(string $name, string $class): void
    {
        if (!in_array(ProvisioningProviderInterface::class, class_implements($class))) {
            throw new \InvalidArgumentException("Class {$class} must implement ProvisioningProviderInterface");
        }

        $this->provisioningProviders[$name] = $class;
    }

    /**
     * Get payment gateway instance
     */
    public function getPaymentGateway(string $name): ?PaymentGatewayInterface
    {
        if (!isset($this->paymentGateways[$name])) {
            return null;
        }

        $extension = Extension::where('name', $name)
            ->where('type', 'payment_gateway')
            ->where('enabled', true)
            ->first();

        if (!$extension) {
            return null;
        }

        $config = $extension->configs->pluck('value', 'key')->toArray();
        return new $this->paymentGateways[$name]($config);
    }

    /**
     * Get provisioning provider instance
     */
    public function getProvisioningProvider(string $name): ?ProvisioningProviderInterface
    {
        if (!isset($this->provisioningProviders[$name])) {
            return null;
        }

        $extension = Extension::where('name', $name)
            ->where('type', 'provisioning_panel')
            ->where('enabled', true)
            ->first();

        if (!$extension) {
            return null;
        }

        $config = $extension->configs->pluck('value', 'key')->toArray();
        return new $this->provisioningProviders[$name]($config);
    }

    /**
     * Get all registered payment gateways
     */
    public function getAvailablePaymentGateways(): array
    {
        return $this->paymentGateways;
    }

    /**
     * Get all registered provisioning providers
     */
    public function getAvailableProvisioningProviders(): array
    {
        return $this->provisioningProviders;
    }

    /**
     * Get all enabled payment gateways
     */
    public function getEnabledPaymentGateways(): array
    {
        $enabled = Extension::where('type', 'payment_gateway')
            ->where('enabled', true)
            ->pluck('name')
            ->toArray();

        return array_intersect_key($this->paymentGateways, array_flip($enabled));
    }

    /**
     * Get all enabled provisioning providers
     */
    public function getEnabledProvisioningProviders(): array
    {
        $enabled = Extension::where('type', 'provisioning_panel')
            ->where('enabled', true)
            ->pluck('name')
            ->toArray();

        return array_intersect_key($this->provisioningProviders, array_flip($enabled));
    }

    /**
     * Auto-discover and register extensions
     */
    public function discoverExtensions(): void
    {
        // Discover payment gateways
        $paymentPath = app_path('Extensions/Payments');
        if (File::exists($paymentPath)) {
            $files = File::files($paymentPath);
            foreach ($files as $file) {
                $className = 'App\\Extensions\\Payments\\' . $file->getFilenameWithoutExtension();
                if (class_exists($className) && in_array(PaymentGatewayInterface::class, class_implements($className))) {
                    $name = strtolower(str_replace('Gateway', '', $file->getFilenameWithoutExtension()));
                    $this->registerPaymentGateway($name, $className);
                }
            }
        }

        // Discover provisioning providers
        $provisioningPath = app_path('Extensions/Provisioning');
        if (File::exists($provisioningPath)) {
            $files = File::files($provisioningPath);
            foreach ($files as $file) {
                $className = 'App\\Extensions\\Provisioning\\' . $file->getFilenameWithoutExtension();
                if (class_exists($className) && in_array(ProvisioningProviderInterface::class, class_implements($className))) {
                    $name = strtolower(str_replace('Provider', '', $file->getFilenameWithoutExtension()));
                    $this->registerProvisioningProvider($name, $className);
                }
            }
        }
    }
}
