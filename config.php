<?php
// Database configuration and connection
// Provides both $conn and $pdo for backward compatibility
if (!isset($conn) || !isset($pdo)) {
    $host = 'localhost';
    $dbname = 'scholarship_system';
    $username = 'root'; // Default XAMPP username
    $password = '';
    $charset = 'utf8mb4';
    $dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    try {
        $conn = new PDO($dsn, $username, $password, $options);
        $pdo = $conn; // Alias for compatibility
    } catch (PDOException $e) {
        // Log error securely in production
        error_log('DB Connection failed: ' . $e->getMessage());
        die('Database connection error. Please contact the administrator.');
    }
}

// ------------------- CSRF Token Setup -------------------
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Generate CSRF token if not set
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Helper function to validate CSRF token
function validate_csrf($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}


// ------------------- Base URL for Redirects -------------------
define('BASE_URL', '/scholarship_system'); // adjust to your root
?>
