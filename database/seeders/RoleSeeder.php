<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'name' => 'super_admin',
                'display_name' => 'Super Administrator',
                'description' => 'Full system access - can manage all settings, users, billing, extensions, and security',
                'permissions' => [
                    // All permissions
                    'admin.access',
                    'admin.dashboard',
                    'admin.financial',
                    'admin.users.view',
                    'admin.users.create',
                    'admin.users.edit',
                    'admin.users.delete',
                    'admin.clients.view',
                    'admin.clients.edit',
                    'admin.products.view',
                    'admin.products.create',
                    'admin.products.edit',
                    'admin.products.delete',
                    'admin.services.view',
                    'admin.services.manage',
                    'admin.invoices.view',
                    'admin.invoices.create',
                    'admin.invoices.edit',
                    'admin.invoices.delete',
                    'admin.payments.view',
                    'admin.extensions.view',
                    'admin.extensions.manage',
                    'admin.health.view',
                    'admin.security.view',
                    'admin.security.manage',
                    'admin.settings.view',
                    'admin.settings.edit',
                    'admin.theme.view',
                    'admin.theme.edit',
                    'admin.tickets.view',
                    'admin.tickets.manage',
                ],
            ],
            [
                'name' => 'billing_admin',
                'display_name' => 'Billing Administrator',
                'description' => 'Manage billing, invoices, payments, and client accounts',
                'permissions' => [
                    'admin.access',
                    'admin.dashboard',
                    'admin.financial',
                    'admin.clients.view',
                    'admin.clients.edit',
                    'admin.services.view',
                    'admin.invoices.view',
                    'admin.invoices.create',
                    'admin.invoices.edit',
                    'admin.payments.view',
                    'admin.tickets.view',
                ],
            ],
            [
                'name' => 'support',
                'display_name' => 'Support Staff',
                'description' => 'Handle customer support tickets and view client information',
                'permissions' => [
                    'admin.access',
                    'admin.dashboard',
                    'admin.clients.view',
                    'admin.services.view',
                    'admin.invoices.view',
                    'admin.tickets.view',
                    'admin.tickets.manage',
                ],
            ],
            [
                'name' => 'client',
                'display_name' => 'Client',
                'description' => 'Standard client account with access to services, billing, and support',
                'permissions' => [
                    'client.dashboard',
                    'client.services.view',
                    'client.services.manage',
                    'client.orders.create',
                    'client.invoices.view',
                    'client.invoices.pay',
                    'client.tickets.view',
                    'client.tickets.create',
                    'client.tickets.reply',
                    'client.account.view',
                    'client.account.edit',
                ],
            ],
        ];

        foreach ($roles as $roleData) {
            Role::updateOrCreate(
                ['name' => $roleData['name']],
                [
                    'display_name' => $roleData['display_name'],
                    'description' => $roleData['description'],
                    'permissions' => json_encode($roleData['permissions']),
                ]
            );
        }

        $this->command->info('✅ Roles created successfully: ' . count($roles) . ' roles');
    }
}
