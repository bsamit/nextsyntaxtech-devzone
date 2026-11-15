<?php

namespace NextSyntaxTech\DevZone\Http\Controllers;

use Illuminate\Routing\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DevZoneAssetController extends Controller
{
    /**
     * Serve local package assets directly (offline, zero-CDN mode).
     *
     * @param string $path
     * @return BinaryFileResponse
     */
    public function serve(string $path)
    {
        $path = ltrim($path, '/\\');

        // Prevent path traversal
        if (str_contains($path, '..')) {
            abort(404);
        }

        $basePath = realpath(__DIR__ . '/../../../resources/assets');
        $fullPath = realpath($basePath . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path));

        if (!$basePath || !$fullPath || !str_starts_with($fullPath, $basePath) || !file_exists($fullPath) || is_dir($fullPath)) {
            abort(404);
        }

        $extension = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));
        $mimeTypes = [
            'css'   => 'text/css; charset=utf-8',
            'js'    => 'application/javascript; charset=utf-8',
            'woff2' => 'font/woff2',
            'woff'  => 'font/woff',
            'ttf'   => 'font/ttf',
            'svg'   => 'image/svg+xml',
            'png'   => 'image/png',
            'jpg'   => 'image/jpeg',
            'jpeg'  => 'image/jpeg',
            'gif'   => 'image/gif',
            'ico'   => 'image/x-icon',
            'json'  => 'application/json',
        ];

        $contentType = $mimeTypes[$extension] ?? 'application/octet-stream';

        return response()->file($fullPath, [
            'Content-Type'  => $contentType,
            'Cache-Control' => 'public, max-age=31536000, immutable',
        ]);
    }
}
