<?php

return [
    // Installation
    'installed' => env('HBM_INSTALLED', false),

    // Billing Settings
    'default_currency' => env('HBM_DEFAULT_CURRENCY', 'USD'),
    'invoice_prefix' => env('HBM_INVOICE_PREFIX', 'INV-'),
    'ticket_prefix' => env('HBM_TICKET_PREFIX', 'TKT-'),

    // Payment Settings
    'payment_due_days' => env('HBM_PAYMENT_DUE_DAYS', 7),
    'overdue_suspend_days' => env('HBM_OVERDUE_SUSPEND_DAYS', 3),
    'overdue_terminate_days' => env('HBM_OVERDUE_TERMINATE_DAYS', 14),

    // Service Settings
    'auto_provision' => env('HBM_AUTO_PROVISION', true),
    'auto_suspend' => env('HBM_AUTO_SUSPEND', true),
    'auto_terminate' => env('HBM_AUTO_TERMINATE', false),

    // Security
    'max_login_attempts' => env('HBM_MAX_LOGIN_ATTEMPTS', 5),
    'login_lockout_minutes' => env('HBM_LOGIN_LOCKOUT_MINUTES', 15),
    'require_2fa_admin' => env('HBM_REQUIRE_2FA_ADMIN', false),
    'session_timeout' => env('HBM_SESSION_TIMEOUT', 120),

    // Theme & UI
    'theme_style' => env('HBM_THEME_STYLE', 'default'),
    'theme_color_scheme' => env('HBM_THEME_COLOR_SCHEME', 'light'),
    'layout_type' => env('HBM_LAYOUT_TYPE', 'left-sidebar'),

    // Extensions
    'extensions_path' => app_path('Extensions'),
    'payment_extensions_namespace' => 'App\\Extensions\\Payments\\',
    'provisioning_extensions_namespace' => 'App\\Extensions\\Provisioning\\',
];
