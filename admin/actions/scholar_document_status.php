<?php
session_start();
require '../../config.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}
header('Content-Type: application/json');
$scholar_id = isset($_GET['scholar_id']) ? intval($_GET['scholar_id']) : 0;
if (!$scholar_id) {
    echo json_encode(['error' => 'No scholar specified']);
    exit;
}
// Get all requirements

// Fetch requirements
$reqs = $conn->query('SELECT document_type FROM requirements')->fetchAll(PDO::FETCH_COLUMN);
// Fetch uploaded documents (case-insensitive, only approved)
$stmt = $conn->prepare('SELECT document_type, file_path, status FROM documents WHERE scholar_id = ?');
$stmt->execute([$scholar_id]);
$docs = $stmt->fetchAll(PDO::FETCH_ASSOC);
$uploaded = [];
foreach ($docs as $doc) {
    $key = mb_strtolower($doc['document_type']);
    // Only mark as uploaded if approved
    if (!isset($uploaded[$key]) || $doc['status'] === 'Approved') {
        $uploaded[$key] = [
            'file_path' => $doc['file_path'],
            'status' => $doc['status']
        ];
    }
}
$status = [];
foreach ($reqs as $type) {
    $key = mb_strtolower($type);
    $doc = $uploaded[$key] ?? null;
    $status[] = [
        'document_type' => $type,
        'uploaded' => $doc && $doc['status'] === 'Approved' && file_exists($doc['file_path']),
        'file_path' => $doc['file_path'] ?? null,
        'status' => $doc['status'] ?? null
    ];
}
echo json_encode(['status' => $status]);
