<?php
/**
 * TEMPORARY SETUP ROUTE
 * 
 * Gunakan ini jika shared hosting TIDAK punya SSH.
 * Akses: https://yourdomain.com/setup-app/GANTI_TOKEN_INI_RANDOM_STRING
 * 
 * ⚠️ WAJIB HAPUS FILE INI SETELAH SETUP SELESAI!
 */

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;

Route::get('/setup-app/{token}', function ($token) {
    // Ganti token ini dengan string random Anda sendiri
    if ($token !== 'GANTI_TOKEN_INI_RANDOM_STRING') {
        abort(404);
    }

    $output = [];

    try {
        // Generate key if not set
        Artisan::call('key:generate', ['--force' => true]);
        $output[] = '✅ APP_KEY generated';
    } catch (\Exception $e) {
        $output[] = '⚠️ key:generate - ' . $e->getMessage();
    }

    try {
        // Run migrations
        Artisan::call('migrate', ['--force' => true]);
        $output[] = '✅ Migrations completed';
    } catch (\Exception $e) {
        $output[] = '❌ migrate - ' . $e->getMessage();
    }

    try {
        // Seed essential data
        Artisan::call('db:seed', ['--force' => true]);
        $output[] = '✅ All seeders completed';
    } catch (\Exception $e) {
        $output[] = '❌ db:seed - ' . $e->getMessage();
    }

    try {
        // Cache configuration
        Artisan::call('config:cache');
        Artisan::call('route:cache');
        Artisan::call('view:cache');
        $output[] = '✅ Config/route/view cached';
    } catch (\Exception $e) {
        $output[] = '⚠️ cache - ' . $e->getMessage();
    }

    try {
        // Storage link
        Artisan::call('storage:link');
        $output[] = '✅ Storage linked';
    } catch (\Exception $e) {
        $output[] = '⚠️ storage:link - ' . $e->getMessage();
    }

    $output[] = '';
    $output[] = '====================================';
    $output[] = '⚠️ SETUP SELESAI!';
    $output[] = '⚠️ HAPUS FILE routes/setup.php SEKARANG!';
    $output[] = '⚠️ Dan kembalikan bootstrap/app.php!';
    $output[] = '====================================';
    $output[] = '';
    $output[] = 'Login: admin@gmail.com / password';
    $output[] = 'SEGERA GANTI PASSWORD SETELAH LOGIN!';

    return '<pre style="font-family:monospace;font-size:14px;padding:20px;background:#1a1a2e;color:#e0e0e0;border-radius:8px;">' . implode("\n", $output) . '</pre>';
});
