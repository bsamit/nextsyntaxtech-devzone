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
        $isSetupDone = config('devzone.setup_completed', true);

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
        $isSetupDone = config('devzone.setup_completed', true);

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
        $isSetupDone = config('devzone.setup_completed', true);
        $configuredKey = config('devzone.passkey');

        if (!$isSetupDone && empty($configuredKey)) {
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
}
