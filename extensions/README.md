# HBM Billing Extensions

This directory contains all extensions for the HBM Billing System, organized by category.

## Directory Structure

Extensions are organized into categories:

```
extensions/
├── payment-gateways/          # Payment gateway integrations
│   ├── stripe/
│   ├── midtrans/
│   ├── xendit/
│   ├── duitku/
│   ├── tripay/
│   ├── paypal/
│   └── cryptomus/
│
└── provisioning-modules/      # Server/service provisioning modules
    ├── pterodactyl/
    ├── proxmox/
    ├── virtualizor/
    ├── virtfusion/
    └── convoy/
```

## What are Extensions?

Extensions allow you to add custom functionality to the billing system without modifying the core code:

- **Payment Gateways**: Accept payments via different providers (Stripe, PayPal, Midtrans, etc.)
- **Provisioning Modules**: Automate server provisioning (Pterodactyl, Proxmox, Virtualizor, etc.)

## Available Extensions

### Payment Gateways

- **Stripe** - International credit card payments
- **Midtrans** - Indonesian payment gateway
- **Xendit** - Indonesian payment gateway
- **Duitku** - Indonesian payment gateway
- **Tripay** - Indonesian payment gateway
- **PayPal** - International PayPal payments
- **Cryptomus** - Cryptocurrency payments

### Provisioning Modules

- **Pterodactyl** - Game server panel integration
- **Proxmox** - Proxmox VE VPS provisioning
- **Virtualizor** - VPS provisioning platform
- **VirtFusion** - VPS management platform
- **Convoy** - Modern VPS control panel

## Extension Structure

Each extension should follow this structure:

```
category/extension-name/
├── Extension.php          # Main extension class (required)
├── config/
│   └── config.php         # Extension configuration
├── database/
│   └── migrations/        # Database migrations
├── routes/
│   ├── web.php            # Web routes
│   └── api.php            # API routes
├── views/                 # Blade views
├── assets/                # CSS, JS, images
│   └── logo.png
├── lang/                  # Translation files
└── README.md              # Extension documentation
```

## Creating an Extension

### Step 1: Choose Category and Create Directory

**For Payment Gateway:**
```bash
mkdir -p extensions/payment-gateways/my-gateway
```

**For Provisioning Module:**
```bash
mkdir -p extensions/provisioning-modules/my-module
```

### Step 2: Create Extension.php

**Payment Gateway Example** (`payment-gateways/my-gateway/Extension.php`):

```php
<?php

namespace Extensions\PaymentGateways\MyGateway;

use App\Extensions\Extension;
use App\Extensions\Contracts\PaymentGatewayInterface;
use App\Models\Invoice;
use App\Models\Payment;

class Extension extends \App\Extensions\Extension implements PaymentGatewayInterface
{
    public function getName(): string
    {
        return 'My Payment Gateway';
    }

    public function getDescription(): string
    {
        return 'Accept payments via My Gateway';
    }

    public function getVersion(): string
    {
        return '1.0.0';
    }

    public function getAuthor(): string
    {
        return 'Your Name';
    }

    public function getExtensionId(): string
    {
        return 'my-gateway';
    }

    public function getGatewayId(): string
    {
        return 'my-gateway';
    }

    public function processPayment(Invoice $invoice, array $paymentData): array
    {
        // Implement payment processing
    }

    public function handleWebhook(array $data): ?Payment
    {
        // Implement webhook handling
    }

    public function refund(Payment $payment, float $amount): array
    {
        // Implement refund
    }

    // ... other required methods
}
```

**Provisioning Module Example** (`provisioning-modules/my-module/Extension.php`):

```php
<?php

namespace Extensions\ProvisioningModules\MyModule;

use App\Extensions\Extension;
use App\Extensions\Contracts\ProvisioningModuleInterface;
use App\Models\Service;

class Extension extends \App\Extensions\Extension implements ProvisioningModuleInterface
{
    public function getName(): string
    {
        return 'My Provisioning Module';
    }

    public function getDescription(): string
    {
        return 'Automate provisioning with My System';
    }

    public function getVersion(): string
    {
        return '1.0.0';
    }

    public function getAuthor(): string
    {
        return 'Your Name';
    }

    public function getExtensionId(): string
    {
        return 'my-module';
    }

    public function getModuleId(): string
    {
        return 'my-module';
    }

    public function createAccount(Service $service, array $params): array
    {
        // Implement account creation
    }

    public function suspendAccount(Service $service): array
    {
        // Implement suspension
    }

    public function terminateAccount(Service $service): array
    {
        // Implement termination
    }

    // ... other required methods
}
```

### Step 3: Namespace Convention

Extensions use a category-based namespace:

- **Payment Gateways**: `Extensions\PaymentGateways\{ExtensionName}`
- **Provisioning Modules**: `Extensions\ProvisioningModules\{ExtensionName}`

**Examples:**
- `extensions/payment-gateways/stripe/` → `Extensions\PaymentGateways\Stripe`
- `extensions/provisioning-modules/pterodactyl/` → `Extensions\ProvisioningModules\Pterodactyl`

### Step 4: Add Extension Configuration

Extensions can store configuration in the database:

```php
// In your extension
$this->setConfig([
    'api_key' => 'your-key',
    'api_secret' => 'your-secret',
]);

// Retrieve configuration
$apiKey = $this->config['api_key'];
```

### Step 5: Add Routes (Optional)

Create `routes/web.php`:

