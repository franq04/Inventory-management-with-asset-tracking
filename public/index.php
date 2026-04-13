<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

if (isset($_SERVER['REQUEST_URI'], $_SERVER['SCRIPT_NAME'])) {
    $requestPath = parse_url((string) $_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $scriptName = (string) $_SERVER['SCRIPT_NAME'];
    $scriptDir = str_replace('\\', '/', dirname($scriptName));

    if (is_string($requestPath) && $scriptDir !== '' && $scriptDir !== '.' && $scriptDir !== '/') {
        if (preg_match('#^' . preg_quote($scriptDir, '#') . '#i', $requestPath, $matches) === 1) {
            $normalizedDir = rtrim($matches[0], '/');
            $normalizedScriptName = $normalizedDir . '/index.php';

            $_SERVER['SCRIPT_NAME'] = $normalizedScriptName;
            $_SERVER['PHP_SELF'] = $normalizedScriptName;
        }
    }
}

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__.'/../vendor/autoload.php';

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once __DIR__.'/../bootstrap/app.php';

$app->handleRequest(Request::capture());
