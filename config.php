<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'one_heart_db');

// Security settings
define('CSRF_TOKEN_SECRET', 'your-secret-key-here');
define('PASSWORD_HASH_COST', 12);

// Error reporting settings
define('ENVIRONMENT', 'development'); // Change to 'production' in live

// Create database connection
try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    $conn->set_charset("utf8mb4");
    
} catch (Exception $e) {
    if (ENVIRONMENT === 'development') {
        die("Database connection error: " . $e->getMessage());
    } else {
        error_log("Database connection error: " . $e->getMessage());
        die("A database error occurred. Please try again later.");
    }
}

// Security functions
function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validate_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Helper functions
function escape_string($string) {
    global $conn;
    return $conn->real_escape_string($string);
}

function close_connection() {
    global $conn;
    $conn->close();
}

// Set error reporting based on environment
if (ENVIRONMENT === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}
?>