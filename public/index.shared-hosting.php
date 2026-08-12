<?php
/**
 * SHARED HOSTING index.php
 * 
 * Upload file ini ke public_html/ di shared hosting.
 * Sesuaikan path '../cwa-app/' dengan nama folder aplikasi Anda.
 * 
 * JANGAN gunakan file ini di development lokal.
 */

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/../cwa-app/storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__.'/../cwa-app/vendor/autoload.php';

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once __DIR__.'/../cwa-app/bootstrap/app.php';

// Tell Laravel that public_html is the public directory
// This fixes: "Vite manifest not found at cwa-app/public/build/manifest.json"
$app->usePublicPath(__DIR__);

$app->handleRequest(Request::capture());
