<?php

use Illuminate\Support\Facades\Route;
use NextSyntaxTech\DevZone\Http\Controllers\DevZoneController;

$prefix = config('devzone.path', 'developer-panel');

Route::prefix($prefix)->group(function () {
    // -------------------------------------------------------------
    // Public Authentication & Setup Wall
    // -------------------------------------------------------------
    Route::middleware(['web'])->group(function () {
        // First-Time Setup Wizard
        Route::get('/setup', [DevZoneController::class, 'setupForm'])->name('devzone.setup');
        Route::post('/setup', [DevZoneController::class, 'setupSubmit'])->name('devzone.setup.submit');

        // Master Developer Login & Logout
        Route::get('/login', [DevZoneController::class, 'loginForm'])->name('devzone.login');
        Route::post('/login', [DevZoneController::class, 'login'])->name('devzone.login.submit');
        Route::post('/logout', [DevZoneController::class, 'logout'])->name('devzone.logout');
    });

    // -------------------------------------------------------------
    // Protected Developer Zone Actions (Requires Valid Session)
    // -------------------------------------------------------------
    Route::middleware(['web', 'devzone.auth'])->as('devzone.')->group(function () {
        Route::get('/', [DevZoneController::class, 'index'])->name('index');

        // Migrations
        Route::post('/migrations/run', [DevZoneController::class, 'runMigration'])->name('migration.run');

        // Seeders
        Route::post('/seeders/run', [DevZoneController::class, 'runSeeder'])->name('seeder.run');

        // Safe Artisan Commands
        Route::post('/artisan/run', [DevZoneController::class, 'runArtisan'])->name('artisan.run');

        // API Documentation (Swagger & Scribe)
        Route::post('/docs/generate', [DevZoneController::class, 'generateApiDocs'])->name('docs.generate');
        Route::post('/swagger/generate', [DevZoneController::class, 'generateApiDocs'])->name('swagger.generate');

        // Live Debug Mode Toggle
        Route::post('/debug/toggle', [DevZoneController::class, 'toggleDebug'])->name('debug.toggle');

        // Database Backup, Download & Delete
        Route::post('/backup/create', [DevZoneController::class, 'createBackup'])->name('backup.create');
        Route::get('/backup/download/{filename}', [DevZoneController::class, 'downloadBackup'])->name('backup.download');
        Route::delete('/backup/delete/{filename}', [DevZoneController::class, 'deleteBackup'])->name('backup.delete');

        // Activity Logs
        Route::post('/logs/clear', [DevZoneController::class, 'clearLogs'])->name('logs.clear');
    });
});
