<?php

namespace App\Providers;

use App\Services\ExtensionManager;
use Illuminate\Support\ServiceProvider;

class ExtensionServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(ExtensionManager $extensionManager): void
    {
        // Auto-discover all extensions
        $extensionManager->discoverExtensions();

        // Manual registration (alternative to auto-discovery)
        // Payment Gateways
        $extensionManager->registerPaymentGateway('midtrans', \App\Extensions\Payments\MidtransGateway::class);
        $extensionManager->registerPaymentGateway('xendit', \App\Extensions\Payments\XenditGateway::class);
        $extensionManager->registerPaymentGateway('duitku', \App\Extensions\Payments\DuitkuGateway::class);
        $extensionManager->registerPaymentGateway('tripay', \App\Extensions\Payments\TripayGateway::class);
        $extensionManager->registerPaymentGateway('paypal', \App\Extensions\Payments\PaypalGateway::class);
        $extensionManager->registerPaymentGateway('stripe', \App\Extensions\Payments\StripeGateway::class);
        $extensionManager->registerPaymentGateway('cryptomus', \App\Extensions\Payments\CryptomusGateway::class);

        // Provisioning Providers
        $extensionManager->registerProvisioningProvider('pterodactyl', \App\Extensions\Provisioning\PterodactylProvider::class);
        $extensionManager->registerProvisioningProvider('proxmox', \App\Extensions\Provisioning\ProxmoxProvider::class);
        $extensionManager->registerProvisioningProvider('virtualizor', \App\Extensions\Provisioning\VirtualizorProvider::class);
        $extensionManager->registerProvisioningProvider('virtfusion', \App\Extensions\Provisioning\VirtfusionProvider::class);
        $extensionManager->registerProvisioningProvider('convoy', \App\Extensions\Provisioning\ConvoyProvider::class);
    }
}
