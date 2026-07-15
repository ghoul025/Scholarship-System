<?php
session_start();
require '../../config.php';
require '../../vendor/autoload.php'; // Assumes PHPMailer is installed via Composer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Helper: check if a column exists in a table in the current database
function columnExists($pdo, $table, $column) {
    try {
        $stmt = $pdo->prepare("SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ? LIMIT 1");
        $stmt->execute([$table, $column]);
        return (bool) $stmt->fetchColumn();
    } catch (\Throwable $e) {
        return false; // On any error, be conservative and say the column does not exist
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['application_id']) && isset($_POST['csrf_token'])) {
    try {
        // Validate CSRF token
        if (!validate_csrf($_POST['csrf_token'])) {
            throw new Exception("Invalid CSRF token.");
        }

        $app_id = intval($_POST['application_id']);
        $stmt = $conn->prepare("SELECT * FROM scholar_applications WHERE id = ?");
        $stmt->execute([$app_id]);
        $app = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$app) {
            throw new Exception("Application not found.");
        }

        // Validate required fields
        if (
            !$app['username'] || !$app['first_name'] || !$app['last_name'] || !$app['email'] ||
            !$app['phone'] || !$app['sex'] || !$app['units'] || !$app['tuition_fee'] ||
            !$app['course'] || !$app['year_level'] || !$app['scholarship_type']
        ) {
            throw new Exception("Incomplete application data.");
        }

        // Validate email (must end with @gmail.com)
        if (!filter_var($app['email'], FILTER_VALIDATE_EMAIL) || !preg_match('/@gmail\.com$/', $app['email'])) {
            throw new Exception("Invalid email format. Must be a Gmail address (e.g., user@gmail.com).");
        }

        // Validate phone (11 digits, starts with 09)
        if (!preg_match('/^09[0-9]{9}$/', $app['phone'])) {
            throw new Exception("Invalid phone number format. Must be 11 digits starting with 09.");
        }

        // Validate extended_name (optional, max 120 characters)
        if ($app['extended_name'] && strlen($app['extended_name']) > 120) {
            throw new Exception("Extended name exceeds 120 characters.");
        }

        // Check duplicates across users/scholars and other pending applications
        $checkUser = $conn->prepare("SELECT id FROM users WHERE username = ? LIMIT 1");
        $checkUser->execute([$app['username']]);
        if ($checkUser->fetch()) {
            throw new Exception("Username already exists in the system. Ask the applicant to choose a different username.");
        }

        // If users table has email field, check it as well (some installs may store email on users)
        if (columnExists($conn, 'users', 'email')) {
            $checkUserEmail = $conn->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
            $checkUserEmail->execute([$app['email']]);
            if ($checkUserEmail->fetch()) {
                throw new Exception("Email already exists in the user accounts. Please verify before approving.");
            }
        }

        $checkPhoneScholar = $conn->prepare("SELECT id FROM scholars WHERE phone = ? LIMIT 1");
        $checkPhoneScholar->execute([$app['phone']]);
        if ($checkPhoneScholar->fetch()) {
            throw new Exception("Phone number already exists for an existing scholar.");
        }

        // Only check scholars.email if that column exists
        if (columnExists($conn, 'scholars', 'email')) {
            $checkEmailScholar = $conn->prepare("SELECT id FROM scholars WHERE email = ? LIMIT 1");
            $checkEmailScholar->execute([$app['email']]);
            if ($checkEmailScholar->fetch()) {
                throw new Exception("Email already registered to an existing scholar account.");
            }
        }

        // Check other pending applications (exclude current application id)
        $checkOtherApps = $conn->prepare("SELECT id FROM scholar_applications WHERE (username = ? OR email = ? OR phone = ?) AND id != ? LIMIT 1");
        $checkOtherApps->execute([$app['username'], $app['email'], $app['phone'], $app_id]);
        if ($checkOtherApps->fetch()) {
            throw new Exception("Another application with the same username/email/phone exists. Resolve duplicates before approving.");
        }

        // Generate plain password
        $plain_password = bin2hex(random_bytes(4)); // 8 hexadecimal characters
        $hashed_password = password_hash($plain_password, PASSWORD_BCRYPT);

        // Insert into users
        $insert_user = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, 'scholar')");
        $insert_user->execute([$app['username'], $hashed_password]);
        $user_id = $conn->lastInsertId();

        // Insert into scholars
        $insert_scholar = $conn->prepare(
            "INSERT INTO scholars (user_id, first_name, middle_name, last_name, extended_name, email, course, year_level, scholarship_type, phone, sex, units, tuition_fee, batch) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );
        $insert_scholar->execute([
            $user_id, $app['first_name'], $app['middle_name'], $app['last_name'],
            $app['extended_name'] ?: null, $app['email'], $app['course'], $app['year_level'],
            $app['scholarship_type'], $app['phone'], $app['sex'], $app['units'],
            $app['tuition_fee'], $app['batch']
        ]);
        $scholar_id = $conn->lastInsertId();

        // Insert into exported_credentials
        $insert_cred = $conn->prepare("INSERT INTO exported_credentials (scholar_id, password_plain) VALUES (?, ?)");
        $insert_cred->execute([$scholar_id, $plain_password]);

        // Send email notification using PHPMailer
        $mail = new PHPMailer(true);
        try {
            // SMTP configuration for Gmail
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = SMTP_USERNAME; // From config.php
            $mail->Password = SMTP_PASSWORD; // From config.php
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port = 465;

            // Email content
            $mail->setFrom(SMTP_USERNAME, 'Scholarship System');
            $mail->addAddress($app['email']);
            $mail->isHTML(true);
            $mail->Subject = 'Scholar Account Created';
            $full_name = trim($app['first_name'] . ' ' . ($app['middle_name'] ? $app['middle_name'] . ' ' : '') . $app['last_name'] . ($app['extended_name'] ? ' ' . $app['extended_name'] : ''));
            $mail->Body = "
                <h2>Scholar Account Created</h2>
                <p>Dear {$full_name},</p>
                <p>Your scholarship application has been approved. Below are your account credentials:</p>
                <ul>
                    <li><strong>Username:</strong> {$app['username']}</li>
                    <li><strong>Password:</strong> {$plain_password}</li>
                </ul>
                <p>Please log in to the scholarship system to access your account. Change your password after logging in for security.</p>
                <p>Best regards,<br>Scholarship System Team</p>
            ";
            $mail->AltBody = "Dear {$full_name},\n\nYour scholarship application has been approved.\nUsername: {$app['username']}\nPassword: {$plain_password}\n\nPlease log in to the scholarship system to access your account. Change your password after logging in for security.\n\nBest regards,\nScholarship System Team";

            $mail->send();
        } catch (Exception $e) {
            throw new Exception("Scholar account created, but email notification failed: {$mail->ErrorInfo}");
        }

        // Delete application
        $delete = $conn->prepare("DELETE FROM scholar_applications WHERE id = ?");
        $delete->execute([$app_id]);

        // Regenerate CSRF token after successful submission
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

        $success_message = "Application approved. Scholar account created. Password: $plain_password. Email notification sent to {$app['email']}.";
    } catch (Exception $e) {
        $error_message = $e->getMessage();
    }
} else {
    $error_message = "Invalid request.";
}

