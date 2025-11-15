# Multi-Language System Documentation

## Overview

The HBM Billing System includes a comprehensive multi-language (i18n) system that allows you to:
- Translate the entire application to any language
- Allow users to select their preferred language
- Store language preferences per user
- Easily add new languages

## Supported Languages

By default, the system supports:
- **English (en)** - Default language
- **Indonesian (id)** - Indonesian Bahasa

## Translation Files Structure

All translation files are located in the `lang/` directory:

```
lang/
├── en/
│   ├── common.php      # Common words, navigation, actions
│   ├── admin.php       # Admin panel translations
│   ├── client.php      # Client portal translations
│   ├── auth.php        # Authentication pages
│   └── emails.php      # Email templates
└── id/
    ├── common.php
    ├── admin.php
    ├── client.php
    ├── auth.php
    └── emails.php
```

## How to Add a New Language

### Step 1: Add Language Code to Config

Edit `config/app.php` and add your language code to `supported_locales`:

```php
'supported_locales' => ['en', 'id', 'es', 'fr'], // Added Spanish and French
```

### Step 2: Create Translation Files

Create a new folder in `lang/` with your language code:

```bash
mkdir lang/es  # For Spanish
```

Copy all files from `lang/en/` to your new language folder:

```bash
cp lang/en/* lang/es/
```

### Step 3: Translate the Content

Open each `.php` file in your new language folder and translate the values:

```php
// lang/es/common.php
return [
    'dashboard' => 'Panel de Control',  // Translated from 'Dashboard'
    'services' => 'Servicios',           // Translated from 'Services'
    // ... more translations
];
```

### Step 4: Update Language Switcher Component

Edit `resources/views/components/language-switcher.blade.php` to add your language name:

```blade
@if($locale === 'en')
    English
@elseif($locale === 'id')
    Indonesia
@elseif($locale === 'es')
    Español
@elseif($locale === 'fr')
    Français
@else
    {{ strtoupper($locale) }}
@endif
```

## Using Translations in Views

### Basic Usage

```blade
{{ __('common.dashboard') }}
<!-- Output: Dashboard (or translated equivalent) -->
```

### With Parameters

```blade
{{ __('emails.welcome.signature', ['company' => 'My Company']) }}
<!-- Output: The My Company Team -->
```

### Nested Arrays

```blade
{{ __('admin.dashboard.title') }}
<!-- Output: Admin Dashboard -->
```

### Choice (Pluralization)

```blade
{{ trans_choice('messages.apples', 10) }}
```

## Language Switching

### For Users

Users can switch languages in two ways:

1. **Language Switcher Component**: Include in your layout:
```blade
<x-language-switcher />
```

2. **Direct URL**: Visit `/language/{locale}` (e.g., `/language/id`)

### Programmatically

```php
// In controller or middleware
App::setLocale('id');

// Get current locale
$currentLocale = App::getLocale();
```

## Language Priority Order

The system determines language in this priority:

1. **URL Parameter**: `?lang=id`
2. **User Preference**: Authenticated user's saved language
3. **Session**: Stored in session after manual switch
4. **Default**: Application default (English)

## Database Fields

### Users Table

Users can save their language preference:

```php
$user->language = 'id';
$user->save();
```

Migration: `2024_01_23_000000_add_language_to_users_table.php`

### Settings Table

System default language can be set in settings:

```php
Setting::set('default_language', 'id');
```

## Middleware

The `SetLocale` middleware automatically sets the locale for each request.

Registered in: `bootstrap/app.php`

```php
\App\Http\Middleware\SetLocale::class
```

## Translation File Sections

### common.php
- Navigation items
- Common actions (create, edit, delete, etc.)
- Status labels
- Common words (name, email, phone, etc.)
- Messages and alerts
- Pagination
- Time periods
- Billing cycles

### admin.php
- Dashboard
- Client management
- Product management
- Service management
- Invoice management
- Payment management
- Support tickets
- Reports & analytics
- Activity logs
- Settings
- Announcements
- Knowledge base

### client.php
- Client dashboard
- Services
- Invoices
- Support tickets
- Profile
- Payment history
- Product ordering
- Announcements
- Knowledge base

### auth.php
- Login
- Registration
- Password reset
- Email verification
- Two-factor authentication
- Logout messages

### emails.php
- Welcome emails
- Invoice emails
- Payment confirmations
- Service notifications
- Support ticket updates
- Password reset
- Payment reminders
- Account updates
- Maintenance notifications

## Best Practices

1. **Always use translation keys**: Never hardcode text in views
   ```blade
   <!-- Bad -->
   <h1>Dashboard</h1>

   <!-- Good -->
   <h1>{{ __('common.dashboard') }}</h1>
   ```

2. **Organize translations logically**: Group related translations together

3. **Use descriptive keys**: Make translation keys self-explanatory
   ```php
   'invoice_payment_received' => 'Invoice Payment Received'
   ```

4. **Keep translation files in sync**: When adding new features, update all language files

5. **Use parameters for dynamic content**:
   ```blade
   {{ __('emails.welcome.greeting', ['name' => $user->name]) }}
   ```

## Testing Translations

### Manual Testing
1. Switch language using the language switcher
2. Navigate through the application
3. Check all pages for untranslated text

### Automated Testing
```php
// In tests
App::setLocale('id');
$response = $this->get('/dashboard');
$response->assertSee('Dasbor'); // Indonesian for Dashboard
```

## Adding Language Switcher to Layouts

### Admin Layout
Add to `resources/views/layouts/app-admin.blade.php`:
```blade
<nav class="navbar">
    <!-- ... other nav items ... -->
    <x-language-switcher />
</nav>
```

### Client Layout
Add to `resources/views/layouts/app-client.blade.php`:
```blade
<header>
    <!-- ... other header items ... -->
    <x-language-switcher />
</header>
```

## Troubleshooting

### Translations Not Showing

1. **Clear cache**:
   ```bash
   php artisan cache:clear
   php artisan config:clear
   php artisan view:clear
   ```

2. **Check locale is set correctly**:
   ```php
   dd(App::getLocale()); // Should output 'en', 'id', etc.
   ```

3. **Verify translation file exists**:
   - Check `lang/{locale}/` folder exists
   - Check translation key exists in file

### Language Not Persisting

1. **Check middleware is registered**: Verify `SetLocale` is in `bootstrap/app.php`
2. **Check session is working**: Verify session driver is configured correctly
3. **Check user table has language column**: Run migration if missing

## Contributing Translations

We welcome translation contributions! To contribute:

1. Fork the repository
2. Add your language translations
3. Test thoroughly
4. Submit a pull request

## License

The translation system is part of the HBM Billing System and follows the same license.
