<?php

// Autoloader (must be loaded first)
require_once __DIR__ . '/../vendor/autoload.php';

// Load environment variables
if (file_exists(__DIR__ . '/../.env')) {
    $dotenv = new Symfony\Component\Dotenv\Dotenv();
    $dotenv->load(__DIR__ . '/../.env');
} else {
    // Fallback to environment variables
    $_ENV['DB_HOST'] = $_ENV['DB_HOST'] ?? 'db';
    $_ENV['DB_NAME'] = $_ENV['DB_NAME'] ?? 'vaulty';
    $_ENV['DB_USER'] = $_ENV['DB_USER'] ?? 'vaulty_user';
    $_ENV['DB_PASS'] = $_ENV['DB_PASS'] ?? 'vaulty_pass';
    $_ENV['JWT_SECRET'] = $_ENV['JWT_SECRET'] ?? 'vaulty_secret_key_change_in_production';
    $_ENV['UPLOAD_DIR'] = $_ENV['UPLOAD_DIR'] ?? '/var/www/storage/uploads';
}

// Error handling
ini_set('display_errors', '0');
error_reporting(E_ALL);

set_error_handler(function($severity, $message, $file, $line) {
    if (!(error_reporting() & $severity)) {
        return;
    }
    throw new ErrorException($message, 0, $severity, $file, $line);
});

register_shutdown_function(function() {
    $error = error_get_last();
    if ($error && ($error['type'] === E_ERROR || $error['type'] === E_PARSE || $error['type'] === E_CORE_ERROR || $error['type'] === E_COMPILE_ERROR)) {
        http_response_code(500);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Critical server error',
            'error' => $_ENV['APP_DEBUG'] ?? false ? $error['message'] : 'Unknown error'
        ]);
        exit;
    }
});

// Main application
try {
    $router = new \Vaulty\Router();
    $router->dispatch();
} catch (\Exception $e) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Internal server error',
        'error' => $_ENV['APP_DEBUG'] ?? false ? $e->getMessage() : 'Unknown error'
    ]);
}