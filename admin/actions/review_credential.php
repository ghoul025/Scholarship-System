<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../includes/csrf.php';
require_once __DIR__ . '/../../includes/audit_log.php';
require_once __DIR__ . '/../../includes/sms_helper.php';

session_start();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit; }
$token = $_POST['csrf_token'] ?? '';
if (!csrf_check($token)) die('CSRF token mismatch');

$cred_id = intval($_POST['cred_id'] ?? 0);
$action = $_POST['action'] ?? '';
$notes = $_POST['notes'] ?? null;

if (!$cred_id || !$action) die('Missing data');

// Validate action
if (!in_array($action, ['approve','reject','request_reupload'])) die('Invalid action');

// Fetch credential
$stmt = $pdo->prepare('SELECT sc.*, s.phone_number, s.first_name, s.last_name FROM scholar_credentials sc JOIN scholars s ON sc.scholar_id = s.id WHERE sc.id = :id');
$stmt->execute([':id'=>$cred_id]);
$cred = $stmt->fetch();
if (!$cred) die('Credential not found');

// Determine new status
$newStatus = 'pending';
if ($action === 'approve') $newStatus = 'approved';
if ($action === 'reject') $newStatus = 'rejected';
if ($action === 'request_reupload') $newStatus = 'reupload_requested';

// Update record
$u = $pdo->prepare('UPDATE scholar_credentials SET status = :st, reviewed_at = NOW(), reviewer_id = :rid, notes = :notes WHERE id = :id');
$u->execute([':st'=>$newStatus, ':rid'=>$_SESSION['user_id'] ?? null, ':notes'=>$notes, ':id'=>$cred_id]);

// Audit
audit_log($_SESSION['user_id'] ?? null, 'review_credential', json_encode(['cred_id'=>$cred_id, 'action'=>$action, 'notes'=>$notes]));

// In-app notification
$msg = '';
if ($newStatus === 'approved') $msg = 'Your credential ('.$cred['type'].') has been approved.';
elseif ($newStatus === 'rejected') $msg = 'Your credential ('.$cred['type'].') was rejected.';
else $msg = 'Please re-upload your credential ('.$cred['type'].').' ;

$stmt = $pdo->prepare('INSERT INTO notifications (scholar_id, type, message, meta) VALUES (:sid, :type, :msg, :meta)');
$stmt->execute([':sid'=>$cred['scholar_id'], ':type'=>'credential_review', ':msg'=>$msg, ':meta'=>json_encode(['cred_id'=>$cred_id, 'status'=>$newStatus])]);

// Send SMS if phone_number available
if (!empty($cred['phone_number'])) {
    $smsMsg = $msg;
    if (!empty($notes)) $smsMsg .= ' Notes: '.$notes;
    // fire and forget
    send_sms($cred['phone_number'], $smsMsg);
}

$_SESSION['success'] = 'Action completed.';
header('Location: /admin/manage_scholars.php');
exit;

?>
