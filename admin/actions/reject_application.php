<?php
session_start();
require '../../config.php';
require '../../vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['application_id']) && isset($_POST['csrf_token'])) {
    try {
        // Validate CSRF token
        if (!validate_csrf($_POST['csrf_token'])) {
            throw new Exception('Invalid CSRF token.');
        }

        $app_id = intval($_POST['application_id']);

        // Fetch application to obtain email and name for notification
        $stmt = $conn->prepare("SELECT * FROM scholar_applications WHERE id = ?");
        $stmt->execute([$app_id]);
        $app = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$app) {
            throw new Exception('Application not found.');
        }

        // Send rejection email if email present
        if (!empty($app['email']) && filter_var($app['email'], FILTER_VALIDATE_EMAIL)) {
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = SMTP_USERNAME;
                $mail->Password = SMTP_PASSWORD;
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                $mail->Port = 465;

                $mail->setFrom(SMTP_USERNAME, 'Scholarship System');
                $mail->addAddress($app['email']);
                $mail->isHTML(true);
                $full_name = trim($app['first_name'] . ' ' . ($app['middle_name'] ? $app['middle_name'] . ' ' : '') . $app['last_name']);
                $mail->Subject = 'Scholarship Application Update';
                $mail->Body = "<h2>Application Update</h2><p>Dear {$full_name},</p><p>We regret to inform you that your scholarship application has been rejected. If you have questions, please contact the scholarship office for more details.</p><p>Regards,<br>Scholarship Office</p>";
                $mail->AltBody = "Dear {$full_name},\n\nWe regret to inform you that your scholarship application has been rejected. If you have questions, please contact the scholarship office for more details.\n\nRegards,\nScholarship Office";

                $mail->send();
            } catch (Exception $e) {
                // Email failed — surface error (mirror approve behavior)
                throw new Exception('Application could not be rejected: email notification failed. ' . $mail->ErrorInfo);
            }
        }

        // Delete application after successful email (or if no valid email)
        $delete = $conn->prepare("DELETE FROM scholar_applications WHERE id = ?");
        $delete->execute([$app_id]);

        // Regenerate CSRF token after successful action
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

        $success_message = "Application rejected and deleted.";
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
        echo json_encode(['success' => false, 'message' => $error_message]);
        exit;
    }
    $new_token = $_SESSION['csrf_token'] ?? '';
    echo json_encode(['success' => true, 'message' => $success_message ?? 'Deleted.', 'csrf_token' => $new_token]);
    exit;
}

// Non-AJAX fallback
if (!empty($error_message)) {
    $_SESSION['batch_error'] = $error_message;
} else {
    $_SESSION['batch_message'] = $success_message;
}

// Build a robust redirect URL similar to approve_application.php
$redirect_path = '/admin/manage_applications.php';
$location = BASE_URL;
if (!preg_match('#^https?://#i', BASE_URL)) {
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? ($_SERVER['SERVER_NAME'] ?? '');
    $base = rtrim($location, '/');
    $location = $scheme . '://' . $host . ($base !== '' ? $base : '');
}
$location = rtrim($location, '/') . $redirect_path;
header("Location: " . $location);
exit;
?>