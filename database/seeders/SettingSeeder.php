<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            // Brand & Company
            ['key' => 'brand_name', 'value' => 'HBM Billing', 'group' => 'general'],
            ['key' => 'company_name', 'value' => 'HBM Hosting & Billing Manager', 'group' => 'general'],
            ['key' => 'company_email', 'value' => 'support@hbm.local', 'group' => 'general'],
            ['key' => 'company_phone', 'value' => '', 'group' => 'general'],
            ['key' => 'company_address', 'value' => '', 'group' => 'general'],
            ['key' => 'base_url', 'value' => config('app.url'), 'group' => 'general'],
            ['key' => 'logo_url', 'value' => '', 'group' => 'general'],
            ['key' => 'favicon_url', 'value' => '', 'group' => 'general'],

            // Localization
            ['key' => 'default_currency', 'value' => 'USD', 'group' => 'localization'],
            ['key' => 'timezone', 'value' => 'UTC', 'group' => 'localization'],
            ['key' => 'date_format', 'value' => 'Y-m-d', 'group' => 'localization'],
            ['key' => 'time_format', 'value' => 'H:i:s', 'group' => 'localization'],
            ['key' => 'language', 'value' => 'en', 'group' => 'localization'],

            // Billing Settings
            ['key' => 'invoice_prefix', 'value' => 'INV-', 'group' => 'billing'],
            ['key' => 'invoice_starting_number', 'value' => '1000', 'group' => 'billing'],
            ['key' => 'payment_due_days', 'value' => '7', 'group' => 'billing'],
            ['key' => 'overdue_suspend_days', 'value' => '3', 'group' => 'billing'],
            ['key' => 'overdue_terminate_days', 'value' => '14', 'group' => 'billing'],
            ['key' => 'tax_enabled', 'value' => 'false', 'group' => 'billing'],
            ['key' => 'tax_rate', 'value' => '0', 'group' => 'billing'],
            ['key' => 'tax_name', 'value' => 'VAT', 'group' => 'billing'],

            // Service Automation
            ['key' => 'auto_provision', 'value' => 'true', 'group' => 'automation'],
            ['key' => 'auto_suspend', 'value' => 'true', 'group' => 'automation'],
            ['key' => 'auto_terminate', 'value' => 'false', 'group' => 'automation'],
            ['key' => 'welcome_email', 'value' => 'true', 'group' => 'automation'],
            ['key' => 'invoice_email', 'value' => 'true', 'group' => 'automation'],
            ['key' => 'payment_email', 'value' => 'true', 'group' => 'automation'],

            // Security
            ['key' => 'max_login_attempts', 'value' => '5', 'group' => 'security'],
            ['key' => 'login_lockout_minutes', 'value' => '15', 'group' => 'security'],
            ['key' => 'require_email_verification', 'value' => 'true', 'group' => 'security'],
            ['key' => 'require_2fa_admin', 'value' => 'false', 'group' => 'security'],
            ['key' => 'session_timeout', 'value' => '120', 'group' => 'security'],
            ['key' => 'password_min_length', 'value' => '8', 'group' => 'security'],
            ['key' => 'password_require_uppercase', 'value' => 'true', 'group' => 'security'],
            ['key' => 'password_require_numbers', 'value' => 'true', 'group' => 'security'],
            ['key' => 'password_require_symbols', 'value' => 'false', 'group' => 'security'],

            // Email Settings (from .env)
            ['key' => 'mail_from_name', 'value' => config('mail.from.name'), 'group' => 'email'],
            ['key' => 'mail_from_address', 'value' => config('mail.from.address'), 'group' => 'email'],

            // Support & Tickets
            ['key' => 'ticket_prefix', 'value' => 'TKT-', 'group' => 'support'],
            ['key' => 'ticket_starting_number', 'value' => '1000', 'group' => 'support'],
            ['key' => 'ticket_auto_close_days', 'value' => '7', 'group' => 'support'],

            // Maintenance
            ['key' => 'maintenance_mode', 'value' => 'false', 'group' => 'maintenance'],
            ['key' => 'maintenance_message', 'value' => 'We are currently performing scheduled maintenance. Please check back soon.', 'group' => 'maintenance'],

            // Client Registration
            ['key' => 'allow_registration', 'value' => 'true', 'group' => 'registration'],
            ['key' => 'registration_approval', 'value' => 'false', 'group' => 'registration'],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                [
                    'value' => $setting['value'],
                    'group' => $setting['group'],
                ]
            );
        }

        $this->command->info('✅ Settings created successfully: ' . count($settings) . ' settings');
    }
}
