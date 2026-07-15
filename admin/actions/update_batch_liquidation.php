<?php
session_start();
require '../../config.php';

// CSRF and admin check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('HTTP/1.1 403 Forbidden');
    exit;
}
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    header('HTTP/1.1 403 Forbidden');
    exit;
}

// Validate input
if (!isset($_POST['batch']) || !isset($_POST['scholarship_type']) || !isset($_POST['liquidated'])) {
    header('HTTP/1.1 400 Bad Request');
    exit;
}

$batch = $_POST['batch'];
$scholarship_type = $_POST['scholarship_type'];
$liquidated = $_POST['liquidated'] === '1' ? 1 : 0;

// Validate batch as decimal
if (!preg_match('/^\d+(\.\d{1,2})?$/', $batch)) {
    header('HTTP/1.1 400 Bad Request');
    exit;
}

// Validate scholarship type
$valid_types = ['TES', 'TDP', 'Others'];
if (!in_array($scholarship_type, $valid_types)) {
    header('HTTP/1.1 400 Bad Request');
    exit;
}

// Update liquidation status for all scholars in the batch
try {
    $stmt = $conn->prepare("UPDATE scholars SET liquidated = ? WHERE batch = ? AND scholarship_type = ?");
    $stmt->execute([$liquidated, $batch, $scholarship_type]);
    header('Content-Type: application/json');
    echo json_encode(['status' => 'success', 'liquidated' => $liquidated ? 'Yes' : 'No']);
} catch (Exception $e) {
    header('HTTP/1.1 500 Internal Server Error');
    exit;
}
?>