```php
<?php

use Illuminate\Support\Facades\Route;

Route::prefix('my-extension')->group(function () {
    Route::get('/callback', function () {
        // Handle callback
    });
});
```

### Step 6: Add Views (Optional)

Create views in the `views/` directory and load them:

```php
// In your extension class
protected function showSettings()
{
    return $this->view('settings', ['config' => $this->config]);
}
```

### Step 7: Add Migrations (Optional)

Create migrations in `database/migrations/`:

```php
// migrations/2024_01_01_000000_create_myextension_table.php
```

Migrations will be run automatically when the extension is installed.

## Available Extension Types

### 1. Payment Gateway Extension

Implement `PaymentGatewayInterface` to add a new payment method.

Required methods:
- `getGatewayId()`: Unique gateway identifier
- `getDisplayName()`: Name shown to users
- `processPayment()`: Process a payment
- `handleWebhook()`: Handle payment notifications
- `refund()`: Process refunds
- `getSupportedMethods()`: Payment methods supported
- `isConfigured()`: Check if gateway is properly configured
- `getFees()`: Get gateway fees

See `extensions/stripe-gateway/Extension.php` for a complete example.

### 2. Provisioning Module Extension

Implement `ProvisioningModuleInterface` to add server provisioning.

Required methods:
- `getModuleId()`: Unique module identifier
- `createAccount()`: Create/provision new service
- `suspendAccount()`: Suspend service
- `unsuspendAccount()`: Reactivate service
- `terminateAccount()`: Delete service
- `changePassword()`: Change service password
- `changePackage()`: Upgrade/downgrade service
- `getUsageStats()`: Get usage statistics
- `testConnection()`: Test server connection

### 3. Custom Feature Extension

Extend `Extension` class for any custom functionality.

You can:
- Register custom routes
- Add custom views
- Hook into system events
- Add custom console commands
- Modify application behavior

## Extension Manager

Access the extension manager in your code:

```php
use App\Extensions\Managers\ExtensionManager;

$extensionManager = app(ExtensionManager::class);

// Get all extensions (all categories)
$extensions = $extensionManager->getExtensions();

// Get extensions by category
$paymentGateways = $extensionManager->getExtensionsByCategory('payment-gateways');
$provisioningModules = $extensionManager->getExtensionsByCategory('provisioning-modules');

// Get specific extension (auto-search all categories)
$extension = $extensionManager->getExtension('stripe');

// Get specific extension by category (faster)
$stripe = $extensionManager->getExtension('stripe', 'payment-gateways');

// Get all payment gateways (quick access)
$gateways = $extensionManager->getPaymentGateways();

// Get specific payment gateway
$stripe = $extensionManager->getPaymentGateway('stripe');

// Get all provisioning modules (quick access)
$modules = $extensionManager->getProvisioningModules();

// Get specific provisioning module
$pterodactyl = $extensionManager->getProvisioningModule('pterodactyl');

// Install/uninstall extension
$extensionManager->installExtension('stripe', 'payment-gateways');
$extensionManager->uninstallExtension('pterodactyl', 'provisioning-modules');

// Get statistics
$stats = $extensionManager->getStatistics();
// Returns: ['total_extensions' => 12, 'payment_gateways' => 7, 'provisioning_modules' => 5]
```

## Extension Lifecycle

1. **Discovery**: Extensions are automatically discovered from the `extensions/` directory
2. **Loading**: Extension.php is loaded and validated
3. **Register**: `register()` method is called
4. **Boot**: `boot()` method is called
5. **Active**: Extension is active and functional

## Best Practices

1. **Namespace**: Use `Extensions\YourExtension` namespace
2. **Extension ID**: Use lowercase with hyphens (e.g., `my-extension`)
3. **Versioning**: Follow semantic versioning (e.g., `1.0.0`)
4. **Error Handling**: Always catch and log exceptions
5. **Configuration**: Store sensitive data encrypted
6. **Documentation**: Include README.md with installation instructions
7. **Testing**: Test your extension thoroughly before deployment
8. **Dependencies**: List any Composer dependencies in documentation

## Security

- Never hardcode API keys or secrets
- Validate all input data
- Use Laravel's built-in security features
- Keep your extension updated
- Follow Laravel coding standards

## Support

For questions or issues with the extension system:
- Check the main documentation: `/EXTENSION_SYSTEM.md`
- Review example extensions in this directory
- Contact support or submit an issue

## Included Extensions

This directory includes the following extensions:

### Payment Gateways (Placeholder Implementations)
- `payment-gateways/stripe/` - Stripe payment gateway
- `payment-gateways/midtrans/` - Midtrans payment gateway
- `payment-gateways/xendit/` - Xendit payment gateway
- `payment-gateways/duitku/` - Duitku payment gateway
- `payment-gateways/tripay/` - Tripay payment gateway
- `payment-gateways/paypal/` - PayPal payment gateway
- `payment-gateways/cryptomus/` - Cryptomus cryptocurrency gateway

### Provisioning Modules (Placeholder Implementations)
- `provisioning-modules/pterodactyl/` - Pterodactyl game panel
- `provisioning-modules/proxmox/` - Proxmox VE
- `provisioning-modules/virtualizor/` - Virtualizor
- `provisioning-modules/virtfusion/` - VirtFusion
- `provisioning-modules/convoy/` - Convoy panel

**Note:** All extensions are currently placeholder implementations with TODO comments. They need full API integration before use in production.

Study the Stripe extension as the most complete example to understand how to build your own extensions.
