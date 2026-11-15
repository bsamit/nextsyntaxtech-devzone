<?php

namespace NextSyntaxTech\DevZone\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use NextSyntaxTech\DevZone\Services\DevZoneService;

class DevZoneController extends Controller
{
    protected DevZoneService $service;

    public function __construct(DevZoneService $service)
    {
        $this->service = $service;
    }

    /**
     * Show First-Time Setup Wizard.
     */
    public function setupForm(Request $request)
    {
        $configuredKey = config('devzone.passkey');
        $isSetupDone = (bool) config('devzone.setup_completed', false);

        // If setup is already done and passkey is set, redirect to login
        if ($isSetupDone && !empty($configuredKey)) {
            return redirect()->route('devzone.login');
        }

        return view('devzone::setup');
    }

    /**
     * Process First-Time Setup Wizard Submission.
     */
    public function setupSubmit(Request $request)
    {
        $configuredKey = config('devzone.passkey');
        $isSetupDone = (bool) config('devzone.setup_completed', false);

        if ($isSetupDone && !empty($configuredKey)) {
            return redirect()->route('devzone.login');
        }

        $request->validate([
            'email'                 => 'required|email',
            'passkey'               => 'required|string|min:6|confirmed',
            'passkey_confirmation'  => 'required|string',
            'path'                  => 'nullable|string|alpha_dash',
        ], [
            'email.required'        => 'Administrator email is required.',
            'passkey.required'      => 'A master access passkey/token is required.',
            'passkey.min'           => 'Passkey must be at least 6 characters.',
            'passkey.confirmed'     => 'The passkey confirmation does not match.',
        ]);

        $email = trim((string) $request->input('email'));
        $passkey = trim((string) $request->input('passkey'));
        $path = trim((string) $request->input('path', 'developer-panel')) ?: 'developer-panel';

        $saved = $this->service->saveSetupCredentials($email, $passkey, $path);

        if (!$saved) {
            return back()->with('error', 'Unable to write credentials to .env file. Please check file permissions.')
                ->withInput();
        }

        return redirect()->route('devzone.login')
            ->with('success', 'Master access passkey configured successfully! You can now log in.');
    }

    /**
     * Display the developer login form.
     */
    public function loginForm(Request $request)
    {
        // If first-time setup is needed
        $isSetupDone = (bool) config('devzone.setup_completed', false);
        $configuredKey = config('devzone.passkey');

        if (!$isSetupDone || empty($configuredKey)) {
            return redirect()->route('devzone.setup');
        }

        $sessionKey = config('devzone.session_key', 'devzone_authenticated_session');
        if ($request->session()->has($sessionKey) && $request->session()->get($sessionKey) === true) {
            return redirect()->route('devzone.index');
        }

        return view('devzone::login');
    }

    /**
     * Authenticate into DevZone with master passkey.
     */
    public function login(Request $request)
    {
        $isSetupDone = (bool) config('devzone.setup_completed', false);
        $expectedKey = (string) config('devzone.passkey');

        if (!$isSetupDone || empty($expectedKey)) {
            return redirect()->route('devzone.setup');
        }

        $throttleKey = 'devzone_login_' . $request->ip();
        $maxAttempts = (int) config('devzone.max_login_attempts', 5);
        $decayMinutes = (int) config('devzone.lockout_minutes', 10);

        if (RateLimiter::tooManyAttempts($throttleKey, $maxAttempts)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            $this->service->logActivity('Login Lockout', "IP {$request->ip()} locked out for {$seconds}s due to consecutive failures", 'error');

            return back()->with('error', "Too many failed attempts. Security lockout active for {$seconds} seconds.")
                ->withInput();
        }

        $request->validate([
            'passkey' => 'required|string',
        ], [
            'passkey.required' => 'Developer access key is required.',
        ]);

        $inputKey = trim((string) $request->input('passkey'));
        $expectedKey = (string) config('devzone.passkey');

        if (!empty($expectedKey) && hash_equals($expectedKey, $inputKey)) {
            RateLimiter::clear($throttleKey);
            $request->session()->regenerate();

            $request->session()->put(config('devzone.session_key'), true);
            $request->session()->put(config('devzone.session_auth_time'), time());

            $this->service->logActivity('Admin Login', 'Authorized developer authenticated successfully', 'success');

            return redirect()->route('devzone.index')
                ->with('success', 'Authenticated successfully. Welcome to NextSyntaxTech DevZone.');
        }

        RateLimiter::hit($throttleKey, $decayMinutes * 60);
        $attemptsLeft = RateLimiter::remaining($throttleKey, $maxAttempts);

        $maskedInput = substr($inputKey, 0, 3) . '***' . substr($inputKey, -3);
        $this->service->logActivity('Failed Login Attempt', "Invalid passkey [{$maskedInput}] from IP {$request->ip()}", 'warning');

        return back()->with('error', "Invalid Developer Access Key. ({$attemptsLeft} attempts remaining before temporary lockout).")
            ->withInput();
    }

