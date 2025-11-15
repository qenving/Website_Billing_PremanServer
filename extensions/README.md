# Extensions Directory

This directory contains all third-party extensions for the HBM Billing System.

## What are Extensions?

Extensions allow you to add custom functionality to the billing system without modifying the core code. Common extensions include:

- **Payment Gateways**: Accept payments via different providers (Stripe, PayPal, etc.)
- **Provisioning Modules**: Automate server provisioning (cPanel, Plesk, Pterodactyl, etc.)
- **Custom Features**: Add any custom functionality you need

## Extension Structure

Each extension should follow this structure:

```
extensions/
└── your-extension/
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
    ├── lang/                  # Translation files
    └── README.md              # Extension documentation
```

## Creating an Extension

### Step 1: Create Extension Directory

```bash
mkdir -p extensions/my-extension
```

### Step 2: Create Extension.php

Create `extensions/my-extension/Extension.php`:

```php
<?php

namespace Extensions\MyExtension;

use App\Extensions\Extension as BaseExtension;

class Extension extends BaseExtension
{
    public function getName(): string
    {
        return 'My Extension';
    }

    public function getDescription(): string
    {
        return 'Description of what my extension does';
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
        return 'my-extension';
    }

    public function register(): void
    {
        // Register any services, bindings, etc.
    }

    public function boot(): void
    {
        // Bootstrap extension code
    }
}
```

### Step 3: Implement Interface (if needed)

For payment gateways, implement `PaymentGatewayInterface`:

```php
use App\Extensions\Contracts\PaymentGatewayInterface;

class Extension extends BaseExtension implements PaymentGatewayInterface
{
    // Implement required methods
}
```

For provisioning modules, implement `ProvisioningModuleInterface`:

```php
use App\Extensions\Contracts\ProvisioningModuleInterface;

class Extension extends BaseExtension implements ProvisioningModuleInterface
{
    // Implement required methods
}
```

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

// Get all extensions
$extensions = $extensionManager->getExtensions();

// Get specific extension
$extension = $extensionManager->getExtension('my-extension');

// Get all payment gateways
$gateways = $extensionManager->getPaymentGateways();

// Get specific gateway
$stripe = $extensionManager->getPaymentGateway('stripe');

// Install/uninstall extension
$extensionManager->installExtension('my-extension');
$extensionManager->uninstallExtension('my-extension');
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

## Example Extensions

This directory includes example extensions:
- `stripe-gateway`: Stripe payment gateway implementation

Study these examples to understand how to build your own extensions.
