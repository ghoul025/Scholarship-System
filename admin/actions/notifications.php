<?php
session_start();
require '../../config.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}
header('Content-Type: application/json');
// Example: fetch latest 20 notifications (uploads, registrations)
$sql = "SELECT n.id, n.type, n.message, n.created_at, CONCAT_WS(' ', s.first_name, s.middle_name, s.last_name) AS full_name, u.username
        FROM notifications n
        LEFT JOIN scholars s ON n.scholar_id = s.id
        LEFT JOIN users u ON s.user_id = u.id
        ORDER BY n.created_at DESC
        LIMIT 20";
$stmt = $conn->query($sql);
$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode(['notifications' => $notifications]);
