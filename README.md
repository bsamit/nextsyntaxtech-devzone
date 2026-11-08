# NextSyntaxTech DevZone

[![Latest Version](https://img.shields.io/badge/version-1.0.0-blue.svg)](https://github.com/nextsyntaxtech/devzone)
[![PHP Version](https://img.shields.io/badge/php-%5E8.2-777bb4.svg)](https://php.net)
[![Laravel Support](https://img.shields.io/badge/laravel-10.x%20%7C%2011.x%20%7C%2012.x-red.svg)](https://laravel.com)
[![License: MIT](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)

**NextSyntaxTech DevZone** is an enterprise-grade, independent Developer Utility & Maintenance Control Panel designed specifically for Laravel applications. It provides a secure, standalone portal to monitor environment health, execute safe Artisan commands, trigger migrations and seeders, manage database backups, compile dynamic API documentation (Swagger & Scribe), and audit administrative actions—without depending on any application sidebar or admin layout.

---

## 🚀 Key Features

- **⚡ Independent Standalone Interface:** Accessible through a dedicated URL (e.g., `/developer-panel`), completely isolated from application routes and admin layouts.
- **🛡️ Master Key Authentication & Session Security:** Requires an authorized master access key (passkey/token) on every session. Features brute-force rate-limiting, inactivity timeouts, and anti-caching security headers.
- **🪄 First-Time Onboarding Setup Wizard:** Automatically prompts for initial master passkey and admin email configuration, writing credentials safely to `.env`.
- **📊 4 Focused Header KPI Cards:**
  1. *Framework & Runtime:* Active PHP version, Laravel framework version, and environment status.
  2. *Database Engine & Size:* Engine detection (MariaDB, MySQL, PostgreSQL, SQLite), server version, and total database size in MB.
  3. *Database Info:* Active database name, total table count, and host/port.
  4. *Migrations Status:* Ran vs. pending count with action-required indicators.
- **🔄 Dynamic API Documentation Manager:** Automatically detects installed API documentation generators:
  - **Knuckles Scribe:** `php artisan scribe:generate` (`/docs`)
  - **L5-Swagger / OpenAPI 3.0:** `php artisan l5-swagger:generate` (`/api/documentation`)
  - Supports side-by-side generators if both packages are installed.
- **🗂️ Laravel Migration Manager:** Lists all application and module migration files. Shows status (Ran / Pending) and batch numbers. Allows running single migrations or executing all pending migrations without destructive resets.
- **🌱 Seeder Manager:** Discovers all seeder classes and permits targeted execution on demand.
- **⚙️ Safe Artisan Execution Center:** Strictly whitelisted execution of safe cache clearing and optimization commands (`optimize:clear`, `config:clear`, `route:clear`, `cache:clear`, `view:clear`, `event:clear`, `storage:link`). Displays complete console outputs in a terminal modal.
- **🐞 Safe Debugging Toggle:** Toggle `APP_DEBUG` directly between `true` and `false` from the panel.
- **💾 Database Backup & Secure Download:** Generates full SQL dumps via `mysqldump` or PDO fallback. Stores backups securely in `storage/app/backup/` with authorized download and deletion.
- **📝 Audit Trail & Activity Logs:** Detailed logging of logins, command executions, backups, and migrations with timestamps (e.g. `20-Oct-2026`) and developer IP tracking.
- **🌓 Adaptive Light & Dark Theme:** Clean, soft-slate light mode and high-contrast dark mode with automatic `localStorage` persistence.

---

## 📋 Requirements

- **PHP:** `^8.2`
- **Laravel Framework:** `^10.0 || ^11.0 || ^12.0`

---

## 📦 How to Publish This Package to GitHub

Follow these steps to upload `nextsyntaxtech-devzone` to your GitHub account:

### Step 1: Initialize Git Repository in the Package Directory
Open your terminal in the `nextsyntaxtech-devzone` folder:
```bash
cd nextsyntaxtech-devzone
git init
```

### Step 2: Stage and Commit the Files
```bash
git add .
git commit -m "feat: initial release of nextsyntaxtech-devzone v1.0.0"
```

### Step 3: Create a Repository on GitHub
1. Log into your GitHub account.
2. Click **New Repository**.
3. Name the repository: `devzone` (or `nextsyntaxtech-devzone`).
4. Set it to **Public** (or **Private**). Do not check "Add a README" since one already exists.

### Step 4: Link and Push to GitHub
```bash
git remote add origin https://github.com/YOUR_GITHUB_USERNAME/devzone.git
git branch -M main
git push -u origin main
```

### Step 5: Tag a Release Version (Optional but Recommended)
```bash
git tag v1.0.0
git push origin v1.0.0
```

---

## 🛠️ How to Install in Any Laravel Project

You can install `nextsyntaxtech/devzone` in any Laravel 10, 11, or 12 project using any of the methods below:

### Method 1: Install from GitHub Repository (VCS)
Add the GitHub repository to the target project's `composer.json`:

```json
"repositories": [
    {
        "type": "vcs",
        "url": "https://github.com/YOUR_GITHUB_USERNAME/devzone.git"
    }
]
```

Then run:
```bash
composer require nextsyntaxtech/devzone:dev-main
```
*(Or specify the tagged version: `composer require nextsyntaxtech/devzone:^1.0`)*

---

### Method 2: Install via Local Path Repository (For Local Development)
If you want to use the local folder directly in your Laravel application:

In the main Laravel project's `composer.json`:
```json
"repositories": [
    {
        "type": "path",
        "url": "./nextsyntaxtech-devzone",
        "options": {
            "symlink": true
        }
    }
]
```

Then run:
```bash
composer require nextsyntaxtech/devzone:@dev
```

---

### Method 3: Install via Packagist (Public Package)
If you submit the package to [Packagist.org](https://packagist.org):
```bash
composer require nextsyntaxtech/devzone
```

---

## ⚡ Quick Start & Configuration

### Option A: Interactive Artisan Installer (Recommended)
Once required via Composer, run the interactive installer:

```bash
php artisan devzone:install
```

The installer will:
1. Publish the configuration file to `config/devzone.php`.
2. Prompt for Administrator Email, Master Passkey, and Access Path.
3. Automatically configure and write values to your `.env` file.
4. Optionally publish Blade views for custom branding.

---

### Option B: Manual Configuration
1. **Publish Configuration:**
   ```bash
   php artisan vendor:publish --tag=devzone-config
   ```

2. **Add Environment Variables to `.env`:**
   ```env
   # Enable or disable the developer panel
   DEVZONE_ENABLED=true

   # Dedicated URL access path (e.g. /developer-panel)
   DEVZONE_PATH=developer-panel

   # Master Developer Access Passkey / Token
   DEVZONE_KEY=amitsaha.cse@gmail.com

   # Authorized Administrator Email
   DEVZONE_EMAIL=amitsaha.cse@gmail.com

   # Mark setup as completed (set to false to trigger web setup wizard)
   DEVZONE_SETUP_COMPLETED=true
   ```

3. **Access the Panel:**
   Open your browser and navigate to:
   ```
   http://your-app.test/developer-panel
   ```
   Enter your configured master passkey to unlock the panel.

---

## 🎨 Customizing Blade Views

If you wish to customize the views, theme colors, or layout, publish the Blade views:

```bash
php artisan vendor:publish --tag=devzone-views
```

The views will be placed in `resources/views/vendor/devzone/`:
- `resources/views/vendor/devzone/layout.blade.php`
- `resources/views/vendor/devzone/index.blade.php`
- `resources/views/vendor/devzone/login.blade.php`
- `resources/views/vendor/devzone/setup.blade.php`

---

## 🔒 Security Best Practices

1. **Keep Credentials Secret:** Ensure `DEVZONE_KEY` is a strong passkey and never committed to source control.
2. **Environment Isolation:** On production environments, you can disable the panel anytime by setting `DEVZONE_ENABLED=false` in `.env`.
3. **Session Inactivity:** Sessions expire automatically after idle timeout (default: 60 minutes, configurable via `config/devzone.php`).
4. **Brute-Force Rate Limiting:** Consecutive failed attempts trigger a temporary IP lockout to protect against unauthorized access.
5. **No Destructive Operations:** DevZone does not offer database wipe, fresh resets, or raw shell access.

---

## 📄 License

NextSyntaxTech DevZone is open-sourced software licensed under the [MIT license](LICENSE).

---

## 👨‍💻 Author & Support

- **Created by:** NextSyntaxTech ([amitsaha.cse@gmail.com](mailto:amitsaha.cse@gmail.com))
- **Website:** [https://nextsyntaxtech.com](https://nextsyntaxtech.com)
