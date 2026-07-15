<?php
session_start();
require 'config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'scholar') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$user_id = $_SESSION['user_id'];
$stmt_scholar = $conn->prepare('SELECT id FROM scholars WHERE user_id = ?');
$stmt_scholar->execute([$user_id]);
$scholar = $stmt_scholar->fetch(PDO::FETCH_ASSOC);
if (!$scholar || !isset($scholar['id'])) {
    echo json_encode(['success' => false, 'message' => 'Scholar not found']);
    exit;
}
$scholar_id = $scholar['id'];


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
$data = [];
foreach ($reqs as $type) {
    $key = mb_strtolower($type);
    $doc = $uploaded[$key] ?? null;
    $data[$type] = [
        'file_path' => $doc['file_path'] ?? null,
        'status' => $doc['status'] ?? null,
        'uploaded' => $doc && $doc['status'] === 'Approved' && file_exists($doc['file_path'])
    ];
}
echo json_encode(['success' => true, 'documents' => $data]);
exit;

