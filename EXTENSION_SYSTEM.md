# Extension System Documentation

## Overview

The HBM Billing System includes a powerful extension system that allows developers to add custom functionality without modifying the core codebase. This system supports:

- **Payment Gateways**: Add custom payment processors
- **Provisioning Modules**: Automate server provisioning
- **Custom Features**: Add any functionality you need
- **Third-party Integrations**: Connect with external services

## Architecture

The extension system is built on these core components:

### 1. Extension Interface (`ExtensionInterface`)

Base interface that all extensions must implement.

Located: `app/Extensions/Contracts/ExtensionInterface.php`

Required methods:
- `getName()`: Extension name
- `getDescription()`: Extension description
- `getVersion()`: Semantic version (e.g., "1.0.0")
- `getAuthor()`: Author name/company
- `boot()`: Bootstrap extension
- `register()`: Register services
- `getConfig()`: Get configuration array
- `isEnabled()`: Check if enabled
- `install()`: Install extension
- `uninstall()`: Uninstall extension

### 2. Specialized Interfaces

#### Payment Gateway Interface
`PaymentGatewayInterface` extends `ExtensionInterface`

Location: `app/Extensions/Contracts/PaymentGatewayInterface.php`

Additional methods:
- `getGatewayId()`: Unique identifier
- `getDisplayName()`: Display name for users
- `getLogo()`: Logo URL/path
- `getConfigFields()`: Admin configuration fields
- `processPayment()`: Process a payment
- `handleWebhook()`: Handle payment callbacks
- `refund()`: Process refunds
- `getSupportedMethods()`: Supported payment methods
- `isConfigured()`: Check configuration status
- `getFees()`: Get gateway fee structure

#### Provisioning Module Interface
`ProvisioningModuleInterface` extends `ExtensionInterface`

Location: `app/Extensions/Contracts/ProvisioningModuleInterface.php`

Additional methods:
- `getModuleId()`: Unique identifier
- `getDisplayName()`: Display name
- `getServerConfigFields()`: Server configuration fields
- `getProductConfigFields()`: Product configuration fields
- `createAccount()`: Provision new service
- `suspendAccount()`: Suspend service
- `unsuspendAccount()`: Reactivate service
- `terminateAccount()`: Terminate service
- `changePassword()`: Change service password
- `changePackage()`: Upgrade/downgrade service
- `getUsageStats()`: Get usage statistics
- `testConnection()`: Test server connection
- `getAvailablePackages()`: List available packages
- `accountExists()`: Check if account exists

### 3. Base Extension Class

`Extension` abstract class provides common functionality.

Location: `app/Extensions/Extension.php`

Features:
- Configuration management (load/save to database)
- Enable/disable functionality
- Extension path helpers
- View loading
- Asset URL generation
- Default install/uninstall implementations

### 4. Extension Manager

`ExtensionManager` handles discovery, loading, and management of extensions.

Location: `app/Extensions/Managers/ExtensionManager.php`

Capabilities:
- Auto-discover extensions from `extensions/` directory
- Load and validate extensions
- Categorize extensions by type
- Install/uninstall extensions
- Enable/disable extensions
- Access extensions programmatically

### 5. Extension Service Provider

`ExtensionServiceProvider` bootstraps the extension system.

Location: `app/Providers/ExtensionServiceProvider.php`

Responsibilities:
- Register `ExtensionManager` as singleton
- Discover and load all extensions
- Load extension routes
- Load extension views
- Boot extensions on application startup

## Creating a Payment Gateway Extension

### Example: Stripe Payment Gateway

See: `extensions/payment-gateways/stripe/Extension.php`

```php
<?php

namespace Extensions\PaymentGateways\Stripe;

use App\Extensions\Extension;
use App\Extensions\Contracts\PaymentGatewayInterface;
use App\Models\Invoice;
use App\Models\Payment;

class Extension extends \App\Extensions\Extension implements PaymentGatewayInterface
{
    public function getExtensionId(): string
    {
        return 'stripe-gateway';
    }

    public function getGatewayId(): string
    {
        return 'stripe';
    }

    public function getConfigFields(): array
    {
        return [
            [
                'name' => 'secret_key',
                'label' => 'Secret Key',
                'type' => 'password',
                'required' => true,
            ],
            // More fields...
        ];
    }

    public function processPayment(Invoice $invoice, array $paymentData): array
    {
        // Payment processing logic
        return [
            'status' => 'success',
            'transaction_id' => '...',
            'message' => '...',
        ];
    }

    // Implement other required methods...
}
```

