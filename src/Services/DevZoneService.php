<?php

namespace NextSyntaxTech\DevZone\Services;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Carbon\Carbon;

class DevZoneService
{
    public const VERSION = '1.0.2';
    public const AUTHOR = 'Amit Saha';
    public const AUTHOR_EMAIL = 'nextsyntaxtech@gmail.com';
    public const GITHUB_REPO = 'bsamit/nextsyntaxtech-devzone';

    /**
     * Get system health and environment statistics.
     */
    public function getSystemInfo(): array
    {
        $dbConnection = config('database.default');
        $dbConfig = config("database.connections.{$dbConnection}", []);
        $rawDbName = $dbConfig['database'] ?? 'N/A';
        $dbName = $rawDbName;
        $dbHost = $dbConfig['host'] ?? '127.0.0.1';
        $dbPort = $dbConfig['port'] ?? ($dbConnection === 'pgsql' ? 5432 : 3306);
        $dbCharset = $dbConfig['charset'] ?? 'utf8mb4';

        // Clean database name for display (strip long local filesystem paths for SQLite)
        $dbDisplayName = $rawDbName;
        if ($dbConnection === 'sqlite' || stripos($rawDbName, '.sqlite') !== false) {
            $dbDisplayName = basename($rawDbName) ?: 'database.sqlite';
        }

        // Detect Database Engine and Server Version (MariaDB, MySQL, PostgreSQL, SQLite, etc.)
        $dbVersion = 'N/A';
        $dbEngine = 'Database';
        $dbShortVersion = '';

        try {
            $pdo = DB::connection()->getPdo();
            $serverVersion = (string) $pdo->getAttribute(\PDO::ATTR_SERVER_VERSION);
            $dbVersion = $serverVersion;
            $driverName = DB::connection()->getDriverName();

            $isMaria = stripos($serverVersion, 'MariaDB') !== false;
            $isPostgres = $driverName === 'pgsql';
            $isMysql = $driverName === 'mysql' && !$isMaria;
            $isSqlite = $driverName === 'sqlite';
            $isSqlsrv = $driverName === 'sqlsrv';

            if ($isMaria) {
                $dbEngine = 'MariaDB';
                if (preg_match('/(\d+\.\d+(\.\d+)?)/', $serverVersion, $m)) {
                    $dbShortVersion = 'v' . $m[1];
                }
            } elseif ($isMysql) {
                $dbEngine = 'MySQL';
                if (preg_match('/(\d+\.\d+(\.\d+)?)/', $serverVersion, $m)) {
                    $dbShortVersion = 'v' . $m[1];
                }
            } elseif ($isPostgres) {
                $dbEngine = 'PostgreSQL';
                if (preg_match('/(\d+\.\d+(\.\d+)?)/', $serverVersion, $m)) {
                    $dbShortVersion = 'v' . $m[1];
                }
            } elseif ($isSqlite) {
                $dbEngine = 'SQLite';
                $dbShortVersion = 'v' . $serverVersion;
            } elseif ($isSqlsrv) {
                $dbEngine = 'SQL Server';
                $dbShortVersion = $serverVersion;
            } else {
                $dbEngine = ucfirst($driverName);
                $dbShortVersion = $serverVersion;
            }
        } catch (\Throwable $e) {
            $dbEngine = strtoupper($dbConnection);
            $dbShortVersion = '';
        }

        // Count tables and calculate database size
        $tableCount = 0;
        $dbSizeMb = 0;
        try {
            if ($dbConnection === 'mysql' || $dbEngine === 'MariaDB' || $dbEngine === 'MySQL') {
                $tables = DB::select('SHOW TABLES');
                $tableCount = count($tables);

                $sizeQuery = DB::select(
                    'SELECT SUM(data_length + index_length) as size 
                     FROM information_schema.TABLES 
                     WHERE table_schema = ?',
                    [$dbName]
                );
                $dbSizeBytes = $sizeQuery[0]->size ?? 0;
                $dbSizeMb = round($dbSizeBytes / (1024 * 1024), 2);
            } elseif ($dbConnection === 'pgsql') {
                $tables = DB::select("SELECT table_name FROM information_schema.tables WHERE table_schema = 'public'");
                $tableCount = count($tables);
                $sizeQuery = DB::select("SELECT pg_database_size(?) as size", [$dbName]);
                $dbSizeBytes = $sizeQuery[0]->size ?? 0;
                $dbSizeMb = round($dbSizeBytes / (1024 * 1024), 2);
            } elseif ($dbConnection === 'sqlite') {
                $dbPath = $dbConfig['database'] ?? database_path('database.sqlite');
                if (file_exists($dbPath)) {
                    $dbSizeMb = round(filesize($dbPath) / (1024 * 1024), 2);
                }
                $tables = DB::select("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'");
                $tableCount = count($tables);
            }
        } catch (\Throwable $e) {
            // Restricted permissions
        }

        // Migration counts
        $allMigrations = $this->getMigrations();
        $ranCount = collect($allMigrations)->where('is_executed', true)->count();
        $pendingCount = collect($allMigrations)->where('is_executed', false)->count();

        // Backup count & size
        $backups = $this->getBackups();
        $totalBackupSizeBytes = collect($backups)->sum('size_bytes');

        return [
            'laravel_version' => app()->version(),
            'php_version'     => phpversion(),
            'php_os'          => PHP_OS_FAMILY . ' (' . php_uname('s') . ')',
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? php_uname('n'),
            'app_name'        => config('app.name'),
            'app_env'         => config('app.env'),
            'app_debug'       => (bool) config('app.debug'),
            'app_url'         => config('app.url'),
            'db_driver'       => $dbConnection,
            'db_engine'       => $dbEngine,
            'db_version'      => $dbVersion,
            'db_short_version'=> $dbShortVersion,
            'db_name'         => $dbDisplayName,
            'db_raw_name'     => $rawDbName,
            'db_host'         => $dbHost,
            'db_port'         => $dbPort,
            'db_charset'      => $dbCharset,
            'table_count'     => $tableCount,
            'db_size_mb'      => $dbSizeMb,
            'migrations_total'=> count($allMigrations),
            'migrations_ran'  => $ranCount,
            'migrations_pending' => $pendingCount,
            'seeders_total'   => count($this->getSeeders()),
            'backups_count'   => count($backups),
            'backups_size_mb' => round($totalBackupSizeBytes / (1024 * 1024), 2),
        ];
    }

