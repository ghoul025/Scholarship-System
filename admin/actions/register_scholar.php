<?php
session_start();
require '../../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['username'], $_POST['phone'], $_POST['sex'], $_POST['units'], $_POST['tuition_fee'], $_POST['course'], $_POST['year_level'], $_POST['scholarship_type']) &&
    (
        (isset($_POST['first_name']) && $_POST['first_name'] && isset($_POST['last_name']) && $_POST['last_name'])
    )
) {
    $username = trim($_POST['username']);

    // Title-case helper (preserve acronyms via mb_convert_case)
    if (!function_exists('to_title_case')) {
        function to_title_case($s) {
            $s = trim((string)$s);
            if ($s === '') return '';
            if (function_exists('mb_convert_case')) return mb_convert_case($s, MB_CASE_TITLE, 'UTF-8');
            return ucwords(strtolower($s));
        }
    }

    $first_name = to_title_case($_POST['first_name'] ?? '');
    $middle_name = to_title_case($_POST['middle_name'] ?? '');
    $last_name = to_title_case($_POST['last_name'] ?? '');
    $phone = trim($_POST['phone']);
    $sex = trim($_POST['sex']);
    $units = intval($_POST['units']);
    $tuition_fee = floatval($_POST['tuition_fee']);
    $course = trim($_POST['course']);
    $year_level = trim($_POST['year_level']);
    $scholarship_type = trim($_POST['scholarship_type']);
    $batch_input = trim($_POST['batch'] ?? '');

    // Validate required fields
    if (!$username || !$first_name || !$last_name || !$phone || !$sex || !$units || !$tuition_fee || !$course || !$year_level || !$scholarship_type) {
        $_SESSION['batch_error'] = 'All fields are required.';
        header('Location: ../manage_scholars.php');
        exit;
    }

    // Validate phone number (11 digits)
    if (!preg_match('/^09[0-9]{9}$/', $phone)) {
        $_SESSION['batch_error'] = 'Invalid phone number format.';
        header('Location: ../manage_scholars.php');
        exit;
    }

    // Validate batch (optional, must be a number or decimal with up to 2 decimal places)
    $batch = null;
    if ($batch_input !== '') {
        if (!preg_match('/^\d+(\.\d{1,2})?$/', $batch_input)) {
            $_SESSION['batch_error'] = 'Batch must be a number or decimal with up to 2 decimal places (e.g., 13 or 13.5).';
            header('Location: ../manage_scholars.php');
            exit;
        }
        $batch = number_format((float)$batch_input, 2, '.', '');
    }

    // Check for duplicate username
    // Check for duplicate username across users and applications
    $stmt = $conn->prepare('SELECT id FROM users WHERE username = ? LIMIT 1');
    $stmt->execute([$username]);
    if ($stmt->fetch()) {
        $_SESSION['batch_error'] = 'Username already exists in user accounts.';
        header('Location: ../manage_scholars.php');
        exit;
    }
    $stmtAppUser = $conn->prepare('SELECT id FROM scholar_applications WHERE username = ? LIMIT 1');
    $stmtAppUser->execute([$username]);
    if ($stmtAppUser->fetch()) {
        $_SESSION['batch_error'] = 'An application already exists with this username.';
        header('Location: ../manage_scholars.php');
        exit;
    }

    // Check phone/email duplicates across scholars and applications
    $stmtPhone = $conn->prepare('SELECT id FROM scholars WHERE phone = ? LIMIT 1');
    $stmtPhone->execute([$phone]);
    if ($stmtPhone->fetch()) {
        $_SESSION['batch_error'] = 'Phone number already registered to an existing scholar.';
        header('Location: ../manage_scholars.php');
        exit;
    }
    $stmtPhoneApp = $conn->prepare('SELECT id FROM scholar_applications WHERE phone = ? LIMIT 1');
    $stmtPhoneApp->execute([$phone]);
    if ($stmtPhoneApp->fetch()) {
        $_SESSION['batch_error'] = 'An application already exists with this phone number.';
        header('Location: ../manage_scholars.php');
        exit;
    }

    // If the admin form included email (it doesn't in the current form), check it too; otherwise skip
    if (!empty($_POST['email'])) {
        $email = trim($_POST['email']);
        $stmtEmail = $conn->prepare('SELECT id FROM scholars WHERE email = ? LIMIT 1');
        $stmtEmail->execute([$email]);
        if ($stmtEmail->fetch()) {
            $_SESSION['batch_error'] = 'Email already registered to an existing scholar.';
            header('Location: ../manage_scholars.php');
            exit;
        }
        $stmtEmailApp = $conn->prepare('SELECT id FROM scholar_applications WHERE email = ? LIMIT 1');
        $stmtEmailApp->execute([$email]);
        if ($stmtEmailApp->fetch()) {
            $_SESSION['batch_error'] = 'An application already exists with this email.';
            header('Location: ../manage_scholars.php');
            exit;
        }
    }

    // Generate password
    $password_plain = substr(str_shuffle('abcdefghjkmnpqrstuvwxyzABCDEFGHJKMNPQRSTUVWXYZ23456789'), 0, 8);
    $password_hash = password_hash($password_plain, PASSWORD_DEFAULT);

    // Create user
    $stmt = $conn->prepare('INSERT INTO users (username, password, role) VALUES (?, ?, ?)');
    $stmt->execute([$username, $password_hash, 'scholar']);
    $user_id = $conn->lastInsertId();

    // Create scholar (include optional batch as decimal)
    $stmt = $conn->prepare('
        INSERT INTO scholars (user_id, first_name, middle_name, last_name, phone, sex, units, tuition_fee, course, year_level, scholarship_type, batch) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ');
    $stmt->execute([$user_id, $first_name, $middle_name ?: null, $last_name, $phone, $sex, $units, $tuition_fee, $course, $year_level, $scholarship_type, $batch]);

    $scholar_id = $conn->lastInsertId();

    // Store credentials for export
    $stmt = $conn->prepare('INSERT INTO exported_credentials (scholar_id, username, password_plain) VALUES (?, ?, ?)');
    $stmt->execute([$scholar_id, $username, $password_plain]);

    // Add to session for export preview
    if (!isset($_SESSION['accounts'])) $_SESSION['accounts'] = [];
    $_SESSION['accounts'][] = [
        'username' => $username,
        'first_name' => $first_name,
        'middle_name' => $middle_name,
        'last_name' => $last_name,
        'password_plain' => $password_plain,
        'batch' => $batch
    ];

    $_SESSION['batch_message'] = 'Scholar registered successfully.';
    header('Location: ../manage_scholars.php');
    exit;
}

$_SESSION['batch_error'] = 'Invalid registration request.';
header('Location: ../manage_scholars.php');
exit;
?>