    /**
     * Terminate developer session.
     */
    public function logout(Request $request)
    {
        $this->service->logActivity('Admin Logout', 'Developer logged out of DevZone', 'info');

        $request->session()->forget([
            config('devzone.session_key'),
            config('devzone.session_auth_time'),
        ]);

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('devzone.login')
            ->with('info', 'Developer session closed securely.');
    }

    /**
     * Main Dashboard View.
     */
    public function index(Request $request)
    {
        $systemInfo      = $this->service->getSystemInfo();
        $migrations      = $this->service->getMigrations();
        $seeders         = $this->service->getSeeders();
        $artisanCommands = config('devzone.allowed_commands', []);
        $backups         = $this->service->getBackups();
        $activityLogs    = $this->service->getActivityLogs(60);
        $apiDocInfo      = $this->service->getApiDocInfo();

        return view('devzone::index', compact(
            'systemInfo',
            'migrations',
            'seeders',
            'artisanCommands',
            'backups',
            'activityLogs',
            'apiDocInfo'
        ));
    }

    /**
     * Execute migrations (Single or All Pending).
     */
    public function runMigration(Request $request): JsonResponse
    {
        $mode = $request->input('mode', 'single');

        if ($mode === 'all') {
            $result = $this->service->runAllPendingMigrations();
            $statusCode = ($result['success'] ?? false) ? 200 : 422;
            return response()->json($result, $statusCode);
        }

        $path = $request->input('path');
        if (empty($path)) {
            return response()->json([
                'success' => false,
                'message' => 'Migration path is required.',
            ], 422);
        }

        $result = $this->service->runSingleMigration($path);
        $statusCode = ($result['success'] ?? false) ? 200 : 422;
        return response()->json($result, $statusCode);
    }

    /**
     * Execute selected Seeder.
     */
    public function runSeeder(Request $request): JsonResponse
    {
        $seederClass = $request->input('class');
        if (empty($seederClass)) {
            return response()->json([
                'success' => false,
                'message' => 'Seeder class name is required.',
            ], 422);
        }

        $result = $this->service->runSeeder($seederClass);
        $statusCode = ($result['success'] ?? false) ? 200 : 422;
        return response()->json($result, $statusCode);
    }

    /**
     * Execute whitelisted Artisan command.
     */
    public function runArtisan(Request $request): JsonResponse
    {
        $command = $request->input('command');
        if (empty($command)) {
            return response()->json([
                'success' => false,
                'message' => 'Command identifier is required.',
            ], 422);
        }

        $result = $this->service->runArtisanCommand($command);
        $statusCode = ($result['success'] ?? false) ? 200 : 422;
        return response()->json($result, $statusCode);
    }

    /**
     * Generate or regenerate Swagger or Scribe API documentation.
     */
    public function generateApiDocs(Request $request): JsonResponse
    {
        $driver = $request->input('driver', 'auto');
        $result = $this->service->generateApiDocumentation($driver);
        $statusCode = ($result['success'] ?? false) ? 200 : 500;
        return response()->json($result, $statusCode);
    }