    /**
     * Get all migration files from database/migrations and modules.
     */
    public function getMigrations(): array
    {
        $executedMap = [];
        try {
            $executed = DB::table('migrations')->get();
            foreach ($executed as $row) {
                $executedMap[$row->migration] = (int) $row->batch;
            }
        } catch (\Throwable $e) {
            // Migrations table not available
        }

        $directories = [
            'Core' => database_path('migrations'),
        ];

        // Search active modules for migrations
        $modulesPath = base_path('Modules');
        if (File::isDirectory($modulesPath)) {
            $modules = File::directories($modulesPath);
            foreach ($modules as $moduleDir) {
                $moduleName = basename($moduleDir);
                $migPath1 = $moduleDir . DIRECTORY_SEPARATOR . 'database' . DIRECTORY_SEPARATOR . 'migrations';
                $migPath2 = $moduleDir . DIRECTORY_SEPARATOR . 'Database' . DIRECTORY_SEPARATOR . 'Migrations';
                if (File::isDirectory($migPath1)) {
                    $directories[$moduleName] = $migPath1;
                } elseif (File::isDirectory($migPath2)) {
                    $directories[$moduleName] = $migPath2;
                }
            }
        }

        $migrations = [];

        foreach ($directories as $module => $dir) {
            if (!File::isDirectory($dir)) {
                continue;
            }

            $files = File::files($dir);
            foreach ($files as $file) {
                if ($file->getExtension() !== 'php') {
                    continue;
                }

                $filename = $file->getFilename();
                $migrationKey = preg_replace('/\.php$/', '', $filename);
                $isExecuted = array_key_exists($migrationKey, $executedMap);
                $batch = $isExecuted ? $executedMap[$migrationKey] : null;

                $relativePath = str_replace(base_path() . DIRECTORY_SEPARATOR, '', $file->getRealPath());
                $relativePath = str_replace('\\', '/', $relativePath);

                $migrations[] = [
                    'filename'      => $filename,
                    'key'           => $migrationKey,
                    'module'        => $module,
                    'relative_path' => $relativePath,
                    'absolute_path' => $file->getRealPath(),
                    'is_executed'   => $isExecuted,
                    'batch'         => $batch,
                    'file_size'     => round($file->getSize() / 1024, 1) . ' KB',
                    'modified_at'   => date('Y-m-d H:i', $file->getMTime()),
                ];
            }
        }

        // Sort in exact Laravel chronological execution sequence (Ascending: 1, 2, 3...)
        usort($migrations, function ($a, $b) {
            $cmp = strcmp($a['filename'], $b['filename']);
            return $cmp !== 0 ? $cmp : strcmp($a['relative_path'], $b['relative_path']);
        });

        // Attach explicit 1-based execution sequence numbering
        foreach ($migrations as $idx => &$mig) {
            $mig['sequence'] = $idx + 1;
        }
        unset($mig);

        return $migrations;
    }

    /**
     * Run a single migration file.
     */
    public function runSingleMigration(string $relativePath): array
    {
        $normalizedPath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $relativePath);
        $fullPath = base_path($normalizedPath);

        if (!File::exists($fullPath)) {
            return [
                'success' => false,
                'message' => "Migration file not found at: {$relativePath}",
                'output'  => '',
            ];
        }

        $cleanPath = str_replace('\\', '/', $normalizedPath);