**Note:** The namespace follows the category-based convention: `Extensions\PaymentGateways\Stripe`

### Configuration Fields

Extensions can define configuration fields for the admin panel:

```php
public function getConfigFields(): array
{
    return [
        [
            'name' => 'api_key',           // Field name
            'label' => 'API Key',          // Display label
            'type' => 'text',              // Field type
            'required' => true,            // Is required
            'description' => 'Your API key', // Help text
        ],
        [
            'name' => 'test_mode',
            'label' => 'Test Mode',
            'type' => 'boolean',
            'default' => false,
        ],
    ];
}
```

Supported field types:
- `text`: Text input
- `password`: Password input (hidden)
- `textarea`: Multi-line text
- `boolean`: Checkbox
- `select`: Dropdown
- `number`: Numeric input

## Creating a Provisioning Module Extension

### Example: cPanel Provisioning

```php
<?php

namespace Extensions\CpanelProvisioning;

use App\Extensions\Extension;
use App\Extensions\Contracts\ProvisioningModuleInterface;
use App\Models\Service;

class Extension extends \App\Extensions\Extension implements ProvisioningModuleInterface
{
    public function getModuleId(): string
    {
        return 'cpanel';
    }

    public function createAccount(Service $service): array
    {
        // Connect to cPanel WHM API
        // Create account

        return [
            'success' => true,
            'message' => 'Account created successfully',
            'service_data' => [
                'username' => $username,
                'password' => $password,
                'domain' => $domain,
                'server_ip' => $serverIp,
            ],
        ];
    }

    public function suspendAccount(Service $service): array
    {
        // Suspend account via API

        return [
            'success' => true,
            'message' => 'Account suspended',
        ];
    }

    // Implement other required methods...
}
```

## Extension Directory Structure

Extensions are organized by category in the `extensions/` directory:

```
extensions/
├── payment-gateways/              # Payment gateway extensions
│   ├── stripe/
│   │   ├── Extension.php          # Main extension class (REQUIRED)
│   │   ├── composer.json          # Composer dependencies (optional)
│   │   ├── routes/
│   │   │   └── web.php            # Custom routes
│   │   ├── views/                 # Blade templates
│   │   ├── assets/                # Public assets (logo, etc.)
│   │   │   └── logo.png
│   │   └── README.md              # Documentation
│   ├── midtrans/
│   ├── xendit/
│   ├── duitku/
│   ├── tripay/
│   ├── paypal/
│   └── cryptomus/
│
└── provisioning-modules/          # Provisioning module extensions
    ├── pterodactyl/
    │   ├── Extension.php          # Main extension class (REQUIRED)
    │   ├── composer.json          # Composer dependencies (optional)
    │   ├── config/
    │   │   └── config.php         # Configuration file
    │   ├── database/
    │   │   └── migrations/        # Database migrations
    │   ├── routes/
    │   │   ├── web.php            # Web routes
    │   │   └── api.php            # API routes
    │   ├── views/                 # Blade templates
    │   │   └── settings.blade.php
    │   ├── assets/                # Public assets
    │   │   ├── css/
    │   │   ├── js/
    │   │   └── images/
    │   ├── lang/                  # Translations
    │   │   ├── en/
    │   │   └── id/
    │   └── README.md              # Documentation
    ├── proxmox/
    ├── virtualizor/
    ├── virtfusion/
    └── convoy/
```

### Category-Based Namespace Convention

Extensions use namespaces based on their category:

- **Payment Gateways**: `Extensions\PaymentGateways\{ExtensionName}`
- **Provisioning Modules**: `Extensions\ProvisioningModules\{ExtensionName}`