// Detect AJAX request
$isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
    || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false)
    || (isset($_POST['ajax']) && $_POST['ajax'] == '1');

if ($isAjax) {
    header('Content-Type: application/json');
    if (!empty($error_message)) {
        // Return error message
        echo json_encode(['success' => false, 'message' => $error_message]);
        exit;
    }
    // Return success and new CSRF token so client can update it
    $new_token = $_SESSION['csrf_token'] ?? '';
    echo json_encode(['success' => true, 'message' => $success_message ?? 'Application approved.', 'password' => $plain_password ?? null, 'csrf_token' => $new_token]);
    exit;
}

// Non-AJAX fallback: set session messages and redirect
if (!empty($error_message)) {
    $_SESSION['batch_error'] = $error_message;
} else {
    $_SESSION['batch_message'] = $success_message;
}

// Build a robust redirect URL to avoid 404s on different hosts (e.g., Hostinger)
// If BASE_URL is an absolute URL (starts with http), use it directly.
$redirect_path = '/admin/manage_applications.php';
$location = BASE_URL;
if (!preg_match('#^https?://#i', BASE_URL)) {
    // Build scheme and host from server variables
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? ($_SERVER['SERVER_NAME'] ?? '');
    // Normalize slashes
    $base = rtrim($location, '/');
    $location = $scheme . '://' . $host . ($base !== '' ? $base : '');
}
$location = rtrim($location, '/') . $redirect_path;
header("Location: " . $location);
exit;
?>