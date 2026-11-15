# nextSyntaxTech DevZone

[![Latest Version](https://img.shields.io/badge/version-1.0.0-blue.svg)](https://github.com/nextsyntaxtech/devzone)
[![PHP Version](https://img.shields.io/badge/php-%5E8.2-777bb4.svg)](https://php.net)
[![Laravel Support](https://img.shields.io/badge/laravel-10.x%20%7C%2011.x%20%7C%2012.x-red.svg)](https://laravel.com)
[![License: MIT](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)

**nextSyntaxTech DevZone** is a standalone Developer Utility & Maintenance Control Panel for Laravel applications. It provides a secure, independent interface for database migrations, seeders, safe Artisan operations, backups, and live API documentation without touching application code.

---

## 🚀 Key Features

- **⚡ Standalone Developer Interface:** Dedicated URL (e.g. `/developer-panel`), completely isolated from application routes and layouts.
- **📦 Zero-CDN & Offline Ready:** All Bootstrap 5 and Boxicons assets are bundled directly within the package — zero external CDN requests.
- **🔔 Blade Alert Component (`<x-devzone::alert>`):** Sleek, customizable Blade alert component for automatic session flash messages and custom notices.
- **💬 Native Interactive Dialogs:** Built-in Bootstrap 5 confirmation modals, loading indicators, and toasts — 100% replacing SweetAlert2.
- **🛡️ Master Key Authentication:** Secure passkey login with rate limiting, inactivity timeouts, and zero-trust authentication.
- **🔑 In-App Credentials Management:** Authenticated developers can change the master access passkey and admin email after verifying their current passkey directly from the UI with zero application downtime.
- **💓 Heartbeat & Telemetry Monitor:** Real-time cardiac pulse telemetry animation and status monitoring across login and header.
- **📊 4 Header KPI Cards:** Real-time metrics for PHP/Laravel runtime, database engine, database info, and migration statuses.
- **🔄 Dynamic API Documentation:** Auto-detects and generates Swagger (OpenAPI 3.0) and Knuckles Scribe documentation.
- **🗂️ Migration & Seeder Manager:** Inspect and execute individual or all pending migrations and seeders safely on demand.
- **⚙️ Safe Artisan Operations:** One-click execution of whitelisted cache clearing and optimization commands with live console modal.
- **🐞 Safe Debugging Toggle:** Switch `APP_DEBUG` safely between enabled and disabled with instant feedback.
- **💾 Database Backup & Download:** Generate and securely download full SQL database dumps stored outside the public directory.
- **📝 Audit Activity Logs:** Full logging of administrative actions, timestamps, and IP addresses with search and filtering.
- **🌓 Light & Dark Theme:** Clean, soft-slate light mode and high-contrast dark mode with automatic state persistence.

---

## 📋 Requirements

- **PHP:** `^8.2`
- **Laravel Framework:** `^10.0 || ^11.0 || ^12.0`

---

## 🛠️ Installation & Updates

Add the GitHub repository to your project's `composer.json`:

```json
"repositories": [
    {
        "type": "vcs",
        "url": "https://github.com/bsamit/nextsyntaxtech-devzone.git"
    }
]
```

Require the package via Composer:

```bash
composer require nextsyntaxtech/devzone:dev-main
```

### Update Package
To update DevZone to the latest version at any time:

```bash
composer update nextsyntaxtech/devzone
```

---

## ⚡ Quick Start & Configuration

### 1. Interactive Installer Command
Run the installer to publish configuration and configure access:

```bash
php artisan devzone:install
```

### 2. Configuration Settings
You can manage all settings directly in `config/devzone.php` or via `.env`:

```php
// config/devzone.php
return [
    'enabled'         => env('DEVZONE_ENABLED', true),
    'path'            => env('DEVZONE_PATH', 'developer-panel'),
    'passkey'         => env('DEVZONE_KEY', null),
    'admin_email'     => env('DEVZONE_EMAIL', null),
    'setup_completed' => env('DEVZONE_SETUP_COMPLETED', false),
];
```

### 3. Access the Panel
Navigate to your application's developer panel URL:

```text
http://your-app.test/developer-panel
```

---

## 🔔 Blade Alert Component

DevZone includes a native Blade alert component:

```blade
{{-- Automatic Session Flash Messages (success, error, warning, info, validation errors) --}}
<x-devzone::alert session />

{{-- Custom Standalone Alerts --}}
<x-devzone::alert type="success" title="Success!" message="Operation completed successfully." />
<x-devzone::alert type="danger" :dismissible="true">An unexpected error occurred.</x-devzone::alert>
```

---

## 💬 Dialog & Toast System (Pure Bootstrap 5)

DevZone replaces SweetAlert2 with pure Bootstrap 5 modals and toasts:

```javascript
// Confirmation Modal
DevZoneAlert.confirm('Run Migration?', 'Apply pending schema changes?').then(res => {
    if (res.isConfirmed) { /* execute */ }
});

// Toast Notification
DevZoneAlert.toast({ icon: 'success', title: 'Saved successfully!' });

// Loading State
DevZoneAlert.loading('Processing...', 'Please wait...');
DevZoneAlert.close();
```

---

## 🔒 Security Best Practices

1. **Passkey Protection:** Use a strong passkey in production environments.
2. **Environment Toggle:** Disable the panel completely in production anytime by setting `'enabled' => false`.
3. **Session Timeout:** Sessions expire automatically after idle timeout (default: 60 minutes).
4. **No Destructive Commands:** Destructive operations like `migrate:fresh` or raw shell executions are strictly excluded.

---

## 📄 License

NextSyntaxTech DevZone is open-sourced software licensed under the [MIT license](LICENSE).

---

## 👨‍💻 Author & Support

- **Author:** NextSyntaxTech ([amitsaha.cse@gmail.com](mailto:amitsaha.cse@gmail.com))
- **Website:** [https://nextsyntaxtech.com](https://nextsyntaxtech.com)
