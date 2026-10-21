<?php
/**
 * TRACE — Lost & Found Intelligence Network
 * Front Controller
 */

declare(strict_types=1);

// Error reporting configuration
error_reporting(E_ALL);
ini_set('display_errors', '0');

// Autoload core framework and application classes
require_once dirname(__DIR__) . '/app/Core/Autoloader.php';
\App\Core\Autoloader::register();

use App\Core\Request;
use App\Core\Session;

try {
    // Start session with secure cookies
    Session::start();

    // Instantiate incoming request
    $request = new Request();

    // Load web routes
    $router = require dirname(__DIR__) . '/routes/web.php';

    // Dispatch request through router & middleware pipeline
    $router->dispatch($request);

} catch (\Throwable $e) {
    error_log("TRACE Exception [{$e->getFile()}:{$e->getLine()}]: " . $e->getMessage() . "\n" . $e->getTraceAsString());
    
    http_response_code(500);
    
    $config = @include dirname(__DIR__) . '/config/app.php';
    $debug = $config['debug'] ?? true;

    if ($debug) {
        echo "<!DOCTYPE html><html lang='en'><head><title>TRACE System Error</title>";
        echo "<style>body{font-family:monospace;background:#111310;color:#F4F2EA;padding:2rem;}pre{background:#1B1E19;padding:1rem;border:1px solid #D7FF64;overflow-x:auto;}</style></head><body>";
        echo "<h1 style='color:#D7FF64;'>TRACE &mdash; Runtime Diagnostic Exception</h1>";
        echo "<p><strong>Message:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
        echo "<p><strong>Location:</strong> " . htmlspecialchars($e->getFile()) . " on line " . $e->getLine() . "</p>";
        echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
        echo "</body></html>";
    } else {
        echo "<!DOCTYPE html><html lang='en'><head><title>System Error &mdash; TRACE</title></head><body style='font-family:sans-serif;background:#111310;color:#F4F2EA;text-align:center;padding:5rem;'>";
        echo "<h1>TRACE Intelligence Network</h1><p>An unexpected system error occurred. Technical diagnostics have been recorded.</p>";
        echo "<a href='/' style='color:#D7FF64;'>Return to Safety</a></body></html>";
    }
}