**Examples:**
- `extensions/payment-gateways/stripe/` → `Extensions\PaymentGateways\Stripe`
- `extensions/provisioning-modules/pterodactyl/` → `Extensions\ProvisioningModules\Pterodactyl`

The ExtensionManager automatically converts folder names to proper namespaces during discovery.

## Using the Extension Manager

### In Controllers

```php
use App\Extensions\Managers\ExtensionManager;

class PaymentController extends Controller
{
    public function processPayment(Request $request, ExtensionManager $extensions)
    {
        $gateway = $extensions->getPaymentGateway($request->gateway);

        if (!$gateway || !$gateway->isConfigured()) {
            return back()->with('error', 'Payment gateway not available');
        }

        $result = $gateway->processPayment($invoice, $request->all());

        if ($result['status'] === 'success') {
            // Handle success
        }
    }
}
```

### In Service Classes

```php
use App\Extensions\Managers\ExtensionManager;

class ProvisioningService
{
    public function __construct(
        protected ExtensionManager $extensions
    ) {}

    public function provisionService(Service $service)
    {
        $module = $this->extensions->getProvisioningModule(
            $service->product->provisioning_module
        );

        if (!$module) {
            throw new \Exception('Provisioning module not found');
        }

        $result = $module->createAccount($service);

        if ($result['success']) {
            $service->update($result['service_data']);
        }
    }
}
```

### In Blade Views

```blade
@php
    $extensions = app(\App\Extensions\Managers\ExtensionManager::class);
    $gateways = $extensions->getPaymentGateways();
@endphp

<select name="gateway">
    @foreach($gateways as $gateway)
        <option value="{{ $gateway->getGatewayId() }}">
            {{ $gateway->getDisplayName() }}
        </option>
    @endforeach
</select>
```

## Extension Configuration Storage

Extensions store configuration in the `settings` table:

```php
// In your extension
public function boot(): void
{
    // Load configuration
    $apiKey = $this->config['api_key'] ?? null;

    // Update configuration
    $this->setConfig([
        'api_key' => 'new-key',
        'api_secret' => 'new-secret',
    ]);
}
```

Configuration is automatically encrypted if the field is marked as `password` type.

## Extension Lifecycle

1. **Discovery**: `ExtensionManager::discoverExtensions()`
   - Scans extension categories (`payment-gateways/`, `provisioning-modules/`)
   - Discovers subdirectories in each category
   - Finds `Extension.php` files in each extension
   - Validates class structure
   - Converts folder names to proper namespaces

2. **Loading**: `loadExtension()`
   - Requires Extension.php file
   - Builds category-based namespace (`Extensions\PaymentGateways\Stripe`)
   - Instantiates extension class
   - Verifies interface implementation (PaymentGatewayInterface or ProvisioningModuleInterface)
   - Checks enabled status

3. **Registration**: `Extension::register()`
   - Register services
   - Bind classes to container
   - Register event listeners

4. **Booting**: `Extension::boot()`
   - Load routes
   - Register views
   - Execute startup code

5. **Runtime**: Extension is active and functional

## Extension Installation

### Automatic Installation

1. Upload extension to `extensions/` directory
2. Extension is auto-discovered on next request
3. If disabled, enable via admin panel

### Manual Installation

```php
$extensionManager = app(ExtensionManager::class);
$extensionManager->installExtension('extension-id');
```

This will:
- Run migrations
- Seed database
- Enable extension
- Boot extension

## Extension Hooks & Events

Extensions can listen to system events:

```php
public function boot(): void
{
    // Listen to invoice paid event
    Event::listen(InvoicePaidEvent::class, function($event) {
        // Do something when invoice is paid
    });

    // Add custom menu items
    View::composer('layouts.admin', function($view) {
        $view->with('customMenuItems', [
            ['label' => 'My Extension', 'url' => route('my-extension.index')],
        ]);
    });
}
```

Available events:
- `InvoicePaidEvent`: Invoice fully paid
- `ServiceProvisionedEvent`: Service provisioned
- `UserRegisteredEvent`: New user registered
- And all standard Laravel events

## Testing Extensions

Create tests in your extension's `tests/` directory:

```php
namespace Extensions\MyExtension\Tests;

use Tests\TestCase;

class ExtensionTest extends TestCase
{
    public function test_extension_loads()
    {
        $extension = app(ExtensionManager::class)
            ->getExtension('my-extension');

        $this->assertNotNull($extension);
        $this->assertTrue($extension->isEnabled());
    }
}
```

## Security Best Practices

1. **Never hardcode secrets**: Use configuration
2. **Validate input**: Always validate and sanitize
3. **Use CSRF protection**: Include @csrf in forms
4. **Encrypt sensitive data**: Mark config fields as 'password'
5. **Use prepared statements**: Prevent SQL injection
6. **Rate limiting**: Implement for API endpoints
7. **Logging**: Log security events
8. **Keep updated**: Update dependencies regularly

## Publishing Extensions

To publish your extension for others:

1. Create GitHub repository
2. Include comprehensive README.md
3. Add installation instructions
4. Include example configuration
5. List dependencies
6. Add license file
7. Tag releases with semantic versioning
8. Submit to extension marketplace (if available)

## Troubleshooting

### Extension Not Loading

1. Check `Extension.php` exists in extension root
2. Verify namespace matches directory name
3. Check logs in `storage/logs/laravel.log`
4. Verify extension is enabled in settings

### Configuration Not Saving

1. Check database connection
2. Verify settings table exists
3. Check file permissions
4. Review error logs

### Routes Not Working

1. Verify routes file exists: `routes/web.php`
2. Check route is registered in `boot()` method
3. Clear route cache: `php artisan route:clear`
4. Check route with: `php artisan route:list`

## API Reference

### ExtensionManager Methods

```php
// Get all extensions (flattened from all categories)
$extensions = $manager->getExtensions();

// Get all extensions by category (returns associative array)
$allExtensions = $manager->getAllExtensions();
// Returns: ['payment-gateways' => [...], 'provisioning-modules' => [...]]

// Get extensions by category
$paymentGateways = $manager->getExtensionsByCategory('payment-gateways');
$provisioningModules = $manager->getExtensionsByCategory('provisioning-modules');

// Get specific extension (auto-search all categories)
$extension = $manager->getExtension('stripe');

// Get specific extension by category (faster, recommended)
$stripe = $manager->getExtension('stripe', 'payment-gateways');
$pterodactyl = $manager->getExtension('pterodactyl', 'provisioning-modules');

// Get payment gateways (quick access by interface)
$gateways = $manager->getPaymentGateways();
$gateway = $manager->getPaymentGateway('stripe');

// Get provisioning modules (quick access by interface)
$modules = $manager->getProvisioningModules();
$module = $manager->getProvisioningModule('pterodactyl');

// Install/uninstall with category
$manager->installExtension('stripe', 'payment-gateways');
$manager->uninstallExtension('pterodactyl', 'provisioning-modules');

// Install/uninstall without category (auto-search)
$manager->installExtension('stripe');  // slower, searches all categories

// Get statistics
$stats = $manager->getStatistics();
// Returns: [
//   'total_extensions' => 12,
//   'payment_gateways' => 7,
//   'provisioning_modules' => 5,
//   'by_category' => [
//     'payment-gateways' => 7,
//     'provisioning-modules' => 5
//   ]
// ]

// Reload extensions
$manager->reload();
```

### Extension Methods

```php
// Configuration
$extension->getConfig();
$extension->setConfig(['key' => 'value']);

// Status
$extension->isEnabled();
$extension->enable();
$extension->disable();

// Info
$extension->getName();
$extension->getVersion();
$extension->getAuthor();

// Lifecycle
$extension->install();
$extension->uninstall();
```

## Contributing

We welcome extension contributions! Please:

1. Follow coding standards
2. Include tests
3. Document thoroughly
4. Submit pull request
5. Respond to code review

## Support

For help with the extension system:
- Read documentation: `/extensions/README.md`
- Review examples: `/extensions/payment-gateways/stripe/` or `/extensions/provisioning-modules/pterodactyl/`
- Check logs: `storage/logs/laravel.log`
- Submit issues: GitHub Issues

## License

The extension system is part of HBM Billing and follows the same license.
