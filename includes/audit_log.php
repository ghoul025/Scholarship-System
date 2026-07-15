<?php
require_once __DIR__ . '/../config.php';

function audit_log($user_id, $action, $details = null) {
    global $pdo;
    $ip = $_SERVER['REMOTE_ADDR'] ?? null;
    $sql = "INSERT INTO audit_logs (user_id, action, details, ip_address) VALUES (:user_id, :action, :details, :ip)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':user_id' => $user_id,
        ':action' => $action,
        ':details' => $details,
        ':ip' => $ip
    ]);
}
?>
