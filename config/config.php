<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set default timezone
date_default_timezone_set('Asia/Kolkata');

// Detect local environment via HTTP_HOST
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$is_local = ($host === 'localhost' || $host === '127.0.0.1' || strpos($host, 'localhost:') === 0 || strpos($host, '127.0.0.1:') === 0);

if ($is_local) {
    // Local settings (XAMPP)
    define('BASE_URL', '/wanderway/');
    
    // Show errors locally for easy debugging
    ini_set('display_errors', '1');
    error_reporting(E_ALL);

    $db_host = 'localhost';
    $db_user = 'root';
    $db_pass = '';
    $db_name = 'wanderway_db';
} else {
    // Live settings (Shared Hosting / InfinityFree)
    define('BASE_URL', '/');
    
    // Hide errors on live server
    ini_set('display_errors', '0');
    error_reporting(0);

    // Load credentials from config/live.php
    $live_config = __DIR__ . '/live.php';
    if (file_exists($live_config)) {
        $live_db = require $live_config;
        $db_host = $live_db['host'] ?? '';
        $db_user = $live_db['user'] ?? '';
        $db_pass = $live_db['pass'] ?? '';
        $db_name = $live_db['name'] ?? '';
    } else {
        die('Live configuration file (config/live.php) is missing.');
    }
}

// Create MySQL database connection using mysqli
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

// Check connection
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Set UTF-8 encoding
$conn->set_charset("utf8mb4");