    /**
     * Toggle APP_DEBUG configuration.
     */
    public function toggleDebug(Request $request): JsonResponse
    {
        try {
            $enable = filter_var($request->input('enable'), FILTER_VALIDATE_BOOLEAN);
            $result = $this->service->toggleAppDebug($enable);
            $statusCode = ($result['success'] ?? false) ? 200 : 500;
            return response()->json($result, $statusCode);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error toggling debug mode: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Generate new database backup.
     */
    public function createBackup(Request $request): JsonResponse
    {
        try {
            $result = $this->service->createDatabaseBackup();
            $statusCode = ($result['success'] ?? false) ? 200 : 500;
            return response()->json($result, $statusCode);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating database backup: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Download an authorized database backup.
     */
    public function downloadBackup(Request $request, string $filename): BinaryFileResponse|JsonResponse
    {
        $fullPath = $this->service->getBackupDownloadPath($filename);

        if (!$fullPath || !file_exists($fullPath)) {
            return response()->json([
                'success' => false,
                'message' => 'Requested database backup file not found or unauthorized.',
            ], 404);
        }

        return response()->download($fullPath, basename($fullPath), [
            'Content-Type'        => 'application/sql',
            'Content-Disposition' => 'attachment; filename="' . basename($fullPath) . '"',
        ]);
    }

    /**
     * Delete an existing database backup.
     */
    public function deleteBackup(Request $request, string $filename): JsonResponse
    {
        try {
            $deleted = $this->service->deleteBackup($filename);

            return response()->json([
                'success' => $deleted,
                'message' => $deleted ? "Backup {$filename} deleted." : "Failed to delete backup {$filename}.",
            ], $deleted ? 200 : 404);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting backup: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Clear panel activity logs.
     */
    public function clearLogs(Request $request): JsonResponse
    {
        try {
            $cleared = $this->service->clearActivityLogs();

            return response()->json([
                'success' => $cleared,
                'message' => 'Activity logs cleared successfully.',
            ], $cleared ? 200 : 500);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error clearing activity logs: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update access credentials after verifying current passkey.
     */
    public function updateCredentials(Request $request)
    {
        $throttleKey = 'devzone_cred_update_' . $request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            $this->service->logActivity('Security Lockout', "IP {$request->ip()} locked out from updating credentials for {$seconds}s", 'error');

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => "Too many verification attempts. Please wait {$seconds} seconds before trying again.",
                ], 429);
            }

            return back()->with('error', "Too many verification attempts. Please wait {$seconds} seconds before trying again.");
        }

        $request->validate([
            'current_passkey'          => 'required|string',
            'new_passkey'              => 'required|string|min:6|confirmed',
            'new_passkey_confirmation' => 'required|string',
            'admin_email'              => 'nullable|email',
        ], [
            'current_passkey.required'  => 'Current master passkey is required for verification.',
            'new_passkey.required'      => 'A new master passkey is required.',
            'new_passkey.min'           => 'New passkey must be at least 6 characters.',
            'new_passkey.confirmed'     => 'New passkey confirmation does not match.',
            'admin_email.email'         => 'Please provide a valid administrator email address.',
        ]);

        $currentPasskey = trim((string) $request->input('current_passkey'));
        $expectedPasskey = (string) config('devzone.passkey');

        if (empty($expectedPasskey) || !hash_equals($expectedPasskey, $currentPasskey)) {
            RateLimiter::hit($throttleKey, 600);
            $remaining = RateLimiter::remaining($throttleKey, 5);

            $this->service->logActivity('Security Warning', "Failed passkey change attempt: current passkey mismatch from IP {$request->ip()}", 'warning');

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => "Current master passkey does not match our records. ({$remaining} attempts remaining)",
                ], 422);
            }

            return back()->with('error', "Current master passkey does not match our records. ({$remaining} attempts remaining)");
        }

        $newPasskey = trim((string) $request->input('new_passkey'));
        if (hash_equals($expectedPasskey, $newPasskey)) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'New passkey cannot be the same as your current passkey.',
                ], 422);
            }

            return back()->with('error', 'New passkey cannot be the same as your current passkey.');
        }

        RateLimiter::clear($throttleKey);

        $email = $request->filled('admin_email') ? trim((string) $request->input('admin_email')) : null;
        $saved = $this->service->updateCredentials($newPasskey, $email);

        if (!$saved) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unable to persist new credentials to .env file. Please check file permissions.',
                ], 500);
            }

            return back()->with('error', 'Unable to persist new credentials to .env file. Please check file permissions.');
        }

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Master credentials have been updated successfully! Please use your new passkey on future logins.',
            ]);
        }

        return back()->with('success', 'Master credentials updated successfully! Please use your new passkey on future logins.');
    }

    /**
     * Check GitHub for latest release updates.
     */
    public function checkVersion(Request $request): JsonResponse
    {
        try {
            $info = $this->service->checkPackageUpdate();
            return response()->json($info);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Unable to check updates: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Execute package update routine.
     */
    public function updateVersion(Request $request): JsonResponse
    {
        try {
            $result = $this->service->updatePackage();
            return response()->json($result);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Update failed: ' . $e->getMessage(),
            ], 500);
        }
    }
}
