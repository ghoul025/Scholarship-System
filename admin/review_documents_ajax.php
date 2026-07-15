<?php
session_start();
require '../config.php';
header('Content-Type: application/json');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']); 
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$document_ids = $data['document_ids'] ?? [];
// Sanitize incoming IDs: ensure integers and remove non-positive values
$document_ids = array_values(array_filter(array_map('intval', (array)$document_ids), function($v){ return $v > 0; }));
$action = $data['action'] ?? null;
$sms_enabled = isset($data['sms_enabled']) ? (bool)$data['sms_enabled'] : true;

// PHPMailer (composer autoload expected in vendor/)
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
} else {
    // If composer autoload isn't available, we'll still proceed but emails will be skipped.
}

if (!$document_ids || !in_array($action, ['approve', 'reject', 'toggle_physical'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid input']); 
    exit;
}

$updated = [];

// Helper: send email via PHPMailer. Returns true on success, false on failure.
function sendDocumentEmail($toEmail, $toName, $subject, $htmlBody) {
    if (!class_exists('PHPMailer\\PHPMailer\\PHPMailer')) {
        // PHPMailer not available
        error_log('PHPMailer not available - skipping email to ' . $toEmail);
        return false;
    }

    $mail = new PHPMailer(true);
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_USERNAME; // defined in config.php
        $mail->Password = SMTP_PASSWORD; // defined in config.php
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Recipients
        $mail->setFrom(SMTP_USERNAME, 'Scholarship Office');
        $mail->addAddress($toEmail, $toName);

        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $htmlBody;
        $mail->AltBody = strip_tags(str_replace(['<br>', '<br/>', '<br />'], "\n", $htmlBody));

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log('Mail error: ' . $mail->ErrorInfo);
        return false;
    }
}



// Toggle physical copy
if ($action === 'toggle_physical') {
    $confirmed = isset($data['confirmed']) ? (int)$data['confirmed'] : 0;
    if (empty($document_ids)) {
        echo json_encode(['success' => false, 'message' => 'Invalid document id']);
        exit;
    }
    $stmt = $pdo->prepare("UPDATE documents SET physical_copy_confirmed = ? WHERE id = ?");
    $success = $stmt->execute([$confirmed, $document_ids[0]]);
    // Recalculate counts to keep UI in sync
    $counts = $pdo->query("SELECT 
            COUNT(CASE WHEN status='Pending' THEN 1 END) AS pending,
            COUNT(CASE WHEN status='Approved' THEN 1 END) AS approved,
            COUNT(CASE WHEN status='Rejected' THEN 1 END) AS rejected
        FROM documents")->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => $success,
        'updated' => [['id' => $document_ids[0], 'physical_copy_confirmed' => $confirmed]],
        'counts' => $counts
    ]);
    exit;
}

// Common placeholder for IDs
$placeholders = implode(',', array_fill(0, count($document_ids), '?'));

// APPROVE FLOW
if ($action === 'approve') {
    // Approve the provided document IDs in the documents table
    $stmt = $pdo->prepare("UPDATE documents SET status = 'Approved' WHERE id IN ($placeholders)");
    $stmt->execute($document_ids);

    // Fetch scholar details for notifications
    $stmt = $pdo->prepare("SELECT d.id, d.document_type, s.first_name, s.phone, s.email, s.id AS scholar_id
                   FROM documents d
                   JOIN scholars s ON d.scholar_id = s.id
                   WHERE d.id IN ($placeholders)");
    $stmt->execute($document_ids);
    $scholars = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($scholars as $sch) {
        if (!empty($sch['email'])) {
            $subject = 'Document Approved: ' . $sch['document_type'];
            $body = "<p>Dear " . htmlspecialchars($sch['first_name']) . ",</p>\n" .
                    "<p>Your document <strong>" . htmlspecialchars($sch['document_type']) . "</strong> has been <strong>Approved</strong> by the scholarship office.</p>\n" .
                    "<p>If you have questions, please reply to this email or contact the office.</p>\n" .
                    "<p>Regards,<br/>Scholarship Office</p>";
            sendDocumentEmail($sch['email'], $sch['first_name'], $subject, $body);
        }
        $updated[] = [
            'id'          => $sch['id'],
            'status'      => 'Approved',
            'badge_class' => 'bg-success'
        ];
    }
}

// REJECT FLOW
elseif ($action === 'reject') {
    // Reject the provided document IDs in the documents table
    $stmt = $pdo->prepare("UPDATE documents SET status = 'Rejected' WHERE id IN ($placeholders)");
    $stmt->execute($document_ids);

    // Fetch scholar details for notifications
    $stmt = $pdo->prepare("SELECT d.id, d.document_type, s.first_name, s.phone, s.email, s.id AS scholar_id
                   FROM documents d
                   JOIN scholars s ON d.scholar_id = s.id
                   WHERE d.id IN ($placeholders)");
    $stmt->execute($document_ids);
    $scholars = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($scholars as $sch) {
        if (!empty($sch['email'])) {
            $subject = 'Document Rejected: ' . $sch['document_type'];
            $body = "<p>Dear " . htmlspecialchars($sch['first_name']) . ",</p>\n" .
                    "<p>Your document <strong>" . htmlspecialchars($sch['document_type']) . "</strong> has been <strong>Rejected</strong>. Please review the requirements and re-submit if applicable.</p>\n" .
                    "<p>If you need assistance, contact the scholarship office.</p>\n" .
                    "<p>Regards,<br/>Scholarship Office</p>";
            sendDocumentEmail($sch['email'], $sch['first_name'], $subject, $body);
        }
        $updated[] = [
            'id'          => $sch['id'], 
            'status'      => 'Rejected', 
            'badge_class' => 'bg-danger'
        ];
    }
}

// Recalculate counts
$counts = $pdo->query("SELECT 
            COUNT(CASE WHEN status='Pending' THEN 1 END) AS pending,
            COUNT(CASE WHEN status='Approved' THEN 1 END) AS approved,
            COUNT(CASE WHEN status='Rejected' THEN 1 END) AS rejected
        FROM documents")->fetch(PDO::FETCH_ASSOC);

echo json_encode([
    'success' => true,
    'updated' => $updated,
    'counts'  => $counts
]);