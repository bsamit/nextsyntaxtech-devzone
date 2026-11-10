<?php

namespace NextSyntaxTech\DevZone\Services;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Carbon\Carbon;

class DevZoneService
{
    /**
     * Get system health and environment statistics.
     */
    public function getSystemInfo(): array
    {
        $dbConnection = config('database.default');
        $dbConfig = config("database.connections.{$dbConnection}", []);
        $rawDbName = $dbConfig['database'] ?? 'N/A';
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

        usort($migrations, fn($a, $b) => strcmp($b['filename'], $a['filename']));

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
        $allCommands = array_keys(Artisan::all());

        $hasSwagger = in_array('l5-swagger:generate', $allCommands) 
            || class_exists(\L5Swagger\L5SwaggerServiceProvider::class) 
            || config('l5-swagger') !== null;

        $hasScribe = in_array('scribe:generate', $allCommands) 
            || class_exists(\Knuckles\Scribe\ScribeServiceProvider::class) 
            || config('scribe') !== null;

        $activeType = 'none';
        if ($hasSwagger && $hasScribe) {
            $activeType = 'both';
            $primaryUrl = url('docs');
            $primaryLabel = 'API Docs (Swagger & Scribe)';
        } elseif ($hasScribe) {
            $activeType = 'scribe';
            $primaryUrl = url('docs');
            $primaryLabel = 'Scribe Docs';
        } elseif ($hasSwagger) {
            $activeType = 'swagger';
            $primaryUrl = url('api/documentation');
            $primaryLabel = 'Swagger Docs';
        } else {
            $primaryUrl = '#';
            $primaryLabel = 'API Documentation';
        }

        return [
            'active_type'   => $activeType,
            'has_swagger'   => $hasSwagger,
            'has_scribe'    => $hasScribe,
            'primary_url'   => $primaryUrl,
            'primary_label' => $primaryLabel,
            'title'         => ($activeType === 'scribe') ? 'Knuckles Scribe API Documentation' : (($activeType === 'both') ? 'API Documentation Manager (Swagger & Scribe)' : 'Swagger / OpenAPI 3.0 Documentation'),
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
            File::put($configPath, $configContent);
            $updated = true;
        }

        try {
            Artisan::call('config:clear');
        } catch (\Throwable $e) {}

        $this->logActivity('Setup Completed', "Master credentials configured for {$email}", 'success');

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
}
