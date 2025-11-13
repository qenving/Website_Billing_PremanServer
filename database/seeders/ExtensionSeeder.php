<?php

namespace Database\Seeders;

use App\Models\Extension;
use App\Models\PaymentExtension;
use App\Models\ProvisioningExtension;
use Illuminate\Database\Seeder;

class ExtensionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Payment Gateway Extensions
        $paymentGateways = [
            [
                'name' => 'midtrans',
                'display_name' => 'Midtrans',
                'type' => 'payment_gateway',
                'description' => 'Indonesian payment gateway with support for VA, QRIS, GoPay, and more',
                'enabled' => false,
                'class_name' => 'App\\Extensions\\Payments\\MidtransGateway',
                'version' => '1.0.0',
                'author' => 'HBM Team',
                'website' => 'https://midtrans.com',
            ],
            [
                'name' => 'xendit',
                'display_name' => 'Xendit',
                'type' => 'payment_gateway',
                'description' => 'Indonesian payment platform supporting VA, retail outlets, and e-wallets',
                'enabled' => false,
                'class_name' => 'App\\Extensions\\Payments\\XenditGateway',
                'version' => '1.0.0',
                'author' => 'HBM Team',
                'website' => 'https://xendit.co',
            ],
            [
                'name' => 'duitku',
                'display_name' => 'Duitku',
                'type' => 'payment_gateway',
                'description' => 'Multi-channel Indonesian payment gateway',
                'enabled' => false,
                'class_name' => 'App\\Extensions\\Payments\\DuitkuGateway',
                'version' => '1.0.0',
                'author' => 'HBM Team',
                'website' => 'https://duitku.com',
            ],
            [
                'name' => 'tripay',
                'display_name' => 'Tripay',
                'type' => 'payment_gateway',
                'description' => 'Indonesian payment gateway with QRIS and virtual account support',
                'enabled' => false,
                'class_name' => 'App\\Extensions\\Payments\\TripayGateway',
                'version' => '1.0.0',
                'author' => 'HBM Team',
                'website' => 'https://tripay.co.id',
            ],
            [
                'name' => 'paypal',
                'display_name' => 'PayPal',
                'type' => 'payment_gateway',
                'description' => 'International payment gateway for credit cards and PayPal accounts',
                'enabled' => false,
                'class_name' => 'App\\Extensions\\Payments\\PaypalGateway',
                'version' => '1.0.0',
                'author' => 'HBM Team',
                'website' => 'https://paypal.com',
            ],
            [
                'name' => 'stripe',
                'display_name' => 'Stripe',
                'type' => 'payment_gateway',
                'description' => 'International payment platform with credit card processing',
                'enabled' => false,
                'class_name' => 'App\\Extensions\\Payments\\StripeGateway',
                'version' => '1.0.0',
                'author' => 'HBM Team',
                'website' => 'https://stripe.com',
            ],
            [
                'name' => 'cryptomus',
                'display_name' => 'Cryptomus',
                'type' => 'payment_gateway',
                'description' => 'Cryptocurrency payment gateway supporting BTC, ETH, USDT, and more',
                'enabled' => false,
                'class_name' => 'App\\Extensions\\Payments\\CryptomusGateway',
                'version' => '1.0.0',
                'author' => 'HBM Team',
                'website' => 'https://cryptomus.com',
            ],
        ];

        foreach ($paymentGateways as $gateway) {
            $extension = Extension::updateOrCreate(
                ['name' => $gateway['name'], 'type' => 'payment_gateway'],
                [
                    'display_name' => $gateway['display_name'],
                    'description' => $gateway['description'],
                    'enabled' => $gateway['enabled'],
                    'class_name' => $gateway['class_name'],
                    'version' => $gateway['version'],
                    'author' => $gateway['author'],
                    'website' => $gateway['website'],
                ]
            );

            // Create payment extension entry
            PaymentExtension::updateOrCreate(
                ['extension_id' => $extension->id],
                [
                    'supported_currencies' => json_encode(['USD', 'IDR', 'EUR']),
                    'min_amount' => 0.01,
                    'max_amount' => 999999.99,
                    'processing_fee_type' => 'percentage',
                    'processing_fee_value' => 0,
                ]
            );
        }

        // Provisioning Panel Extensions
        $provisioningProviders = [
            [
                'name' => 'pterodactyl',
                'display_name' => 'Pterodactyl Panel',
                'type' => 'provisioning_panel',
                'description' => 'Game server management panel with support for Minecraft, CS:GO, and more',
                'enabled' => false,
                'class_name' => 'App\\Extensions\\Provisioning\\PterodactylProvider',
                'version' => '1.0.0',
                'author' => 'HBM Team',
                'website' => 'https://pterodactyl.io',
            ],
            [
                'name' => 'proxmox',
                'display_name' => 'Proxmox VE',
                'type' => 'provisioning_panel',
                'description' => 'Open-source virtualization platform for VPS and VM provisioning',
                'enabled' => false,
                'class_name' => 'App\\Extensions\\Provisioning\\ProxmoxProvider',
                'version' => '1.0.0',
                'author' => 'HBM Team',
                'website' => 'https://proxmox.com',
            ],
            [
                'name' => 'virtualizor',
                'display_name' => 'Virtualizor',
                'type' => 'provisioning_panel',
                'description' => 'VPS management panel with support for KVM, OpenVZ, Xen, and more',
                'enabled' => false,
                'class_name' => 'App\\Extensions\\Provisioning\\VirtualizorProvider',
                'version' => '1.0.0',
                'author' => 'HBM Team',
                'website' => 'https://virtualizor.com',
            ],
            [
                'name' => 'virtfusion',
                'display_name' => 'Virtfusion',
                'type' => 'provisioning_panel',
                'description' => 'Modern VPS control panel with clean API and user-friendly interface',
                'enabled' => false,
                'class_name' => 'App\\Extensions\\Provisioning\\VirtfusionProvider',
                'version' => '1.0.0',
                'author' => 'HBM Team',
                'website' => 'https://virtfusion.com',
            ],
            [
                'name' => 'convoy',
                'display_name' => 'Convoy Panel',
                'type' => 'provisioning_panel',
                'description' => 'Proxmox-based VPS management panel with modern interface',
                'enabled' => false,
                'class_name' => 'App\\Extensions\\Provisioning\\ConvoyProvider',
                'version' => '1.0.0',
                'author' => 'HBM Team',
                'website' => 'https://convoypanel.com',
            ],
        ];

        foreach ($provisioningProviders as $provider) {
            $extension = Extension::updateOrCreate(
                ['name' => $provider['name'], 'type' => 'provisioning_panel'],
                [
                    'display_name' => $provider['display_name'],
                    'description' => $provider['description'],
                    'enabled' => $provider['enabled'],
                    'class_name' => $provider['class_name'],
                    'version' => $provider['version'],
                    'author' => $provider['author'],
                    'website' => $provider['website'],
                ]
            );

            // Create provisioning extension entry
            ProvisioningExtension::updateOrCreate(
                ['extension_id' => $extension->id],
                [
                    'supported_service_types' => json_encode(['vps', 'dedicated', 'game_server']),
                    'auto_provision' => false,
                    'auto_suspend' => false,
                    'auto_terminate' => false,
                ]
            );
        }

        $totalExtensions = count($paymentGateways) + count($provisioningProviders);
        $this->command->info('✅ Extensions registered successfully: ' . $totalExtensions . ' extensions');
        $this->command->info('   - ' . count($paymentGateways) . ' payment gateways');
        $this->command->info('   - ' . count($provisioningProviders) . ' provisioning providers');
    }
}
