<?php

/**
 * Use this when the FULL Laravel app (app/, bootstrap/, config/, vendor/, etc.)
 * and the contents of public/ are ALL directly inside your document root (e.g. public_html).
 *
 * Copy this file to your document root as index.php (replacing the default).
 * Paths use __DIR__ so vendor, bootstrap, storage are in the same folder as index.php.
 */

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

$base = __DIR__;

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = $base . '/storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require $base . '/vendor/autoload.php';

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once $base . '/bootstrap/app.php';

$app->handleRequest(Request::capture());
