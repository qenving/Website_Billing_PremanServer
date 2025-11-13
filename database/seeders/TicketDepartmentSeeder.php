<?php

namespace Database\Seeders;

use App\Models\TicketDepartment;
use Illuminate\Database\Seeder;

class TicketDepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $departments = [
            [
                'name' => 'Billing',
                'description' => 'Questions about invoices, payments, and billing issues',
                'email' => 'billing@hbm.local',
                'is_active' => true,
            ],
            [
                'name' => 'Technical Support',
                'description' => 'Technical issues with services, server problems, and configurations',
                'email' => 'support@hbm.local',
                'is_active' => true,
            ],
            [
                'name' => 'Sales',
                'description' => 'Questions about products, pricing, and custom solutions',
                'email' => 'sales@hbm.local',
                'is_active' => true,
            ],
            [
                'name' => 'Abuse',
                'description' => 'Report abuse, spam, or terms of service violations',
                'email' => 'abuse@hbm.local',
                'is_active' => true,
            ],
        ];

        foreach ($departments as $department) {
            TicketDepartment::updateOrCreate(
                ['name' => $department['name']],
                $department
            );
        }

        $this->command->info('✅ Ticket departments created successfully: ' . count($departments) . ' departments');
    }
}
