<?php
// Start session (must be called before any output)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Enable full error reporting and logging for development
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', 'c:/xampp/php/logs/php_error.log');

// --- Configuration ---
// Using the full path to ensure config file is found
$appDir = dirname(__DIR__);
require_once $appDir . '/config/config.php';

// Verify constants are defined
if (!defined('APPROOT')) {
    die('Critical error: APPROOT constant is not defined.');
}

// --- Core Libraries & Helpers ---
// Load session helper early
require_once $appDir . '/core/functions.php';
require_once $appDir . '/helpers/SessionHelper.php';
require_once $appDir . '/models/Database.php';    // Load Database class
require_once $appDir . '/core/Controller.php';   // Load Base Controller
require_once $appDir . '/core/Router.php';       // Load Router

// --- Autoloader (Simple PSR-4ish) ---
// Automatically loads classes from controllers, models, and helpers directories
spl_autoload_register(function ($className) {
    // Define base directories for autoloading
    $baseDirs = [
        'controllers' => APPROOT . '/controllers/',
        'models'      => APPROOT . '/models/',
        'helpers'     => APPROOT . '/helpers/',
        // Add other directories like 'libraries' if needed
    ];

    // Check each base directory for the class file
    foreach ($baseDirs as $dir) {
        $file = $dir . $className . '.php';
        if (file_exists($file)) {
            require_once $file;
            return; // Stop searching once found
        }
    }
});

// --- Error Reporting (Development vs Production) ---
// Show all errors during development
// error_reporting(E_ALL);
// ini_set('display_errors', 1);
// In production, log errors instead:
// error_reporting(0);
// ini_set('display_errors', 0);
// ini_set('log_errors', 1);
// ini_set('error_log', APPROOT . '/../logs/php_error.log'); // Ensure logs directory exists and is writable

// --- Instantiate Router & Dispatch ---
try {
    $router = new Router();
    $router->dispatch();
} catch (Exception $e) {
    // Catch potential exceptions during routing/controller instantiation
    error_log("Unhandled Exception: " . $e->getMessage() . "\n" . $e->getTraceAsString());
    // Display a generic error message to the user in production
    // In development, you might want to show more details
    http_response_code(500);
    echo "<h1>An unexpected error occurred</h1>";
    echo "<p>We are sorry for the inconvenience. Please try again later.</p>";
    // Optionally include a simple error view
    // require_once APPROOT . '/views/pages/errors/500.php';
}