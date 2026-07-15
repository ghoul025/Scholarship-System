<?php
session_start();
require 'config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'scholar') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
    exit;
}

// CSRF check
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    echo json_encode(['success' => false, 'message' => 'Invalid CSRF token.']);
    exit;
}

$doc_type = $_POST['document_type'] ?? '';
if (empty($doc_type)) {
    echo json_encode(['success' => false, 'message' => 'No document type specified.']);
    exit;
}

$user_id = $_SESSION['user_id'];
$stmt_scholar = $conn->prepare('SELECT id FROM scholars WHERE user_id = ?');
$stmt_scholar->execute([$user_id]);
$scholar = $stmt_scholar->fetch(PDO::FETCH_ASSOC);
if (!$scholar || !isset($scholar['id'])) {
    echo json_encode(['success' => false, 'message' => 'Scholar not found.']);
    exit;
}
$scholar_id = $scholar['id'];

// Get file path
$stmt = $conn->prepare('SELECT file_path FROM documents WHERE scholar_id = ? AND document_type = ?');
$stmt->execute([$scholar_id, $doc_type]);
$file_path = $stmt->fetchColumn();

// Delete physical file if exists
if ($file_path && file_exists($file_path)) {
    @unlink($file_path);
}

// Instead of deleting, reset file_path and status to 'pending'
$stmt = $conn->prepare('UPDATE documents SET file_path = NULL, status = "pending" WHERE scholar_id = ? AND document_type = ?');
$stmt->execute([$scholar_id, $doc_type]);

echo json_encode(['success' => true, 'message' => 'Document unsubmitted and status reset to Pending.']);
exit;
