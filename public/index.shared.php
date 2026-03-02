<?php

/**
 * Use this file when the Laravel app lives OUTSIDE public_html and you cannot
 * change the document root. Copy this to your document root as index.php.
 *
 * Setup: Put the full Laravel app in a folder next to public_html, e.g.:
 *   /home/username/glm/          <- app, bootstrap, config, .env, vendor, etc.
 *   /home/username/public_html/ <- copy contents of glm/public/ here, then use this as index.php
 *
 * If your app folder has a different name, change APP_BASE_PATH below.
 */

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Path to Laravel app (one folder up from public_html, folder name = glm)
$appBasePath = dirname(__DIR__) . '/glm';

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = $appBasePath . '/storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require $appBasePath . '/vendor/autoload.php';

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once $appBasePath . '/bootstrap/app.php';

$app->handleRequest(Request::capture());