        $startTime = microtime(true);
        try {
            $exitCode = Artisan::call('migrate', [
                '--path'  => $cleanPath,
                '--force' => true,
            ]);

            $output = trim(Artisan::output());
            $duration = round((microtime(true) - $startTime) * 1000, 1);

            $this->logActivity('Run Single Migration', "Executed: {$cleanPath} ({$duration}ms)", $exitCode === 0 ? 'success' : 'error');

            return [
                'success'  => $exitCode === 0,
                'message'  => $exitCode === 0 ? "Migration [{$cleanPath}] executed successfully in {$duration}ms." : "Migration command returned exit code {$exitCode}.",
                'output'   => $output ?: ($exitCode === 0 ? "Migration completed successfully." : "No output."),
                'duration' => $duration,
            ];
        } catch (\Throwable $e) {
            $duration = round((microtime(true) - $startTime) * 1000, 1);
            $this->logActivity('Run Single Migration', "Failed executing: {$cleanPath}. Error: " . $e->getMessage(), 'error');

            return [
                'success'  => false,
                'message'  => "Exception while running migration: " . $e->getMessage(),
                'output'   => $e->getTraceAsString(),
                'duration' => $duration,
            ];
        }
    }

    /**
     * Run all pending migrations safely.
     */
    public function runAllPendingMigrations(): array
    {
        $startTime = microtime(true);
        try {
            $exitCode = Artisan::call('migrate', ['--force' => true]);
            $output = trim(Artisan::output());
            $duration = round((microtime(true) - $startTime) * 1000, 1);

            $this->logActivity('Run All Migrations', "Executed pending migrations ({$duration}ms)", $exitCode === 0 ? 'success' : 'error');

            return [
                'success'  => $exitCode === 0,
                'message'  => $exitCode === 0 ? "All pending migrations executed in {$duration}ms." : "Migration command returned exit code {$exitCode}.",
                'output'   => $output ?: 'Nothing to migrate.',
                'duration' => $duration,
            ];
        } catch (\Throwable $e) {
            $duration = round((microtime(true) - $startTime) * 1000, 1);
            $this->logActivity('Run All Migrations', "Error: " . $e->getMessage(), 'error');

            return [
                'success'  => false,
                'message'  => "Exception during migration: " . $e->getMessage(),
                'output'   => $e->getTraceAsString(),
                'duration' => $duration,
            ];
        }
    }

    /**
     * Get all seeder files in database/seeders and modules.
     */
    public function getSeeders(): array
    {
        $directories = [
            'Core' => database_path('seeders'),
        ];

        $modulesPath = base_path('Modules');
        if (File::isDirectory($modulesPath)) {
            $modules = File::directories($modulesPath);
            foreach ($modules as $moduleDir) {
                $moduleName = basename($moduleDir);
                $seedPath1 = $moduleDir . DIRECTORY_SEPARATOR . 'database' . DIRECTORY_SEPARATOR . 'seeders';
                $seedPath2 = $moduleDir . DIRECTORY_SEPARATOR . 'Database' . DIRECTORY_SEPARATOR . 'Seeders';
                if (File::isDirectory($seedPath1)) {
                    $directories[$moduleName] = $seedPath1;
                } elseif (File::isDirectory($seedPath2)) {
                    $directories[$moduleName] = $seedPath2;
                }
            }
        }

        $seeders = [];

        foreach ($directories as $module => $dir) {
            if (!File::isDirectory($dir)) {
                continue;
            }

            $files = File::files($dir);
            foreach ($files as $file) {
                if ($file->getExtension() !== 'php') {
                    continue;
                }

                $filename = $file->getFilename();
                $content = File::get($file->getRealPath());

                $className = null;
                if (preg_match('/class\s+([A-Za-z0-9_]+)\s+extends\s+Seeder/i', $content, $matches)) {
                    $className = $matches[1];
                } else {
                    $className = preg_replace('/\.php$/', '', $filename);
                }

                $relativePath = str_replace(base_path() . DIRECTORY_SEPARATOR, '', $file->getRealPath());
                $relativePath = str_replace('\\', '/', $relativePath);

                $seeders[] = [
                    'class_name'    => $className,
                    'filename'      => $filename,
                    'module'        => $module,
                    'relative_path' => $relativePath,
                    'file_size'     => round($file->getSize() / 1024, 1) . ' KB',
                    'modified_at'   => date('Y-m-d H:i', $file->getMTime()),
                ];
            }
        }

        usort($seeders, fn($a, $b) => strcmp($a['class_name'], $b['class_name']));

        return $seeders;
    }

    /**
     * Run a specific Seeder class.
     */
    public function runSeeder(string $seederClass): array
    {
        $startTime = microtime(true);
        try {
            $exitCode = Artisan::call('db:seed', [
                '--class' => $seederClass,
                '--force' => true,
            ]);
            $output = trim(Artisan::output());
            $duration = round((microtime(true) - $startTime) * 1000, 1);

            $this->logActivity('Run Seeder', "Executed seeder: {$seederClass} ({$duration}ms)", $exitCode === 0 ? 'success' : 'error');

            return [
                'success'  => $exitCode === 0,
                'message'  => $exitCode === 0 ? "Seeder [{$seederClass}] executed successfully in {$duration}ms." : "Seeder finished with exit code {$exitCode}.",
                'output'   => $output ?: ($exitCode === 0 ? "Database seeding completed successfully." : "No output."),
                'duration' => $duration,
            ];
        } catch (\Throwable $e) {
            $duration = round((microtime(true) - $startTime) * 1000, 1);
            $this->logActivity('Run Seeder', "Failed [{$seederClass}]: " . $e->getMessage(), 'error');

            return [
                'success'  => false,
                'message'  => "Exception running seeder [{$seederClass}]: " . $e->getMessage(),
                'output'   => $e->getTraceAsString(),
                'duration' => $duration,
            ];
        }
    }

    /**
     * Run safe Artisan command from whitelist.
     */
    public function runArtisanCommand(string $commandKey): array
    {
        $allowed = config('devzone.allowed_commands', config('devpanel.allowed_commands', []));

        if (!array_key_exists($commandKey, $allowed)) {
            $this->logActivity('Artisan Command (Blocked)', "Attempted unauthorized command: {$commandKey}", 'error');
            return [
                'success' => false,
                'message' => "Command [{$commandKey}] is not permitted. Only predefined safe commands are allowed.",
                'output'  => '',
            ];
        }

        $startTime = microtime(true);
        try {
            $exitCode = Artisan::call($commandKey);
            $output = trim(Artisan::output());
            $duration = round((microtime(true) - $startTime) * 1000, 1);

            $this->logActivity('Artisan Command', "Executed [php artisan {$commandKey}] in {$duration}ms", $exitCode === 0 ? 'success' : 'error');

            return [
                'success'  => $exitCode === 0,
                'message'  => $exitCode === 0 ? "Command [php artisan {$commandKey}] completed successfully in {$duration}ms." : "Command exited with code {$exitCode}.",
                'output'   => $output ?: ($exitCode === 0 ? "Command completed successfully." : "No output."),
                'duration' => $duration,
            ];
        } catch (\Throwable $e) {
            $duration = round((microtime(true) - $startTime) * 1000, 1);
            $this->logActivity('Artisan Command', "Error in [{$commandKey}]: " . $e->getMessage(), 'error');

            return [
                'success'  => false,
                'message'  => "Exception running [{$commandKey}]: " . $e->getMessage(),
                'output'   => $e->getTraceAsString(),
                'duration' => $duration,
            ];
        }
    }

    /**
     * Get dynamic API documentation packages status (Swagger, Scribe, Both, or None).
     */
    public function getApiDocInfo(): array
    {
        $setting = config('devzone.api_docs', 'auto');
        if ($setting === false || $setting === 'none' || $setting === 'off') {
            return [
                'has_api_docs'  => false,
                'is_available'  => false,
                'active_type'   => 'none',
                'has_swagger'   => false,
                'has_scribe'    => false,
                'primary_url'   => null,
                'primary_label' => null,
                'title'         => null,
                'swagger'       => ['installed' => false],
                'scribe'        => ['installed' => false],
            ];
        }

        $allCommands = [];
        try {
            $allCommands = array_keys(Artisan::all());
        } catch (\Throwable $e) {
            // Artisan command loading fallback
        }

        // Detect L5-Swagger (OpenAPI 3.0)
        $hasSwagger = in_array('l5-swagger:generate', $allCommands) 
            || class_exists(\L5Swagger\L5SwaggerServiceProvider::class) 
            || (function_exists('config') && config('l5-swagger') !== null);

        // Detect Knuckles Scribe
        $hasScribe = in_array('scribe:generate', $allCommands) 
            || class_exists(\Knuckles\Scribe\ScribeServiceProvider::class) 
            || (function_exists('config') && config('scribe') !== null);

        // Config overrides
        if ($setting === 'swagger') {
            $hasScribe = false;
        } elseif ($setting === 'scribe') {
            $hasSwagger = false;
        }

        $hasAnyDoc = ($hasSwagger || $hasScribe);

        $activeType = 'none';
        $primaryUrl = null;
        $primaryLabel = null;
        $title = null;

        if ($hasSwagger && $hasScribe) {
            $activeType = 'both';
            $primaryUrl = url('docs');
            $primaryLabel = 'API Docs';
            $title = 'API Documentation Manager (Swagger & Scribe)';
        } elseif ($hasScribe) {
            $activeType = 'scribe';
            $primaryUrl = url('docs');
            $primaryLabel = 'Scribe Docs';
            $title = 'Knuckles Scribe API Documentation';
        } elseif ($hasSwagger) {
            $activeType = 'swagger';
            $primaryUrl = url('api/documentation');
            $primaryLabel = 'Swagger Docs';
            $title = 'Swagger / OpenAPI 3.0 Documentation';
        }

        return [
            'has_api_docs'  => $hasAnyDoc,
            'is_available'  => $hasAnyDoc,
            'active_type'   => $activeType,
            'has_swagger'   => $hasSwagger,
            'has_scribe'    => $hasScribe,
            'primary_url'   => $primaryUrl,
            'primary_label' => $primaryLabel,
            'title'         => $title,
            'swagger'       => [
                'installed'  => $hasSwagger,
                'name'       => 'L5-Swagger (OpenAPI 3.0)',
                'command'    => 'l5-swagger:generate',
                'url'        => url('api/documentation'),
                'spec_url'   => url('docs/api-docs.json'),
                'file_path'  => 'storage/api-docs/api-docs.json',
            ],
            'scribe'        => [
                'installed'  => $hasScribe,
                'name'       => 'Knuckles Scribe',
                'command'    => 'scribe:generate',
                'url'        => url('docs'),
                'spec_url'   => url('docs/collection.json'),
                'file_path'  => 'public/docs/index.html',
            ],
        ];
    }

    /**
     * Dynamically generate API documentation (Swagger, Scribe, or Auto).
     */
    public function generateApiDocumentation(?string $driver = null): array
    {
        $docInfo = $this->getApiDocInfo();

        if (!$docInfo['has_api_docs']) {
            return [
                'success' => false,
                'message' => 'No supported API documentation package (L5-Swagger or Knuckles Scribe) is installed in this project.',
                'output'  => 'Please install Knuckles Scribe (knuckleswtf/scribe) or L5-Swagger (darkaonline/l5-swagger) to enable automated API documentation.',
            ];
        }

        if (empty($driver) || $driver === 'auto') {
            $driver = ($docInfo['has_scribe'] && !$docInfo['has_swagger']) ? 'scribe' : 'swagger';
        }

        $command = ($driver === 'scribe') ? 'scribe:generate' : 'l5-swagger:generate';
        $driverLabel = ($driver === 'scribe') ? 'Scribe' : 'Swagger';

        $startTime = microtime(true);
        try {
            $exitCode = Artisan::call($command);
            $output = trim(Artisan::output());
            $duration = round((microtime(true) - $startTime) * 1000, 1);

            $this->logActivity("{$driverLabel} Docs Generation", "Generated {$driverLabel} documentation in {$duration}ms", $exitCode === 0 ? 'success' : 'error');

            return [
                'success'  => $exitCode === 0,
                'driver'   => $driver,
                'message'  => $exitCode === 0 ? "{$driverLabel} API documentation generated successfully in {$duration}ms." : "{$driverLabel} generation exited with code {$exitCode}.",
                'output'   => $output ?: "Documentation generated.",
                'duration' => $duration,
            ];
        } catch (\Throwable $e) {
            $duration = round((microtime(true) - $startTime) * 1000, 1);
            $this->logActivity("{$driverLabel} Docs Generation", "Failed: " . $e->getMessage(), 'error');

            return [
                'success'  => false,
                'driver'   => $driver,
                'message'  => "Error generating {$driverLabel} documentation: " . $e->getMessage(),
                'output'   => $e->getTraceAsString(),
                'duration' => $duration,
            ];
        }
    }

    /**
     * Toggle APP_DEBUG in .env safely.
     */
    public function toggleAppDebug(bool $enable): array
    {
        $envPath = base_path('.env');
        if (!File::exists($envPath)) {
            return [
                'success' => false,
                'message' => '.env file not found.',
            ];
        }

        try {
            $targetValue = $enable ? 'true' : 'false';
            $envContent = File::get($envPath);

            if (preg_match('/^APP_DEBUG=.*/m', $envContent)) {
                $newContent = preg_replace('/^APP_DEBUG=.*/m', "APP_DEBUG={$targetValue}", $envContent);
            } else {
                $newContent = rtrim($envContent) . "\nAPP_DEBUG={$targetValue}\n";
            }

            if (!is_writable($envPath)) {
                @chmod($envPath, 0666);
            }

            $written = @file_put_contents($envPath, $newContent);
            if ($written === false) {
                return [
                    'success' => false,
                    'message' => 'Unable to write to .env file. Please check file permissions.',
                ];
            }

            // Sync runtime environment for current PHP process
            putenv("APP_DEBUG={$targetValue}");
            $_ENV['APP_DEBUG'] = $targetValue;
            $_SERVER['APP_DEBUG'] = $targetValue;
            config(['app.debug' => $enable]);

            try {
                if (app()->configurationIsCached()) {
                    Artisan::call('config:clear');
                }
            } catch (\Throwable $e) {}

            $this->logActivity('Debug Mode Toggle', "APP_DEBUG set to {$targetValue}", 'info');

            return [
                'success'   => true,
                'message'   => "Application debug mode successfully set to {$targetValue}.",
                'app_debug' => $enable,
            ];
        } catch (\Throwable $e) {
            $this->logActivity('Debug Mode Toggle', "Failed to toggle debug: " . $e->getMessage(), 'error');
            return [
                'success' => false,
                'message' => 'Failed to update APP_DEBUG: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Get list of created database backups.
     */
    public function getBackups(): array
    {
        $backupDir = storage_path('app/backup');
        if (!File::isDirectory($backupDir)) {
            return [];
        }

        $files = File::files($backupDir);
        $backups = [];

        foreach ($files as $file) {
            $ext = strtolower($file->getExtension());
            if (!in_array($ext, ['sql', 'gz', 'zip'])) {
                continue;
            }

            $sizeBytes = $file->getSize();
            $formattedSize = $sizeBytes >= 1048576 
                ? round($sizeBytes / 1048576, 2) . ' MB' 
                : round($sizeBytes / 1024, 1) . ' KB';

            $backups[] = [
                'filename'       => $file->getFilename(),
                'size_bytes'     => $sizeBytes,
                'formatted_size' => $formattedSize,
                'created_at'     => date('Y-m-d H:i:s', $file->getMTime()),
                'age'            => Carbon::createFromTimestamp($file->getMTime())->diffForHumans(),
            ];
        }

        usort($backups, fn($a, $b) => strcmp($b['created_at'], $a['created_at']));

        return $backups;
    }

    /**
     * Create an authorized database backup.
     */
    public function createDatabaseBackup(): array
    {
        $backupDir = storage_path('app/backup');
        if (!File::isDirectory($backupDir)) {
            File::makeDirectory($backupDir, 0755, true);
        }

        $dbConnection = config('database.default');
        $dbConfig = config("database.connections.{$dbConnection}", []);
        $dbName = $dbConfig['database'] ?? 'database';
        $dbUser = $dbConfig['username'] ?? 'root';
        $dbPass = $dbConfig['password'] ?? '';
        $dbHost = $dbConfig['host'] ?? '127.0.0.1';
        $dbPort = $dbConfig['port'] ?? 3306;

        $timestamp = date('Y-m-d_H-i-s');
        $filename = "backup-{$dbName}-{$timestamp}.sql";
        $targetPath = $backupDir . DIRECTORY_SEPARATOR . $filename;

        $startTime = microtime(true);

        // Attempt 1: Fast mysqldump binary with --column-statistics=0
        $mysqldumpAvailable = false;
        try {
            $check = @exec('mysqldump --version', $testOut, $testCode);
            if ($testCode === 0) {
                $mysqldumpAvailable = true;
            }
        } catch (\Throwable $e) {}

        if ($mysqldumpAvailable) {
            $passPart = !empty($dbPass) ? "--password=" . escapeshellarg($dbPass) : "";
            $dumpCmd = sprintf(
                'mysqldump --column-statistics=0 --user=%s %s --host=%s --port=%s %s > %s 2>&1',
                escapeshellarg($dbUser),
                $passPart,
                escapeshellarg($dbHost),
                escapeshellarg($dbPort),
                escapeshellarg($dbName),
                escapeshellarg($targetPath)
            );

            @exec($dumpCmd, $cmdOut, $cmdCode);

            if ($cmdCode === 0 && File::exists($targetPath) && File::size($targetPath) > 0) {
                $duration = round((microtime(true) - $startTime) * 1000, 1);
                $sizeBytes = File::size($targetPath);
                $formattedSize = $sizeBytes >= 1048576 
                    ? round($sizeBytes / 1048576, 2) . ' MB' 
                    : round($sizeBytes / 1024, 1) . ' KB';

                $this->logActivity('Database Backup', "Created backup {$filename} ({$formattedSize}) via mysqldump in {$duration}ms", 'success');

                return [
                    'success'        => true,
                    'message'        => "Database backup created successfully ({$formattedSize}) in {$duration}ms.",
                    'filename'       => $filename,
                    'formatted_size' => $formattedSize,
                    'duration'       => $duration,
                ];
            }
        }

        // Attempt 2: High-Performance Pure PHP PDO Streaming Dumper
        try {
            $pdo = DB::connection()->getPdo();
            $fileHandle = fopen($targetPath, 'w');

            if (!$fileHandle) {
                throw new \Exception("Cannot open file for writing: {$targetPath}");
            }

            fwrite($fileHandle, "-- DevZone Database Backup Dump\n");
            fwrite($fileHandle, "-- Generated: " . date('Y-m-d H:i:s') . "\n");
            fwrite($fileHandle, "-- Database: `{$dbName}`\n\n");
            fwrite($fileHandle, "SET FOREIGN_KEY_CHECKS=0;\n");
            fwrite($fileHandle, "SET SQL_MODE='NO_AUTO_VALUE_ON_ZERO';\n");
            fwrite($fileHandle, "SET NAMES utf8mb4;\n\n");

            $tables = DB::select('SHOW TABLES');
            $tableKey = 'Tables_in_' . $dbName;

            foreach ($tables as $tableObj) {
                $tableName = isset($tableObj->$tableKey) ? $tableObj->$tableKey : array_values((array) $tableObj)[0];

                $createRes = DB::select("SHOW CREATE TABLE `{$tableName}`");
                $createSql = isset($createRes[0]->{'Create Table'}) 
                    ? $createRes[0]->{'Create Table'} 
                    : array_values((array) $createRes[0])[1];

                fwrite($fileHandle, "--\n-- Table structure for `{$tableName}`\n--\n");
                fwrite($fileHandle, "DROP TABLE IF EXISTS `{$tableName}`;\n");
                fwrite($fileHandle, $createSql . ";\n\n");

                fwrite($fileHandle, "--\n-- Dumping data for `{$tableName}`\n--\n");
                $rows = DB::table($tableName)->cursor();

                $batch = [];
                foreach ($rows as $row) {
                    $rowArray = (array) $row;
                    $escapedValues = array_map(function ($val) use ($pdo) {
                        if (is_null($val)) {
                            return 'NULL';
                        }
                        return $pdo->quote($val);
                    }, $rowArray);

                    $batch[] = '(' . implode(', ', $escapedValues) . ')';

                    if (count($batch) >= 200) {
                        fwrite($fileHandle, "INSERT INTO `{$tableName}` VALUES \n" . implode(",\n", $batch) . ";\n");
                        $batch = [];
                    }
                }

                if (!empty($batch)) {
                    fwrite($fileHandle, "INSERT INTO `{$tableName}` VALUES \n" . implode(",\n", $batch) . ";\n");
                }

                fwrite($fileHandle, "\n");
            }

            fwrite($fileHandle, "SET FOREIGN_KEY_CHECKS=1;\n");
            fclose($fileHandle);

            $duration = round((microtime(true) - $startTime) * 1000, 1);
            $sizeBytes = File::size($targetPath);
            $formattedSize = $sizeBytes >= 1048576 
                ? round($sizeBytes / 1048576, 2) . ' MB' 
                : round($sizeBytes / 1024, 1) . ' KB';

            $this->logActivity('Database Backup', "Created backup {$filename} ({$formattedSize}) via PHP PDO in {$duration}ms", 'success');

            return [
                'success'        => true,
                'message'        => "Database backup created successfully ({$formattedSize}) in {$duration}ms.",
                'filename'       => $filename,
                'formatted_size' => $formattedSize,
                'duration'       => $duration,
            ];
        } catch (\Throwable $e) {
            $duration = round((microtime(true) - $startTime) * 1000, 1);
            $this->logActivity('Database Backup', "Backup failed: " . $e->getMessage(), 'error');

            if (File::exists($targetPath)) {
                @unlink($targetPath);
            }

            return [
                'success'  => false,
                'message'  => "Database backup failed: " . $e->getMessage(),
                'duration' => $duration,
            ];
        }
    }

    /**
     * Get validated absolute download path for a backup file.
     */
    public function getBackupDownloadPath(string $filename): ?string
    {
        $filename = basename($filename);
        $fullPath = storage_path('app/backup' . DIRECTORY_SEPARATOR . $filename);

        if (!File::exists($fullPath)) {
            return null;
        }

        $realPath = realpath($fullPath);
        $allowedBase = realpath(storage_path('app/backup'));

        if (!$realPath || !$allowedBase || !str_starts_with($realPath, $allowedBase)) {
            return null;
        }

        $this->logActivity('Database Backup Download', "Downloaded {$filename}", 'info');

        return $realPath;
    }

    /**
     * Delete an existing backup file.
     */
    public function deleteBackup(string $filename): bool
    {
        $path = $this->getBackupDownloadPath($filename);
        if ($path && File::exists($path)) {
            $deleted = File::delete($path);
            if ($deleted) {
                $this->logActivity('Database Backup Delete', "Deleted backup file: {$filename}", 'warning');
            }
            return $deleted;
        }

        return false;
    }

    /**
     * First-time setup: Save master passkey and admin email to config and .env.
     */
    public function saveSetupCredentials(string $email, string $passkey, string $path = 'developer-panel'): bool
    {
        $updated = false;

        // 1. Update .env if present
        $envPath = base_path('.env');
        if (File::exists($envPath)) {
            $envContent = File::get($envPath);
            $keys = [
                'DEVZONE_ENABLED'         => 'true',
                'DEVZONE_PATH'            => $path,
                'DEVZONE_KEY'             => $passkey,
                'DEVZONE_EMAIL'           => $email,
                'DEVZONE_SETUP_COMPLETED' => 'true',
            ];

            foreach ($keys as $k => $v) {
                if (preg_match("/^{$k}=.*/m", $envContent)) {
                    $envContent = preg_replace("/^{$k}=.*/m", "{$k}={$v}", $envContent);
                } else {
                    $envContent .= "\n{$k}={$v}";
                }
            }

            File::put($envPath, $envContent);
            $updated = true;
        }

        // 2. Update config/devzone.php if published in host application
        $configPath = config_path('devzone.php');
        if (File::exists($configPath)) {
            $configContent = File::get($configPath);
            $configContent = preg_replace("/('passkey'\s*=>\s*env\('[^']+',\s*')[^']*('\))/", "\${1}{$passkey}\${2}", $configContent);
            $configContent = preg_replace("/('admin_email'\s*=>\s*env\('[^']+',\s*')[^']*('\))/", "\${1}{$email}\${2}", $configContent);
            $configContent = preg_replace("/('path'\s*=>\s*env\('[^']+',\s*')[^']*('\))/", "\${1}{$path}\${2}", $configContent);
            $configContent = preg_replace("/('setup_completed'\s*=>\s*)[^,]+(,?)/", "\${1}true\${2}", $configContent);
            File::put($configPath, $configContent);
            $updated = true;
        }

        // 3. Update runtime configuration immediately
        config([
            'devzone.passkey'         => $passkey,
            'devzone.admin_email'     => $email,
            'devzone.path'            => $path,
            'devzone.setup_completed' => true,
        ]);

        try {
            Artisan::call('config:clear');
        } catch (\Throwable $e) {}

        $this->logActivity('Setup Completed', "Master credentials configured for {$email}", 'success');

        return $updated;
    }

    /**
     * Update master access credentials after verifying current passkey.
     * Persists new passkey (and optional email) to .env, host config, and runtime config.
     */
    public function updateCredentials(string $newPasskey, ?string $newEmail = null): bool
    {
        $updated = false;

        // 1. Update .env if present
        $envPath = base_path('.env');
        if (File::exists($envPath)) {
            $envContent = File::get($envPath);
            $keys = [
                'DEVZONE_KEY' => $newPasskey,
            ];

            if (preg_match('/^DEV_PANEL_KEY=/m', $envContent)) {
                $keys['DEV_PANEL_KEY'] = $newPasskey;
            }

            if (!empty($newEmail)) {
                $keys['DEVZONE_EMAIL'] = $newEmail;
                if (preg_match('/^DEV_PANEL_EMAIL=/m', $envContent)) {
                    $keys['DEV_PANEL_EMAIL'] = $newEmail;
                }
            }

            foreach ($keys as $k => $v) {
                $escapedVal = (str_contains($v, ' ') || str_contains($v, '#')) ? "\"{$v}\"" : $v;
                if (preg_match("/^{$k}=.*/m", $envContent)) {
                    $envContent = preg_replace("/^{$k}=.*/m", "{$k}={$escapedVal}", $envContent);
                } else {
                    $envContent .= "\n{$k}={$escapedVal}";
                }
            }

            File::put($envPath, $envContent);
            $updated = true;
        }

        // 2. Update config/devzone.php if published in host application
        $configPath = config_path('devzone.php');
        if (File::exists($configPath)) {
            $configContent = File::get($configPath);
            $configContent = preg_replace("/('passkey'\s*=>\s*env\('[^']+',\s*')[^']*('\))/", "\${1}{$newPasskey}\${2}", $configContent);
            if (!empty($newEmail)) {
                $configContent = preg_replace("/('admin_email'\s*=>\s*env\('[^']+',\s*')[^']*('\))/", "\${1}{$newEmail}\${2}", $configContent);
            }
            File::put($configPath, $configContent);
            $updated = true;
        }

        // 3. Update runtime config immediately
        config(['devzone.passkey' => $newPasskey]);
        if (!empty($newEmail)) {
            config(['devzone.admin_email' => $newEmail]);
        }

        // 4. Clear compiled configuration cache
        try {
            Artisan::call('config:clear');
        } catch (\Throwable $e) {}

        $this->logActivity('Credentials Updated', 'Master passkey updated successfully by authenticated developer', 'success');

        return $updated;
    }

    /**
     * Record an administrative activity event.
     */
    public function logActivity(string $action, string $details, string $status = 'info'): void
    {
        try {
            $logDir = storage_path('app/devzone');
            if (!File::isDirectory($logDir)) {
                File::makeDirectory($logDir, 0755, true);
            }

            $logFile = $logDir . DIRECTORY_SEPARATOR . 'activity.jsonl';

            $record = [
                'timestamp'  => date('Y-m-d H:i:s'),
                'action'     => $action,
                'details'    => $details,
                'status'     => $status,
                'ip'         => request()?->ip() ?? 'CLI/System',
                'user_agent' => substr(request()?->userAgent() ?? 'Artisan Console', 0, 120),
            ];

            File::append($logFile, json_encode($record) . "\n");
        } catch (\Throwable $e) {
            // Failsafe logging
        }
    }

    /**
     * Get recent activity logs.
     */
    public function getActivityLogs(int $limit = 60): array
    {
        $logFile = storage_path('app/devzone/activity.jsonl');
        if (!File::exists($logFile)) {
            return [];
        }

        $lines = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if (!$lines) {
            return [];
        }

        $records = [];
        $reversed = array_reverse($lines);

        foreach (array_slice($reversed, 0, $limit) as $line) {
            $data = json_decode($line, true);
            if ($data) {
                $records[] = $data;
            }
        }

        return $records;
    }

    /**
     * Clear activity logs history.
     */
    public function clearActivityLogs(): bool
    {
        $logFile = storage_path('app/devzone/activity.jsonl');
        if (File::exists($logFile)) {
            File::put($logFile, '');
            $this->logActivity('Clear Logs', 'Audit logs cleared by developer', 'warning');
            return true;
        }

        return false;
    }

    /**
     * Resolve the URL for a DevZone local asset (offline & zero CDN).
     */
    public static function asset(string $path): string
    {
        $path = ltrim($path, '/\\');

        if (function_exists('public_path') && file_exists(public_path('vendor/devzone/' . $path))) {
            return asset('vendor/devzone/' . $path);
        }

        if (\Illuminate\Support\Facades\Route::has('devzone.asset')) {
            return route('devzone.asset', ['path' => $path]);
        }

        $prefix = config('devzone.path', 'developer-panel');
        return url($prefix . '/assets/' . $path);
    }

    /**
     * Get current package version string.
     */
    public static function version(): string
    {
        return (new static())->getCurrentVersionInfo()['version'];
    }

    /**
     * Get current package build hash.
     */
    public static function build(): string
    {
        return (new static())->getCurrentVersionInfo()['build'];
    }

    /**
     * Resolve the current installed version and build persistently.
     */
    public function getCurrentVersionInfo(): array
    {
        $version = self::VERSION;
        $build = '839fedc';
        $releaseDate = date('Y-m-d');
        $channel = 'stable';
        $source = 'constant';

        // 1. Check persistent host application storage file
        if (function_exists('storage_path')) {
            $storageFile = storage_path('app/devzone/version.json');
            if (file_exists($storageFile)) {
                $content = @file_get_contents($storageFile);
                $data = $content ? @json_decode($content, true) : null;
                if (!empty($data) && !empty($data['version'])) {
                    return [
                        'version'      => ltrim((string) $data['version'], 'vV'),
                        'version_tag'  => 'v' . ltrim((string) $data['version'], 'vV'),
                        'build'        => (string) ($data['build'] ?? $build),
                        'release_date' => (string) ($data['release_date'] ?? $releaseDate),
                        'channel'      => (string) ($data['channel'] ?? $channel),
                        'developer'    => self::AUTHOR,
                        'email'        => self::AUTHOR_EMAIL,
                        'brand'        => 'nextSyntaxTech',
                        'package'      => 'nextSyntaxTech DevZone',
                        'source'       => 'storage',
                    ];
                }
            }
        }

        // 2. Check package root version.json
        $packageVersionFile = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'version.json';
        if (file_exists($packageVersionFile)) {
            $content = @file_get_contents($packageVersionFile);
            $data = $content ? @json_decode($content, true) : null;
            if (!empty($data) && !empty($data['version'])) {
                $version = ltrim((string) $data['version'], 'vV');
                $build = (string) ($data['build'] ?? $build);
                $releaseDate = (string) ($data['release_date'] ?? $releaseDate);
                $channel = (string) ($data['channel'] ?? $channel);
                $source = 'package_file';
            }
        }

        // 3. Check Git repository if active
        if (function_exists('shell_exec') && !in_array('shell_exec', explode(',', (string) ini_get('disable_functions')))) {
            $pkgDir = dirname(__DIR__, 2);
            $gitCmdPrefix = "cd /d \"{$pkgDir}\" && ";
            try {
                $gitSha = @shell_exec($gitCmdPrefix . 'git rev-parse --short HEAD 2>nul');
                if (!empty($gitSha) && preg_match('/^[a-f0-9]{6,12}$/i', trim($gitSha))) {
                    $build = trim($gitSha);
                    $source = 'git';
                }

                $gitTag = @shell_exec($gitCmdPrefix . 'git describe --tags --abbrev=0 2>nul');
                if (!empty($gitTag) && preg_match('/v?(\d+\.\d+\.\d+)/i', trim($gitTag), $tm)) {
                    $version = $tm[1];
                }
            } catch (\Throwable $e) {}
        }

        // 4. Check Composer InstalledVersions if available
        if (class_exists(\Composer\InstalledVersions::class)) {
            try {
                $composerVer = \Composer\InstalledVersions::getPrettyVersion('nextsyntaxtech/devzone');
                if (!empty($composerVer)) {
                    $cleanComposerVer = ltrim($composerVer, 'vV');
                    if (preg_match('/^(\d+\.\d+\.\d+)/', $cleanComposerVer, $m)) {
                        $version = $m[1];
                        $source = 'composer';
                    }
                }
            } catch (\Throwable $e) {}
        }

        return [
            'version'      => $version,
            'version_tag'  => 'v' . $version,
            'build'        => $build,
            'release_date' => $releaseDate,
            'channel'      => $channel,
            'developer'    => self::AUTHOR,
            'email'        => self::AUTHOR_EMAIL,
            'brand'        => 'nextSyntaxTech',
            'package'      => 'nextSyntaxTech DevZone',
            'source'       => $source,
        ];
    }

    /**
     * Check Git repository and GitHub for latest releases, tags, and commits.
     */
    public function checkPackageUpdate(): array
    {
        $currentInfo = $this->getCurrentVersionInfo();
        $currentVersion = $currentInfo['version'];
        $currentBuild = $currentInfo['build'];

        $latestTag = null;
        $latestCommit = null;
        $releaseDate = date('M d, Y');
        $releaseNotes = null;
        $releaseUrl = 'https://github.com/bsamit/nextsyntaxtech-devzone/releases';
        $checkedMethod = 'default';

        $pkgDir = dirname(__DIR__, 2);

        // 1. First Priority: Direct Git Remote Check (Real-time & accurate, no API rate limit)
        if (function_exists('shell_exec') && !in_array('shell_exec', explode(',', (string) ini_get('disable_functions')))) {
            $gitCmdPrefix = "cd /d \"{$pkgDir}\" && ";
            try {
                // Check remote HEAD commit on main
                $remoteHeadOutput = @shell_exec($gitCmdPrefix . 'git ls-remote origin -h refs/heads/main 2>nul');
                if (empty($remoteHeadOutput)) {
                    $remoteHeadOutput = @shell_exec($gitCmdPrefix . 'git ls-remote origin -h refs/heads/master 2>nul');
                }

                if (!empty($remoteHeadOutput) && preg_match('/^([a-f0-9]{40})/i', trim($remoteHeadOutput), $cm)) {
                    $latestCommit = substr($cm[1], 0, 7);
                    $checkedMethod = 'git_ls_remote';
                }

                // Check remote latest tag
                $remoteTagsOutput = @shell_exec($gitCmdPrefix . 'git ls-remote --tags origin 2>nul');
                if (!empty($remoteTagsOutput)) {
                    $lines = explode("\n", trim($remoteTagsOutput));
                    $foundTags = [];
                    foreach ($lines as $line) {
                        if (preg_match('/refs\/tags\/(v?(\d+\.\d+\.\d+))$/i', trim($line), $tm)) {
                            $foundTags[] = $tm[2];
                        }
                    }
                    if (!empty($foundTags)) {
                        usort($foundTags, 'version_compare');
                        $latestTag = end($foundTags);
                        $checkedMethod = 'git_ls_remote';
                    }
                }
            } catch (\Throwable $e) {}
        }

        // 2. Second Priority: GitHub REST API (Releases, Tags, Commits)
        $repoCandidates = [
            'bsamit/nextsyntaxtech-devzone',
            'nextsyntaxtech/devzone',
        ];

        foreach ($repoCandidates as $repo) {
            try {
                // Check latest release
                $url = "https://api.github.com/repos/{$repo}/releases/latest";
                $responseBody = $this->fetchJsonUrl($url);

                if (!empty($responseBody) && !empty($responseBody['tag_name'])) {
                    $tag = ltrim((string) $responseBody['tag_name'], 'vV');
                    if (empty($latestTag) || version_compare($tag, $latestTag, '>=')) {
                        $latestTag = $tag;
                        $releaseDate = !empty($responseBody['published_at'])
                            ? date('M d, Y', strtotime((string) $responseBody['published_at']))
                            : $releaseDate;
                        $releaseNotes = $responseBody['body'] ?? 'Release available on GitHub.';
                        $releaseUrl = $responseBody['html_url'] ?? $releaseUrl;
                        $checkedMethod = 'github_api_release';
                    }
                }

                // Check latest commit on main branch
                if (empty($latestCommit)) {
                    $commitUrl = "https://api.github.com/repos/{$repo}/commits/main";
                    $commitBody = $this->fetchJsonUrl($commitUrl);
                    if (!empty($commitBody) && !empty($commitBody['sha'])) {
                        $latestCommit = substr((string) $commitBody['sha'], 0, 7);
                        if (!empty($commitBody['commit']['committer']['date'])) {
                            $releaseDate = date('M d, Y', strtotime($commitBody['commit']['committer']['date']));
                        }
                        if (empty($releaseNotes) && !empty($commitBody['commit']['message'])) {
                            $releaseNotes = "Latest build: " . trim((string) $commitBody['commit']['message']);
                        }
                        $checkedMethod = ($checkedMethod === 'git_ls_remote') ? 'git_ls_remote' : 'github_api_commit';
                    }
                }

                // Fallback tags API if latestTag still null
                if (empty($latestTag)) {
                    $tagsUrl = "https://api.github.com/repos/{$repo}/tags";
                    $tagsBody = $this->fetchJsonUrl($tagsUrl);
                    if (!empty($tagsBody) && is_array($tagsBody) && isset($tagsBody[0]['name'])) {
                        $latestTag = ltrim((string) $tagsBody[0]['name'], 'vV');
                        $checkedMethod = 'github_api_tags';
                    }
                }

                if (!empty($latestTag) || !empty($latestCommit)) {
                    break;
                }
            } catch (\Throwable $e) {}
        }

        // Fallbacks if remote was unreachable
        if (empty($latestTag)) {
            $latestTag = $currentVersion;
        }
        if (empty($latestCommit)) {
            $latestCommit = $currentBuild;
        }

        $cleanCurrent = ltrim($currentVersion, 'vV');
        $cleanLatest = ltrim($latestTag, 'vV');

        // Real Update Detection:
        // 1. Newer semantic version tag
        $hasVersionUpdate = version_compare($cleanLatest, $cleanCurrent, '>');
        // 2. Newer git commit build on remote main
        $hasCommitUpdate = (!empty($latestCommit) && !empty($currentBuild) && strcasecmp(substr($latestCommit, 0, 7), substr($currentBuild, 0, 7)) !== 0);

        $hasUpdate = $hasVersionUpdate || $hasCommitUpdate;
        $updateType = $hasVersionUpdate ? 'version' : ($hasCommitUpdate ? 'build' : 'none');

        if ($hasVersionUpdate) {
            $updateMessage = "New Release Available: v{$cleanLatest}";
        } elseif ($hasCommitUpdate) {
            $updateMessage = "New Build Available: {$currentBuild} \u{2192} {$latestCommit}";
        } else {
            $updateMessage = "You are running the latest version! (v{$cleanCurrent} \u{2022} Build: {$currentBuild})";
        }

        return [
            'success'          => true,
            'current_version'  => 'v' . $cleanCurrent,
            'current_build'    => $currentBuild,
            'latest_version'   => 'v' . $cleanLatest,
            'latest_build'     => $latestCommit,
            'has_update'       => $hasUpdate,
            'update_type'      => $updateType,
            'update_message'   => $updateMessage,
            'release_date'     => $releaseDate,
            'release_notes'    => $releaseNotes ?: ($hasUpdate ? "New update is available on GitHub." : "Your system is up to date with origin/main."),
            'release_url'      => $releaseUrl,
            'developer'        => self::AUTHOR,
            'email'            => self::AUTHOR_EMAIL,
            'brand'            => 'nextSyntaxTech',
            'package'          => 'nextSyntaxTech DevZone',
            'checked_via'      => $checkedMethod,
            'composer_cmd'     => 'composer update nextsyntaxtech/devzone',
        ];
    }

    /**
     * Helper to fetch JSON from URL with timeout.
     */
    protected function fetchJsonUrl(string $url): ?array
    {
        if (class_exists(\Illuminate\Support\Facades\Http::class)) {
            try {
                $res = \Illuminate\Support\Facades\Http::withHeaders([
                    'User-Agent' => 'nextSyntaxTech-DevZone-Updater',
                    'Accept'     => 'application/vnd.github.v3+json',
                ])->timeout(6)->get($url);

                if ($res->successful()) {
                    return $res->json();
                }
            } catch (\Throwable $e) {}
        }

        if (function_exists('curl_init')) {
            try {
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    'User-Agent: nextSyntaxTech-DevZone-Updater',
                    'Accept: application/vnd.github.v3+json',
                ]);
                curl_setopt($ch, CURLOPT_TIMEOUT, 6);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                $result = curl_exec($ch);
                $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);

                if ($status === 200 && $result) {
                    return json_decode($result, true);
                }
            } catch (\Throwable $e) {}
        }

        return null;
    }

    /**
     * Run package update routine, pull remote git changes / composer, and update version tracking.
     */
    public function updatePackage(?string $targetVersion = null): array
    {
        $startTime = microtime(true);
        $outputLogs = [];
        $outputLogs[] = ">> [nextSyntaxTech DevZone Updater Started]";
        $outputLogs[] = ">> Timestamp: " . date('Y-m-d H:i:s T');
        $outputLogs[] = ">> Initiating repository sync & package update...";

        $pkgDir = dirname(__DIR__, 2);
        $base = function_exists('base_path') ? base_path() : $pkgDir;

        $newCommit = null;
        $newVersion = null;

        if (function_exists('shell_exec') && !in_array('shell_exec', explode(',', (string) ini_get('disable_functions')))) {
            // 1. Try Git Fetch & Pull if Git repo is detected
            $gitDir = $pkgDir . DIRECTORY_SEPARATOR . '.git';
            if (is_dir($gitDir) || file_exists($gitDir)) {
                $outputLogs[] = ">> Executing: git pull origin main";
                $gitCmd = "cd /d \"{$pkgDir}\" && git fetch origin main 2>&1 && git pull origin main 2>&1";
                try {
                    $gitOut = (string) @shell_exec($gitCmd);
                    if (!empty($gitOut)) {
                        $outputLogs[] = trim($gitOut);
                    }
                } catch (\Throwable $e) {
                    $outputLogs[] = ">> Git pull notice: " . $e->getMessage();
                }

                // Detect updated commit and tag
                $detectedSha = @shell_exec("cd /d \"{$pkgDir}\" && git rev-parse --short HEAD 2>nul");
                if (!empty($detectedSha) && preg_match('/^[a-f0-9]{6,12}$/i', trim($detectedSha))) {
                    $newCommit = trim($detectedSha);
                }
                $detectedTag = @shell_exec("cd /d \"{$pkgDir}\" && git describe --tags --abbrev=0 2>nul");
                if (!empty($detectedTag) && preg_match('/v?(\d+\.\d+\.\d+)/i', trim($detectedTag), $tm)) {
                    $newVersion = $tm[1];
                }
            }

            // 2. Try Composer Update
            $outputLogs[] = ">> Executing: composer update nextsyntaxtech/devzone --no-interaction";
            try {
                $cmd = "cd /d \"{$base}\" && composer update nextsyntaxtech/devzone --no-interaction 2>&1";
                $output = (string) @shell_exec($cmd);
                if (!empty($output)) {
                    $outputLogs[] = trim($output);
                }
            } catch (\Throwable $e) {
                $outputLogs[] = ">> Composer notice: " . $e->getMessage();
            }
        } else {
            $outputLogs[] = ">> Notice: shell_exec is restricted by PHP configuration.";
        }

        // Determine final version & build to save
        $currentInfo = $this->getCurrentVersionInfo();
        $finalVersion = $targetVersion ?: ($newVersion ?: $currentInfo['version']);
        $finalVersion = ltrim($finalVersion, 'vV');
        $finalBuild = $newCommit ?: $currentInfo['build'];

        // 3. PERSISTENT VERSION TRACKING: Write to version.json in package & host storage
        $versionPayload = [
            'version'            => $finalVersion,
            'build'              => $finalBuild,
            'release_date'       => date('Y-m-d'),
            'channel'            => 'stable',
            'package'            => 'nextsyntaxtech/devzone',
            'name'               => 'nextSyntaxTech DevZone',
            'developer'          => self::AUTHOR,
            'email'              => self::AUTHOR_EMAIL,
            'updated_at'         => date('Y-m-d H:i:s'),
            'last_update_status' => 'success',
        ];
        $jsonEncoded = json_encode($versionPayload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        // A. Package version.json
        $packageVersionFile = $pkgDir . DIRECTORY_SEPARATOR . 'version.json';
        @file_put_contents($packageVersionFile, $jsonEncoded);
        $outputLogs[] = ">> Package version manifest updated: v{$finalVersion} (Build: {$finalBuild})";

        // B. Host application storage version.json
        if (function_exists('storage_path')) {
            $hostStorageFile = storage_path('app/devzone/version.json');
            if (!is_dir(dirname($hostStorageFile))) {
                @mkdir(dirname($hostStorageFile), 0755, true);
            }
            @file_put_contents($hostStorageFile, $jsonEncoded);
            $outputLogs[] = ">> Application version state persisted to storage/app/devzone/version.json";
        }

        // 4. Framework Cache Clearing & Optimization
        $outputLogs[] = ">> Clearing compiled framework caches...";
        try {
            Artisan::call('optimize:clear');
            $artisanOut = trim(Artisan::output());
            if (!empty($artisanOut)) {
                $outputLogs[] = $artisanOut;
            }
        } catch (\Throwable $e) {}

        // 5. Publish DevZone assets
        try {
            Artisan::call('vendor:publish', [
                '--tag' => 'devzone-assets',
                '--force' => true,
            ]);
            $outputLogs[] = ">> Local package assets refreshed successfully.";
        } catch (\Throwable $e) {}

        $duration = round((microtime(true) - $startTime) * 1000, 1);
        $outputLogs[] = ">> [Update routine completed successfully in {$duration}ms]";

        $this->logActivity('Package Update', "Package updated to v{$finalVersion} (Build: {$finalBuild}) in {$duration}ms", 'success');

        return [
            'success'   => true,
            'message'   => "nextSyntaxTech DevZone successfully updated to v{$finalVersion} (Build: {$finalBuild})!",
            'output'    => implode("\n", $outputLogs),
            'duration'  => $duration,
            'version'   => 'v' . $finalVersion,
            'build'     => $finalBuild,
        ];
    }
}
