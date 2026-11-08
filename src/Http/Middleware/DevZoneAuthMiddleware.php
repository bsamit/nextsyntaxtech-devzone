<?php

namespace NextSyntaxTech\DevZone\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DevZoneAuthMiddleware
{
    /**
     * Handle an incoming request for DevZone Developer Utility Panel.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Check if panel is globally enabled
        if (!config('devzone.enabled', true)) {
            abort(404);
        }

        // 2. Check if first-time token setup is required
        $isSetupDone = config('devzone.setup_completed', true);
        $configuredKey = config('devzone.passkey');

        if (!$isSetupDone && empty($configuredKey)) {
            if ($request->routeIs('devzone.setup') || $request->routeIs('devzone.setup.submit')) {
                return $next($request);
            }
            return redirect()->route('devzone.setup');
        }

        $sessionKey = config('devzone.session_key', 'devzone_authenticated_session');
        $sessionTimeKey = config('devzone.session_auth_time', 'devzone_authenticated_at');
        $timeout = config('devzone.session_timeout', 3600);

        // 3. Verify Session Authentication
        if (!$request->session()->has($sessionKey) || $request->session()->get($sessionKey) !== true) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated developer session. Please authenticate with your access key.',
                ], 401);
            }

            return redirect()->route('devzone.login')
                ->with('warning', 'Please authenticate with your developer access key to proceed.');
        }

        // 4. Check Session Inactivity Timeout
        $authenticatedAt = (int) $request->session()->get($sessionTimeKey, 0);
        if ($authenticatedAt > 0 && (time() - $authenticatedAt) > $timeout) {
            $request->session()->forget([$sessionKey, $sessionTimeKey]);

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Developer session has expired due to inactivity. Please log in again.',
                ], 401);
            }

            return redirect()->route('devzone.login')
                ->with('warning', 'Developer session expired due to inactivity. Please re-enter your access key.');
        }

        // 5. Update Activity Timestamp
        $request->session()->put($sessionTimeKey, time());

        // 6. Security Response Headers (Prevent Browser Caching)
        $response = $next($request);

        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0, private');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        return $response;
    }
}
