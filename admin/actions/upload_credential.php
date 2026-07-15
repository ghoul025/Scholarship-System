<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../includes/csrf.php';
require_once __DIR__ . '/../../includes/credentials_helper.php';
require_once __DIR__ . '/../../includes/audit_log.php';

session_start();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); exit;
}

$token = $_POST['csrf_token'] ?? '';
if (!csrf_check($token)) {
    die('CSRF token mismatch');
}

$scholar_id = intval($_POST['scholar_id'] ?? 0);
$type = $_POST['type'] ?? '';
if (!$scholar_id || !$type || !isset($_FILES['file'])) {
    die('Missing parameters');
}

$res = store_credential_file($scholar_id, $type, $_FILES['file']);
if (!$res['ok']) {
    $_SESSION['error'] = $res['error'];
    header('Location: /admin/manage_scholars.php');
    exit;
}

// Audit
$user_id = $_SESSION['user_id'] ?? null;
audit_log($user_id, 'upload_credential', json_encode(['scholar_id'=>$scholar_id, 'type'=>$type, 'path'=>$res['path']]));

// Notification placeholder
$stmt = $pdo->prepare("INSERT INTO notifications (scholar_id, type, message, meta) VALUES (:sid, 'credential_uploaded', :msg, :meta)");
$stmt->execute([':sid'=>$scholar_id, ':msg'=>'Credential uploaded ('.$type.')', ':meta'=>json_encode(['path'=>$res['path']])]);

$_SESSION['success'] = 'File uploaded and queued for review.';
header('Location: /admin/manage_scholars.php');
exit;

?>
