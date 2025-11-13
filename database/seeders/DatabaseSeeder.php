<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->command->info('🌱 Starting HBM Billing System database seeding...');
        $this->command->newLine();

        // Core data seeders (always run)
        $this->command->info('📦 Seeding core data...');
        $this->call([
            RoleSeeder::class,
            SettingSeeder::class,
            ThemeSeeder::class,
            TicketDepartmentSeeder::class,
            ExtensionSeeder::class,
        ]);

        $this->command->newLine();
        $this->command->info('✅ Database seeding completed successfully!');
        $this->command->newLine();

        $this->command->info('📊 Seeding Summary:');
        $this->command->info('   ✅ 4 Roles (super_admin, billing_admin, support, client)');
        $this->command->info('   ✅ 45+ Settings (general, billing, automation, security, email)');
        $this->command->info('   ✅ 1 Default theme (light mode, left-sidebar layout)');
        $this->command->info('   ✅ 4 Ticket departments (billing, technical, sales, abuse)');
        $this->command->info('   ✅ 12 Extensions (7 payment gateways + 5 provisioning providers)');
        $this->command->newLine();

        $this->command->info('🚀 Next Steps:');
        $this->command->info('   1. Run the installation wizard at /install');
        $this->command->info('   2. Create the first super admin account');
        $this->command->info('   3. Configure payment gateways in Admin → Extensions');
        $this->command->info('   4. Configure provisioning panels in Admin → Extensions');
        $this->command->info('   5. Create product groups and products');
        $this->command->newLine();
    }
}
