<?php
require 'config.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Ensure $pdo is defined for DB access
if (!isset($pdo) && isset($conn)) {
    $pdo = $conn;
}
if (!isset($pdo)) {
    try {
        $pdo = new PDO('mysql:host=localhost;dbname=scholarship_system', 'root', '');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database connection failed.']);
        exit;
    }
}

// --- Brute-force protection ---
if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = 0;
    $_SESSION['last_attempt_time'] = 0;
}

$lockout_duration = 600; // 10 minutes
$max_attempts = 10;

// Handle POST login
$response = ['success' => false, 'message' => 'Invalid login'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';

    // Check lockout
    if ($_SESSION['login_attempts'] >= $max_attempts && (time() - $_SESSION['last_attempt_time']) < $lockout_duration) {
        $remaining = $lockout_duration - (time() - $_SESSION['last_attempt_time']);
        echo json_encode(['success' => false, 'message' => "Too many login attempts. Try again in {$remaining} seconds."]);
        exit;
    }

    // Fetch user
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Dummy hash to prevent timing attacks
    $dummy_hash = '$2y$10$usesomesillystringforsalt$';

    if ($user && password_verify($password, $user['password'])) {
        // Success: regenerate session ID
        session_regenerate_id(true);

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['login_attempts'] = 0; // reset attempts

        // Determine redirect
        if ($user['role'] === 'admin') {
            if (!empty($user['main_admin']) && $user['main_admin'] == 1) {
                $_SESSION['user_type'] = 'main_admin';
                $redirect = 'admin/main_admin_dashboard.php';
            } else {
                $_SESSION['user_type'] = 'admin';
                $redirect = 'admin/dashboard.php';
            }
        } elseif ($user['role'] === 'scholar') {
            $_SESSION['user_type'] = 'scholar';
            $redirect = 'dashboard.php';
        } else {
            $redirect = 'index.php';
        }

        echo json_encode(['success' => true, 'redirect' => $redirect]);
        exit;

    } else {
        // Failed login
        password_verify($password, $dummy_hash); // always call to avoid timing attacks
        $_SESSION['login_attempts'] += 1;
        $_SESSION['last_attempt_time'] = time();
        $response['message'] = 'Invalid username or password';
    }
}

// JSON response
header('Content-Type: application/json');
echo json_encode($response);
