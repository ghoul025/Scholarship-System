<?php
require_once __DIR__ . '/../config.php';

function validate_credential_file($file) {
    // $file is from $_FILES['...']
    $allowedTypes = [
        'application/pdf',
        'image/jpeg',
        'image/png'
    ];
    $maxBytes = 5 * 1024 * 1024; // 5MB
    if ($file['error'] !== UPLOAD_ERR_OK) return 'Upload error';
    if ($file['size'] > $maxBytes) return 'File too large';
    if (!in_array($file['type'], $allowedTypes)) return 'Invalid file type';
    return true;
}

function store_credential_file($scholar_id, $type, $file) {
    // Validate
    $valid = validate_credential_file($file);
    if ($valid !== true) return ['ok' => false, 'error' => $valid];

    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $safeName = 'doc_' . $scholar_id . '_' . strtolower($type) . '_' . time() . '.' . $ext;
    $uploadDir = __DIR__ . '/../Uploads/credentials/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
    $dest = $uploadDir . $safeName;
    if (!move_uploaded_file($file['tmp_name'], $dest)) {
        return ['ok' => false, 'error' => 'Failed to move uploaded file'];
    }

    // Insert or update record in documents table
    global $pdo;
    if (!isset($pdo)) {
        require __DIR__ . '/../config.php';
    }
    $sql = "INSERT INTO documents (scholar_id, document_type, file_path, status, created_at) VALUES (:sid, :type, :path, 'Pending', NOW()) ON DUPLICATE KEY UPDATE file_path = VALUES(file_path), status = 'Pending', created_at = VALUES(created_at)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':sid' => $scholar_id, ':type' => $type, ':path' => 'Uploads/credentials/' . $safeName]);

    return ['ok' => true, 'path' => 'Uploads/credentials/' . $safeName];
}

function get_scholar_credentials($scholar_id) {
    global $pdo;
    if (!isset($pdo)) {
        require __DIR__ . '/../config.php';
    }
    $sql = "SELECT * FROM documents WHERE scholar_id = :sid";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':sid' => $scholar_id]);
    $rows = $stmt->fetchAll();
    $out = [];
    foreach ($rows as $r) {
        $key = mb_strtolower($r['document_type']);
        // Only count as complete if status is 'Approved'
        if (!isset($out[$key]) || $r['status'] === 'Approved') {
            $out[$key] = $r;
        }
    }
    return $out;
}

?>
