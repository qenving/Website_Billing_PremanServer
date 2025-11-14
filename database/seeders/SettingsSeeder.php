<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            // General Settings
            [
                'key' => 'company_name',
                'value' => 'HBM Billing',
                'type' => 'text',
                'group' => 'general',
                'label' => 'Company Name',
                'description' => 'Your company name displayed throughout the system',
                'sort_order' => 1,
            ],
            [
                'key' => 'company_email',
                'value' => 'support@example.com',
                'type' => 'text',
                'group' => 'general',
                'label' => 'Company Email',
                'description' => 'Primary contact email',
                'sort_order' => 2,
            ],
            [
                'key' => 'company_phone',
                'value' => '+1234567890',
                'type' => 'text',
                'group' => 'general',
                'label' => 'Company Phone',
                'sort_order' => 3,
            ],
            [
                'key' => 'company_address',
                'value' => '123 Business Street',
                'type' => 'text',
                'group' => 'general',
                'label' => 'Company Address',
                'sort_order' => 4,
            ],
            [
                'key' => 'currency',
                'value' => 'USD',
                'type' => 'text',
                'group' => 'general',
                'label' => 'Currency',
                'description' => 'Default currency code (USD, EUR, IDR, etc.)',
                'sort_order' => 5,
            ],
            [
                'key' => 'timezone',
                'value' => 'UTC',
                'type' => 'text',
                'group' => 'general',
                'label' => 'Timezone',
                'sort_order' => 6,
            ],
            [
                'key' => 'date_format',
                'value' => 'Y-m-d',
                'type' => 'text',
                'group' => 'general',
                'label' => 'Date Format',
                'sort_order' => 7,
            ],

            // Billing Settings
            [
                'key' => 'invoice_prefix',
                'value' => 'INV',
                'type' => 'text',
                'group' => 'billing',
                'label' => 'Invoice Prefix',
                'description' => 'Prefix for invoice numbers',
                'sort_order' => 1,
            ],
            [
                'key' => 'invoice_due_days',
                'value' => '7',
                'type' => 'number',
                'group' => 'billing',
                'label' => 'Invoice Due Days',
                'description' => 'Default days until invoice is due',
                'sort_order' => 2,
            ],
            [
                'key' => 'suspension_grace_days',
                'value' => '7',
                'type' => 'number',
                'group' => 'billing',
                'label' => 'Suspension Grace Period',
                'description' => 'Days after due date before service suspension',
                'sort_order' => 3,
            ],
            [
                'key' => 'termination_days',
                'value' => '30',
                'type' => 'number',
                'group' => 'billing',
                'label' => 'Termination Days',
                'description' => 'Days after suspension before service termination',
                'sort_order' => 4,
            ],
            [
                'key' => 'tax_enabled',
                'value' => '0',
                'type' => 'boolean',
                'group' => 'billing',
                'label' => 'Enable Tax',
                'sort_order' => 5,
            ],
            [
                'key' => 'tax_rate',
                'value' => '0',
                'type' => 'number',
                'group' => 'billing',
                'label' => 'Default Tax Rate (%)',
                'sort_order' => 6,
            ],

            // Email Settings
            [
                'key' => 'email_send_invoices',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'email',
                'label' => 'Send Invoice Emails',
                'sort_order' => 1,
            ],
            [
                'key' => 'email_send_payment_confirmation',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'email',
                'label' => 'Send Payment Confirmations',
                'sort_order' => 2,
            ],
            [
                'key' => 'email_send_service_notifications',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'email',
                'label' => 'Send Service Notifications',
                'sort_order' => 3,
            ],

            // System Settings
            [
                'key' => 'maintenance_mode',
                'value' => '0',
                'type' => 'boolean',
                'group' => 'system',
                'label' => 'Maintenance Mode',
                'description' => 'Put site in maintenance mode',
                'sort_order' => 1,
            ],
            [
                'key' => 'allow_registration',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'system',
                'label' => 'Allow New Registrations',
                'sort_order' => 2,
            ],
            [
                'key' => 'require_email_verification',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'system',
                'label' => 'Require Email Verification',
                'sort_order' => 3,
            ],
            [
                'key' => 'session_lifetime',
                'value' => '120',
                'type' => 'number',
                'group' => 'system',
                'label' => 'Session Lifetime (minutes)',
                'sort_order' => 4,
            ],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}
